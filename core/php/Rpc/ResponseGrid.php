<?php
declare(strict_types = 1);

namespace biwi\edit\Rpc;

/**
 * Klasse für ein RPC-Response an ein Formular
 */
final class ResponseGrid extends ResponseBase {
    public $columns = [];
    public $rows = [];
    public $primaryKeys = [];
    public $sort = null;
    public $count = null;
    public $addonArgs = null;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * @param array|\stdClass $columns
     */
    public function addColumns($columns): void {
        if (!\is_array($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }
    }


    /**
     * @param array|\stdClass $primaryKeys
     */
    public function addPrimaryKeys($primaryKeys): void {
        if (!\is_array($primaryKeys)) {
            $primaryKeys = [$primaryKeys];
        }

        foreach ($primaryKeys as $primaryKey) {
            $this->primaryKeys[] = $primaryKey;
        }
    }


    /**
     * @param array|\stdClass $rows
     */
    public function addRows($rows): void {
        if (!\is_array($rows)) {
            $rows = [$rows];
        }

        foreach ($rows as $row) {
            $this->rows[] = $row;
        }
    }


    /**
     * overwrite: Werte für callback-Funktion aufbereiten
     * @return \stdClass
     */
    public function jsonSerialize(): \stdClass {
        $cbData = new \stdClass();
        if ($this->columns) {
            $cbData->columns = $this->columns;
        }
        if ($this->rows) {
            $cbData->rows = $this->rows;
        }
        if ($this->primaryKeys) {
            $cbData->primaryKeys = $this->primaryKeys;
        }
        if ($this->sort) {
            $cbData->sort = $this->sort;
        }
        if ($this->count) {
            $cbData->count = $this->count;
        }
        if ($this->addonArgs) {
            $cbData->addonArgs = $this->addonArgs;
        }
        return $cbData;
    }


    /**
     * @param string $field
     * @param string $direction
     */
    public function setSort(string $field, string $direction = 'ASC'): void {
        $direction = mb_strtoupper($direction);
        if (!\in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        $this->sort = new \stdClass();
        $this->sort->field = $field;
        $this->sort->direction = $direction;
    }

}
