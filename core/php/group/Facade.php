<?php
declare(strict_types = 1);

namespace biwi\edit\group;

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

        $groupId = $args->id;

        // Get Create Daten
        $qryBld = new edit\SqlSelector('group');
        $qryBld->addSelectElement('group.createId');
        $qryBld->addSelectElement('group.createDate');

        $qryBld->addWhereElement('group.groupId = :groupId');
        $qryBld->addParam(':groupId', $groupId, \PDO::PARAM_INT);

        // Nur die erste Version laden
        $qryBld->addWhereElement('`group` .version = (SELECT
                MIN(version)
            FROM
                `group`  AS maxVersion
            WHERE `group` .groupId = maxVersion.groupId)');

        $createRow = $qryBld->execute($this->app->getDb(), false);
        unset ($qryBld);


        // Get Change Daten
        $qryBld = new edit\SqlSelector('group');
        $qryBld->addSelectElement('group.changeId');
        $qryBld->addSelectElement('group.changeDate');

        $qryBld->addWhereElement('group.groupId = :groupId');
        $qryBld->addParam(':groupId', $groupId, \PDO::PARAM_INT);

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

        $loader = new edit\GridLoader($this->app, $args, 'group');

        // Status
        $statusSql = '
            IF(group.state = 10, :status_10,
                IF(group.state = 20, :status_20,
                    IF(group.state = 30, :status_30,
                        IF(group.state = 40, :status_40, :status_ukn)
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
        $loader->addPrimaryColumn('group.groupId', $this->app->getText('Personen Gruppe') . ' ' . $this->app->getText('ID'));
        $loader->addPrimaryColumn('group.version', $this->app->getText('Version'));

        $loader->addColumn($this->app->getText('Name'), 'group.name');
//        $loader->addColumn($this->app->getText('Beschreibung'), 'group.description');
        $loader->addColumn($this->app->getText('Status'), $statusSql);
        $loader->addColumn($this->app->getText('Author'), 'group.createId');
        $loader->addColumn($this->app->getText('Zuletzt bearbeitet'), 'group.changeDate', ['width' => 120, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);

        $loader->addSort('name');

        // Nur die letzte Version laden
        $loader->addWhereElement('`group`.version = (SELECT
                MAX(version)
            FROM
                `group` AS groupVersion
            WHERE `group`.groupId = groupVersion.groupId)');

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
        $groupId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $groupId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $row = [];
        if ($groupId) {
            $qryBld = new edit\SqlSelector('group');
            $qryBld->addSelectElement('group.groupId');
            $qryBld->addSelectElement('group.version');
            $qryBld->addSelectElement('group.name');
            $qryBld->addSelectElement('group.groupType');
            $qryBld->addSelectElement('group.dayFounding');
            $qryBld->addSelectElement('group.monthFounding');
            $qryBld->addSelectElement('group.yearFounding');
            $qryBld->addSelectElement('group.beforeChristFounding');
            $qryBld->addSelectElement('group.dayResolution');
            $qryBld->addSelectElement('group.monthResolution');
            $qryBld->addSelectElement('group.yearResolution');
            $qryBld->addSelectElement('group.beforeChristResolution');
            $qryBld->addSelectElement('group.text');

            $qryBld->addWhereElement('group.groupId = :groupId');
            $qryBld->addParam(':groupId', $groupId, \PDO::PARAM_INT);

            // Wenn eine Version übergeben wurde, diese laden
            if ($version) {
                $qryBld->addWhereElement('group.version = :version');
                $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

            // Die neuste Version laden
            } else {
                // Nur die letzte Version laden
                $qryBld->addWhereElement('`group` .version = (SELECT
                    MAX(version)
                FROM
                    `group`  AS maxVersion
                WHERE `group` .groupId = maxVersion.groupId)');
            }


            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);

        } else {
            $row['groupId'] = null;
            $row['version'] = null;
            $row['level'] = null;
            $row['name'] = null;
            $row['groupType'] = null;
            $row['dayFounding'] = null;
            $row['monthFounding'] = null;
            $row['yearFounding'] = null;
            $row['beforeChristFounding'] = null;
            $row['dayResolution'] = null;
            $row['monthResolution'] = null;
            $row['yearResolution'] = null;
            $row['beforeChristResolution'] = null;
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
     * Personengruppen für Combo zurückgeben
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

        $loader = new edit\ComboLoader($this->app, $args, 'group');
        $loader->setCaptionSql('group.name');
        $loader->setValueSql('group.groupId', true);

        // Nur die letzte Version laden
        $loader->getQueryBuilder()->addWhereElement('`group`.version = (SELECT
                MAX(version)
            FROM
                `group` AS groupVersion
            WHERE `group`.groupId = groupVersion.groupId)');

        if (property_exists($args, 'personId') && $args->personId) {
            $loader->getQueryBuilder()->addWhereElement('group.groupId NOT IN (SELECT groupId FROM personGroup WHERE personGroup.personId = :personId AND state = 10)');
            $loader->getQueryBuilder()->addParam(':personId', $args->personId);
        }

        return $loader->execute();
    }


    /**
     * Gibt die schon vorhandenen Typen aus der DB zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getOtherType(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $comboLoader = new edit\ComboLoader($this->app, $args, 'group');
        $comboLoader->setCaptionSql('group.groupType');
        $comboLoader->setValueSql('group.groupType');
        $comboLoader->setDistinct(true);

        return $comboLoader->execute();

    }


    /**
     * Gibt die Quellen zurück
     * @param \stdClass $args
     * @return object
     * @throws edit\ExceptionNotice
     * @throws ExceptionNotice
     */
    public function getSources(\stdClass $args): object {

        $groupId = null;
        $version = null;
        $assignTable = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $groupId = $args->id;
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
        $category = edit\app\App::getCategoryByName($this->app, 'group');
        $sourceId = edit\source\Source::getSourceId($field, $groupId, $category, $assignTable);

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
            $tableName = 'group';
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $category = edit\app\App::getCategoryByName($this->app, $tableName);

            $formPacket['categoryId'] = $category['categoryId'];

            if ($formPacket['groupId']) {
                $formPacket['id'] = $formPacket['groupId'];
                $formPacket['oldVal_groupId'] = $formPacket['groupId'];
            }

            if ($formPacket['version']) {
                $formPacket['oldVal_version'] = $formPacket['version'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
            $save->save($formPacket);
            $groupId = (int)$save->getPrimaryKey()->value;
            $version = (int)$save->getVersion();
            unset ($save);

            $formPacket['groupId'] = $groupId;
            $formPacket['version'] = $version;

            // Quellen speichern wenn vorhaden
            if ($formPacket['sources']) {
                $saveSource = new edit\SaveSource($this->app, $category);
                $saveSource->save($formPacket);
                unset($saveSource);
            }

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $groupId;
            $response->version = $version;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}
