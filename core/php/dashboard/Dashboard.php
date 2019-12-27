<?php
declare(strict_types = 1);

namespace biwi\edit\dashboard;

use biwi\edit;

/**
 * Class Dashboard
 *
 * @package edit\kgweb\kg\dashboard
 */
class Dashboard {


    /**
     * Speichert die Werte der Kacheln
     * @param edit\App $app
     * @param array $portlets
     * @return void
     */
    public static function updatePortletState(edit\App $app, array $portlets): void {
        // Alle Löschen
        $st = $app->getDb()->prepare('DELETE FROM dashboardPortlet WHERE userId = :userId');
        $st->bindParam(':userId', $app->getSession()->userId, \PDO::PARAM_STR);
        $st->execute();

        // Aktuelle eintragen
        $st = $app->getDb()->prepare('INSERT INTO `dashboardPortlet` (`userId`, `portlet`, `visible`, `sort`) VALUES (:userId, :portlet, :visible, :sort)');
        $st->bindParam(':userId', $app->getSession()->userId, \PDO::PARAM_STR);

        foreach ($portlets as $portlet) {
            if ($portlet instanceof \stdClass) {
                $st->bindParam(':portlet', $portlet->portlet, \PDO::PARAM_STR);
                $st->bindParam(':visible', $portlet->visible, \PDO::PARAM_INT);
                $st->bindParam(':sort', $portlet->sort, \PDO::PARAM_INT);
                $st->execute();
            }
        }
        unset ($st);
    }

    /**
     * Gibt die aktuellen Werte der Kacheln zurück
     * @param edit\App $app
     * @return array
     */
    public static function getPortletState(edit\App $app): array {
        $sqlSelector = new edit\SqlSelector('dashboardPortlet');
        $sqlSelector->addWhereElement('userId = :userId');
        $sqlSelector->addParam(':userId', $app->getSession()->userId);
        $sqlSelector->addOrderByElement('`sort` ASC');
        $rows = $sqlSelector->execute($app->getDb());
        unset ($sqlSelector);

        return $rows;
    }
}
