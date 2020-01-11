<?php

declare(strict_types = 1);

namespace biwi\edit;

use biwi\edit;

/**
 * Hilfsklasse zum Speichern der Sources. Hier werden alle verschiedenen Arten automatisch gespeichert
 */
class SaveSource{
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


    public function save(array &$formPacket): void {

        // Alle Quellen durchgehen
        foreach ($formPacket['sources'] as $field => $sourceType) {

            // Überprüfen ob Array sonst eins machen
            if (!is_array($sourceType)) {$sourceType = json_decode(json_encode($sourceType), true);}

            // Wenn Bibelquellen verfügbar sind
            if ($sourceType['bible'] && count($sourceType['bible'])) {

                // Transaktion starten
                $this->app->getDb()->beginTransaction();

                // Alle Einträge durchgehen
                foreach ($sourceType['bible'] as $entry) {

                    // stdClass in Array umwandeln
                    $entry = json_decode(json_encode($entry), true);

                    $entry['id'] = $formPacket['id'];
                    $entry['version'] = $formPacket['version'];

                    $result = self::_saveEntry($field, $entry, 'bibleSource');
                }

                // Transaktion beenden
                $this->app->getDb()->commit();
            }

            // Wenn Webquellen verfügbar sind
            if ($sourceType['web'] && count($sourceType['web'])) {

                // Transaktion starten
                $this->app->getDb()->beginTransaction();

                // Alle Einträge durchgehen
                foreach ($sourceType['web'] as $entry) {

                   // stdClass in Array umwandeln
                    $entry = json_decode(json_encode($entry), true);

                    $entry['id'] = $formPacket['id'];
                    $entry['version'] = $formPacket['version'];

                    $result = self::_saveEntry($field, $entry, 'webSource');
                }

                // Transaktion beenden
                $this->app->getDb()->commit();
            }

            // Wenn andere Quellen verfügbar sind
            if ($sourceType['other'] && count($sourceType['other'])) {

                // Transaktion starten
                $this->app->getDb()->beginTransaction();

                // Alle Einträge durchgehen
                foreach ($sourceType['other'] as $entry) {

                    // stdClass in Array umwandeln
                    $entry = json_decode(json_encode($entry), true);

                    $entry['id'] = $formPacket['id'];
                    $entry['version'] = $formPacket['version'];

                    $result = self::_saveEntry($field, $entry, 'otherSource');
                }

                // Transaktion beenden
                $this->app->getDb()->commit();
            }
        }
    }


    private function _saveEntry(string $field, array $formPacket, string $tableName): void {

        // Wenn eine ID übergeben wurde, diese als oldVal weitergeben
        if ($formPacket[$tableName . 'Id']) {
            $formPacket['oldVal_' . $tableName . 'Id'] = $formPacket[$tableName . 'Id'];
        }

        // Eindeutige ID erstellen wenn nicht vorhanden
        if ($formPacket['sourceId']) {
            $formPacket['oldVal_sourceId'] = $formPacket['sourceId'];
        } else {
            $formPacket['sourceId'] = edit\source\Source::getSourceId($field, $formPacket['id'], $this->category, $this->assignTableName);
        }

        // Einträge speichern
        $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
        $save->save($formPacket);
        unset ($save);
    }
}
