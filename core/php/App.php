<?php
declare(strict_types = 1);

namespace biwi\edit;

use Sabre\HTTP;
use Sabre\Event\EventEmitter;
use biwi\edit;

class App {
    /**
     * @var string
     */
    private $version = '0.0.1';
    /**
     * @var array
     */
    private $config;
    /**
     * @var Db
     */
    private $db;
    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;
    /**
     * Wird von der kijs-Library verwendet
     *
     * @var bool
     */
    private $ignoreWarnings;
    /**
     * @var string
     */
    private $languageId;
    /**
     * @var Modules
     */
    private $modules;
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Request
     */
    private $request;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * App constructor.
     *
     * @param array $config
     */
    public function __construct(array $config) {
        $this->config = $config;

        // Events -> geeignet um Funktionen in ausschaltbaren Modulen event-abhängig auszuführen.
        // Ist das Modul ausgeschaltet, registriert es sich auch nicht für die Events.
        $this->event = new EventEmitter;

        // Exception-Handler initialisieren
        $this->exceptionHandler = new ExceptionHandler($this);

        // Module ermitteln
        $this->modules = new Modules($this, "App", $this->getConfig("module"));

        // Sabre HTTP Request
        $this->request = HTTP\Sapi::getRequest();

        // Erweitern durch eigene Klasse
        $this->request = new Request($this->request);

        // Sabre HTTP Response
        $this->response = new HTTP\Response(200);

        // Erweitern durch eigene Klasse
        $this->response = new Response($this->response);

        // DB öffnen
        try {
            $this->db = new Db(
                $this->getConfig("database, dsn"),
                $this->getConfig("database, user"),
                $this->getConfig("database, password"),
                [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $msg = $this->getExceptionHandler()->handleException($e, 'Die Verbindung zur Datenbank konnte nicht hergestellt werden.');
            die($msg . PHP_EOL);echo $msg;
        }

        // Session auslesen
        $this->session = null;
        session_start();
        if (\array_key_exists("biwi", $_SESSION) && ($_SESSION["biwi"] instanceof Session)) {
            $this->session = $_SESSION["biwi"];
        } else {
            $this->session = new Session();
        }

        // Router
        $router = new Router\Router($this);
        $router = $router->handleRequest($this->request, $this->response);

        // Exit
        $this->kiExit();
    }


    /**
     * Gibt das Konfigurations-Array zurück
     * Wird $elements übergeben (Bsp: "develop,use_minify") wird der Wert dieser
     * Konfiguration zurückgegeben, sonst wird das ganze Config-Array zurückgegeben.
     *
     * @param string|null $elements
     * @return array|mixed|string
     */
    public function getConfig(string $elements = null) {
        $cfg = $this->config;
        $elements = explode(",", $elements);
        foreach ($elements as $val) {
            $cfg = empty($cfg[trim($val)]) ? null : $cfg[trim($val)];
        }

        return $cfg;
    }


    /**
     * Gibt die App-Datenbank zurück
     *
     * @return Db|null
     */
    public function getDb(): ?Db {
        return $this->db;
    }


    /**
     * @return ExceptionHandler
     */
    public function getExceptionHandler(): ExceptionHandler {
        return $this->exceptionHandler;
    }


    public function getLanguage(string $language = null): string {
        return 'de';
    }


    /**
     * @return Modules
     */
    public function getModules(): Modules {
        return $this->modules;
    }


    /**
     * @return Session
     */
    public function getSession(): Session {
        return $this->session;
    }


    /**
     * Gibt den übersetzten Text zurück.
     *
     * @param string $key ->
     * @param string $variant
     * @param string|array|int|float $args
     * @param string $languageId
     * @return string
     */
    public function getText(string $key, string $variant='', $args = null, string $languageId = 'de'): string {
        return $key;//\KiLang::getText($key, $variant, $args, $languageId);
    }


    /**
     * Gibt die aktuelle URL der App zurück
     *
     * @return string
     */
    public function getUrl(): string {
        $url = \array_key_exists('HTTPS', $_SERVER) ? 'https://' : 'http://';
        $url .= $_SERVER['HTTP_HOST'];
        $mainDir = $this->getConfig('url, mainDir');
        if ($mainDir) {
            $url .= '/' . $mainDir;
        }

        // Falls die App in einem Ordner läuft, ist der korrekte Pfad mit slash am Ende.
        // Siehe https://httpd.apache.org/docs/2.4/mod/mod_dir.html#DirectorySlash
        if (mb_substr($url, -1) !== '/' && mb_substr($url, -4) !== '.php') {
            $url .= '/';
        }

        return $url;
    }


    public function getUserId(): int {
        return 1;
    }


    public function getUserName(int $userId): string {
        return 'Joel Kohler';
    }


    /**
     * Gibt die App-Version zurück
     * @return string
     */
    public function getVersion(): string {
        return $this->version;
    }


    /**
     * Gibt die UserId des eingeloggten Benutzers zurück
     * oder ein Leerstring falls guest
     *
     * @return string
     */
    public function getLoggedInUserId(): string {
        $userId = $this->getSession()->userId;
        if (!$userId || $userId === "guest") {
            return "";
        }

        return $userId;
    }


    /**
     * Gibt die aktuelle Benutzergruppe zurück.
     * 1 für Lieferant, 2 für Fachbereich und 3 für Admins und 0 für undefiniert (z.B. guest)
     *
     * @return int
     */
    public function getLoggedInUserType(): int {
//        if ($this->checkRights($this->getConfig('rights, adminFunction'))) {
//            return 3;
//        }
//        if ($this->checkRights($this->getConfig('rights, fachbereichFunction'))) {
//            return 2;
//        }
//        if ($this->getLoggedInLieferantId() && $this->checkRights($this->getConfig('rights, lieferantFunction'))) {
//            return 1;
//        }
//
//        $loggenInLieferantId = $this->getLoggedInLieferantId();
//        $hasRights = $this->checkRights($this->getConfig('rights, lieferantFunction'));

        return 3;
    }


    /**
     * Handels last json Exception
     *
     * @param string $action msg, log, exception
     * @return string
     * @throws \Exception
     */
    public function handleJsonError(string $action = "exception"): string {
        $lastErrorInt = json_last_error();

        if ($lastErrorInt > 0) {

            // Define the errors.
            $constants = get_defined_constants(true);
            $json_errors = [];
            foreach ($constants["json"] as $name => $value) {
                if (!strncmp($name, "JSON_ERROR_", 11)) {
                    $json_errors[$value] = $name;
                }
            }

            // Get message
            $msg = "";
            try {
                $msg = json_last_error_msg();
            } catch (\Exception $e) {}
            if (!empty($json_errors[$lastErrorInt])) {
                $msg .= "\n" . $json_errors[$lastErrorInt];
            }
            $msg = "JSON Fehler: " . ($msg ?: "Syntaxfehler, ungültiges JSON.");
            switch ($action) {

                case "msg":
                    return $msg;

                case "log":
                    edit\app\App::writeMsgToLog($this, $msg);
                    break;

                default:
                    throw new \Exception($msg);
            }

        }

        return "";
    }


    /**
     * Gibt zurück, ob der Benutzer eingeloggt ist
     *
     * @return bool
     */
    public function isLoggedIn(): bool {
        //return (bool)$this->getLoggedInUserId();
        return true;
    }



    /**
     * Beendet den Request und flushed den Response an den Browser / CLI.
     *
     * @param string $msg           -> Msg für den User
     * @param bool $error           -> Gibt an ob es sich bei der msg um eine Fehlermeldung handelt
     * @param bool $deleteSession   -> Löscht die aktive Session
     */
    public function kiExit(string $msg = "", bool $error = true, bool $deleteSession = false): void {

        // Session wieder schreiben
        $_SESSION["biwi"] = $this->session;

        // SessionHandler schliessen
        if (method_exists($this->sessionHandler, 'closeWrite')) {
            $this->sessionHandler->closeWrite();

            // Falls die Session von einem REST-Client ist, löschen
            if ($deleteSession) {
                $this->sessionHandler->destroy(session_id());
            }
        }

        if ($this->db instanceof Db) {

            // Rollback
            $this->db->rollBackIfTransaction();
        }

        // DB schliessen
        unset($this->db);

        // End script
        if ($msg) {

            // Msg an User senden
            if ($error && $this->response->getStatus() === 200) {
                $this->response->setStatus(400); // Bad request
            }
            $this->response->setBody($msg);
            HTTP\Sapi::sendResponse($this->response);
            if ($error) {
                exit(1);
            }
            exit(0);
        }

        // Response an Browser senden
        HTTP\Sapi::sendResponse($this->response);
        exit(0);
    }


    /**
     * @param bool $ignoreWarnings
     */
    public function setIgnoreWarnings(bool $ignoreWarnings): void {
        $this->ignoreWarnings = $ignoreWarnings;
    }

}
