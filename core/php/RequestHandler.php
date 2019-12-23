<?php

require_once '../../config/config.php';
require_once './autoload.php';

// App-Instanz erstelllen
$app = new App($biwi_config);

// Request abfragen
$request = json_decode(file_get_contents("php://input"));

$responseData = null;
$data = null;

// Der Content-Type muss "application/json" und die Method POST sein,
// damit Cross Site Attacken (Cross-Origin Resource Sharing [CORS]) verhindert werden.
//if ($request->getHeader("Content-Type") !== "application/json") {
//    $this->app->kiExit("Invalid Content-Type.");
//}
//if (mb_strtoupper($request->getMethod()) !== "POST") {
//    $this->app->kiExit("Invalid http request method: " . $request->getMethod());
//}

// Request holen
//$dataJson = $request->getBodyAsString();
//if (!$dataJson) {
//    $this->app->kiExit("Invalid request.");
//}

//$data = json_decode($dataJson);
//try {
//    $this->app->handleJsonError();
//} catch (\Throwable $e) {
//    $response->addHeader("Content-Type", "application/json");
//    $this->app->kiExit($this->app->getExceptionHandler()->handleException($e, "json"));
//}

// Funktion aufrufen
if (\is_array($request)) {
    $responseData = [];
    foreach ($request as $d) {
        $responseData[] = doRpc($app, $d);
    }
} else {
    $responseData = doRpc($app, $request);
}

// Response schreiben
//$response->addHeader("Content-Type", "application/json");
//$response->setBody(json_encode($responseData));

// Rückgabewert von Funktion an JS zurückgeben
echo json_encode($responseData);


/**
     * @param object $cdata
     * @return \stdClass
     */
function doRpc(\App $app, object $cdata): \stdClass {
    try {

        // Modul und Funktionsname ermitteln
        $moduleName = "";
        $functionName = "";
        if ($cdata->facadeFn) {
            $tmp = explode(".", $cdata->facadeFn);
            $className = array_shift($tmp);
            $functionName = implode(".", $tmp);
        }
        if (!$className || !$functionName) {
            throw new \Exception("Call to undefined Class: " . $cdata->facadeFn);
        }

        // Modul ermitteln und laden
//        $mod = $this->app->getModules()->getModule($moduleName);
//        if (isset($mod)) {
//            $className = $mod->getFacadeClassName();
//            $class = new $className($this->app);
//        } else {
//            throw new \Exception("Call to undefined module: " . $cdata->facadeFn);
//        }

        // Ist die Funktion vorhanden?
        if (!method_exists($className, $functionName)) {
            throw new \Exception("Call to undefined method: " . $functionName . " in module: " . $className);
        }

        // Funktion ausführen
        $app->setIgnoreWarnings((bool)$cdata->ignoreWarnings);
        $params = [];
        if (isset($cdata->requestData) && \is_array($cdata->requestData)) {
            $params = $cdata->requestData;
        } else if (isset($cdata->requestData)) {
            $params = [$cdata->requestData];
        }

        $response = new \stdClass();
        $response->tid = $cdata->tid;
        $response->facadeFn = $cdata->facadeFn;
        $response->responseData = \call_user_func_array([$className, $functionName], $params);

        // Prüfen, ob eingeloggt
        if (!$app->isLoggedIn()) {
            $response->isNotLoggedIn = true;
        }

        // Nachrichten anhängen
        if ($response->responseData instanceof ki\Rpc\ResponseBase) {
            foreach ($response->responseData->getMessages() as $key => $value) {
                $response->{$key} = $value;
            }
        }

        return $response;

    } catch (ki\Rpc\Warning $w) {

        $return = new \stdClass();
        $return->tid = $cdata->tid;
        $return->facadeFn = $cdata->facadeFn;
        $return->responseData = null;

        if ($cdata->ignoreWarnings === true) {

            // falls ignoreWarning auf true ist, geben wir einen Fehler aus.
            $return->errorMsg = $app->getExceptionHandler()->handleException(
                new \Exception('Es wurde eine Warning geworfen, obwohl "ignoreWarnings" gesetzt ist. (' . $w->getFile() . ':' . $w->getLine() . ')', 0, $w),
                '',
                'html'
            );

        } else {

            // Warnungen anzeigen
            $return->warningMsg = ['msg' => $w->getMessage(), 'title' => $w->getTitle()];
        }

        return $return;

    } catch (ki\ExceptionNotice $e) {

        // Fehlerauswertung und Rückgabe
        $return = new \stdClass();
        $return->tid = $cdata->tid;
        $return->facadeFn = $cdata->facadeFn;
        $return->responseData = null;
        $return->errorMsg = nl2br($e->getMessage());
        return $return;

    } catch (\Throwable $e) {

        // Fehlerauswertung und Rückgabe
        $return = new \stdClass();
        $return->tid = $cdata->tid;
        $return->facadeFn = $cdata->facadeFn;
        $return->responseData = null;
        $return->errorMsg = nl2br($app->getExceptionHandler()->handleException($e, '', 'text'));
        return $return;
    }
}