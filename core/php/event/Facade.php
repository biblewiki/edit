<?php
declare(strict_types = 1);

namespace biwi\edit\event;

use biwi\edit;

/**
 * Class Facade
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
     * @param edit\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }


    /**
     * Details von ID aus DB holen
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseDefault
     * @throws \Exception
     */
    public function getDetailHtml(\stdClass $args): edit\Rpc\ResponseDefault {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // Überprüfen ob einen ID übergeben wurde
        if (!property_exists($args, 'id') || !$args->id) {
            throw new edit\ExceptionNotice($this->app->getText('Es wurde keine ID übergeben.'));
        }

        $eventId = $args->id;

        // Get Create Daten
        $qryBld = new edit\SqlSelector('event');
        $qryBld->addSelectElement('event.createId');
        $qryBld->addSelectElement('event.createDate');

        $qryBld->addWhereElement('event.eventId = :eventId');
        $qryBld->addParam(':eventId', $eventId, \PDO::PARAM_INT);

        // Nur die erste Version laden
        $qryBld->addWhereElement('`event` .version = (SELECT
                MIN(version)
            FROM
                `event`  AS maxVersion
            WHERE `event` .eventId = maxVersion.eventId)');

        $createRow = $qryBld->execute($this->app->getDb(), false);
        unset ($qryBld);


        // Get Change Daten
        $qryBld = new edit\SqlSelector('event');
        $qryBld->addSelectElement('event.changeId');
        $qryBld->addSelectElement('event.changeDate');

        $qryBld->addWhereElement('event.eventId = :eventId');
        $qryBld->addParam(':eventId', $eventId, \PDO::PARAM_INT);

        $qryBld->addOrderByElement('changeDate ASC');

        $changeRows = $qryBld->execute($this->app->getDb());
        unset ($qryBld);


        $html = '';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td><b>Erstellt</b></td>';
        $html .= '<td>' . $this->app->getUserName($createRow['createId']) . '</td>';
        $html .= '<td>' . gmdate('d.m.Y H:i', $createRow['createDate']) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td><b>Geändert</b></td>';
        $html .= '<td></td>';
        $html .= '</tr>';

        foreach ($changeRows as $changeRow) {
            $html .= '<tr>';
            $html .= '<td></td>';
            $html .= '<td>' . $this->app->getUserName($changeRow['changeId']) . '</td>';
            $html .= '<td>' . gmdate('d.m.Y H:i', $changeRow['changeDate']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $return = new edit\Rpc\ResponseDefault();
        $return->html = $html;
        return $return;
    }


    public function getGridData(\stdClass $args): edit\Rpc\ResponseGrid {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $loader = new edit\GridLoader($this->app, $args, 'event');

        // Status
        $statusSql = '
            IF(event.state = 10, :status_10,
                IF(event.state = 20, :status_20,
                    IF(event.state = 30, :status_30,
                        IF(event.state = 40, :status_40, :status_ukn)
                    )
                )
            )';

        $loader->getQueryBuilderForSelect()->addParam(':status_10', $this->app->getSetting('state10'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_10', $this->app->getSetting('state10'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_20', $this->app->getSetting('state20'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_20', $this->app->getSetting('state20'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_30', $this->app->getSetting('state30'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_30', $this->app->getSetting('state30'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_40', $this->app->getSetting('state40'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_40', $this->app->getSetting('state40'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);

        // Primary Keys
        $loader->addPrimaryColumn('event.eventId', $this->app->getText('Personen Gruppe') . ' ' . $this->app->getText('ID'));
        $loader->addPrimaryColumn('event.version', $this->app->getText('Version'));

        $loader->addColumn($this->app->getText('Name'), 'event.name');
        $loader->addColumn($this->app->getText('Status'), $statusSql);
        $loader->addColumn($this->app->getText('Author'), 'event.createId');
        $loader->addColumn($this->app->getText('Zuletzt bearbeitet'), 'event.changeDate', ['width' => 120, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);

        $loader->addSort('name');

        // Nur die letzte Version laden
        $loader->addWhereElement('`event`.version = (SELECT
                MAX(version)
            FROM
                `event` AS eventVersion
            WHERE `event`.eventId = eventVersion.eventId)');

        $result = $loader->load();

        foreach ($result->rows as &$row) {
            $row['createId'] = $this->app->getUserName($row['createId']);
        }

        return $result;
    }


    /**
     * Gibt das Formular zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseForm
     */
    public function getFormData(\stdClass $args): edit\Rpc\ResponseForm {
        $eventId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $eventId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $row = [];
        if ($eventId) {
            $qryBld = new edit\SqlSelector('event');
            $qryBld->addSelectElement('event.eventId');
            $qryBld->addSelectElement('event.version');
            $qryBld->addSelectElement('event.level');
            $qryBld->addSelectElement('event.name');
            $qryBld->addSelectElement('event.dayStart');
            $qryBld->addSelectElement('event.monthStart');
            $qryBld->addSelectElement('event.yearStart');
            $qryBld->addSelectElement('event.beforeChristStart');
            $qryBld->addSelectElement('event.dayEnd');
            $qryBld->addSelectElement('event.monthEnd');
            $qryBld->addSelectElement('event.yearEnd');
            $qryBld->addSelectElement('event.beforeChristEnd');
            $qryBld->addSelectElement('event.text');

            $qryBld->addWhereElement('event.eventId = :eventId');
            $qryBld->addParam(':eventId', $eventId, \PDO::PARAM_INT);

            // Wenn eine Version übergeben wurde, diese laden
            if ($version) {
                $qryBld->addWhereElement('event.version = :version');
                $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

            // Die neuste Version laden
            } else {
                // Nur die letzte Version laden
                $qryBld->addWhereElement('`event` .version = (SELECT
                    MAX(version)
                FROM
                    `event`  AS maxVersion
                WHERE `event` .eventId = maxVersion.eventId)');
            }


            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);

        } else {
            $row['eventId'] = null;
            $row['version'] = null;
            $row['level'] = null;
            $row['name'] = null;
            $row['dayStart'] = null;
            $row['monthStart'] = null;
            $row['yearStart'] = null;
            $row['beforeChristStart'] = null;
            $row['dayEnd'] = null;
            $row['monthEnd'] = null;
            $row['yearEnd'] = null;
            $row['beforeChristEnd'] = null;
            $row['text'] = null;

        }

        // neuer Datensatz?
        if (\property_exists($args, 'create') && $args->create === true) {
            unset($row['mitteilungId']);
        }

        $row['openTS'] = date('Y-m-d H:i:s');

        $return = new edit\Rpc\ResponseForm();
        $return->setFormData($row);
        return $return;
    }


    /**
     * Gibt die Daten für das Combo zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getForCombo(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $loader = new edit\ComboLoader($this->app, $args, 'event');
        $loader->setCaptionSql('event.name');
        $loader->setValueSql('event.eventId', true);

        // Nur die letzte Version laden
        $loader->getQueryBuilder()->addWhereElement('`event`.version = (SELECT
                MAX(version)
            FROM
                `event` AS eventVersion
            WHERE `event`.eventId = eventVersion.eventId)');

        return $loader->execute();
    }


    /**
     * Gibt die Quellen zurück
     * @param \stdClass $args
     * @return object
     * @throws edit\ExceptionNotice
     * @throws ExceptionNotice
     */
    public function getSources(\stdClass $args): object {

        $eventId = null;
        $version = null;
        $assignTable = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $eventId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        // Überprüfen ob ein Feld übergeben wurde
        if (!property_exists($args, 'field') || !$args->field) {
            throw new ExceptionNotice($this->app->getText('Es wurde kein Feld übergeben.'));
        }

        // Zuweisungstabelle auslesen wenn vorhanden
        if (property_exists($args, 'assignTable') && $args->assignTable) {
            $assignTable = $args->assignTable;
        }

        $field = $args->field;
        $category = edit\app\App::getCategoryByName($this->app, 'event');
        $sourceId = edit\source\Source::getSourceId($field, $eventId, $category, $assignTable);

        $return = edit\source\Source::getSources($this->app, $sourceId, $version);

        return $return;
    }


    /**
     * Speichert das Formular
     *
     * @param \stdClass $args
     * @return edit\Rpc\ResponseDefault
     */
    public function saveDetailForm(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $tableName = 'event';
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $category = edit\app\App::getCategoryByName($this->app, $tableName);

            $formPacket['categoryId'] = $category['categoryId'];

            if ($formPacket['eventId']) {
                $formPacket['id'] = $formPacket['eventId'];
                $formPacket['oldVal_eventId'] = $formPacket['eventId'];
            }

            if ($formPacket['version']) {
                $formPacket['oldVal_version'] = $formPacket['version'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
            $save->save($formPacket);
            $eventId = (int)$save->getPrimaryKey()->value;
            $version = (int)$save->getVersion();
            unset ($save);

            $formPacket['eventId'] = $eventId;
            $formPacket['version'] = $version;

            // Quellen speichern wenn vorhaden
            if ($formPacket['sources']) {
                $saveSource = new edit\SaveSource($this->app, $category);
                $saveSource->save($formPacket);
                unset($saveSource);
            }

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $eventId;
            $response->version = $version;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}
