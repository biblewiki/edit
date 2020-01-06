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
            throw new edit\ExceptionNotice($this->app->getText('Es wurde keine ID übergeben'));
        }

        // SQL
        $qryBld = new edit\SqlSelector('person');
        $qryBld->addSelectElement('person.personId');
        $qryBld->addSelectElement('person.name');
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
}
