<?php
declare(strict_types = 1);


class Person {
    public static function getGridData(App $app, object $args): array { 
        
        $filter = $args->filter;
        
        $qryBld = new SqlSelector('person');
        $qryBld->addSelectElement('person.personId');
        $qryBld->addSelectElement('person.version');
        $qryBld->addSelectElement('person.name as name');
        $qryBld->addSelectElement('person.believer');
        
        
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
                $qryBld->addWhereElement("person.`$column` LIKE '$value%'");
            }
        }
        
        
        
        $rows = $qryBld->execute($app->getDb());
        
//        $st = $this->app->getDb()->prepare('
//            SELECT (
//                SELECT COUNT(*)
//                FROM `produkt_zuweisung`
//                WHERE `produkt_zuweisung`.`produktId` = :produktId
//                AND `produkt_zuweisung`.`status` IN (20, 40)
//            ) AS `usageZuweisung`,
//            (
//                SELECT COUNT(*)
//                FROM `produkt2preisRezept`
//                WHERE `produkt2preisRezept`.`produktId` = :produktId
//            ) `usageRezept`');
//        $st->bindParam(':produktId', $produktId, \PDO::PARAM_INT);
//        $st->execute();
//        $row = $st->fetch(\PDO::FETCH_ASSOC);
//        $usageZuweisung = (int)$row['usageZuweisung'];
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