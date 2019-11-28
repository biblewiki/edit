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

    public function getUserName($userId) {
        return 'Joel Kohler';
    }
}
