<?php

//namespace ki\kgweb\ki;


/**
 * Class ExceptionHandler
 *
 * @package ki\kgweb\ki
 */
class ExceptionHandler {
    /**
     * @var App
     */
    private $app;
    /**
     * @var string
     */
    private $userId;


    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    public function __construct(App $app) {
        $this->app = $app;
        $this->userId = "guest";

        if ($this->app->getConfig("exceptionHandling, enableGlobalExceptionHandler")) {

            // Catch Exceptions
            set_exception_handler([$this, "handleGlobalException"]);

            // Catch errors triggered by trigger_error() and some system errors
            set_error_handler([$this, "handleGlobalError"]);
        }

        if ($this->app->getConfig("exceptionHandling, enableFatalExceptionHandler")) {

            // Catch fatal errors
            register_shutdown_function([$this, "handleFatalException"]);
        }
    }


    /**
     * Handels exceptions and errors manually caught in code
     *
     * @param \Throwable $e
     * @param string $customMsg
     * @param string $format html, text, json
     * @param null $showDetails -> Falls true werden Details angezeigt, auch wenn in der Config showDetails = false ist.
     * @return string
     */
    public function handleException(\Throwable $e, string $customMsg = "", string $format = "", $showDetails = null): string {

        // Variablen
        $errNo = (int)$e->getCode();
        $errStr = $e->getMessage();
        $errFile = $e->getFile();
        $errLine = $e->getLine();
        $errTrace = $e->getTraceAsString();
        $isSqlException = $e instanceof \PDOException;
        $xDebugMsg = $e->xdebug_message ?? '';
        $isWebServer = true; //@$this->app->isFromWebserver();
        $msg = "";

        // Format
        if (!$format) {
            if (!$isWebServer) {
                $format = 'text';
            } elseif (\array_key_exists('CONTENT_TYPE', $_SERVER) && strtolower($_SERVER['CONTENT_TYPE']) === 'application/json') {
                $format = 'json';
            } else {
                $format = 'html';
            }
        }

        if ($e instanceof ExceptionNotice) {

            // Titel
            $title = @$this->app->getText("Hinweis");
            if (!$title) {
                $title = "Hinweis";
            }

            // Eigene Exception
            switch ($format) {
                case 'text':
                    $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, true, $xDebugMsg, $showDetails);
                    break;
                case 'json':
                    $msg = $this->getExceptionAsJson($title, $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, true, $xDebugMsg, $showDetails);
                    break;
                default:
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, true, $xDebugMsg, $showDetails);
            }
        } else {

            // Titel
            $title = @$this->app->getText("Es ist ein Fehler aufgetreten");
            if (!$title) {
                $title = "Es ist ein Fehler aufgetreten";
            }

            // In Logdatei schreiben
            $this->writeToExceptionLogFile($errNo, $errStr, $errFile, $errLine);

            // Mail senden
            $this->sendMail($errNo, $errStr, $errFile, $errLine);

            // Msg holen
            switch ($format) {
                case 'text':
                    $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, false, $xDebugMsg, $showDetails);
                    break;
                case 'json':
                    $msg = $this->getExceptionAsJson($title, $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, false, $xDebugMsg, $showDetails);
                    break;
                default:
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, false, $xDebugMsg, $showDetails);
            }
        }

        if ($this->app->getConfig("develop, use_chromephp")) {

            // ChromePhp
            if ($e instanceof ExceptionNotice) {
                fb($this->getExceptionAsText("Hinweis", $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, true, $xDebugMsg));
            } else {
                fb($this->getExceptionAsText("Es ist ein Fehler aufgetreten", $errNo, $errStr, $errFile, $errLine, $errTrace, $customMsg, $isSqlException, false, $xDebugMsg));
            }
        }

        return $msg;
    }


    /**
     * Handels not catchable exceptions like out of memory
     */
    public function handleFatalException(): void {
        $e = error_get_last();
        if ($e !== null && ($e["type"] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR))) {
            $this->handleGlobalException(new \ErrorException($e["message"], 0, $e["type"], $e["file"], $e["line"]));
        }
    }


    /**
     * Handels exceptions not manually caught in code
     *
     * @param \Throwable $e
     */
    public function handleGlobalException(\Throwable $e): void {
        $errNo = (int)$e->getCode();
        $errStr = $e->getMessage();
        $errFile = $e->getFile();
        $errLine = $e->getLine();
        $errTrace = $e->getTraceAsString();
        $isSqlException = $e instanceof \PDOException;
        $xDebugMsg = $e->xdebug_message ?? '';
        $isWebServer = true;//@$this->app->isFromWebserver();
        $msg = "";

        if ($e instanceof ExceptionNotice) {

            // Titel
            $title = @$this->app->getText("Hinweis");
            if (!$title) {
                $title = "Hinweis";
            }

            // Eigene Exception
            if ($isWebServer) {
                $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, $errTrace, "", $isSqlException, true, $xDebugMsg);
            } else {
                $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, $errTrace, "", $isSqlException, true, $xDebugMsg);
            }
        } else {

            // In Logdatei schreiben
            $this->writeToExceptionLogFile($errNo, $errStr, $errFile, $errLine);

            // Mail senden
            $this->sendMail($errNo, $errStr, $errFile, $errLine);

            // Titel
            $title = @$this->app->getText("Es ist ein Fehler aufgetreten");
            if (!$title) {
                $title = "Es ist ein Fehler aufgetreten";
            }

            // Fehler anzeigen
            if ($isWebServer) {
                if (!empty($_SERVER["HTTP_ACCEPT"]) && strpos($_SERVER["HTTP_ACCEPT"], 'json') !== false) {
                    $msg = $this->getExceptionAsJson($title, $errNo, $errStr, $errFile, $errLine, $errTrace, "", $isSqlException, false, $xDebugMsg);
                } else {
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, $errTrace, "", $isSqlException, false, $xDebugMsg);
                }
            } else {
                $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, $errTrace, "", $isSqlException, false, $xDebugMsg);
            }
        }

        if ($this->app->getConfig("develop, use_chromephp"))  {

            // ChromePHP
            ChromePhp::exception($e);
            ChromePhp::groupCollapsed("Trace");
            foreach (explode("\n", $e->getTraceAsString()) as $line) {
                ChromePhp::log($line);
            }
            ChromePhp::groupEnd();
        }

        die($msg . PHP_EOL);
    }


    /**
     * Handels errors triggered by trigger_error() and some other not catchable system errors
     *
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @return bool
     * @throws \ErrorException
     */
    public function handleGlobalError(int $errNo, string $errStr, string $errFile, int $errLine): bool {

        // Titel
        $title = @$this->app->getText("Es ist ein Fehler aufgetreten");
        if (!$title) {
            $title = "Es ist ein Fehler aufgetreten";
        }

        // Falls mit trigger_error ausgelöst, zeigen wir den Fehler mit dem entsprechenden Hinweis an
        $msg = "";
        $isWebServer = true;//@$this->app->isFromWebserver();
        switch ($errNo) {
            case E_USER_ERROR:

                // Falls in Config so angegeben, Errors in Exceptions umwandeln.
                // Dies erlaubt das catchen innerhalb des Codes mit try. Errors selber werden oft nicht gecatched.
                if ($this->app->getConfig("exceptionHandling, convertErrors") && (error_reporting() & $errNo)) {
                    throw new \ErrorException($errStr, $errNo, E_ERROR, $errFile, $errLine);
                }

                // In Logdatei schreiben
                $this->writeToExceptionLogFile($errNo, $errStr, $errFile, $errLine, "User-Error");

                // Mail senden
                $this->sendMail($errNo, $errStr, $errFile, $errLine, "", "User-Error");

                if ($this->app->getConfig("develop, use_chromephp")) {

                    // ChromePhp
                    fb($this->getExceptionAsText("Es ist ein Fehler aufgetreten", $errNo, $errStr, $errFile, $errLine, "", "Fehler"));
                }

                // Fehler Level
                $level = @$this->app->getText("Fehler");
                if (!$level) {
                    $level = "Fehler";
                }

                // Fehler anzeigen
                if ($isWebServer) {
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                } else {
                    $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                }

                break;

            case E_USER_WARNING:

                if ($this->app->getConfig("develop, use_chromephp")) {

                    // ChromePhp
                    fb($this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, "", "Warnung"));
                }

                // Fehler Level
                $level = @$this->app->getText("Warnung");
                if (!$level) {
                    $level = "Warnung";
                }

                // Fehler anzeigen
                if ($isWebServer) {
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                } else {
                    $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                }
                break;

            case E_USER_DEPRECATED:

                if ($this->app->getConfig("develop, use_chromephp")) {

                    // ChromePhp
                    fb($this->getExceptionAsText("Es ist ein Fehler aufgetreten", $errNo, $errStr, $errFile, $errLine, "", "Veraltet"));
                }

                // Fehler Level
                $level = @$this->app->getText("Warnung");
                if (!$level) {
                    $level = "Warnung";
                }

                // Fehler anzeigen
                if ($isWebServer) {
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                } else {
                    $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                }
                break;

            case E_USER_NOTICE:

                if ($this->app->getConfig("develop, use_chromephp")) {

                    // ChromePhp
                    fb($this->getExceptionAsText("Es ist ein Fehler aufgetreten", $errNo, $errStr, $errFile, $errLine, "", "Hinweis"));
                }

                // Fehler Level
                $level = @$this->app->getText("Hinweis");
                if (!$level) {
                    $level = "Hinweis";
                }

                // Fehler anzeigen
                if ($isWebServer) {
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                } else {
                    $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine, "", $level);
                }
                break;

            default:

                if (!$this->app->getConfig("develop, error_reporting_console") || ($this->app->getConfig("develop, error_reporting_console") & $errNo)) {
                    if ($this->app->getConfig("develop, use_chromephp")) {

                        // ChromePhp
                        fb($this->getExceptionAsText("Fehler", $errNo, $errStr, $errFile, $errLine));
                    } elseif ($this->app->getConfig("develop, debug_mode")) {

                        // Falls Debug-Mode aber kein ChromePhp kann hier ein Breakpoint gesetzt werden um die Meldungen zu erhalten
                        $log = $this->getExceptionAsText("Fehler", $errNo, $errStr, $errFile, $errLine);
                        $setLogBreakPointHere = 1;
                    }
                }

                if (!(error_reporting() & $errNo)) {

                    // This error code is not included in error_reporting, so let it fall
                    // through to the standard PHP error handler
                    return false;
                }

                // Kommt z.B. vor wenn Log-File nicht schreibbar ist oder anderen Ausnahme-Fehler

                // Falls in Config so angegeben, Errors in Exceptions umwandeln.
                // Dies erlaubt das catchen innerhalb des Codes mit try. Errors selber werden oft nicht gecatched.
                if ($this->app->getConfig("exceptionHandling, convertErrors")) {
                    throw new \ErrorException($errStr, $errNo, E_ERROR, $errFile, $errLine);
                }

                // In Logdatei schreiben
                $this->writeToExceptionLogFile($errNo, $errStr, $errFile, $errLine, "Ausnahme-Fehler");

                // Fehler anzeigen
                if ($isWebServer) {
                    $msg = $this->getExceptionAsHtml($title, $errNo, $errStr, $errFile, $errLine);
                } else {
                    $msg = $this->getExceptionAsText($title, $errNo, $errStr, $errFile, $errLine);
                }
                break;
        }

        die($msg . PHP_EOL);

        // Don't execute PHP internal error handler. Stehen lassen als Reminder!
        return true;
    }


    /**
     * @param $userId
     */
    public function setUserId(string $userId): void {
        $this->userId = $userId;
    }


    /**
     * Write something in log file
     *
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @param string $customMsg
     */
    public function writeToExceptionLogFile(int $errNo, string $errStr, string $errFile, int $errLine, string $customMsg = ""): void {
        if (!$this->app->getConfig("exceptionHandling, logfile, enable")) {
            return;
        }

        $path = $this->app->getConfig("exceptionHandling, logfile, path");
        try {
            if ($path && !file_exists($path)) {
                file_put_contents($path, "");
            }
        } catch (\Throwable $e) {}
        if ($path && is_writable($path)) {
            $message = date("d.m.Y H:i:s") . " ";
            if ($customMsg) {
                $message .= "$customMsg ";
            }
            $message .= "User: {$this->userId} ";
            $message .= "Code: $errNo ";
            $message .= "File: $errFile ";
            $message .= "Row: $errLine ";
            $message .= $errStr . "\n";

            error_log($message, 3, $path);
        }
    }



    // -------------------------------------------------------------------
    // Private Functions
    // -------------------------------------------------------------------

    /**
     * Gibt die Fehlermeldung als HTML zurück
     *
     * @param string $title
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @param string $errTrace
     * @param string $customMsg
     * @param bool $isSqlException
     * @param bool $isExceptionNotice
     * @param string $xDebugMsg
     * @param bool|null $showDetails
     * @return string
     */
    private function getExceptionAsHtml(
        string $title,
        int $errNo,
        string $errStr,
        string $errFile,
        int $errLine,
        string $errTrace = '',
        string $customMsg = '',
        bool $isSqlException = false,
        bool $isExceptionNotice = false,
        string $xDebugMsg = '',
        bool $showDetails = null
    ): string {

        // App-Name
        $appName = @$this->app->getText("suissetec Kalkulation");
        if (!$appName) {
            $appName = "Es ist ein Fehler aufgetreten";
        }

        // Fehlermeldung allgemein
        $errorMsg = @$this->app->getText("Es ist ein Fehler aufgetreten");
        if (!$errorMsg) {
            $errorMsg = "Es ist ein Fehler aufgetreten";
        }

        // HTML
        $html = "
            <html>
            <head>
            <title>{$appName} - {$title}</title>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
            <style type='text/css'>
                body {
                    background-color: #f6f6f6 !important;
                }
                p {
                  font: 15px arial,tahoma,helvetica,sans-serif !important;
                }
                div.border1 {
                 margin: 20px !important;
                 padding: 4px !important;
                 background-color: #3f8eff !important;
                 border: 4px solid #24810F !important;
                 border-radius: 40px !important;
                }
                div.border2 {
                 margin: 0 !important;
                 padding: 15px 15px 15px 70px !important;
                 background: #ffffff !important;
                 border: 1px solid #e7e7e7 !important;
                 border-radius: 34px !important;
                 min-height: 200px !important;
                }
                img.logo {
                    opacity: 0.6 !important;
                    float: right !important;
                }
            </style>
            </head>
            <body>
            <div class='border1'>
            <div class='border2'>";

        if ($showDetails || $this->app->getConfig("exceptionHandling, showDetails")) {

            // Alle Details anzeigen
            if ($customMsg) {
                $html .= "<p>$customMsg</p>\n";
            }
            $html .= "<p>" . htmlentities($errStr, ENT_QUOTES) . "</p>\n";
            $html .= "<p>";
            $html .= "Code: $errNo<br />\n";
            $html .= "File: " . htmlentities($errFile, ENT_QUOTES) . "<br />\n";
            $html .= "Line: $errLine<br />\n";
            if (!$isExceptionNotice) {
                $html .= "&nbsp;<br />\n";
                if ($errTrace) {
                    $html .= "Trace:<br />\n";
                    $html .= nl2br(htmlentities($errTrace, ENT_QUOTES)) . "<br />\n";
                }
                $html .= "</p>\n";
                if ($xDebugMsg) {
                    $html .= "<table>$xDebugMsg</table><br /><br />";
                }
            }
            if ($isSqlException) {

                // Genauere Fehlermeldung. Insbesondere bei Fremdschlüsseln wichtig
                $detail = null;
                try {
                    $st = $this->app->getDb()->query("SHOW ENGINE INNODB STATUS");
                    $detail = $st->fetch(\PDO::FETCH_OBJ);
                } catch (\Throwable $err) {}
                if ($detail) {
                    preg_match('/-{24}\nLATEST FOREIGN KEY ERROR\n-{24}.*?(?=--)/s', $detail->Status, $matches);
                    if ($matches) {
                        $html .= "<br />\n" . nl2br(htmlentities($matches[0], ENT_QUOTES)) . "<br />\n";
                    }
                }
            }
        } else if ($this->app->getConfig("exceptionHandling, showGeneralErrorMsg")) {

            // Nur allgemeine Meldung anzeigen
            $html .= "<p>$errorMsg</p>\n";

        } else {

            // Fehlermeldung Datenbank
            $errorMsgDb = @$this->app->getText("Datenbank Fehler");
            if (!$errorMsgDb) {
                $errorMsgDb = "Datenbank Fehler";
            }

            // Nur Fehler ohne Details anzeigen
            $html .= "<p>$errorMsg:</p>\n";
            if ($customMsg) {
                $html .= "<p>$customMsg</p>\n";
            }
            if ($isSqlException) {
                if ($this->app->getConfig("exceptionHandling, showSqlExceptions")) {
                    $html .= "<p>" . htmlentities($this->getBetterSqlMessage($errNo, $errStr), ENT_QUOTES) . "</p>\n";
                } else {
                    $html .= "<p>$errorMsgDb</p>\n";
                }
            }
        }
        $html .= "<img class='logo' src='../ressources/images/logo.svg' alt='Logo' width='80' height='80'>";
        $html .= "<div style='clear: right;'></div>";
        $html .= "</div></div>";
        $html .= "</body></html>";

        return $html;
    }


    /**
     * Gibt die Fehlermeldung als JSON zurück
     *
     * @param string $title
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @param string $errTrace
     * @param string $customMsg
     * @param bool $isSqlException
     * @param bool $isExceptionNotice
     * @param string $xDebugMsg
     * @param bool|null $showDetails
     * @return string
     */
    private function getExceptionAsJson (
        string $title,
        int $errNo,
        string $errStr,
        string $errFile,
        int $errLine,
        string $errTrace = '',
        string $customMsg = '',
        bool $isSqlException = false,
        bool $isExceptionNotice = false,
        string $xDebugMsg = '',
        bool $showDetails = null
    ): string {

        // Fehlermeldung allgemein
        $errorMsg = @$this->app->getText("Es ist ein Fehler aufgetreten");
        if (!$errorMsg) {
            $errorMsg = "Es ist ein Fehler aufgetreten";
        }

        // Json
        $json = "{";
        $json .= "\"status\":\"error\",\n";
        $json .= "\"title\":\"$title\",\n";

        if ($showDetails || $this->app->getConfig("exceptionHandling, showDetails")) {

            // Alle Details anzeigen
            if ($customMsg) {
                $json .= "\"customMsg\":\"$customMsg\",\n";
            }
            $json .= "\"msg\":\"$errStr\",\n";
            $json .= "\"code\":\"$errNo\",\n";
            $json .= "\"file\":\"$errFile\",\n";
            $json .= "\"line\":\"$errLine\",\n";
//            if (!$isExceptionNotice && $errTrace) {
//                $json .= "\"Trace\":\"" . preg_replace("/\\r\\n|\\n|\\r/",' ', $errTrace) ."\",\n";
//            }
            // Hier könnte noch die genauere Fehlermeldung eingebaut werden, wie bei Text und HTML
        } else if ($this->app->getConfig("exceptionHandling, showGeneralErrorMsg")) {

            // Nur allgemeine Meldung anzeigen
            $json .= "\"msg\":\"{$errorMsg}\",\n";

        } else {

            // Fehlermeldung Datenbank
            $errorMsgDb = @$this->app->getText("Datenbank Fehler");
            if (!$errorMsgDb) {
                $errorMsgDb = "Datenbank Fehler";
            }

            // Nur Fehler ohne Details anzeigen
            if ($customMsg) {
                $json .= "\"customMsg\":\"$customMsg\",\n";
            }
            if ($isSqlException) {
                if ($this->app->getConfig("exceptionHandling, showSqlExceptions")) {
                    $json .= "\"msg\":\"{$this->getBetterSqlMessage($errNo, $errStr)}\",\n";
                } else {
                    $json .= "\"msg\":\"{$errorMsgDb}\",\n";
                }
            }
        }

        // Weitere Eigenschaften für Kompatibilität mit REST API
        $json .= "\"results\":\"[]\",\n";
        $json .= "\"errorsForUser\":\"[]\",\n";

        // Komma und Whitespaces am Schluss löschen
        $json = rtrim($json, " \x0B\0\r\n\t\,");

        // Abschliessen
        $json .= "\n}";

        return $json;
    }


    /**
     * Gibt die Fehlermeldung als Text zurück
     *
     * @param string $title
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @param string $errTrace
     * @param string $customMsg
     * @param bool $isSqlException
     * @param bool $isExceptionNotice
     * @param string $xDebugMsg
     * @param bool|null $showDetails
     * @return string
     */
    private function getExceptionAsText (
        string $title,
        int $errNo,
        string $errStr,
        string $errFile,
        int $errLine,
        string $errTrace = '',
        string $customMsg = '',
        bool $isSqlException = false,
        bool $isExceptionNotice = false,
        string $xDebugMsg = '',
        bool $showDetails = null
    ): string {

        // Fehlermeldung allgemein
        $errorMsg = @$this->app->getText("Es ist ein Fehler aufgetreten");
        if (!$errorMsg) {
            $errorMsg = "Es ist ein Fehler aufgetreten";
        }

        // Text
        $text = "$title\n";
        if ($showDetails || $this->app->getConfig("exceptionHandling, showDetails")) {

            // Alle Details anzeigen
            if ($customMsg) {
                $text .= "$customMsg\n";
            }
            $text .= "$errStr\n";
            $text .= "Code: $errNo\n";
            $text .= "File: $errFile\n";
            $text .= "Line: $errLine\n";
            $text .= "\n";
            if (!$isExceptionNotice && $errTrace) {
                $text .= "Trace:\n";
                $text .= "$errTrace\n";
            }
            $text .= "\n";

            if ($isSqlException) {

                // Genauere Fehlermeldung. Insbesondere bei Fremdschlüsseln wichtig
                $detail = null;
                try {
                    $st = $this->app->getDb()->query("SHOW ENGINE INNODB STATUS");
                    $detail = $st->fetch(\PDO::FETCH_OBJ);
                } catch (\Throwable $err) {
                    $debug = true;
                }
                if ($detail) {
                    preg_match('/-{24}\nLATEST FOREIGN KEY ERROR\n-{24}.*?(?=--)/s', $detail->Status, $matches);
                    if ($matches) {
                        $text .= "\n" . $matches[0];
                    }
                }
            }
//            if ($xDebugMsg) $text .= "$xDebugMsg\n";
        } else if ($this->app->getConfig("exceptionHandling, showGeneralErrorMsg")) {

            // Nur allgemeine Meldung anzeigen
            $text .= "$errorMsg\n";

        } else {

            // Fehlermeldung Datenbank
            $errorMsgDb = @$this->app->getText("Datenbank Fehler");
            if (!$errorMsgDb) {
                $errorMsgDb = "Datenbank Fehler";
            }

            // Nur Fehler ohne Details anzeigen
            if ($customMsg) {
                $text .= "$customMsg\n";
            }
            if ($isSqlException) {
                if ($this->app->getConfig("exceptionHandling, showSqlExceptions")) {
                    $text .= $this->getBetterSqlMessage($errNo, $errStr) . "\n";
                } else {
                    $text .= "$errorMsgDb\n";
                }
            }
        }

        return $text;
    }


    /**
     * Versendet eine Mail gemäss Angaben config
     *
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @param string $errTrace
     * @param string $customMsg
     */
    private function sendMail(
        int $errNo,
        string $errStr,
        string $errFile,
        int $errLine,
        string $errTrace = "",
        string $customMsg = ""
    ): void {
        if (!$this->app->getConfig("exceptionHandling, sendMail, enable")) {
            return;
        }
        $to = $this->app->getConfig("exceptionHandling, sendMail, to");
        $from = $this->app->getConfig("exceptionHandling, sendMail, from");
        $subject = $this->app->getConfig("exceptionHandling, sendMail, subject");

        if ($to && $from && $subject) {
            $message = "";
            $message .= "Es ist ein Fehler aufgetreten:\n";
            if ($customMsg) {
                $message .= "$customMsg\n";
            }
            $message .= "Date: " . date("d.m.Y H:i:s") . "\n";
            $message .= "User: " . $this->userId . "\n";
            $message .= "\n";
            $message .= "$errStr\n";
            $message .= "\n";
            $message .= "Code: $errNo\n";
            $message .= "File: $errFile\n";
            $message .= "Line: $errLine\n";
            $message .= "\n";
            if ($errTrace) {
                $message .= "Trace:\n";
                $message .= "$errTrace\n";
            }

            $headers = "From: " . $from . "\n";
            $parameters = "-f" . $from;

            $subject = utf8_decode($subject);
            $message = utf8_decode($message);

            mail($to, $subject, $message, $headers, $parameters);
        }
    }


    /**
     * Ersetzt englische SQL Meldungen durch deutsche Meldungen
     *
     * @param int $errNo
     * @param string $errStr
     * @return string
     */
    private function getBetterSqlMessage(int $errNo, string $errStr): string {
        $matches = [];
        switch ($errNo) {
            case 23000:
                if (preg_match("/1062 Duplicate entry '([^']+)' for key '([^']+)'/", $errStr, $matches)) {
                    if ($matches[2] === 'PRIMARY') {
                        return 'Der Datensatz \'' . $matches[1] . '\' ist bereits vorhanden.';
                    }

                    return 'Der Datensatz \'' . $matches[1] . '\' ist im Feld \'' . $matches[2] . '\' bereits vorhanden.';
                }
                if (preg_match("/1048 Column \'([^\']+)\' cannot be null/", $errStr, $matches)) {
                    return 'Der Datensatz \'' . $matches[1] . '\' darf nicht leer sein.';
                }
        }

        return $errStr;
    }
}

