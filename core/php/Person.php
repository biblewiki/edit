<?php
declare(strict_types = 1);


class Person {
    public static function getGridData(App $app, object $args) { 
        
        $qryBld = new SqlSelector('person');
        $qryBld->addSelectElement('person.name as name');
        
        //$qryBld->addFromElement('LEFT JOIN produkt_zuweisung_text AS text_it ON produkt_zuweisung.produktZuweisungId = text_it.produktZuweisungId AND text_it.languageId = \'it\'');
        //$qryBld->addFromElement('INNER JOIN ausfuehrung_text ON ausfuehrung.ausfuehrungId = ausfuehrung_text.ausfuehrungId AND ausfuehrung_text.languageId = :languageId');
        
        $qryBld->addWhereElement('person.name IS NOT NULL');
        //$qryBld->addParam(':produktZuweisungId', $produktZuweisungId, \PDO::PARAM_INT);
            
        $rows = $qryBld->execute($app->getDb());
        return ['rows' => $rows];
    }

    public static function saveForm(App $app, object $args) { 

        $formPacket = $args->formPacket;
        //$formPacket['personId'] = 5;
        //$formPacket['oldVal_personId'] = 5;
        //$formPacket['oldVal_version'] = 5;
        //$formPacket['version'] = 5;
        $formPacket['openTS'] = date('Y-m-d H:i:s');
        $formPacket['state'] = 0;
        //$formPacket['beforeChristBirth'] = isset($_POST['beforeChristBirth']) ? 1 : 0;
        //$formPacket['beforeChristDeath'] = isset($_POST['beforeChristBirth']) ? 1 : 0;
        //$formPacket['beforeChristProfStart'] = isset($_POST['beforeChristBirth']) ? 1 : 0;
        //$formPacket['beforeChristProfEnd'] = isset($_POST['beforeChristBirth']) ? 1 : 0;
        //$formPacket['believer'] = isset($_POST['believer']) ? 1 : 0;

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