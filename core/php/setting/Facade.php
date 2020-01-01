<?php
declare(strict_types = 1);

namespace biwi\edit\setting;

use biwi\edit;

/**
 * Class Facade
 *
 * @package biwi\edit\setting
 */
class Facade {

    /**
     * @var ki\App
     */
    protected $app;

    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    /**
     * Facade constructor.
     *
     * @param ki\App $app
     */
    public function __construct(ki\App $app) {

        if ($app) {

            // App zuweisen
            $this->app = $app;
        }
    }


    /**
     * Gibt die Daten für die Setting-Auflistung zurück
     *
     * @param \stdClass $params
     * @return \stdClass
     */
    public function getListData(\stdClass $params): \stdClass {

        // Parameter dekodieren
        $module = htmlspecialchars($params->module, ENT_QUOTES);

        $rows = [];

        // Daten aufbereiten
        $data = [];
        foreach ($rows as $row) {
            $itm = new \stdClass();
            $itm->title = str_replace("\n", "<br>", htmlentities($row['title'], ENT_QUOTES, "UTF-8"));
            $itm->setting = htmlspecialchars($row['setting'], ENT_QUOTES);
            $itm->value = str_replace("\n", "<br>", htmlentities($row['value'], ENT_QUOTES, "UTF-8"));
            $itm->caption = str_replace("\n", "<br>", htmlentities($row['caption'], ENT_QUOTES, "UTF-8"));
            $itm->description = str_replace("\n", "<br>", htmlentities($row['description'], ENT_QUOTES, "UTF-8"));
            $data[] = $itm;
        }
        unset($rows);

        // Response für Setting-List
        $return = new \stdClass();
        if ($module === "") {
            $module = 'general';
        }
        if ($this->app->checkRights($this->app->getConfig('rights, adminFunction'))) {
            $return->rows = $data;      // Daten
        } else {

            // Wenn keine Berechtigungen vorhanden sind: keine DS zurückgeben
            $return->rows = [];         // Daten
        }

        return $return;
    }


    /**
     * Gibt einen Wert aus den Settings zurück
     *
     * @param string $setting
     * @param bool $silent
     * @param bool $asLines
     * @return \stdClass
     * @throws \Exception
     */
    public function getSetting(string $setting, bool $silent, bool $asLines): \stdClass {

        // Rechte überprüfen
        if (!$this->app->checkRights()) {

            // Fehlermeldung
            throw new ki\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // Response
        $return = new \stdClass();
        $return->success = true;
        $return->value = $this->app->getSetting($setting, $silent, $asLines);

        return $return;
    }


    /**
     * Gibt die Daten für das Detail-Formular zurück
     *
     * @param string $setting
     * @return \stdClass
     */
    public function loadDetailForm(string $setting): \stdClass {
        try {

            // Rechte überprüfen
            if (!$this->app->checkRights()) {

                // Fehlermeldung
                throw new ki\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            // ID dekodieren
            $setting = htmlspecialchars_decode($setting);

            // Setting holen
            $row = Setting::getSetting($this->app, $setting);

            // Rechte überprüfen
            if (!$this->app->checkRights($this->app->getConfig('rights, adminFunction'))) {

                // Fehlermeldung
                $return = new \stdClass();
                $return->success = false;
                $return->msg = 'Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang.';

                return $return;
            }

            // Rückgabe vorbereiten
            $data = new \stdClass();
            $data->caption = htmlspecialchars($row['caption'], ENT_QUOTES);
            $data->value = $row['value'];
//            $data->statusInfo = Utilities::getStatusInfo($this->app, $row, 'setting');

            // Load Form Response
            $return = new \stdClass();
            $return->success = true;
            $return->data = $data;

            return $return;

        } catch (\Throwable $e) {

            // Fehlerauswertung und Rückgabe
            $return = new \stdClass();
            $return->success = false;
            $return->msg = $this->app->getExceptionHandler()->handleException($e);

            return $return;
        }
    }


    /**
     * Speichert die Daten des Detail-Formulars
     *
     * @param \stdClass $formObject
     * @param string $setting
     * @return \stdClass
     */
    public function saveDetailForm(\stdClass $formObject, string $setting): \stdClass {
        try {

            // Transaktion starten
            $this->app->getDb()->beginTransaction();

            // FormPacket erzeugen
            $formPacket = get_object_vars($formObject);

            // Setting holen
            $row = Setting::getSetting($this->app, $setting);

            // Rechte überprüfen
            if (!$this->app->checkRights($this->app->getConfig('rights, adminFunction'))) {

                // Rollback
                $this->app->getDb()->rollBackIfTransaction();

                // Fehlermeldung
                $return = new \stdClass();
                $return->success = false;
                $return->msg = 'Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang.';

                return $return;

            }
            if (!$row['editable']) {

                // Rollback
                $this->app->getDb()->rollBackIfTransaction();

                // Fehlermeldung
                $return = new \stdClass();
                $return->success = false;
                $return->msg = 'Diese Einstellung kann nicht verändert werden.';

                return $return;
            }

            $userId = $this->app->getSession()->userId;
            $timestamp = date('Y-m-d H:i:s');

            // Datensatz ändern
            $st = $this->app->getDb()->prepare('
                UPDATE
                    `setting`
                SET
                    `value` = :value,
                    `changeId` = :userId,
                    `changeDate` = :timestamp
                WHERE
                    `setting` = :setting
            ');
            $st->bindParam(':setting', $setting, \PDO::PARAM_STR);
            $st->bindParam(':value', $formPacket['value'], \PDO::PARAM_STR);
            $st->bindParam(':userId', $userId, \PDO::PARAM_STR);
            $st->bindParam(':timestamp', $timestamp, \PDO::PARAM_STR);
            $st->execute();
            unset($st);

            // Transaktion abschliessen
            $this->app->getDb()->commit();

            // Response
            $return = new \stdClass();
            $return->success = true;

            return $return;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            // Fehlerauswertung und Rückgabe
            $return = new \stdClass();
            $return->success = false;
            $return->msg = $this->app->getExceptionHandler()->handleException($e);

            return $return;
        }
    }
}