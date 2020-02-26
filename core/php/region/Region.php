<?php
declare(strict_types = 1);

namespace biwi\edit\region;

use biwi\edit;

/**
 * Class Region
 */
class Region {

    /**
     * Gibt eine Region zurück
     *
     * @param \biwi\edit\App $app
     * @param int $regionId
     * @return array
     * @throws edit\ExceptionNotice
     */
    public static function getRegion(edit\App $app, int $regionId): array {

        // Überprüfen ob einen ID übergeben wurde
        if (!$regionId) {
            throw new edit\ExceptionNotice($app->getText('Es wurde keine ID übergeben'));
        }

        // SQL
        $qryBld = new edit\SqlSelector('region');
        $qryBld->addSelectElement('region.regionId');
        $qryBld->addSelectElement('region.name');
        $qryBld->addSelectElement('region.text');
        $qryBld->addSelectElement('region.dayFounding');

        $qryBld->addWhereElement('region.regionId = :regionId');
        $qryBld->addParam(':regionId', $regionId, \PDO::PARAM_INT);

        // Nur die erste Version laden
        $qryBld->addWhereElement('region.version = (SELECT
                MIN(version)
            FROM
                region AS regionVersion
            WHERE region.regionId = regionVersion.regionId)');

        $row = $qryBld->execute($app->getDb(), false);
        unset ($qryBld);

        return $row;
    }


    /**
     * Gibt die Regionen zurück
     *
     * @param \biwi\edit\App $app
     * @param \stdClass $args
     * @return array
     */
    public static function getRegions(edit\App $app, \stdClass $args): array {

        $regionId = null;
        $onlyOthers = null;

        // Überprüfen ob einen ID übergeben wurde
        if (property_exists($args, 'regionId') && $args->regionId) {
            $regionId = $args->regionId;
        }

        // Überprüfen ob eine Region nicht übergeben werden darf
        if (property_exists($args, 'onlyOthers') && $args->onlyOthers) {
            $onlyOthers = $args->onlyOthers;
        }

        // SQL
        $qryBld = new edit\SqlSelector('region');
        $qryBld->addSelectElement('region.regionId');
        $qryBld->addSelectElement('region.name');
        $qryBld->addSelectElement('region.text');
        $qryBld->addSelectElement('region.dayFounding');

        if ($regionId) {

            // Nur alle anderen Regionen laden
            if ($onlyOthers) {
                $qryBld->addWhereElement('region.regionId != :regionId');
                $qryBld->addParam(':regionId', $regionId, \PDO::PARAM_INT);

            // Gibt die Region zurück
            } else {
                $qryBld->addWhereElement('region.regionId = :regionId');
                $qryBld->addParam(':regionId', $regionId, \PDO::PARAM_INT);
            }
        }

        // Nur die erste Version laden
        $qryBld->addWhereElement('region.version = (SELECT
                MIN(version)
            FROM
                region AS regionVersion
            WHERE region.regionId = regionVersion.regionId)');

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
    public static function saveGroup(edit\App $app, array $formPacket): edit\Rpc\ResponseDefault {

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

                // Wenn keine Version angegeben wurde
                if (!$group['version']) {

                    // Alle Einträge durchgehen
                    foreach ($group as $key => $entry) {

                        // Wenn in einem Key das Wort Version vorkommt, dieses in den Eintrag "Version" schreiben
                        if (strpos($key, 'version') !== false) {
                            $group['version'] = $entry;
                        }
                    }
                }

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
    public static function saveNames(edit\App $app, array $formPacket): edit\Rpc\ResponseDefault {

        foreach($formPacket['names'] as $name) {

            // stdClass in Array umwandeln
            $name = json_decode(json_encode($name), true);

            // Personen ID und Version übernehmen
            $name['personId'] = $formPacket['personId'];
            $name['version'] = $formPacket['version'];

            if ($name['personNameId']) {
                $name['oldVal_personNameId'] = $name['personNameId'];
            }

            $save = new edit\SaveData($app, $app->getLoggedInUserId(), 'personName');
            $save->save($name);
            $nameId = (int)$save->getPrimaryKey()->value;
            unset ($save);

            // Quellen speichern wenn vorhaden
            if ($name['sources']) {
                $name['id'] = $nameId;
                $category = edit\app\App::getCategoryByName($app, 'person');
                $saveSource = new edit\SaveSource($app, $category, 'personName');
                $saveSource->save($name);
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
    public static function saveRelationship(edit\App $app, array $formPacket): edit\Rpc\ResponseDefault {

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
