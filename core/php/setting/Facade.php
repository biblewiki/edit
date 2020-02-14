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
     * @var edit\App
     */
    protected $app;

    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    /**
     * Facade constructor.
     *
     * @param edit\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
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
        if ($this->app->getLoggedInUserRole() > 2) {
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
//    public function getSetting(string $setting, bool $silent, bool $asLines): \stdClass {
//
//        // Rechte überprüfen
//        if ($this->app->getLoggedInUserRole() < 3) {
//            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
//        }
//
//        // Response
//        $return = new \stdClass();
//        $return->success = true;
//        $return->value = $this->app->getSetting($setting, $silent, $asLines);
//
//        return $return;
//    }


    /**
     * Gibt die Settings zurück
     *
     * @param \stdClass $args
     * @return \edit\kgweb\edit\Rpc\ResponseDefault
     * @throws edit\ExceptionNotice
     */
    public function getSettings(\stdClass $args): edit\Rpc\ResponseDefault {

        // Rechte überprüfen
        if ($this->app->getLoggedInUserRole() < 50) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // Abfrage
        $st = new edit\SqlSelector('setting');
        $st->addSelectElement('setting.title');
        $st->addSelectElement('setting.setting');
        $st->addSelectElement('setting.caption');
        $st->addSelectElement('setting.description');
        $st->addSelectElement('setting.value');

        $st->addWhereElement('editable = 1');
        $st->addOrderByElement('module ASC');
        $st->addOrderByElement('sort ASC');

        $rows = $st->execute($this->app->getDb());
        unset($st);

        foreach ($rows as &$row) {
            $row['value'] = nl2br($row['value']);
            $row['description'] = nl2br($row['description']);
        }

        // Response
        $return = new edit\Rpc\ResponseDefault();
        $return->success = true;
        $return->settings = $rows;

        return $return;
    }


    /**
     * Gibt die Daten für das Detail-Formular zurück
     *
     * @param string $setting
     * @return \stdClass
     */
//    public function loadDetailForm(string $setting): \stdClass {
//        try {
//
//            // Rechte überprüfen
//            if (!$this->app->getLoggedInUserRole()) {
//
//                // Fehlermeldung
//                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
//            }
//
//            // ID dekodieren
//            $setting = htmlspecialchars_decode($setting);
//
//            // Setting holen
//            $row = Setting::getSetting($this->app, $setting);
//
//            // Rechte überprüfen
//            if ($this->app->getLoggedInUserRole() < 3) {
//
//                // Fehlermeldung
//                $return = new \stdClass();
//                $return->success = false;
//                $return->msg = 'Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang.';
//
//                return $return;
//            }
//
//            // Rückgabe vorbereiten
//            $data = new \stdClass();
//            $data->caption = htmlspecialchars($row['caption'], ENT_QUOTES);
//            $data->value = $row['value'];
////            $data->statusInfo = Utilities::getStatusInfo($this->app, $row, 'setting');
//
//            // Load Form Response
//            $return = new \stdClass();
//            $return->success = true;
//            $return->data = $data;
//
//            return $return;
//
//        } catch (\Throwable $e) {
//
//            // Fehlerauswertung und Rückgabe
//            $return = new \stdClass();
//            $return->success = false;
//            $return->msg = $this->app->getExceptionHandler()->handleException($e);
//
//            return $return;
//        }
//    }


    /**
     * Speichert ein Setting
     * @param \stdClass $args
     * @return \edit\kgweb\edit\Rpc\ResponseDefault
     * @throws \Throwable
     * @throws edit\ExceptionNotice
     */
    public function saveSetting(\stdClass $args): edit\Rpc\ResponseDefault {
        try {

            // Rechte überprüfen
            if ($this->app->getLoggedInUserRole() < 50) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $formPacket = get_object_vars($args->formData);
            $formPacket['setting'] = array_key_first($formPacket);
            $formPacket['oldVal_setting'] = $formPacket['setting'];
            $formPacket['value'] = $formPacket[array_key_first($formPacket)];

            $saveData = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), 'setting');
            $saveData->save($formPacket);
            unset($saveData);

            return new edit\Rpc\ResponseDefault();

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }


    /**
     * Speichert die Daten des Detail-Formulars
     *
     * @param \stdClass $formObject
     * @param string $setting
     * @return \stdClass
     */
//    public function saveDetailForm(\stdClass $formObject, string $setting): \stdClass {
//        try {
//
//            // Transaktion starten
//            $this->app->getDb()->beginTransaction();
//
//            // FormPacket erzeugen
//            $formPacket = get_object_vars($formObject);
//
//            // Setting holen
//            $row = Setting::getSetting($this->app, $setting);
//
//            // Rechte überprüfen
//            if ($this->app->getLoggedInUserRole() < 3) {
//
//                // Rollback
//                $this->app->getDb()->rollBackIfTransaction();
//
//                // Fehlermeldung
//                $return = new \stdClass();
//                $return->success = false;
//                $return->msg = 'Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang.';
//
//                return $return;
//
//            }
//            if (!$row['editable']) {
//
//                // Rollback
//                $this->app->getDb()->rollBackIfTransaction();
//
//                // Fehlermeldung
//                $return = new \stdClass();
//                $return->success = false;
//                $return->msg = 'Diese Einstellung kann nicht verändert werden.';
//
//                return $return;
//            }
//
//            $userId = $this->app->getSession()->userId;
//            $timestamp = date('Y-m-d H:i:s');
//
//            // Datensatz ändern
//            $st = $this->app->getDb()->prepare('
//                UPDATE
//                    `setting`
//                SET
//                    `value` = :value,
//                    `changeId` = :userId,
//                    `changeDate` = :timestamp
//                WHERE
//                    `setting` = :setting
//            ');
//            $st->bindParam(':setting', $setting, \PDO::PARAM_STR);
//            $st->bindParam(':value', $formPacket['value'], \PDO::PARAM_STR);
//            $st->bindParam(':userId', $userId, \PDO::PARAM_STR);
//            $st->bindParam(':timestamp', $timestamp, \PDO::PARAM_STR);
//            $st->execute();
//            unset($st);
//
//            // Transaktion abschliessen
//            $this->app->getDb()->commit();
//
//            // Response
//            $return = new \stdClass();
//            $return->success = true;
//
//            return $return;
//
//        } catch (\Throwable $e) {
//
//            // Rollback
//            $this->app->getDb()->rollBackIfTransaction();
//
//            // Fehlerauswertung und Rückgabe
//            $return = new \stdClass();
//            $return->success = false;
//            $return->msg = $this->app->getExceptionHandler()->handleException($e);
//
//            return $return;
//        }
//    }
}