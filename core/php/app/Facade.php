<?php
declare(strict_types = 1);

namespace biwi\edit\app;

use biwi\edit;

/**
 * Class Facade
 *
 * @package ki\kgweb\kg\app
 */
class Facade {
    /**
     * @var ki\App
     */
    protected $app;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Facade constructor.
     *
     * @param ki\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;

        // Überprüfen ob der Ordner für die Übersetzungen in der Config angegeben wurde
        if (!$this->app->getConfig('paths, translationFolder')) {
            //throw new edit\ExceptionNotice($this->app->getText("Es wurde kein Ordner für die Sprachdateien angegeben."));
        }
    }


    /**
     * Gibt den HTML-Code für den About-Dialog zurück
     *
     * @param $jsInfo
     * @return edit\RpcResponseDefault
     */
    public function getAboutHtml($jsInfo): edit\RpcResponseDefault {

        // Rechte überprüfen
        if (!$this->app->checkRights()) {

            // Fehlermeldung
            throw new ki\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $kgWebVersion = $this->app->getVersion();
        $phpVersion = PHP_VERSION;
        $server = PHP_OS;
        $currentGuests = $this->app->getSessionHandler()->getUserCount('guest');
        $currentUsers = \count($this->app->getSessionHandler()->getCurrentUsers());
        $gc_maxLifetime = ini_get('session.gc_maxlifetime');
        $gc_probability = ini_get('session.gc_probability');
        $gc_divisor = ini_get('session.gc_divisor');
        $copyrightYear = '2018-' . date('Y');

        // mySql-Version ermitteln
        $st = $this->app->getDb()->query('SELECT version() As ve');
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        $st = null;
        $mySqlVersion = $row['ve'];

        // Webserver Version ermitteln
        $arr = explode('/', $_SERVER['SERVER_SOFTWARE']);
        $webserver = \array_key_exists(0, $arr) ? str_replace(' ', ' ', $arr[0]) : '';
        $arr = \array_key_exists(1, $arr) ? explode(' ', $arr[1]) : [];
        $arr = \array_slice($arr, 0, -1);
        $webserver .= ' ' . implode(' ', $arr);

        $html = "<div style=\"width:100%;\">";
        $html .= "<img src=\"core/ressources/img/suissetec.svg\" style=\"display:block; margin:10px auto 0; border:none;\" alt=\"Kipfer Informatik\" />";
        $html .= "<p>&nbsp;</p>";
        $html .= "<table class=\"about\">";

        $html .= "<tr><td colspan=\"2\"><b>{$this->app->getText("Versionen")}</b></td></tr>";
        $html .= "<tr><td>kgweb:</td><td>$kgWebVersion</td></tr>";
        $html .= "<tr><td><a href=\"http://www.php.net\" target=\"_blank\">PHP</a>:</td><td>$phpVersion</td></tr>";
        $html .= "<tr><td><a href=\"http://www.mysql.com\" target=\"_blank\">MySQL</a>:</td><td>$mySqlVersion</td></tr>";
        $html .= "<tr><td>{$this->app->getText("Webserver")}:</td><td>$webserver</td></tr>";
        $html .= "<tr><td>{$this->app->getText("Server")}:</td><td>$server</td></tr>";
        $html .= "<tr><td>kijs:</td><td>{$jsInfo->kijs}</td></tr>";
        $html .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";

        $html .= "<tr><td colspan=\"2\"><b>{$this->app->getText("Aktueller Benutzer")}</b></td></tr>";
        $html .= "<tr><td>{$this->app->getText("Benutzer")}:</td><td>{$this->app->getSession()->userId}</td></tr>";
        $html .= "<tr><td>{$this->app->getText("Browser")}:</td><td>" . htmlspecialchars($jsInfo->browser) . "</td></tr>";
        $html .= "<tr><td>{$this->app->getText("System")}:</td><td>" . htmlspecialchars($jsInfo->os) . "</td></tr>";
        $html .= "<tr><td>{$this->app->getText("Bildschirm")}:</td><td>" . htmlspecialchars($jsInfo->screen) . "</td></tr>";
        $html .= "<tr><td>{$this->app->getText("Browsersprache")}:</td><td>" . htmlspecialchars($jsInfo->language) . "</td></tr>";

        $html .= "<tr><td colspan=\"2\">&nbsp;</td></tr>";

        $html .= "<tr><td colspan=\"2\"><b>Session</b></td></tr>";
        $html .= "<tr><td style=\"padding-right:10px;\">{$this->app->getText("Anzahl Gäste")}:</td><td>$currentGuests</td></tr>";
        $html .= "<tr><td>{$this->app->getText("Anzahl Benutzer")}:</td><td>$currentUsers</td></tr>";
        $html .= "<tr><td>Lifetime:</td><td>". round($gc_maxLifetime/3600,1) ."h</td></tr>";
        $html .= "<tr><td style=\"padding-right:10px;white-space:nowrap;\">Garbage Collector:</td><td>{$gc_probability}/{$gc_divisor}</td></tr>";

        $html .= "</table>";
        $html .= "<p>&nbsp;</p>";
        $html .= "<p>&copy; {$copyrightYear} <a href=\"http://www.kipferinformatik.ch\" target=\"_blank\">Kipfer Informatik</a></p>";
        $html .= "</div>";

        $return = new edit\RpcResponseDefault();
        $return->html = $html;
        return $return;

    }


    /**
     * Gibt die Standardwerte für den Login-Dialog zurück
     *
     * @return edit\RpcResponseDefault
     */
    public function getLoginDefaults(): edit\RpcResponseDefault {
        $useCaptcha = (bool)$this->app->getSession()->useCaptcha;
        $captchaText = null;
        $png = null;

        if ($useCaptcha) {
            list($captchaText, $png) = App::createCaptchaPng();
        }
        $this->app->getSession()->captcha = $captchaText;

        $return = new edit\RpcResponseDefault();
        $return->captcha = $useCaptcha;
        $return->captchaImg = $png ? 'data:image/png;base64,' . base64_encode($png) : null;
        return $return;
    }


    /**
     * Login
     *
     * @param \stdClass $formPacket
     * @return edit\RpcResponseDefault
     */
    public function login(\stdClass $formPacket): edit\RpcResponseDefault {

        // Variablen
        $userId = (string)$formPacket->formData->userId;
        $password = (string)$formPacket->formData->password;
        $authToken = (string)$formPacket->formData->authToken;
        $useCaptcha = (bool)$this->app->getSession()->useCaptcha;
        $captcha = isset($formPacket->formData->captcha) ? (string)$formPacket->formData->captcha : null;
        $autoLogin = isset($formPacket->formData->autoLogin) ? (bool)$formPacket->formData->autoLogin : false;
        $msg = '';
        $success = false;
        $reload = false;

        // Fehlermeldung
        $errMsg = $this->app->getText("Ungültiger Benutzername und/oder Passwort");
        if ($useCaptcha) {
            $errMsg = $this->app->getText("Ungültiger Benutzername, Passwort und/oder Sicherheitscode.");
        }

        // Captcha prüfen
        if ($useCaptcha && $this->app->getSession()->captcha !== $captcha) {
            $msg = $errMsg;
        }

        if (!$msg) {

            // Login prüfen
            $res = App::login($this->app, $userId, $password, $authToken, $autoLogin);
            if ($res === 1) {
                $success = true;
                $this->app->getSession()->useCaptcha = false;
            } elseif ($res === 0) {
                $msg = $errMsg;
            } elseif ($res === -1) {
                $msg = $this->app->getText("Login nicht mehr möglich. Die Seite wird neu geladen.");
                $reload = true;
            }
        }

        // Response
        $return = new edit\RpcResponseDefault();
        $return->success = $success;
        $return->msg = $msg;
        $return->reload = $reload;
        $return->loggedInUserId = $this->app->getLoggedInUserId();
        $return->loggedInuserRole = $this->app->getLoggedInUserRole();
        $return->loggedInLieferantId = $this->app->getLoggedInLieferantId();

        // Sprache von user-config
        $userCnf = kg\user\User::getUserConfig($this->app);
        $return->guiLanguageId = empty($userCnf['languageId']) ? $this->app->getLanguageId() : $userCnf['languageId'];

        return $return;
    }


    /**
     * Gibt den navi-Baum zurück.
     */
    public function getNaviTree(): edit\Rpc\ResponseDefault {

        $elements = [];

        $btn = new \stdClass();
        $btn->caption = $this->app->getText('Dashboard');
        $btn->name = 'biwi_dashboard_Dashboard';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = $this->app->getText('Personen');
        $btn->name = 'biwi_person_Overview';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = $this->app->getText('Tiere');
        $btn->name = 'biwi_animal_Overview';
        $elements[] = $btn;

        // ****************************
        // Personen
        // ****************************


//            $personen = new \stdClass();
//            $personen->caption = $this->app->getText('Personen');
//            $personen->name = 'personen';
//            $personen->elements = [];
//            $elements[] = $personen;
//
//            $btn = new \stdClass();
//            $btn->caption = $this->app->getText('Person');
//            $btn->name = 'biwi_person_Person';
//            $personen->elements[] = $btn;
//
//            $btn = new \stdClass();
//            $btn->caption = $this->app->getText('Übersicht');
//            $btn->name = 'biwi_person_Overview';
//            $personen->elements[] = $btn;


        // ****************************
        // Administration
        // ****************************

        if ($this->app->getLoggedInUserRole() >= 50) {
            $administration = new \stdClass();
            $administration->caption = $this->app->getText('Administration');
            $administration->name = 'administration';
            $administration->elements = [];
            $elements[] = $administration;

            $btn = new \stdClass();
            $btn->caption = $this->app->getText('Beziehungen');
            $btn->name = 'biwi_relationship_Relationship';
            $administration->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = $this->app->getText('Mitteilungen');
            $btn->name = 'biwi_message_Message';
            $administration->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = $this->app->getText('Benutzer');
            $btn->name = 'biwi_user_User';
            $administration->elements[] = $btn;
        }

        // Response
        $return = new edit\Rpc\ResponseDefault();
        $return->elements = $elements;
        return $return;
    }



    /**
     * Gibt alle übersetzten Texte zurück
     *
     * @param string $languageId
     * @return edit\RpcResponseDefault
     */
    public function getTexts(string $languageId): edit\Rpc\ResponseDefault {
        global $translations;
        $texts = [];

//        $filepath = $this->app->getConfig('paths, translationFolder') . DIRECTORY_SEPARATOR;
//
//        if (!$translations || !array_key_exists($languageId, $translations)){
//            if (file_exists($filepath . $languageId . '.php')) {
//                require_once $filepath . DIRECTORY_SEPARATOR . $languageId . '.php';
//            }
//        }
//
//        if (\array_key_exists($languageId, $translations)) {
//            $texts = $translations[$languageId];
//        }

        $return = new edit\Rpc\ResponseDefault();
        $return->texts = $texts;
        return $return;
    }


    /**
     * Logout
     * @return edit\RpcResponseDefault
     */
    public function logout(): edit\Rpc\ResponseDefault {
        session_destroy();
        return new edit\Rpc\ResponseDefault();
    }


    /**
     * Speichert eine Javascript-Fehlermeldung ins Error-Log.
     * @param \stdClass $error
     * @return void
     */
    public function jsErrorLog(\stdClass $error): void {

        // Loggen?
        if ($this->app->getConfig('exceptionHandling,logJavascriptErrors') !== true) {
            return;
        }

        $log = '';
        $log .= 'Date  : ' . \date('d.m.Y H:i:s'). "\n";
        $log .= 'User  : ' . $this->app->getSession()->userId . "\n";

        if ($error->message) {
            $log .= 'Msg   : ' . $error->message . "\n";
        }
        if ($error->filename) {
            $log .= 'File  : ' . $error->filename . "\n";
        }
        if ($error->lineNumber) {
            $log .= 'Line  : ' . $error->lineNumber . "\n";
        }
        if ($error->columnNumber) {
            $log .= 'Column: ' . $error->columnNumber . "\n";
        }
        if ($error->stack) {
            $log .= 'Stack : ' . "\n" . \trim($error->stack) . "\n";
        }
        $log .= "--------------------------------------\n";

        if (\is_dir('log')) {
            // Log-Datei erstellen
            if (!\is_file('log/jsExceptions.log')) {
                \file_put_contents('log/jsExceptions.log', '');
            }

            // Log-Datei schreiben
            if (\is_file('log/jsExceptions.log') && \is_writable('log/jsExceptions.log')) {
                \file_put_contents('log/jsExceptions.log', $log, \FILE_APPEND);
            }
        }
    }
}
