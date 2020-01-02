<?php
declare(strict_types = 1);

namespace biwi\edit\source;

use biwi\edit;

/**
 * Class Person
 */
class Source {

    public static function getBibleSources(edit\App $app, string $sourceId, int $version): array {

        $qryBld = new edit\SqlSelector('bibleSource');
        $qryBld->addSelectElement('bibleSource.bibleSourceId');
        $qryBld->addSelectElement('bibleSource.sourceId');
        $qryBld->addSelectElement('bibleSource.version');
        $qryBld->addSelectElement('bibleSource.bookId');
        $qryBld->addSelectElement('bibleSource.chapterId');
        $qryBld->addSelectElement('bibleSource.verseId');

        $qryBld->addWhereElement('bibleSource.sourceId = :sourceId');
        $qryBld->addParam(':sourceId', $sourceId, \PDO::PARAM_STR);

        // Wenn eine Version übergeben wurde, diese laden
        if ($version) {
            $qryBld->addWhereElement('bibleSource.version = :version');
            $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

        // Die neuste Version laden
        } else {
            // Nur die letzte Version laden
            $qryBld->addWhereElement('bibleSource.version = (SELECT
                MAX(version)
            FROM
                bibleSource AS personVersion
            WHERE bibleSource.sourceId = personVersion.sourceId)');
        }

        $rows = $qryBld->execute($app->getDb());
        unset ($qryBld);

        return $rows;
    }


    public static function getSources(edit\App $app, string $sourceId, int $version): array {

        $return = [];
        $return['bible'] = self::getBibleSources($app, $sourceId, $version);
        $return['web'] = self::getWebSources($app, $sourceId, $version);

        return $return;
    }


    public static function getSourceId($field, $personId, $category): string {

        // Eindeutige ID erstellen bestehend aus: Kategorie ID _ Eintrag ID _ Feldname
        return $category['categoryId'] . '_' . $personId . '_' . $field;
    }

    public static function getWebSources(edit\App $app, string $sourceId, int $version): array {

        $qryBld = new edit\SqlSelector('webSource');
        $qryBld->addSelectElement('webSource.webSourceId');
        $qryBld->addSelectElement('webSource.sourceId');
        $qryBld->addSelectElement('webSource.version');
        $qryBld->addSelectElement('webSource.name');
        $qryBld->addSelectElement('webSource.description');
        $qryBld->addSelectElement('webSource.url');

        $qryBld->addWhereElement('webSource.sourceId = :sourceId');
        $qryBld->addParam(':sourceId', $sourceId, \PDO::PARAM_STR);

        // Wenn eine Version übergeben wurde, diese laden
        if ($version) {
            $qryBld->addWhereElement('webSource.version = :version');
            $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

        // Die neuste Version laden
        } else {
            // Nur die letzte Version laden
            $qryBld->addWhereElement('webSource.version = (SELECT
                MAX(version)
            FROM
                webSource AS personVersion
            WHERE webSource.sourceId = personVersion.sourceId)');
        }

        $rows = $qryBld->execute($app->getDb());
        unset ($qryBld);

        return $rows;
    }
}
