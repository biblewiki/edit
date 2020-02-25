<?php
declare(strict_types = 1);

namespace biwi\edit\epoch;

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

        $epochId = $args->id;

        // Get Create Daten
        $qryBld = new edit\SqlSelector('epoch');
        $qryBld->addSelectElement('epoch.createId');
        $qryBld->addSelectElement('epoch.createDate');

        $qryBld->addWhereElement('epoch.epochId = :epochId');
        $qryBld->addParam(':epochId', $epochId, \PDO::PARAM_INT);

        // Nur die erste Version laden
        $qryBld->addWhereElement('`epoch` .version = (SELECT
                MIN(version)
            FROM
                `epoch`  AS maxVersion
            WHERE `epoch` .epochId = maxVersion.epochId)');

        $createRow = $qryBld->execute($this->app->getDb(), false);
        unset ($qryBld);


        // Get Change Daten
        $qryBld = new edit\SqlSelector('epoch');
        $qryBld->addSelectElement('epoch.changeId');
        $qryBld->addSelectElement('epoch.changeDate');

        $qryBld->addWhereElement('epoch.epochId = :epochId');
        $qryBld->addParam(':epochId', $epochId, \PDO::PARAM_INT);

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

        $loader = new edit\GridLoader($this->app, $args, 'epoch');

        // Status
        $statusSql = '
            IF(epoch.state = 10, :status_10,
                IF(epoch.state = 20, :status_20,
                    IF(epoch.state = 30, :status_30,
                        IF(epoch.state = 40, :status_40, :status_ukn)
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
        $loader->addPrimaryColumn('epoch.epochId', $this->app->getText('Personen Gruppe') . ' ' . $this->app->getText('ID'));
        $loader->addPrimaryColumn('epoch.version', $this->app->getText('Version'));

        $loader->addColumn($this->app->getText('Name'), 'epoch.name');
        $loader->addColumn($this->app->getText('Status'), $statusSql);
        $loader->addColumn($this->app->getText('Author'), 'epoch.createId');
        $loader->addColumn($this->app->getText('Zuletzt bearbeitet'), 'epoch.changeDate', ['width' => 120, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);

        $loader->addSort('name');

        // Nur die letzte Version laden
        $loader->addWhereElement('`epoch`.version = (SELECT
                MAX(version)
            FROM
                `epoch` AS epochVersion
            WHERE `epoch`.epochId = epochVersion.epochId)');

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
        $epochId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $epochId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $row = [];
        if ($epochId) {
            $qryBld = new edit\SqlSelector('epoch');
            $qryBld->addSelectElement('epoch.epochId');
            $qryBld->addSelectElement('epoch.version');
            $qryBld->addSelectElement('epoch.level');
            $qryBld->addSelectElement('epoch.name');
            $qryBld->addSelectElement('epoch.dayStart');
            $qryBld->addSelectElement('epoch.monthStart');
            $qryBld->addSelectElement('epoch.yearStart');
            $qryBld->addSelectElement('epoch.beforeChristStart');
            $qryBld->addSelectElement('epoch.dayEnd');
            $qryBld->addSelectElement('epoch.monthEnd');
            $qryBld->addSelectElement('epoch.yearEnd');
            $qryBld->addSelectElement('epoch.beforeChristEnd');
            $qryBld->addSelectElement('epoch.text');

            $qryBld->addWhereElement('epoch.epochId = :epochId');
            $qryBld->addParam(':epochId', $epochId, \PDO::PARAM_INT);

            // Wenn eine Version übergeben wurde, diese laden
            if ($version) {
                $qryBld->addWhereElement('epoch.version = :version');
                $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

            // Die neuste Version laden
            } else {
                // Nur die letzte Version laden
                $qryBld->addWhereElement('`epoch` .version = (SELECT
                    MAX(version)
                FROM
                    `epoch`  AS maxVersion
                WHERE `epoch` .epochId = maxVersion.epochId)');
            }


            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);

        } else {
            $row['epochId'] = null;
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

        $loader = new edit\ComboLoader($this->app, $args, 'epoch');
        $loader->setCaptionSql('epoch.name');
        $loader->setValueSql('epoch.epochId', true);

        // Nur die letzte Version laden
        $loader->getQueryBuilder()->addWhereElement('`epoch`.version = (SELECT
                MAX(version)
            FROM
                `epoch` AS epochVersion
            WHERE `epoch`.epochId = epochVersion.epochId)');

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

        $epochId = null;
        $version = null;
        $assignTable = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $epochId = $args->id;
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
        $category = edit\app\App::getCategoryByName($this->app, 'epoch');
        $sourceId = edit\source\Source::getSourceId($field, $epochId, $category, $assignTable);

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
            $tableName = 'epoch';
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $category = edit\app\App::getCategoryByName($this->app, $tableName);

            $formPacket['categoryId'] = $category['categoryId'];

            if ($formPacket['epochId']) {
                $formPacket['id'] = $formPacket['epochId'];
                $formPacket['oldVal_epochId'] = $formPacket['epochId'];
            }

            if ($formPacket['version']) {
                $formPacket['oldVal_version'] = $formPacket['version'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
            $save->save($formPacket);
            $epochId = (int)$save->getPrimaryKey()->value;
            $version = (int)$save->getVersion();
            unset ($save);

            $formPacket['epochId'] = $epochId;
            $formPacket['version'] = $version;

            // Quellen speichern wenn vorhaden
            if ($formPacket['sources']) {
                $saveSource = new edit\SaveSource($this->app, $category);
                $saveSource->save($formPacket);
                unset($saveSource);
            }

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $epochId;
            $response->version = $version;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}
