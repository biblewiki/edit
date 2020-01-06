<?php
declare(strict_types = 1);

namespace biwi\edit\relationship;

use biwi\edit;

/**
 * Class Relationship
 */
class Relationship {


    /**
     * Gibt die Beziehungsarten zurück
     *
     * @param \biwi\edit\App $app
     * @param int|null $sex
     * @return array
     */
    public static function getRelationships(edit\App $app, ?int $sex = null): array {

        // SQL
        $qryBld = new edit\SqlSelector('relationship');
        $qryBld->addSelectElement('relationship.relationshipId');
        $qryBld->addSelectElement('relationship.name');

        if ($sex && $sex !== 3) {
            $qryBld->addWhereElement('relationship.sex = :sex');
            $qryBld->addParam(':sex', $sex);
        }

        $rows = $qryBld->execute($app->getDb());
        unset ($qryBld);

        return $rows;
    }


    /**
     * Umgekehrte Beziehungen holen
     *
     * @param \biwi\edit\App $app
     * @param int $personId
     * @param int $version
     * @return array
     */
    public static function getReverseRelationshipGridData(edit\App $app, ?int $personId = null, ?int $version = null): array {

        // SQL Abfrage vorbereiten
        $qryBld = new edit\SqlSelector('personRelationship');
        $qryBld->addSelectElement('personRelationship.personRelationshipId');
        $qryBld->addSelectElement('personRelationship.personId AS secondPersonId');
        $qryBld->addSelectElement('personRelationship.version');
        $qryBld->addSelectElement('person.name');
        $qryBld->addSelectElement('person.description');
        $qryBld->addSelectElement('person.sex');
        $qryBld->addSelectElement('personRelationship.secondPersonId AS personId');
        $qryBld->addSelectElement('returnRelationship.relationshipId');
        $qryBld->addSelectElement('returnRelationship.name AS relationshipName');
        $qryBld->addSelectElement('personRelationship.fatherAge');

        // Person auslesen
        $qryBld->addFromElement('INNER JOIN person ON personRelationship.personId = person.personId');

        // Beziehung auslesen
        $qryBld->addFromElement('INNER JOIN relationship ON personRelationship.relationshipId = relationship.relationshipId');

        // Umgekehrte Beziehung auslesen, da die Beziehung umgedreht dargestellt wird
        $qryBld->addFromElement('INNER JOIN relationship AS returnRelationship ON (person.sex = 2 AND returnRelationship.relationshipId = relationship.returnWRelationshipId) '
                . 'OR (person.sex <> 2 AND returnRelationship.relationshipId = relationship.returnMRelationshipId)');

        // Wenn eine Person übergeben wurde, nur die Bezihungen von dieser Person laden
        if ($personId) {
            $qryBld->addWhereElement('personRelationship.secondPersonId = :personId');
            $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);
        }

        // Wenn eine Version mitgegeben wurde, diese laden
        if ($version) {
            $qryBld->addWhereElement('person.version = :version');
            $qryBld->addParam(':version', $version);

        // Nur die letzte Version laden
        } else {
        $qryBld->addWhereElement('person.version = (SELECT
                MAX(version)
            FROM
                person AS personVersion
            WHERE personRelationship.personId = personVersion.personId)');
        }

        // SQL Abfrage ausführen
        $rows = $qryBld->execute($app->getDb());

        // Rückgabe an aufrufende Funktion
        return $rows;
    }
}
