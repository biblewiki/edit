<?php
declare(strict_types = 1);

namespace ki\kgweb\ki\Rpc;

/**
 * Klasse für ein RPC-Response an ein Kombinationsfeld
 */
final class ResponseCombo extends ResponseBase {
    public $rows = [];
    public $spinboxMessage;

    // -----------------
    // Public
    // -----------------

    /**
     * Fügt eine row zum Combo hinzu.
     * @param array $rows eine Row oder ein array von Rows
     */
    public function addRows(array $rows): void {
        // falls rows nur eine row ist, ist es nicht zweidimensional und wird erweitert
        $rows = $this->uniDimensionalToTwoDimensional($rows);

        foreach ($rows as $row) {
            $this->rows[] = $row;
        }
    }

    /**
     * Fügt eine Nachricht hinzu, die ganz unten in der Spinbox angezeigt wird.
     * @param string $message
     * @return void
     */
    public function addSpinboxMessage(string $message): void {
        $this->spinboxMessage = $message;
    }

    // -----------------
    // Protected
    // -----------------

    /**
     * overwrite: Werte für callback-Funktion aufbereiten
     * @return \stdClass
     */
    public function jsonSerialize(): \stdClass {
        $cbData = new \stdClass();
        $cbData->rows = $this->rows;

        if (\is_string($this->spinboxMessage)) {
            $cbData->spinboxMessage = $this->spinboxMessage;
        }

        return $cbData;
    }

    // -----------------
    // Private
    // -----------------

    /**
     * Macht aus einem eindimensionalem Array ein zweidimensionales Array.
     * @param array $rows
     * @return array
     */
    private function uniDimensionalToTwoDimensional(array $rows): array {
        if (\count($rows) > 0 && !\is_array($rows[\array_keys($rows)[0]])) {
            return [$rows];
        }
        return $rows;
    }

}
