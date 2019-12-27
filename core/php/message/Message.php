<?php
declare(strict_types = 1);

namespace biwi\edit\message;

use biwi\edit;

/**
 * Class Mitteilung
 *
 * @package edit\mitteilung
 */
class Message {

    public static function getActiveMessage(edit\App $app): array {
        $qryBld = new edit\SqlSelector('message');
        $qryBld->addSelectElement('message.text');

        // sort
        $qryBld->addOrderByElement('dateFrom ASC');

        // where
        // Nur Mitteilungen die in diesem Zeitraum angezeigt werden sollen
        $qryBld->addWhereElement('message.dateFrom < NOW() AND (message.dateTo >  NOW() OR message.dateTo IS NULL)');

        $rows = $qryBld->execute($app->getDb());
        unset ($qryBld);

        return $rows;
    }
}
