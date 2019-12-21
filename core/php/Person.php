<?php
declare(strict_types = 1);

/**
 * Klasse Person
 * Enthält nur statische Funktionen
 */
class Person {

    /**
     * Daten für das Grid laden
     *
     * @param App $app
     * @param object $args
     * @return array
     */
    public static function getGridData(App $app, object $args): array {
        $filter = $args->filter;

        // SQL Abfrage vorbereiten
        $qryBld = new SqlSelector('person'); // Tabellenname mitgeben
        $qryBld->addSelectElement('person.personId');
        $qryBld->addSelectElement('person.version');
        $qryBld->addSelectElement('person.name');
        $qryBld->addSelectElement('person.description');
        $qryBld->addSelectElement('person.state');
        $qryBld->addSelectElement('person.changeId');
        $qryBld->addSelectElement('person.changeDate');

        // Nur die letzte Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MAX(version)
            FROM
                person AS personVersion
            WHERE person.personId = personVersion.personId)');

        // Filter anwenden
        foreach($filter as $column => $value ) {

            // Bei Checkboxen kommt nur Wert false und true
            if ($value === false || $value === true || is_int($value)) {
                $qryBld->addWhereElement("person.`$column` = '$value'");

            // Bei Strings suchen nach Inhalt
            } else if ($value != '') {
                $qryBld->addWhereElement("person.`$column` LIKE '%$value%'");
            }
        }

        // SQL Abfrage ausführen
        $rows = $qryBld->execute($app->getDb());

        // Alle Zeilen durchgehen
        foreach($rows as &$row) {

            // User anhand von ID holen
            $row['changeId'] = $app->getUserName($row['changeId']);

            // UNIX Timestamp in Datum umwandeln
            $row['changeDate'] = gmdate("d.m.Y H:i", $row['changeDate']);
        }

