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
        $json = file_get_contents("../json/relationship.json");
        $json = json_decode($json);
        
        $lang = $app->getLanguage();
        $rows = [];
        
        // Alle Zeilen durchgehen
        foreach($json->relationship->$lang as $key => $value) {
            $rows[] = ['relationshipId' => (int) $key, 'name' => $value];
        }

        // Rückgabe an Request Handler
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
      
        // SQL Abfrage vorbereiten
        $qryBld = new SqlSelector('personRelationship'); // Tabellenname mitgeben
        $qryBld->addSelectElement('personRelationship.personId');
        $qryBld->addSelectElement('personRelationship.version');
        $qryBld->addSelectElement('person.description');
        $qryBld->addSelectElement('personRelationship.secondPersonId');
        $qryBld->addSelectElement('personRelationship.relationshipId');
        $qryBld->addSelectElement('personRelationship.fatherAge');

        $qryBld->addFromElement('LEFT JOIN person ON personRelationship.personId = person.personId');
        
        // Nur die letzte Version laden
        $qryBld->addWhereElement('person.version = (SELECT
                MAX(version)
            FROM
                person AS personVersion
            WHERE personRelationship.secondPersonId = personVersion.personId)');
        
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

        // Rückgabe an Request Handler
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
        $gridDataRelationship = $args->gridDataRelationship;
        // Evtl. Validation

        // Aktueller Timestamp erstellen
        $formPacket['openTS'] = date('Y-m-d H:i:s');
        
        // ToDo: Status richtig setzen, wenn neu oder existierend
        // Standardstatus setzen
        $formPacket['state'] = 10;

        // Transaktion starten
        //$app->getDb()->beginTransaction();

        // Formular speichern
        $save = new SaveData($app, 1, 'person');
        $save->save($formPacket);
        $personId = (int)$save->getPrimaryKey()->value;
        $version = $save->getVersion();
        unset($save);
        
        // Transaktion beenden
        //$app->getDb()->commit();
        
        foreach($gridDataRelationship as $entry) {
            
            foreach($entry as $key => $value) {
                
                switch($key){
                    case 'personId':
                        $packet['oldPersonId'] = $value;
                    
                    default:
                    $packet[$key] = $value;
                }
            }

            $packet['version'] = $version;
            $packet['personId'] = $personId;
            
            // Formular speichern
            $gridSave = new SaveData($app, 1, 'personRelationship');
            $gridSave->save($packet);
            unset($gridSave);
        }
        
        
        
        
        $returnData['success'] = true;

        // Rückgabe an Request Handler
        return $returnData;
    }
}