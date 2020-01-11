<?php
declare(strict_types = 1);

namespace biwi\edit\group;

use biwi\edit;

/**
 * Class Group
 */
class Group {

    /**
     * Gibt eine Gruppe zurück
     *
     * @param \biwi\edit\App $app
     * @param int $groupId
     * @return array
     * @throws edit\ExceptionNotice
     */
    public static function getGroup(edit\App $app, int $groupId): array {

        // Überprüfen ob einen ID übergeben wurde
        if (!$groupId) {
            throw new edit\ExceptionNotice($app->getText('Es wurde keine ID übergeben'));
        }

        // SQL
        $qryBld = new edit\SqlSelector('group');
        $qryBld->addSelectElement('group.groupId');
        $qryBld->addSelectElement('group.name');

        $qryBld->addWhereElement('group.groupId = :groupId');
        $qryBld->addParam(':groupId', $groupId, \PDO::PARAM_INT);

        // Nur die letzte Version laden
        $qryBld->addWhereElement('`group`.version = (SELECT
                    MAX(version)
                FROM
                    `group` AS groupVersion
                WHERE `group`.groupId = groupVersion.groupId)');

        $row = $qryBld->execute($app->getDb(), false);
        unset ($qryBld);

        return $row;
    }
}
