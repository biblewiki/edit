<?php
declare(strict_types = 1);

namespace biwi\edit\setting;

use biwi\edit;

/**
 * Class Setting
 *
 * @package ki\kgweb\kg\setting
 */
class Setting {

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Gibt den Wert einer Einstellung zurück.
     * ACHTUNG $app-getSetting bietet mehr Möglichkeiten und ist zu bevorzugen!
     *
     * @param ki\App $app
     * @param string $setting
     * @return array
     */
    public static function getSetting(edit\App $app, string $setting): array {
        $st = $app->getDb()->prepare('
            SELECT
                `setting`.`setting`,
                `setting`.`value`,
                `setting`.`caption`,
                `setting`.`description`,
                `setting`.`editable`,
                `setting`.`createId`,
                `setting`.`changeId`,
                UNIX_TIMESTAMP(`setting`.`createDate`) AS `createDate`,
                UNIX_TIMESTAMP(`setting`.`changeDate`) AS `changeDate`
            FROM
                `setting`
            WHERE
                `setting` = :setting
        ');
        $st->bindParam(':setting', $setting, \PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        unset($st);

        return \is_array($row) ? $row : [];
    }


    /**
     * Speichert den Wert einer Einstellung
     *
     * @param ki\App $app
     * @param string $setting
     * @param $value
     */
    public static function saveSetting(ki\App $app, string $setting, $value): void {
        $userId = $app->getSession()->userId;
        $timestamp = date('Y-m-d H:i:s');
        $tsSql = date('Y-m-d H:i:s', strtotime($timestamp));
        $st = $app->getDb()->prepare('
            UPDATE
                `setting`
            SET
                `value` = :value,
                `changeId` = :userId,
                `changeDate` = :changeDate
            WHERE
                `setting` = :setting
        ');
        $st->bindParam(':setting', $setting, \PDO::PARAM_STR);
        $st->bindParam(':value', $value, \PDO::PARAM_STR);
        $st->bindParam(':userId', $userId, \PDO::PARAM_STR);
        $st->bindParam(':changeDate', $tsSql, \PDO::PARAM_STR);
        $st->execute();
    }

}