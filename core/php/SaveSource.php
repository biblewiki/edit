<?php

declare(strict_types = 1);

namespace biwi\edit;

use biwi\edit;

/**
 * Hilfsklasse zum Speichern der x_text Tabellen. Die Daten in unterschiedlichen
 * Sprachen müssen im formPacket mit _<code> vorhanden sein. Bsp:
 * (lieferantId => 1, name_de => suissetec, name_fr => suissetec, name_it => suissetec)
 * Die Klasse erstellt danach 3 Zeilen. Bestehende Rows werden überschrieben.
 */
class SaveSource{
    /**
     * @param edit\App $app
     */
    private $app;
    /**
     * @var string
     */
    private $category;
    /**
     * @var string
     */
    private $userId;


    // -------------------------------------------------------
    // Public Methods
    // -------------------------------------------------------
    public function __construct(edit\App $app, array $category) {
        $this->app = $app;
        $this->category = $category;
        $this->userId = $userId;
    }


    public function save(array &$formPacket): void {

        // Alle Quellen durchgehen
        foreach ($formPacket['sources'] as $field => $sourceType) {

            // Wenn Bibelquellen verfügbar sind
            if (property_exists($sourceType, 'bible') && $sourceType->bible) {

                // Transaktion starten
                $this->app->getDb()->beginTransaction();

                // Alle Einträge durchgehen
                foreach ($sourceType->bible as $entry) {

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
            if (property_exists($sourceType, 'web') && $sourceType->web) {

                // Transaktion starten
                $this->app->getDb()->beginTransaction();

                // Alle Einträge durchgehen
                foreach ($sourceType->web as $entry) {

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
            if (property_exists($sourceType, 'other') && $sourceType->other) {

                // Transaktion starten
                $this->app->getDb()->beginTransaction();

                // Alle Einträge durchgehen
                foreach ($sourceType->other as $entry) {

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
            $formPacket['sourceId'] = edit\source\Source::getSourceId($field, $formPacket['id'], $this->category);
        }

        // Einträge speichern
        $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), $tableName);
        $save->save($formPacket);
        unset ($save);
    }
}
