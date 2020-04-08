<?php
declare(strict_types = 1);

namespace biwi\edit\region;

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
     * Beziehung(en) löschen
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseDefault
     * @throws \Throwable
     * @throws edit\ExceptionNotice
     * @throws edit\Rpc\Warning
     */
        public function deleteRelationship(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $ids = \property_exists($args, 'selection') ? $args->selection : [];

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            if (!$ids) {
                throw new edit\ExceptionNotice($this->app->getText('Es wurde kein Datensatz ausgewählt.'));
            }

            if (!$this->app->isIgnoreWarnings()) {
                throw new edit\Rpc\Warning($this->app->getText('Möchten Sie die ausgewählten Datensätze wirklich löschen?'), $this->app->getText('Löschen') . '?');
            }

            // Transaktion starten
            $this->app->getDb()->beginTransaction();

            // sql
            $st = $this->app->getDb()->prepare('DELETE FROM personRelationship WHERE personRelationshipId = :personRelationshipId');

            foreach ($ids as $id) {
                $st->bindValue(':personRelationshipId', $id, \PDO::PARAM_INT);
                $st->execute();
            }
            unset ($st);

            // Kategorie holen
            $category = edit\app\App::getCategoryByName($this->app, 'region');

            // Quellen aus DB löschen
            $deleteSources = new edit\DeleteSource($this->app, $category, 'personRelationship');
            $deleteSources->delete($ids);

            // Transaktion beenden
            $this->app->getDb()->commit();

            $response = new edit\Rpc\ResponseDefault();
            $response->return = $ids;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
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

        $personId = $args->id;

        // Get Create Daten
        $qryBld = new edit\SqlSelector('person');
        $qryBld->addSelectElement('person.createId');
        $qryBld->addSelectElement('person.createDate');

        $qryBld->addWhereElement('person.personId = :personId');
        $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);

        // Nur die erste Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MIN(version)
            FROM
                person AS personVersion
            WHERE person.personId = personVersion.personId)');

        $createRow = $qryBld->execute($this->app->getDb(), false);
        unset ($qryBld);


        // Get Change Daten
        $qryBld = new edit\SqlSelector('person');
        $qryBld->addSelectElement('person.changeId');
        $qryBld->addSelectElement('person.changeDate');

        $qryBld->addWhereElement('person.personId = :personId');
        $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);

        $qryBld->addOrderByElement('changeDate ASC');

        $changeRows = $qryBld->execute($this->app->getDb());
        unset ($qryBld);


        $html = '';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td><b>Erstellt</b></td>';
        $html .= '<td>' . $createRow['createId'] . '</td>';
        $html .= '<td>' . gmdate('d.m.Y H:i', $createRow['createDate']) . '</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<td><b>Geändert</b></td>';
        $html .= '<td></td>';
        $html .= '</tr>';

        foreach ($changeRows as $changeRow) {
            $html .= '<tr>';
            $html .= '<td></td>';
            $html .= '<td>' . $changeRow['changeId'] . '</td>';
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

        $loader = new edit\GridLoader($this->app, $args, 'region');

        // Status
        $statusSql = '
            IF(region.state = 10, :status_10,
                IF(region.state = 20, :status_20,
                    IF(region.state = 30, :status_30,
                        IF(region.state = 40, :status_40, :status_ukn)
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
        $loader->addPrimaryColumn('region.regionId', $this->app->getText('Region') . ' ' . $this->app->getText('ID'));
        $loader->addPrimaryColumn('region.version', $this->app->getText('Version'));

        $loader->addColumn($this->app->getText('Name'), 'region.name');
        $loader->addColumn($this->app->getText('Beschreibung'), 'region.text');
        $loader->addColumn($this->app->getText('Status'), $statusSql);
        $loader->addColumn($this->app->getText('Author'), 'region.createId');
        $loader->addColumn($this->app->getText('Zuletzt bearbeitet'), 'region.changeDate', ['width' => 120, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);

        $loader->addSort('name');

        // Nur die letzte Version laden
        $loader->addWhereElement('region.version = (SELECT
                MAX(version)
            FROM
                region AS regionVersion
            WHERE region.regionId = regionVersion.regionId)
        ');

        return $loader->load();
    }


    /**
     * Gibt das Formular zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseForm
     */
    public function getFormData(\stdClass $args): edit\Rpc\ResponseForm {
        $regionId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $regionId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $row = [];
        if ($regionId) {
            $qryBld = new edit\SqlSelector('region');
            $qryBld->addSelectElement('region.regionId');
            $qryBld->addSelectElement('region.version');
            $qryBld->addSelectElement('region.name');
            $qryBld->addSelectElement('region.personId');
            $qryBld->addSelectElement('region.dayFounding');
            $qryBld->addSelectElement('region.monthFounding');
            $qryBld->addSelectElement('region.yearFounding');
            $qryBld->addSelectElement('region.beforeChristFounding');
            $qryBld->addSelectElement('region.roughlyFounding');
            $qryBld->addSelectElement('region.dayResolution');
            $qryBld->addSelectElement('region.monthResolution');
            $qryBld->addSelectElement('region.yearResolution');
            $qryBld->addSelectElement('region.roughlyResolution');
            $qryBld->addSelectElement('region.area');
            $qryBld->addSelectElement('region.population');
            $qryBld->addSelectElement('region.text');
            $qryBld->addSelectElement('region.state');
            $qryBld->addSelectElement('region.createDate');

            //$qryBld->addFromElement('LEFT JOIN personProficiency ON personProficiency.personId = person.personId AND personProficiency.version = person.version');

            //$qryBld->addWhereElement('person.personId = :personId');
            //$qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);

            // Wenn eine Version übergeben wurde, diese laden
            if ($version) {
                //$qryBld->addWhereElement('person.version = :version');
                //$qryBld->addParam(':version', $version, \PDO::PARAM_INT);

            // Die neuste Version laden
            } else {
                // Nur die letzte Version laden
                $qryBld->addWhereElement('person.version = (SELECT
                    MAX(version)
                FROM
                    person AS personVersion
                WHERE person.personId = personVersion.personId)');
            }

            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);

        } else {
            $row['regionId'] = null;
            $row['version'] = null;
            $row['name'] = null;
            $row['description'] = null;
            $row['personId'] = null;
            $row['dayFounding'] = null;
            $row['monthFounding'] = null;
            $row['yearFounding'] = null;
            $row['beforeChristFounding'] = null;
            $row['roughlyFounding'] = null;
            $row['dayResolution'] = null;
            $row['monthResolution'] = null;
            $row['yearResolution'] = null;
            $row['roughlyResolution'] = null;
            $row['area'] = null;
            $row['population'] = null;
            $row['text'] = null;
            $row['state'] = null;
            $row['createDate'] = null;
        }

        // neuer Datensatz?
        if (\property_exists($args, 'create') && $args->create === true) {
            unset($row['regionId']);
        }

        $row['openTS'] = date('Y-m-d H:i:s');

        $return = new edit\Rpc\ResponseForm();
        $return->setFormData($row);
        return $return;
    }


    /**
     * Personen für Combo zurückgeben
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

        $persons = edit\person\Person::getPersons($this->app, $args);

        // Name mit Beschreibung für Combo erstellen
        foreach ($persons as &$person) {
            $person['comboName'] = $person['name'] . ':  ' .$person['description'];
        }

        $response = new edit\Rpc\ResponseCombo();
        $response->addRows($persons);
        return $response;
    }


    /**
     * Gibt Infos für einen neue Geruppe im Grid zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseDefault
     * @throws edit\ExceptionNotice
     */
    public function getForGroupGrid(\stdClass $args): edit\Rpc\ResponseDefault {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // Überprüfen ob einen groupId übergeben wurde
        if (!property_exists($args, 'groupId') || !$args->groupId) {
            throw new edit\ExceptionNotice($this->app->getText('Es wurde keine ID übergeben'));
        }

        $return = new edit\Rpc\ResponseDefault();
        $return->data = edit\group\Group::getGroup($this->app, $args->groupId);

        return $return;
    }


    /**
     * Gibt Infos für einen neue Beziehung im Grid zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseDefault
     * @throws edit\ExceptionNotice
     */
    public function getForRelationshipGrid(\stdClass $args): edit\Rpc\ResponseDefault {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // Überprüfen ob einen personId übergeben wurde
        if (!property_exists($args, 'personId') || !$args->personId) {
            throw new edit\ExceptionNotice($this->app->getText('Es wurde keine ID übergeben'));
        }

        // Überprüfen ob einen relationshipId übergeben wurde
        if (!property_exists($args, 'relationshipId') || !$args->relationshipId) {
            throw new edit\ExceptionNotice($this->app->getText('Es wurde keine ID übergeben'));
        }

        // Höchste ID in Tabelle auslesen
        $st = new edit\SqlSelector('personRelationship');
        $st->addSelectElement('MAX(personRelationshipId) AS maxId');
        $row = $st->execute($this->app->getDb(), false);
        unset ($st);

        $personData = edit\person\Person::getPerson($this->app, $args->personId);
        $relationshipData = edit\relationship\Relationship::getRelationship($this->app, $args->relationshipId);

        $data['relationshipName'] = $relationshipData['name'];
        $data['name'] = $personData['name'];
        $data['description'] = $personData['description'];
        $data['personRelationshipId'] = $row['maxId'] + 1;

        $return = new edit\Rpc\ResponseDefault();
        $return->data = $data;

        return $return;
    }


    /**
     * Gibt das Personengruppen Grid zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseGrid
     * @throws edit\ExceptionNotice
     */
    public function getGroupGrid(\stdClass $args): edit\Rpc\ResponseGrid {

        $personId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'personId') && $args->personId) {
            $personId = $args->personId;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $loader = new edit\GridLoader($this->app, $args, 'personGroup');

        // Primary Key
        $loader->addPrimaryColumn('personGroup.personGroupId', $this->app->getText('Beziehungs') . ' ' . $this->app->getText('ID'));

        $loader->addColumn($this->app->getText('Person') . ' ' . $this->app->getText('ID'), 'personGroup.personId', ['visible' => false]);
        $loader->addColumn($this->app->getText('Version'), 'personGroup.versionPerson', ['visible' => false]);
        $loader->addColumn($this->app->getText('Gruppe') . ' ' . $this->app->getText('ID'), 'personGroup.groupId', ['visible' => false]);
        $loader->addColumn($this->app->getText('Gruppe'), 'group.name');

        // Gruppe auslesen
        $loader->addFromElement('INNER JOIN `group` ON personGroup.groupId = group.groupId');

        // Nur Einträge mit 'normalem' Staus laden
        $loader->addWhereElement('personGroup.`state` < 100');

        // Nur die Gruppen für diese Person laden
        $loader->addWhereElement('personGroup.personId = :personId');
        $loader->getQueryBuilderForSelect()->addParam(':personId', $personId, \PDO::PARAM_INT);
        $loader->getCntQueryBuilderForSelect()->addParam(':personId', $personId, \PDO::PARAM_INT);

        // Nur die letzte Versionen laden
        $loader->addWhereElement('personGroup.versionPerson = (SELECT
                MAX(versionPerson)
            FROM
                personGroup AS personGroupVersion
            WHERE personGroup.personGroupId = personGroupVersion.personGroupId)
        ');

        $loader->addWhereElement('personGroup.versionGroup = (SELECT
                MAX(versionGroup)
            FROM
                personGroup AS personGroupVersion
            WHERE personGroup.personGroupId = personGroupVersion.personGroupId)
        ');

        $response = $loader->load();
        return $response;
    }


    /**
     * Gibt die vorhandenen Namen aus der DB zurück
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseDefault
     */
    public function getNames(\stdClass $args): edit\Rpc\ResponseDefault {
        $names = [];

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'personId') && $args->personId) {
            $personId = $args->personId;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        if ($personId) {
            $sqlSelector = new edit\SqlSelector('personName');
            $sqlSelector->addSelectElement('personName.personNameId');
            $sqlSelector->addSelectElement('personName.name');
            $sqlSelector->addSelectElement('personName.description');
            $sqlSelector->addSelectElement('personName.visible');

            $sqlSelector->addWhereElement('personName.personId = :personId');
            $sqlSelector->addParam('personId', $personId);

            if ($version) {
                $sqlSelector->addWhereElement('personName.version = :version');
                $sqlSelector->addParam('version', $version);
            }
            $names = $sqlSelector->execute($this->app->getDb());
            unset($sqlSelector);
        }

        $response = new edit\Rpc\ResponseDefault();
        $response->names = $names;

        return $response;
    }


    /**
     * Gibt die schon vorhandenen Berufe aus der DB zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getPersonProficiency(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $comboLoader = new edit\ComboLoader($this->app, $args, 'personProficiency');
        $comboLoader->setCaptionSql('personProficiency.proficiency');
        $comboLoader->setValueSql('personProficiency.proficiency');
        $comboLoader->setDistinct(true);

        return $comboLoader->execute();
    }


    /**
     * Beziehungen nach Geschlecht für Combo zurückgeben
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getRelationshipForCombo(\stdClass $args): edit\Rpc\ResponseCombo {
        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // Überprüfen ob einen ID übergeben wurde
        if (!property_exists($args, 'personId') || !$args->personId) {
            throw new edit\ExceptionNotice($this->app->getText('Es wurde keine ID übergeben'));
        }

        $person = edit\person\Person::getPerson($this->app, $args->personId);

        $relationships = edit\relationship\Relationship::getRelationships($this->app, $person['sex']);

        $response = new edit\Rpc\ResponseCombo();
        $response->addRows($relationships);
        return $response;
    }


    /**
     * Gibt das Beziehungs Grid zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseGrid
     * @throws edit\ExceptionNotice
     */
    public function getRelationshipGrid(\stdClass $args): edit\Rpc\ResponseGrid {

        $personId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'personId') && $args->personId) {
            $personId = $args->personId;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $loader = new edit\GridLoader($this->app, $args, 'personRelationship');

        // Primary Key
        $loader->addPrimaryColumn('personRelationship.personRelationshipId', $this->app->getText('Beziehungs') . ' ' . $this->app->getText('ID'));

        $loader->addColumn($this->app->getText('Version'), 'personRelationship.version', ['visible' => false]);
        $loader->addColumn($this->app->getText('Bezugsperson'), 'secondPerson.name');
        $loader->addColumn($this->app->getText('Beziehungart'), 'secondPerson.description');
        $loader->addColumn($this->app->getText('Beziehungsart'), 'relationship.name', null, 'relationshipName');  // Umgekehrte Beziehung
        $loader->addColumn($this->app->getText('Alter Vater'), 'personRelationship.fatherAge');

        $loader->getQueryBuilderForSelect()->addSelectElement('personRelationship.personId');
        $loader->getCntQueryBuilderForSelect()->addSelectElement('personRelationship.personId');
                $loader->getQueryBuilderForSelect()->addSelectElement('personRelationship.secondPersonId');
        $loader->getCntQueryBuilderForSelect()->addSelectElement('personRelationship.secondPersonId');
                $loader->getQueryBuilderForSelect()->addSelectElement('relationship.relationshipId');
        $loader->getCntQueryBuilderForSelect()->addSelectElement('relationship.relationshipId');

        // Person auslesen
        $loader->addFromElement('INNER JOIN person AS secondPerson ON personRelationship.secondPersonId = secondPerson.personId');

        // Beziehung auslesen
        $loader->addFromElement('INNER JOIN relationship ON personRelationship.relationshipId = relationship.relationshipId');

        // Nur Einträge mit 'normalem' Staus laden
        $loader->addWhereElement('personRelationship.`state` < 100');

        // Nur die Beziehungen für diese Person laden
        $loader->addWhereElement('personRelationship.personId = :personId');
        $loader->getQueryBuilderForSelect()->addParam(':personId', $personId, \PDO::PARAM_INT);
        $loader->getCntQueryBuilderForSelect()->addParam(':personId', $personId, \PDO::PARAM_INT);

        // Wenn eine Version übergeben wurde, diese laden
        if ($version) {
            $loader->addWhereElement('personRelationship.version = :version');
            $loader->getQueryBuilderForSelect()->addParam(':version', $version, \PDO::PARAM_INT);
            $loader->getCntQueryBuilderForSelect()->addParam(':version', $version, \PDO::PARAM_INT);

        // Die neuste Version laden
        } else {
            $loader->addWhereElement('personRelationship.version = (
            SELECT
                MAX(version)
            FROM
                personRelationship AS personRelationshipVersion
            WHERE personRelationship.personId = personRelationshipVersion.personId)');
        }

        // Nur die neuste Version der Person laden
        $loader->addWhereElement('secondPerson.version = (
            SELECT
                MAX(version)
            FROM
                person AS personVersion
            WHERE personRelationship.secondPersonId = personVersion.personId)');

        $response = $loader->load();
        $response->addRows(edit\relationship\Relationship::getReverseRelationshipGridData($this->app, $personId, $version));
        return $response;
    }


    /**
     * Gibt die Quellen zurück
     * @param \stdClass $args
     * @return object
     * @throws edit\ExceptionNotice
     * @throws ExceptionNotice
     */
    public function getSources(\stdClass $args): object {

        $regionId = null;
        $version = null;
        $assignTable = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $regionId = $args->id;
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
        $category = edit\app\App::getCategoryByName($this->app, 'region');
        $sourceId = edit\source\Source::getSourceId($field, $regionId, $category, $assignTable);

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
            $tableName = 'region';
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $category = edit\app\App::getCategoryByName($this->app, $tableName);

            $formPacket['categoryId'] = $category['categoryId'];

            if ($formPacket['regionId']) {
                $formPacket['id'] = $formPacket['regionId'];
                $formPacket['oldVal_regionId'] = $formPacket['regionId'];
            }

            if ($formPacket['version']) {
                $formPacket['oldVal_version'] = $formPacket['version'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
            $save->save($formPacket);
            $regionId = (int)$save->getPrimaryKey()->value;
            $version = (int)$save->getVersion();
            unset ($save);

            $formPacket['regionId'] = $regionId;
            $formPacket['version'] = $version;

            // Beruf(e) in separater Tabelle speichern
            /*
            if ($formPacket['proficiency']) {
                $saveProfeciency = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), 'personProficiency');
                $saveProfeciency->save($formPacket);
                unset ($saveProfeciency);
            }

            // Beziehungen speichern wenn vorhaden
            if ($formPacket['relationships']) {
                $saveRelationship = edit\person\Person::saveRelationship($this->app, $formPacket);
            }

            // Gruppen speichern wenn vorhaden
            if ($formPacket['groups']) {
                $saveGroups = edit\person\Person::saveGroup($this->app, $formPacket);
            }

            // Namen speichern
            if ($formPacket['names']) {
                $saveNames = edit\person\Person::saveNames($this->app, $formPacket);
            }
            */

            // Quellen speichern wenn vorhaden
            if ($formPacket['sources']) {
                $saveSource = new edit\SaveSource($this->app, $category);
                $saveSource->save($formPacket);
                unset($saveSource);
            }

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $regionId;
            $response->version = $version;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}
