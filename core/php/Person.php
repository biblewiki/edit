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
        
        // Join für nur die neuste Version zu laden
        $qryBld->addFromElement('INNER JOIN (
            SELECT
                personId,
                MAX(version) AS maxVersion
            FROM
                person
            GROUP BY
                personId) AS version ON person.personId = version.personId
	AND person.version = version.maxVersion');

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
            $row['changeId'] = $app->getUser($row['changeId']);
            
            // UNIX Timestamp in Datum umwandeln
            $row['changeDate'] = gmdate("d.m.Y H:i", $row['changeDate']);
        }

        // Rückgabe an JS
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
        
        // Join für nur die neuste Version zu laden
        $qryBld->addFromElement('INNER JOIN (
            SELECT
                personId,
                MAX(version) AS maxVersion
            FROM
                person
            GROUP BY
                personId) AS version ON person.personId = version.personId
	AND person.version = version.maxVersion');

        // SQL Abfrage ausführen
        $rows = $qryBld->execute($app->getDb());

        // Alle Zeilen durchgehen
        foreach($rows as &$row) {
            
            // User anhand von ID holen
            //$row['changeId'] = $app->getUser($row['changeId']);
            
            // UNIX Timestamp in Datum umwandeln
            //$row['changeDate'] = gmdate("d.m.Y H:i", $row['changeDate']);
        }

        // Rückgabe an JS
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
        
         // Join für nur die neuste Version zu laden
        $qryBld->addFromElement('INNER JOIN (
            SELECT
                personId,
                MAX(version) AS maxVersion
            FROM
                person
            GROUP BY
                personId) AS version ON person.personId = version.personId
	AND person.version = version.maxVersion');
        
        $qryBld->addWhereElement('person.personId = :id');
        $qryBld->addParam(':id', $id, \PDO::PARAM_INT);
        
        // SQL Abfrage ausführen
        $row = $qryBld->execute($app->getDb());
        
        //Rückgabe an JS
        return ['row' => $row[0]];
    }

    
    /**
     * Beziehungsarten für das Combo laden
     * 
     * @param App $app
     * @param object $args
     * @return array
     */
    public static function getRelationshipForCombo(App $app, object $args): array { 
        $json = file_get_contents("../json/relationship.json");
        $json = json_decode($json);
        
        $lang = $app->getLanguage();
        $rows = [];
        
        // Alle Zeilen durchgehen
        foreach($json->relationship->$lang as $value) {
            $rows[] = $value;
        }

        // Rückgabe an JS
        return ['rows' => $rows];
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

        // Evtl. Validation

        // Aktueller Timestamp erstellen
        $formPacket['openTS'] = date('Y-m-d H:i:s');
        
        // ToDo: Status richtig setzen, wenn neu oder existierend
        // Standardstatus setzen
        $formPacket['state'] = 10;

        // Transaktion starten
        $app->getDb()->beginTransaction();

        // Formular speichern
        $save = new SaveData($app, 1, 'person');
        $save->save($formPacket);

        // Transaktion beenden
        $app->getDb()->commit();
        $returnData['success'] = true;

        // Rückgabe an JS
        return $returnData;
    }
}