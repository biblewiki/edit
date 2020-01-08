<?php

declare(strict_types = 1);

namespace biwi\edit;

use biwi\edit;

/**
 * Hilfsklasse zum Löschen der Sources. Hier werden alle verschiedenen Arte automatisch gelöscht
 */
class DeleteSource{
    /**
     * @param edit\App $app
     */
    private $app;
    /**
     * @var array
     */
    private $category;
    /**
     * @var string
     */
    private $assignTableName;


    // -------------------------------------------------------
    // Public Methods
    // -------------------------------------------------------

    /**
     *
     * @param \biwi\edit\App $app
     * @param array $category
     * @param string|null $assignTableName  // Zuweisungstabellenname für eine eindeutige Identifizierung des Quellenfeldes
     */
    public function __construct(edit\App $app, array $category, ?string $assignTableName = null) {
        $this->app = $app;
        $this->category = $category;
        $this->assignTableName = $assignTableName;
    }


    public function delete(array $ids): void {

        $table = $this->assignTableName ?: $this->category['tableName'];

        foreach ($ids as $id) {
            $columns = self::_getColumnNames($table);

            foreach ($columns as $column) {
                $sourceId = edit\source\Source::getSourceId($column, $id, $this->category, $this->assignTableName);

                self::_deleteSource($sourceId, 'bibleSource');
                self::_deleteSource($sourceId, 'webSource');
                self::_deleteSource($sourceId, 'otherSource');
            }
        }
    }

    // Private
    private function _getColumnNames(string $table): array {

        // SQL vorbereiten
        $st = $this->app->getDb()->query('SHOW COLUMNS FROM `'.$table . '`');
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
        unset($st);

        $names = [];

        foreach ($rows as $row) {
            $names[] = $row['Field'];
        }

        return $names;
    }


    private function _deleteSource(string $sourceId, string $table): void {

        // Quellen aus DB löschen
        $st = $this->app->getDb()->prepare("DELETE FROM $table WHERE sourceId = :sourceId");
        $st->bindParam(':sourceId', $sourceId);
        $st->execute();
        unset ($st);
    }
}
