<?php
declare(strict_types = 1);

namespace biwi\edit\source;

use biwi\edit;

/**
 * Class Person
 */
class Source {

    public static function getBibleSources(edit\App $app, string $sourceId, ?int $version = null): array {

        $qryBld = new edit\SqlSelector('bibleSource');
        $qryBld->addSelectElement('bibleSource.bibleSourceId');
        $qryBld->addSelectElement('bibleSource.sourceId');
        $qryBld->addSelectElement('bibleSource.version');
        $qryBld->addSelectElement('bibleSource.bookId');
        $qryBld->addSelectElement('bibleSource.chapterId');
        $qryBld->addSelectElement('bibleSource.verseId');

        $qryBld->addWhereElement('bibleSource.state < 100');
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

        foreach ($rows as &$row) {
            $row['openTS'] = date('Y-m-d H:i:s');
        }

        return $rows;
    }


    public static function getOtherSources(edit\App $app, string $sourceId, ?int $version = null): array {

        $qryBld = new edit\SqlSelector('otherSource');
        $qryBld->addSelectElement('otherSource.otherSourceId');
        $qryBld->addSelectElement('otherSource.sourceId');
        $qryBld->addSelectElement('otherSource.version');
        $qryBld->addSelectElement('otherSource.title');
        $qryBld->addSelectElement('otherSource.name');
        $qryBld->addSelectElement('otherSource.description');
        $qryBld->addSelectElement('otherSource.type');
        $qryBld->addSelectElement('otherSource.workName');
        $qryBld->addSelectElement('otherSource.medium');
        $qryBld->addSelectElement('otherSource.number');
        $qryBld->addSelectElement('otherSource.edition');
        $qryBld->addSelectElement('otherSource.locality');
        $qryBld->addSelectElement('otherSource.publishCompany');
        $qryBld->addSelectElement('otherSource.publishDate');
        $qryBld->addSelectElement('otherSource.language');
        $qryBld->addSelectElement('otherSource.isbnDoiIssn');
        $qryBld->addSelectElement('otherSource.url');
        $qryBld->addSelectElement('otherSource.downloadDate');
        $qryBld->addSelectElement('otherSource.rights');
        $qryBld->addSelectElement('otherSource.extra');

        $qryBld->addWhereElement('otherSource.state < 100');
        $qryBld->addWhereElement('otherSource.sourceId = :sourceId');
        $qryBld->addParam(':sourceId', $sourceId, \PDO::PARAM_STR);

        // Wenn eine Version übergeben wurde, diese laden
        if ($version) {
            $qryBld->addWhereElement('otherSource.version = :version');
            $qryBld->addParam(':version', $version, \PDO::PARAM_INT);

        // Die neuste Version laden
        } else {
            // Nur die letzte Version laden
            $qryBld->addWhereElement('otherSource.version = (SELECT
                MAX(version)
            FROM
                otherSource AS personVersion
            WHERE otherSource.sourceId = personVersion.sourceId)');
        }

        $rows = $qryBld->execute($app->getDb());
        unset ($qryBld);

        foreach ($rows as &$row) {
            $row['openTS'] = date('Y-m-d H:i:s');
        }

        return $rows;
    }


    public static function getSources(edit\App $app, string $sourceId, ?int $version = null): object {

        $return = new \stdClass;
        $return->bible = self::getBibleSources($app, $sourceId, $version);
        $return->web = self::getWebSources($app, $sourceId, $version);
        $return->other = self::getOtherSources($app, $sourceId, $version);

        return $return;
    }


    public static function getSourceId(string $field, int $entryId, array $category, ?string $assignTableName = null): string {

        // Eindeutige ID erstellen bestehend aus: Kategorie ID _ Eintrag ID _ Feldname
        $sourceId = $category['categoryId'] . '_' . $entryId . '_' . $field;

        if ($assignTableName) {
            $sourceId .= '_' . $assignTableName;
        }

        return $sourceId;
    }

    public static function getWebSources(edit\App $app, string $sourceId, ?int $version = null): array {

        $qryBld = new edit\SqlSelector('webSource');
        $qryBld->addSelectElement('webSource.webSourceId');
        $qryBld->addSelectElement('webSource.sourceId');
        $qryBld->addSelectElement('webSource.version');
        $qryBld->addSelectElement('webSource.name');
        $qryBld->addSelectElement('webSource.description');
        $qryBld->addSelectElement('webSource.url');

        $qryBld->addWhereElement('webSource.state < 100');
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

        foreach ($rows as &$row) {
            $row['openTS'] = date('Y-m-d H:i:s');
        }

        return $rows;
    }
}