        // Rückgabe an Request Handler
        return ['rows' => $rows];
    }


    /**
     * Daten für das Combo laden
     *
     * @param App $app
     * @param object $args
     * @return array
     */
    public static function getForCombo(App $app, object $args): array {
        // SQL Abfrage vorbereiten
        $qryBld = new SqlSelector('person'); // Tabellenname mitgeben
        $qryBld->addSelectElement('person.personId');
        $qryBld->addSelectElement('person.name');

        // Nur die letzte Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MAX(version)
            FROM
                person AS personVersion
            WHERE person.personId = personVersion.personId)');

        // SQL Abfrage ausführen
        $rows = $qryBld->execute($app->getDb());

        // Rückgabe an Request Handler
        return ['rows' => $rows];
    }


    /**
     * Daten für das Formular laden
     *
     * @param App $app
     * @param object $args
     * @return array
     */
    public static function getFormData(App $app, object $args): array {
        $id = $args->id;

        // SQL Abfrage vorbereiten
        $qryBld = new SqlSelector('person');
        $qryBld->addSelectElement('*');  // ToDo: Nur die nötigen Einträge holen

        // Nur die letzte Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MAX(version)
            FROM
                person AS personVersion
            WHERE person.personId = personVersion.personId)');

        $qryBld->addWhereElement('person.personId = :id');
        $qryBld->addParam(':id', $id, \PDO::PARAM_INT);

        // SQL Abfrage ausführen
        $row = $qryBld->execute($app->getDb());

        //Rückgabe an Request Handler
        return ['row' => $row[0]];
    }


    /**
     * Beziehungsarten für das Combo laden
     *
     * @param App $app
     * @param object $args
     * @return array
     */
    public static function getRelationship(App $app, object $args): array {
        // SQL Abfrage vorbereiten
        $qryBld = new SqlSelector('relationship');
        $qryBld->addSelectElement('relationshipId');
        $qryBld->addSelectElement('name');

        // SQL Abfrage ausführen
        $rows = $qryBld->execute($app->getDb());

        //Rückgabe an Request Handler
        return ['rows' => $rows];
    }


    /**
     * Daten für das Beziehungs-Grid laden
     *
     * @param App $app
     * @param object $args
     * @return array
     */
    public static function getRelationshipGridData(App $app, object $args): array {
        $filter = $args->filter;
        $id = $args->id;

        $rows = [];

        if ($id) {
            // "Noramale" Beziehungen holen

            // SQL Abfrage vorbereiten
            $qryBld = new SqlSelector('personRelationship'); // Tabellenname mitgeben
            $qryBld->addSelectElement('personRelationship.personId');
            //$qryBld->addSelectElement('personRelationship.personId AS old_personId');
            $qryBld->addSelectElement('personRelationship.version');
            $qryBld->addSelectElement('person.description');
            $qryBld->addSelectElement('personRelationship.secondPersonId');
            //$qryBld->addSelectElement('personRelationship.secondPersonId AS old_secondPersonId');
            $qryBld->addSelectElement('personRelationship.relationshipId');
            //$qryBld->addSelectElement('personRelationship.relationshipId AS old_relationshipId');
            $qryBld->addSelectElement('personRelationship.fatherAge');

            $qryBld->addFromElement('LEFT JOIN person ON personRelationship.secondPersonId = person.personId');

            // Nur die letzte Version laden
            $qryBld->addWhereElement('personRelationship.version = (SELECT
                    MAX(version)
                FROM
                    personRelationship AS personRelationshipVersion
                WHERE personRelationship.personId = personRelationshipVersion.personId)');
            $qryBld->addWhereElement('person.version = (SELECT
                    MAX(version)
                FROM
                    person AS personVersion
                WHERE personRelationship.secondPersonId = personVersion.personId)');


            $qryBld->addWhereElement("personRelationship.personId = '$id'");


            // Filter anwenden
            foreach($filter as $column => $value ) {

                if ($column === 'description' && $value != ''){
                    $qryBld->addWhereElement("person.`$column` LIKE '%$value%'");
                }
                // Bei Checkboxen kommt nur Wert false und true
                else if (is_int($value) && $value !== 0) {
                    $qryBld->addWhereElement("personRelationship.`$column` = '$value'");

                // Bei Strings suchen nach Inhalt
                } else if ($value != '') {
                    $qryBld->addWhereElement("personRelationship.`$column` LIKE '%$value%'");
                }
            }

            // SQL Abfrage ausführen
            $rows = $qryBld->execute($app->getDb());

            $reverseRows = self::_getReverseRelationshipGridData($app, $args);

            $rows = array_merge($rows, $reverseRows);
        }

        // Rückgabe an Request Handler
        return ['rows' => $rows];
    }


    private static function _getReverseRelationshipGridData(App $app, object $args): array {
        $filter = $args->filter;
        $id = $args->id;

        // Umgekehrte Beziehungen holen

        // SQL Abfrage vorbereiten
        $qryBld = new SqlSelector('personRelationship'); // Tabellenname mitgeben
        $qryBld->addSelectElement('personRelationship.personId');
        //$qryBld->addSelectElement('personRelationship.personId AS old_personId');
        $qryBld->addSelectElement('personRelationship.version');
        $qryBld->addSelectElement('person.description');
        $qryBld->addSelectElement('person.sex');
        $qryBld->addSelectElement('personRelationship.secondPersonId');
        //$qryBld->addSelectElement('personRelationship.secondPersonId AS old_secondPersonId');
        $qryBld->addSelectElement('relationship.reverseMRelationshipId');
        $qryBld->addSelectElement('relationship.reverseWRelationshipId');
        //$qryBld->addSelectElement('personRelationship.relationshipId AS old_relationshipId');
        $qryBld->addSelectElement('personRelationship.fatherAge');

        $qryBld->addFromElement('LEFT JOIN person ON personRelationship.personId = person.personId');
        $qryBld->addFromElement('LEFT JOIN relationship ON personRelationship.relationshipId = relationship.relationshipId');

        // Nur die letzte Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MAX(version)
            FROM
                person AS personVersion
            WHERE personRelationship.personId = personVersion.personId)');

        $qryBld->addWhereElement("personRelationship.secondPersonId = '$id'");

        // Filter anwenden
        foreach($filter as $column => $value ) {

            if ($column === 'description' && $value != ''){
                $qryBld->addWhereElement("person.`$column` LIKE '%$value%'");
            }
            // Bei Checkboxen kommt nur Wert false und true
            else if (is_int($value) && $value !== 0) {
                $qryBld->addWhereElement("personRelationship.`$column` = '$value'");

            // Bei Strings suchen nach Inhalt
            } else if ($value != '') {
                $qryBld->addWhereElement("personRelationship.`$column` LIKE '%$value%'");
            }
        }

        // SQL Abfrage ausführen
        $rows = $qryBld->execute($app->getDb());

        foreach($rows as &$row) {
            $row['reverse'] = true;

            // Für Anzeige im Grid verdrehen
            $personId = $row['personId'];
            $secondPersonId = $row['secondPersonId'];

            $row['personId'] = $secondPersonId;
            $row['secondPersonId'] = $personId;

            if ($row['sex'] === 'm') {
                $row['relationshipId'] = $row['reverseMRelationshipId'];
            } else if ($row['sex'] === 'w') {
                $row['relationshipId'] = $row['reverseWRelationshipId'];
            }
        }

        // Rückgabe an aufrufende Funktion
        return $rows;
    }


    /**
     * Formular in DB speichern
     *
     * @param App $app
     * @param object $args
     * @return array
     */
    public static function saveForm(App $app, object $args): array {
        $formPacket = $args->formPacket;
        $gridDataRelationship = $args->gridDataRelationship;
        // Evtl. Validation

        // Aktueller Timestamp erstellen
        $formPacket['openTS'] = date('Y-m-d H:i:s');

        // ToDo: Status richtig setzen, wenn neu oder existierend
        // Standardstatus setzen
        $formPacket['state'] = 10;

        //Transaktion starten
        //$app->getDb()->beginTransaction();

        // Formular speichern
        $save = new SaveData($app, $app->getUserId(), 'person');
        $save->save($formPacket);
        $personId = (int)$save->getPrimaryKey()->value;
        $version = $save->getVersion();
        unset($save);

        $params = new stdClass;
        $params->id = $formPacket['personId'];

        // Alle Beziehungen von dieser Person löschen
//        $app->getDb()->clearParams();
//        $st = $app->getDb()->prepare('
//            DELETE FROM personRelationship
//            WHERE personId = :personId OR
//            secondPersonId = :personId
//            ');
//
//        $st->bindValue(':personId', $formPacket['personId'], \PDO::PARAM_INT);
//        $st->execute();
//        unset ($st);


        //Beziehungen eintragen
        foreach($gridDataRelationship as $entry) {
            // Object in Array umwandeln
            $entry = (array) $entry;

            $entry['version'] = $version;
            $entry['personId'] = $personId;

            // Formular speichern
            $gridSave = new SaveData($app, 1, 'personRelationship');
            $gridSave->save($entry);
            unset($gridSave);
            unset($entry);
        }

        // Transaktion beenden
        //$app->getDb()->commit();

        $returnData['success'] = true;

        // Rückgabe an Request Handler
        return $returnData;
    }
}
