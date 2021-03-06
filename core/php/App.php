<?php
declare(strict_types = 1);

namespace biwi\edit;

use Sabre\HTTP;
use Sabre\Event\EventEmitter;
use biwi\edit;

require_once 'Session.php';

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
    /**
     * @var Session
     */
    private $session;
    /**
     * @var SessionHandler
     */
    private $sessionHandler;

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
        $this->ignoreWarnings = false;

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

        // Eigener SessionHandler: Sessions werden in DB gespeichert
        $this->sessionHandler = new SessionHandler($this);

        // Session auslesen
        $this->session = null;

        session_start();
        if (\array_key_exists("biwi", $_SESSION) && $_SESSION["biwi"]) {
            $this->session = $_SESSION["biwi"];
        } else {
            $this->session = new \biwi\Session();
        }

        // Sprache herausfinden
        $this->languageId = $this->getLanguage();

        // Router
        $router = new Router\Router($this);
        $router = $router->handleRequest($this->request, $this->response);

        // Exit
        $this->biwiExit();
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


    /**
     * Gibt die languageId zurück
     *
     * @return string
     * @throws \Exception
     */
    private function getLanguage(): string {

        // Variablen
        $languageId = $this->request->getGet("lang");
        $path = $this->request->getGet("path");

        // Wenn path übergeben wurde (von mod_rewrite) languageId ermitteln
        if (!$languageId && $path) {

            // Pfad aufsplitten in die Bestandteile
            $pathElements = explode("/", $path);

            if (\array_key_exists(1, $pathElements) && $pathElements[1]) {
                $languageId = $pathElements[1];
            }
        }

        // Standardsprache ermitteln
        $defaultLanguageId = $this->getSetting("defaultLanguageId");

        // Erlaubte Sprachen ermitteln
        $sql = "
            SELECT
                `languageId`
            FROM
                `language`
            WHERE
                forGui = 1 AND active = 1
        ";

        $st = $this->getDb()->query($sql);
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
        unset($st);
        $allowedLanguageIds = [];
        foreach ($rows as $row) {
            $allowedLanguageIds[] = $row["languageId"];
        }
        unset($row);

        // Sprache aus User-Config in DB ermitteln
        if ($this->getLoggedInUserId()) {
            $st = $this->getDb()->prepare('SELECT languageId FROM user WHERE userId = :userId');
            $st->bindValue(':userId', $this->getLoggedInUserId(), \PDO::PARAM_STR);
            $st->execute();
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            if ($row && $row['languageId'] && \in_array($row['languageId'], $allowedLanguageIds, true)) {
                $languageId = $row['languageId'];
            }
            unset ($st, $row);
        }

        if ($languageId && \in_array($languageId, $allowedLanguageIds, true)) {

            // Wenn eine Sprache angefordert wurde, diese in der Session merken
            $this->getSession()->languageId = $languageId;

        } elseif ($this->getSession()->languageId && \in_array($this->getSession()->languageId, $allowedLanguageIds, true)) {

            // Wenn keine Sprache angefordert wurde: die Sprache aus der Session nehmen, wenn vorhanden
            $languageId = $this->getSession()->languageId;

        } else {

            // Wenn auch in der Session keine Sprache war: die Browsersprache nehmen und in der Session merken
            $languageId = $this->request->getBrowserLanguage($allowedLanguageIds, $defaultLanguageId);
            $this->getSession()->languageId = $languageId;
        }

        if (!$languageId || !\in_array($languageId, $allowedLanguageIds, true)) {

            // Wenn eine ungültige Sprache angefordert wurde: die Defaultsprache nehmen
            $languageId = $defaultLanguageId;
        }

        return $languageId;
    }


    /**
     * Gibt die Sprache des Browsers zurück
     * @return string
     */
    public function getLanguageId(): string {
        return $this->languageId;
    }


    /**
     * @return Modules
     */
    public function getModules(): Modules {
        return $this->modules;
    }

    /**
     * Gibt die CLient IP zurück
     *
     * @return string
     */
    public function getClientIp(): string {

        // check ip from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
          $ip=$_SERVER['HTTP_CLIENT_IP'];

          // to check ip is pass from proxy
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
          $ip=$_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }


    /**
     * @return Session
     */
    public function getSession() {
        return $this->session;
    }


    /**
     * Gibt den Wert einer Einstellung zurück
     *
     * @param $setting
     * @param bool $silent Keine Fehler werfen
     * @param bool $asLines Array mit Zeilen zurückgeben
     * @return string | array
     * @throws \Exception
     */
    public function getSetting(string $setting, bool $silent = false, bool $asLines = false) {
        $row = edit\setting\Setting::getSetting($this, $setting);

        if (!$silent) {
            if (!$row) {
                throw new \Exception("Das Setting $setting wurde nicht gefunden.");
            }
            if ($row["value"] === "") {
                throw new \Exception("Das Setting $setting enthält keinen Wert.");
            }
        }

        // Als einzelne Zeilen
        if ($asLines) {
            $lines = [];
            $tmpArray = preg_split("/\r|\n/", $row ? $row["value"] : "", 0, PREG_SPLIT_NO_EMPTY);
            if ($tmpArray) {
                foreach ($tmpArray as $el) {
                    if (trim($el) !== '') {
                        $lines[] = trim($el);
                    }
                }
            }
            unset($el);
            return $lines;
        }

        return $row ? $row["value"] : null;
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


    /**
     * Gibt den User Vor- und Nachname zurück
     * @param int $userId
     * @return string
     */
    public function getUserName(int $userId): string {
        $st = $this->getDb()->prepare('SELECT firstName, lastName FROM user WHERE userId = :userId');
        $st->bindParam(':userId', $userId);
        $st->execute();
        $row = $st->fetch();
        unset($st);

        return $row['firstName'] . ' ' . $row['lastName'];
    }


    /**
     * Gibt die UserId des eingeloggten Benutzers zurück
     *
     * @return string
     */
    public function getLoggedInUserId(): int {
        $userId = $this->getSession()->userId;
        if (!$userId || $userId === "guest") {
            return 0;
        }

        return $userId;
    }


    /**
     * Gibt die userRole des eingeloggten Benutzers zurück
     *
     * @return int
     */
    public function getLoggedInUserRole(): int {
        $userRole = $this->getSession()->userRole;
        if (!$userRole) {
            return 0;
        }

        return $userRole;
    }


    /**
     * Gibt die App-Version zurück
     * @return string
     */
    public function getVersion(): string {
        return $this->version;
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
     * @return bool
     */
    public function isIgnoreWarnings(): bool {
        return $this->ignoreWarnings;
    }


    /**
     * Gibt zurück, ob der Benutzer eingeloggt ist
     *
     * @return bool
     */
    public function isLoggedIn(): bool {
        return (bool)$this->getLoggedInUserId();
    }


    /**
     * Beendet den Request und flushed den Response an den Browser / CLI.
     *
     * @param string $msg           -> Msg für den User
     * @param bool $error           -> Gibt an ob es sich bei der msg um eine Fehlermeldung handelt
     * @param bool $deleteSession   -> Löscht die aktive Session
     */
    public function biwiExit(string $msg = "", bool $error = true, bool $deleteSession = false): void {

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


    /**
     * Wird von der kijs-Library verwendet.
     * Zeigt eine Warnung mit OK und Abbrechen-Button an.
     *
     * @param string $msg
     * @return null|\stdClass
     */
    public function showWarning(string $msg): ?\stdClass {
        if ($this->ignoreWarnings) {
            return null;
        }

        $return = new \stdClass();
        $return->warningMsg = $msg;
        return $return;
    }


    /**
     * Schreibt einen Eintrag in den Action Log
     * @param int $categoryId
     * @param int $entryId
     * @param string $action
     * @return void
     */
    public function writeToActionLog(int $categoryId, int $entryId, string $action): void {
        try {
            $formPacket= [];
            $formPacket['categoryId'] = $categoryId;
            $formPacket['entryId'] = $entryId;
            $formPacket['action'] = $action;

            $saveData = new SaveData($this, $this->getLoggedInUserId(), 'actionLog');
            $saveData->save($formPacket);
            unset($saveData);

        } catch (\Throwable $e) {}
    }


    /**
     * Schreibt einen Eintrag in den Event Log
     * @param string $table
     * @param string $command
     * @param array $formPacket
     * @return void
     * @throws \Throwable
     */
    public function writeToEventLog(string $table, string $command, ?array $formPacket = null): void {
        try {
            $st = $this->getDb()->prepare('
                INSERT INTO eventLog (clientIp, userId, client, targetTable, command, formPacket)
                VALUES (:clientIp, :userId, :client, :table, :command, :formPacket)
            ');

            $st->bindParam(':clientIp', $this->getClientIp(), \PDO::PARAM_STR);
            $st->bindParam(':userId', $this->getLoggedInUserId(), \PDO::PARAM_INT);
            $st->bindParam(':client', $_SERVER['HTTP_USER_AGENT'], \PDO::PARAM_STR);
            $st->bindParam(':table', $table, \PDO::PARAM_STR);
            $st->bindParam(':command', trim($command), \PDO::PARAM_STR);
            $st->bindParam(':formPacket', json_encode($formPacket), \PDO::PARAM_STR);

            $st->execute();
            unset($st);

        } catch (\Throwable $e) {
            throw $e;
        }
    }


    /**
     * Schreibt einen Eintrag in das Log File
     * @param int $code
     * @param string $table
     * @param int $entryId
     * @param string $action
     * @param string $msg
     * @param string $logName
     */
    public function writeToLogFile(int $code, string $table, int $entryId, string $action, string $msg, string $logName = 'action'): void {

        $path = ($this->getConfig('paths, log') ?: 'log') . DIRECTORY_SEPARATOR;
        $userName = $this->getUserName($this->getLoggedInUserId());

        $entry = date('d.m.Y H:i:s') . ' ' . $userName . ' ' . $code . ' ' . $table . ' ' . $entryId . ' ' . $action . ' ' . $msg;

        try {
            file_put_contents($path . $logName . '.log', $entry, FILE_APPEND);
        } catch (\Throwable $e) {}
    }
}
