<?php
declare(strict_types = 1);

class App {
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

        // Exception-Handler initialisieren
        $this->exceptionHandler = new ExceptionHandler($this);

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
     * Gibt den navi-Baum zurück.
     */
    public function getNaviTree(): \Rpc\ResponseDefault {

        $elements = [];

        $btn = new \stdClass();
        $btn->caption = self::getText('Dashboard');
        $btn->name = 'kg_dashboard_Dashboard';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = self::getText('Produkte');
        $btn->name = 'kg_produkt_Produkt';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = self::getText('Produktimport');
        $btn->name = 'kg_produkt_ImportQueue';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = self::getText('Zuweisungen');
        $btn->name = 'kg_zuweisung_Zuweisung';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = self::getText('Preise');
        $btn->name = 'kg_preisrezept_PreisRezept';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = self::getText('Log');
        $btn->name = 'kg_log_Log';
        $elements[] = $btn;

        $btn = new \stdClass();
        $btn->caption = self::getText('Positionen');
        $btn->name = 'kg_position_Position';
        $elements[] = $btn;

        // ****************************
        // Stammdaten
        // ****************************


            $stammdaten = new \stdClass();
            $stammdaten->caption = self::getText('Stammdaten');
            $stammdaten->name = 'stammdaten';
            $stammdaten->elements = [];
            $elements[] = $stammdaten;

            $btn = new \stdClass();
            $btn->caption = self::getText('Artikelgruppen');
            $btn->name = 'kg_stammdaten_Artikelgruppe';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Buchtexte');
            $btn->name = 'kg_stammdaten_Buchtexte';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Ausführungen');
            $btn->name = 'kg_stammdaten_Ausfuehrung';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Zeiten');
            $btn->name = 'kg_stammdaten_Zeit';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Branchen');
            $btn->name = 'kg_stammdaten_Branche';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Einheiten');
            $btn->name = 'kg_stammdaten_Einheit';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Hilfsmaterialfaktor');
            $btn->name = 'kg_stammdaten_Hilfsmaterialfaktor';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Kostenelement');
            $btn->name = 'kg_stammdaten_Kostenelement';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Kosten');
            $btn->name = 'kg_stammdaten_Kosten';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Icons');
            $btn->name = 'kg_stammdaten_Icon';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Lieferanten');
            $btn->name = 'kg_stammdaten_Lieferant';
            $stammdaten->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Produktgruppen');
            $btn->name = 'kg_stammdaten_Produktgruppe';
            $stammdaten->elements[] = $btn;



        // ****************************
        // Administration
        // ****************************


            $administration = new \stdClass();
            $administration->caption = self::getText('Administration');
            $administration->name = 'administration';
            $administration->elements = [];
            $elements[] = $administration;

            $btn = new \stdClass();
            $btn->caption = self::getText('Kapitel');
            $btn->name = 'kg_kapitel_Kapitel';
            $administration->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Einzelpreise');
            $btn->name = 'kg_dienstleistung_Einzelpreis';
            $administration->elements[] = $btn;

            $btn = new \stdClass();
            $btn->caption = self::getText('Email Config');
            $btn->name = 'kg_email_Email';
            $administration->elements[] = $btn;


        // Response
        $return = new \Rpc\ResponseDefault();
        $return->elements = $elements;
        return $return;
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


    public function getTexts() {
        $return = new stdClass();
        $return->texts = [];
        return $return;
    }


    public function getUserId(): int {
        return 1;
    }


    public function getUserName(int $userId): string {
        return 'Joel Kohler';
    }


    /**
     * Gibt zurück, ob der Benutzer eingeloggt ist
     *
     * @return bool
     */
    public function isLoggedIn(): bool {
        return (bool)$this->getUserId();
    }


    /**
     * Speichert eine Javascript-Fehlermeldung ins Error-Log.
     * @param \stdClass $error
     * @return void
     */
    public function jsErrorLog(\stdClass $error): void {

        // Loggen?
        //if (self::getConfig('exceptionHandling,logJavascriptErrors') !== true) {
        //    return;
        //}

        $log = '';
        $log .= 'Date  : ' . \date('d.m.Y H:i:s'). "\n";
        //$log .= 'User  : ' . self::getSession()->userId . "\n";

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


    /**
     * @param bool $ignoreWarnings
     */
    public function setIgnoreWarnings(bool $ignoreWarnings): void {
        $this->ignoreWarnings = $ignoreWarnings;
    }

}
