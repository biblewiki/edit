<?php
declare(strict_types = 1);

namespace biwi\edit\person;

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


    public function deleteRelationship(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $ids = \property_exists($args, 'selection') ? $args->selection : [];

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserType()) {
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
            $st = $this->app->getDb()->prepare('DELETE FROM personRelationship WHERE relationshipId = :relationshipId');

            foreach ($ids as $id) {
                $st->bindValue(':relationshipId', $id, \PDO::PARAM_INT);
                $st->execute();
            }
            unset ($st);

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
        if (!$this->app->getLoggedInUserType()) {
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
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $loader = new edit\GridLoader($this->app, $args, 'person');

        // Status
        $statusSql = '
            IF(person.state = 10, :status_10,
                IF(person.state = 20, :status_20,
                    IF(person.state = 30, :status_30,
                        IF(person.state = 40, :status_40, :status_ukn)
                    )
                )
            )';

        $loader->getQueryBuilderForSelect()->addParam(':status_10', $this->app->getText('Privater Entwurf'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_10', $this->app->getText('Privater Entwurf'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_20', $this->app->getText('Freigegebener Entwurf'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_20', $this->app->getText('Freigegebener Entwurf'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_30', $this->app->getText('Unveröffentlichter Eintrag'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_30', $this->app->getText('Unveröffentlichter Eintrag'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_40', $this->app->getText('Veröffentlichter Eintrag'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_40', $this->app->getText('Veröffentlichter Eintrag'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':status_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':status_ukn', $this->app->getText('Unbekannt'), \PDO::PARAM_STR);

        // Primary Keys
        $loader->addPrimaryColumn('person.personId', $this->app->getText('Person') . ' ' . $this->app->getText('ID'));
        $loader->addPrimaryColumn('person.version', $this->app->getText('Version'));

        $loader->addColumn($this->app->getText('Name'), 'person.name');
        $loader->addColumn($this->app->getText('Beschreibung'), 'person.description');
        $loader->addColumn($this->app->getText('Status'), $statusSql);
        $loader->addColumn($this->app->getText('Author'), 'person.createId');
        $loader->addColumn($this->app->getText('Zuletzt bearbeitet'), 'person.changeDate', ['width' => 120, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);

        // Nur die letzte Versionen laden
        $loader->addWhereElement('person.version = (SELECT
            MAX(version)
        FROM
            person AS personVersion
        WHERE person.personId = personVersion.personId)');

        return $loader->load();
    }

        /**
     * Gibt das Formular zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseForm
     */
    public function getFormData(\stdClass $args): edit\Rpc\ResponseForm {
        $personId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $personId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $row = [];
        if ($personId) {
            $qryBld = new edit\SqlSelector('person');
            $qryBld->addSelectElement('person.personId');
            $qryBld->addSelectElement('person.version');
            $qryBld->addSelectElement('person.name');
            $qryBld->addSelectElement('person.description');
            $qryBld->addSelectElement('person.sex');
            $qryBld->addSelectElement('person.believer');

            $qryBld->addWhereElement('person.personId = :personId');
            $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);

            // Wenn eine Version übergeben wurde, diese laden
            if ($version) {
                $qryBld->addWhereElement('person.version = :version');
                $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

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
            $row['personId'] = null;
            $row['version'] = null;
            $row['name'] = null;
            $row['description'] = null;
            $row['sex'] = null;
            $row['believer'] = null;
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

    public function getPersons(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $persons = edit\person\Person::getPersons($this->app, $args);

        $response = new edit\Rpc\ResponseCombo();
        $response->addRows($persons);
        return $response;
    }

    public function getRelationshipGrid(\stdClass $args): edit\Rpc\ResponseGrid {

        $personId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
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

        // Primary Keys
        $loader->addPrimaryColumn('personRelationship.relationshipId', $this->app->getText('Beziehung') . ' ' . $this->app->getText('ID'));

        $loader->addColumn($this->app->getText('Bezugsperson'), 'secondPerson.name');
        $loader->addColumn($this->app->getText('Beschreibung'), 'secondPerson.description');
        $loader->addColumn($this->app->getText('Beziehungsart'), 'relationship.name');
        $loader->addColumn($this->app->getText('Alter Vater'), 'personRelationship.fatherAge');

        $loader->addFromElement('INNER JOIN person AS secondPerson ON personRelationship.secondPersonId = secondPerson.personId');
        $loader->addFromElement('INNER JOIN relationship ON personRelationship.relationshipId = relationship.relationshipId');

        // Wenn eine Person übergeben wurde, nur die Beziehungen für diese Person laden
        if ($personId) {
            $loader->addWhereElement('personRelationship.personId = :personId');
            $loader->getQueryBuilderForSelect()->addParam(':personId', $personId, \PDO::PARAM_INT);
            $loader->getCntQueryBuilderForSelect()->addParam(':personId', $personId, \PDO::PARAM_INT);
        }

        // Wenn eine Version übergeben wurde, diese laden
        if ($version) {
            $loader->addWhereElement('personRelationship.version = :version');
            $loader->getQueryBuilderForSelect()->addParam(':version', $version, \PDO::PARAM_INT);
            $loader->getCntQueryBuilderForSelect()->addParam(':version', $version, \PDO::PARAM_INT);

        // Die neuste Version laden
        } else {
//            $loader->addWhereElement('personRelationship.version = (SELECT
//                MAX(version)
//            FROM
//                personRelationship AS personRelationshipVersion
//            WHERE personRelationship.personId = personRelationshipVersion.personId)');
        }

        // Nur die letzte Version laden
        $loader->addWhereElement('secondPerson.version = (SELECT
                    MAX(version)
                FROM
                    person AS personVersion
                WHERE personRelationship.secondPersonId = personVersion.personId)');

        $response = $loader->load();
        return $response;
    }


    public function getRelationships(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $relationships = edit\person\Person::getRelationships($this->app, $args);

        $response = new edit\Rpc\ResponseCombo();
        $response->addRows($relationships);
        return $response;
    }


    public function getSources(\stdClass $args): object {

        $personId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $personId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        // Überprüfen ob ein Feld übergeben wurde
        if (!property_exists($args, 'field') && $args->field) {
            throw new ExceptionNotice($this->app->getText('Es wurde kein Feld übergeben.'));
        }

        $field = $args->field;
        $category = edit\app\App::getCategoryByName($this->app, 'person');
        $sourceId = edit\source\Source::getSourceId($field, $personId, $category);

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
            $tableName = 'person';
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserType()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $category = edit\app\App::getCategoryByName($this->app, $tableName);

            $formPacket['categoryId'] = $category['categoryId'];

            if ($formPacket['personId']) {
                $formPacket['id'] = $formPacket['personId'];
                $formPacket['oldVal_personId'] = $formPacket['personId'];
            }

            if ($formPacket['version']) {
                $formPacket['oldVal_version'] = $formPacket['version'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
            $save->save($formPacket);
            $personId = (int)$save->getPrimaryKey()->value;
            $version = (int)$save->getVersion();
            unset ($save);

            // Quellen speichern wenn vorhaden
            if ($formPacket['sources']) {
                $formPacket['personId'] = $personId;
                $formPacket['version'] = $version;

                $saveSource = new edit\SaveSource($this->app, $category);
                $saveSource->save($formPacket);
                unset($saveSource);
            }

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $personId;
            $response->version = $version;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }


    /**
     * Speichert das Beziehungs-Formular
     *
     * @param \stdClass $args
     * @return edit\Rpc\ResponseDefault
     */
    public function saveRelationship(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserType()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $save = new edit\SaveData($this->app, $this->app->getSession()->userId, 'personRelationship');
            $save->save($formPacket);
            $personId = (int)$save->getPrimaryKey()->value;
            unset ($save);

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $personId;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}