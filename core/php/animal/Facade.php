<?php
declare(strict_types = 1);

namespace biwi\edit\animal;

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
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // Überprüfen ob einen ID übergeben wurde
        if (!property_exists($args, 'id') || !$args->id) {
            throw new edit\ExceptionNotice($this->app->getText('Es wurde keine ID übergeben.'));
        }

        $animalId = $args->id;

        // Get Create Daten
        $qryBld = new edit\SqlSelector('animal');
        $qryBld->addSelectElement('animal.createId');
        $qryBld->addSelectElement('animal.createDate');

        $qryBld->addWhereElement('animal.animalId = :animalId');
        $qryBld->addParam(':animalId', $animalId, \PDO::PARAM_INT);

        // Nur die erste Version laden
        $qryBld->addWhereElement('animal.version = (SELECT
                MIN(version)
            FROM
                animal AS animalVersion
            WHERE animal.animalId = animalVersion.animalId)');

        $createRow = $qryBld->execute($this->app->getDb(), false);
        unset ($qryBld);


        // Get Change Daten
        $qryBld = new edit\SqlSelector('animal');
        $qryBld->addSelectElement('animal.changeId');
        $qryBld->addSelectElement('animal.changeDate');

        $qryBld->addWhereElement('animal.animalId = :animalId');
        $qryBld->addParam(':animalId', $animalId, \PDO::PARAM_INT);

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

        $loader = new edit\GridLoader($this->app, $args, 'animal');

        // Status
        $statusSql = '
            IF(animal.state = 10, :status_10,
                IF(animal.state = 20, :status_20,
                    IF(animal.state = 30, :status_30,
                        IF(animal.state = 40, :status_40, :status_ukn)
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
        $loader->addPrimaryColumn('animal.animalId', $this->app->getText('Tier') . ' ' . $this->app->getText('ID'));
        $loader->addPrimaryColumn('animal.version', $this->app->getText('Version'));

        $loader->addColumn($this->app->getText('Name'), 'animal.animalSpecies');
        $loader->addColumn($this->app->getText('Beschreibung'), 'animal.description');
        $loader->addColumn($this->app->getText('Status'), $statusSql);
        $loader->addColumn($this->app->getText('Author'), 'animal.createId');
        $loader->addColumn($this->app->getText('Zuletzt bearbeitet'), 'animal.changeDate', ['width' => 120, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);

        $loader->addSort('animalSpecies');

        // Nur die letzte Versionen laden
        $loader->addWhereElement('animal.version = (SELECT
            MAX(version)
        FROM
        animal AS animalVersion
        WHERE animal.animalId = animalVersion.animalId)');

        return $loader->load();
    }

        /**
     * Gibt das Formular zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseForm
     */
    public function getFormData(\stdClass $args): edit\Rpc\ResponseForm {
        $animalId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $animalId = $args->id;
        }

        // Version auslesen wenn vorhanden
        if (property_exists($args, 'version') && $args->version) {
            $version = $args->version;
        }

        $row = [];
        if ($animalId) {
            $qryBld = new edit\SqlSelector('animal');
            $qryBld->addSelectElement('animal.animalId');
            $qryBld->addSelectElement('animal.version');
            $qryBld->addSelectElement('animal.animalSpecies');
            $qryBld->addSelectElement('animal.description');
            $qryBld->addSelectElement('animal.age');
            $qryBld->addSelectElement('animal.number');
            $qryBld->addSelectElement('animal.personId');

            $qryBld->addWhereElement('animal.animalId = :animalId');
            $qryBld->addParam(':animalId', $animalId, \PDO::PARAM_INT);

            // Wenn eine Version übergeben wurde, diese laden
            if ($version) {
                $qryBld->addWhereElement('animal.version = :version');
                $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

            // Die neuste Version laden
            } else {
                // Nur die letzte Version laden
                $qryBld->addWhereElement('animal.version = (SELECT
                    MAX(version)
                FROM
                animal AS animalVersion
                WHERE animal.animalId = animalVersion.animalId)');
            }


            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);

        } else {
            $row['animalId'] = null;
            $row['version'] = null;
            $row['animalSpecies'] = null;
            $row['description'] = null;
            $row['age'] = null;
            $row['number'] = null;
            $row['secondPersonId'] = null;
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

    public function getAnimals(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $animal = edit\animal\Animal::getAnimals($this->app, $args);

        $response = new edit\Rpc\ResponseCombo();
        $response->addRows($animal);
        return $response;
    }


    public function getSources(\stdClass $args): object {

        $animalId = null;
        $version = null;

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // ID auslesen wenn vorhanden
        if (property_exists($args, 'id') && $args->id) {
            $animalId = $args->id;
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
        $category = edit\app\App::getCategoryByName($this->app, 'animal');
        $sourceId = edit\source\Source::getSourceId($field, $animalId, $category);

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
            $tableName = 'animal';
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserType()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $category = edit\app\App::getCategoryByName($this->app, $tableName);

            $formPacket['categoryId'] = $category['categoryId'];

            if ($formPacket['animalId']) {
                $formPacket['id'] = $formPacket['animalId'];
                $formPacket['oldVal_animalId'] = $formPacket['animalId'];
            }

            if ($formPacket['version']) {
                $formPacket['oldVal_version'] = $formPacket['version'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
            $save->save($formPacket);
            $animalId = (int)$save->getPrimaryKey()->value;
            $version = (int)$save->getVersion();
            unset ($save);

            // Quellen speichern wenn vorhaden
            if ($formPacket['sources']) {
                $formPacket['animalId'] = $animalId;
                $formPacket['version'] = $version;

                $saveSource = new edit\SaveSource($this->app, $category);
                $saveSource->save($formPacket);
                unset($saveSource);
            }

            $response = new edit\Rpc\ResponseDefault();
            $response->id = $animalId;
            $response->version = $version;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }

}