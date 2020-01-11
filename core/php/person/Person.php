<?php
declare(strict_types = 1);

namespace biwi\edit\person;

use biwi\edit;

/**
 * Class Person
 */
class Person {

    /**
     * Gibt eine Person zurück
     *
     * @param \biwi\edit\App $app
     * @param int $personId
     * @return array
     * @throws edit\ExceptionNotice
     */
    public static function getPerson(edit\App $app, int $personId): array {

        // Überprüfen ob einen ID übergeben wurde
        if (!$personId) {
            throw new edit\ExceptionNotice($app->getText('Es wurde keine ID übergeben'));
        }

        // SQL
        $qryBld = new edit\SqlSelector('person');
        $qryBld->addSelectElement('person.personId');
        $qryBld->addSelectElement('person.name');
        $qryBld->addSelectElement('person.description');
        $qryBld->addSelectElement('person.sex');

        $qryBld->addWhereElement('person.personId = :personId');
        $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);

        // Nur die erste Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MIN(version)
            FROM
                person AS personVersion
            WHERE person.personId = personVersion.personId)');

        $row = $qryBld->execute($app->getDb(), false);
        unset ($qryBld);

        return $row;
    }


    /**
     * Gibt die Personen zurück
     *
     * @param \biwi\edit\App $app
     * @param \stdClass $args
     * @return array
     */
    public static function getPersons(edit\App $app, \stdClass $args): array {

        $personId = null;
        $onlyOthers = null;

        // Überprüfen ob einen ID übergeben wurde
        if (property_exists($args, 'personId') && $args->personId) {
            $personId = $args->personId;
        }

        // Überprüfen ob eine Person nicht übergeben werden darf
        if (property_exists($args, 'onlyOthers') && $args->onlyOthers) {
            $onlyOthers = $args->onlyOthers;
        }

        // SQL
        $qryBld = new edit\SqlSelector('person');
        $qryBld->addSelectElement('person.personId');
        $qryBld->addSelectElement('person.name');
        $qryBld->addSelectElement('person.sex');

        if ($personId) {

            // Nur alle anderen Personen laden
            if ($onlyOthers) {
                $qryBld->addWhereElement('person.personId != :personId');
                $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);

            // Gibt die Person zurück
            } else {
                $qryBld->addWhereElement('person.personId = :personId');
                $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);
            }
        }

        // Nur die erste Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MIN(version)
            FROM
                person AS personVersion
            WHERE person.personId = personVersion.personId)');

        $rows = $qryBld->execute($app->getDb());
        unset ($qryBld);

        return $rows;
    }


    /**
     * Speichert das Gruppen-Formular
     *
     * @param \stdClass $args
     * @return edit\Rpc\ResponseDefault
     */
    public function saveGroup(edit\App $app, array $formPacket): edit\Rpc\ResponseDefault {

        foreach($formPacket['groups'] as $group) {

            // stdClass in Array umwandeln
            $group = json_decode(json_encode($group), true);

            if ($group['personGroupId']) {
                $group['oldVal_personGroupId'] = $group['personGroupId'];
            }

            if ($group['version']) {
                $group['versionPerson'] = $group['version'];
            }

            $save = new edit\SaveData($app, $app->getLoggedInUserId(), 'personGroup');
            $save->save($group);
            $personRelationshipId = (int)$save->getPrimaryKey()->value;
            unset ($save);

            // Quellen speichern wenn vorhaden
            if ($group['sources']) {
                $group['id'] = $personRelationshipId;
                $category = edit\app\App::getCategoryByName($app, 'person');
                $saveSource = new edit\SaveSource($app, $category, 'personGroup');
                $saveSource->save($group);
                unset($saveSource);
            }
        }

        $response = new edit\Rpc\ResponseDefault();
        return $response;
    }


    /**
     * Speichert das Beziehungs-Formular
     *
     * @param \stdClass $args
     * @return edit\Rpc\ResponseDefault
     */
    public function saveRelationship(edit\App $app, array $formPacket): edit\Rpc\ResponseDefault {

        foreach($formPacket['relationships'] as $relationship) {

            // stdClass in Array umwandeln
            $relationship = json_decode(json_encode($relationship), true);

            if ($relationship['personRelationshipId']) {
                $relationship['oldVal_personRelationshipId'] = $relationship['personRelationshipId'];
            }

            $save = new edit\SaveData($app, $app->getLoggedInUserId(), 'personRelationship');
            $save->save($relationship);
            $personRelationshipId = (int)$save->getPrimaryKey()->value;
            unset ($save);

            // Quellen speichern wenn vorhaden
            if ($relationship['sources']) {
                $relationship['id'] = $personRelationshipId;
                $category = edit\app\App::getCategoryByName($app, 'person');
                $saveSource = new edit\SaveSource($app, $category, 'personRelationship');
                $saveSource->save($relationship);
                unset($saveSource);
            }
        }

        $response = new edit\Rpc\ResponseDefault();
        return $response;
    }
}
