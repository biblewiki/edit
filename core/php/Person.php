<?php
declare(strict_types = 1);


class Person {
    public static function getGridData(App $app, object $args): array { 
        
        $filter = $args->filter;
        
        $qryBld = new SqlSelector('person');
        $qryBld->addSelectElement('person.personId');
        $qryBld->addSelectElement('person.version');
        $qryBld->addSelectElement('person.name');
        $qryBld->addSelectElement('person.description');
        $qryBld->addSelectElement('person.state');
        $qryBld->addSelectElement('person.changeId');
        $qryBld->addSelectElement('person.changeDate');
        
        //$qryBld->addFromElement('LEFT JOIN produkt_zuweisung_text AS text_it ON produkt_zuweisung.produktZuweisungId = text_it.produktZuweisungId AND text_it.languageId = \'it\'');
        $qryBld->addFromElement('INNER JOIN (
            SELECT
                personId,
                MAX(version) AS maxVersion
            FROM
                person
            GROUP BY
                personId) as version ON person.personId = version.personId
	AND person.version = version.maxVersion');

        
        //$qryBld->addWhereElement('person.name IS NOT NULL');
        //$qryBld->addParam(':produktZuweisungId', $produktZuweisungId, \PDO::PARAM_INT);
            
        foreach($filter as $column => $value ) {
            
            if ($value === false || $value === true) {
                $qryBld->addWhereElement("person.`$column` = '$value'");
            } else if ($value != '') {
                $qryBld->addWhereElement("person.`$column` LIKE '%$value%'");
            }
        }
        
        $rows = $qryBld->execute($app->getDb());
        
        foreach($rows as &$row) {
            $row['author'] = $app->getUser($row['changeId']);
            $row['changeDate'] = gmdate("d.m.Y H:i", $row['changeDate']);
        }
        

        return ['rows' => $rows];
    }


    public static function getFormData(App $app, object $args): array { 
        $id = $args->id;
        
        $qryBld = new SqlSelector('person');
        $qryBld->addSelectElement('*');
        $qryBld->addSelectElement('MAX(version) as version');
        
        //$qryBld->addFromElement('LEFT JOIN produkt_zuweisung_text AS text_it ON produkt_zuweisung.produktZuweisungId = text_it.produktZuweisungId AND text_it.languageId = \'it\'');
        //$qryBld->addFromElement('INNER JOIN ausfuehrung_text ON ausfuehrung.ausfuehrungId = ausfuehrung_text.ausfuehrungId AND ausfuehrung_text.languageId = :languageId');
        
        $qryBld->addWhereElement('person.personId = :id');
        $qryBld->addParam(':id', $id, \PDO::PARAM_INT);
            
        $row = $qryBld->execute($app->getDb());
        return ['row' => $row[0]];
    }


    public static function saveForm(App $app, object $args): array { 

        $formPacket = $args->formPacket;

        // Evtl. Validation

        $formPacket['openTS'] = date('Y-m-d H:i:s');
        $formPacket['state'] = 0;

        // Transaktion starten
        $app->getDb()->beginTransaction();

        // Formular speichern
        $save = new SaveData($app, 1, 'person');
        $save->save($formPacket);

        // Transaktion beenden
        $app->getDb()->commit();
        $returnData['success'] = true;

        return $returnData;
    }
}