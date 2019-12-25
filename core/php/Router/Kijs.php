<?php
declare(strict_types = 1);

namespace biwi\edit\Router;

use biwi\edit;

/**
 * Class Kijs
 *
 * @package ki\kgweb\ki\Router
 */
class Kijs implements RouterInterface {
    /**
     * @var ki\App
     */
    protected $app;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Kijs constructor.
     *
     * @param ki\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }


    /**
     * @param ki\RequestInterface $request
     * @param ki\ResponseInterface $response
     */
    public function handleRequest(edit\RequestInterface $request, edit\ResponseInterface $response): void {
        $responseData = null;
        $data = null;

        // Der Content-Type muss "application/json" und die Method POST sein,
        // damit Cross Site Attacken (Cross-Origin Resource Sharing [CORS]) verhindert werden.
        if ($request->getHeader("Content-Type") !== "application/json") {
            $this->app->kiExit("Invalid Content-Type.");
        }
        if (mb_strtoupper($request->getMethod()) !== "POST") {
            $this->app->kiExit("Invalid http request method: " . $request->getMethod());
        }

        // Request holen
        $dataJson = $request->getBodyAsString();
        if (!$dataJson) {
            $this->app->kiExit("Invalid request.");
        }

        $data = json_decode($dataJson);
        try {
            $this->app->handleJsonError();
        } catch (\Throwable $e) {
            $response->addHeader("Content-Type", "application/json");
            $this->app->kiExit($this->app->getExceptionHandler()->handleException($e, "json"));
        }

        // Funktion aufrufen
        if (\is_array($data)) {
            $responseData = [];
            foreach ($data as $d) {
                $responseData[] = $this->doRpc($d);
            }
        } else {
            $responseData = $this->doRpc($data);
        }

        // Response schreiben
        $response->addHeader("Content-Type", "application/json");
        $response->setBody(json_encode($responseData));
    }


    // -------------------------------------------------------------------
    // Protected Functions
    // -------------------------------------------------------------------

    /**
     * @param object $cdata
     * @return \stdClass
     */
    protected function doRpc(object $cdata): \stdClass {
        try {

            // Modul und Funktionsname ermitteln
            $moduleName = "";
            $functionName = "";
            if ($cdata->facadeFn) {
                $tmp = explode(".", $cdata->facadeFn);
                $moduleName = array_shift($tmp);
                $functionName = implode(".", $tmp);
            }
            if (!$moduleName || !$functionName) {
                throw new \Exception("Call to undefined module: " . $cdata->facadeFn);
            }

            // Modul ermitteln und laden
            $mod = $this->app->getModules()->getModule($moduleName);
            if (isset($mod)) {
                $className = $mod->getFacadeClassName();
                $class = new $className($this->app);
            } else {
                throw new \Exception("Call to undefined module: " . $cdata->facadeFn);
            }

            // Ist die Funktion vorhanden?
            if (!method_exists($class, $functionName)) {
                throw new \Exception("Call to undefined method: " . $functionName . " in module: " . $moduleName);
            }

            // Funktion ausführen
            $this->app->setIgnoreWarnings((bool)$cdata->ignoreWarnings);
            $params = [];
            if (isset($cdata->requestData) && \is_array($cdata->requestData)) {
                $params = $cdata->requestData;
            } elseif (isset($cdata->requestData)) {
                $params = [$cdata->requestData];
            }

            $response = new \stdClass();
            $response->tid = $cdata->tid;
            $response->facadeFn = $cdata->facadeFn;
            $response->responseData = \call_user_func_array([$class, $functionName], $params);

            // Prüfen, ob eingeloggt
            if (!$this->app->isLoggedIn()) {
                $response->isNotLoggedIn = true;
            }

            // Nachrichten anhängen
            if ($response->responseData instanceof ki\Rpc\ResponseBase) {
                $errorTitle = $this->app->getText('Fehler');
                $infoTitle = $this->app->getText('Info');
                $cornerMsgTitle = $this->app->getText('Info');
                $warningTitle = $this->app->getText('Warnung');

                $response->responseData->setDefaultTitle($errorTitle, $infoTitle, $cornerMsgTitle, $warningTitle);
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
                $return->errorMsg = $this->app->getExceptionHandler()->handleException(
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
            $return->errorMsg = nl2br($this->app->getExceptionHandler()->handleException($e, '', 'text'));
            return $return;
        }
    }

}

