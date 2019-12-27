<?php
declare(strict_types = 1);

namespace biwi\edit;

class GridLoader {
    protected $app;
    protected $tableName;
    protected $getMetaData;
    protected $sort = [];
    protected $start;
    protected $limit;

    protected $primaryKeys = [];
    protected $columns = [];
    protected $querys = [];
    protected $filters = [];

    /**
     * QueryBuilder um das Grid zu füllen
     * @var QueryBuilderForSelect
     */
    protected $sqlSelector;

    /**
     * QueryBuilder um die Anzahl der Datensätze zu ermitteln
     * @var QueryBuilderForSelect
     */
    protected $sqlSelectorCnt;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     *
     * @param App $app
     * @param \stdClass $gridArgs
     * @param string $tableName
     * @throws \Exception
     */
    public function __construct(App $app, \stdClass $gridArgs, string $tableName) {
        $this->app = $app;

        // Validation der Argumente (Schutz vor SQL-Injection)
        $gridArgs->getMetaData = (bool)$gridArgs->getMetaData;  // Ist wahr, beim ersten Aufruf oder beim Wechseln der Ansicht

        if (!$tableName || !\preg_match("/^[A-Za-z0-9._\-ÄÖÜäöüÀÉÈàéèâÂ]+$/u", $tableName)) {
            throw new \Exception('Ungültiger Tabellenname!');
        }

        // Argumente
        $this->tableName = $tableName;

        if (property_exists($gridArgs, 'sort')) {
            $sort = \is_array($gridArgs->sort) ? $gridArgs->sort : [$gridArgs->sort];
            $this->sort = \array_merge($this->sort, $sort);
        }
        $this->getMetaData = property_exists($gridArgs, 'getMetaData') ? (bool)$gridArgs->getMetaData : false;
        $this->start = property_exists($gridArgs, 'start') ? (int)$gridArgs->start : null;
        $this->limit = property_exists($gridArgs, 'limit') ? (int)$gridArgs->limit : (int)$app->getSetting('pageSize');

        $this->filters = property_exists($gridArgs, 'filter') && is_array($gridArgs->filter) ? $gridArgs->filter : [];

        // QueryBuilder Initialisieren
        //$this->sqlSelector = new QueryBuilderForSelect();
        //$this->sqlSelectorCnt = new QueryBuilderForSelect();

        //        $this->sqlSelector->addFromElement('`' . $tableName . '`');
        //$this->sqlSelectorCnt->addFromElement('`' . $tableName . '`');

        // Limit setzen
        //if ($this->start !== null && $this->limit !== null) {
        //    $this->sqlSelector->setLimit($this->start, $this->limit);
        //}

        // Suche
        //if (\property_exists($gridArgs, 'search') && \is_string($gridArgs->search)) {
        //    $this->search($gridArgs->search);
        //}

        $sqlSelector = new SqlSelector($tableName);
        $sqlSelectorCnt = new SqlSelector($tableName);

        $sqlSelector->addFromElement('`' . $tableName . '`');
        $sqlSelectorCnt->addFromElement('`' . $tableName . '`');

        // Limit setzen
        if ($this->start !== null && $this->limit !== null) {
            $sqlSelector->setLimit($this->start, $this->limit);
        }

        $this->sqlSelector = $sqlSelector;
        $this->sqlSelectorCnt = $sqlSelectorCnt;

        // Suche
        if (\property_exists($gridArgs, 'search') && \is_string($gridArgs->search)) {
            $this->search($gridArgs->search);
        }
    }


    /**
     * Fügt eine Spalte hinzu, welche defaultmässig ausgeblendet ist.
     * @param string $sqlFieldName Der primary der Tabelle: tabelle.column
     * @param string $caption Die Bezeichnung der Spalte
     * @param array $columnCnf
     * @param string|null $columnName
     * @return void
     */
    public function addPrimaryColumn(string $sqlFieldName, string $caption='ID', ?array $columnCnf=null, ?string $columnName=null): void {
        $this->primaryKeys[] = $this->getColumNameFromSql($sqlFieldName, $caption, 0);

        // Default: Nicht sichtbar
        if (!\is_array($columnCnf)) {
            $columnCnf = ['visible' => false, 'xtype' => 'kijs.gui.grid.columnConfig.Number'];
        } elseif (!\array_key_exists('visible', $columnCnf)) {
            $columnCnf['visible'] = false;
        }

        $this->addColumn($caption, $sqlFieldName, $columnCnf, $columnName);
    }


    /**
     * Fügt eine column hinzu
     * @param string $caption
     * @param string $sqlSelect
     * @param array|null $columnCnf
     * @param string|null $columnName
     * @return void
     */
    public function addColumn(string $caption, string $sqlSelect, ?array $columnCnf=null, ?string $columnName=null): void {
        $id = \count($this->columns);
        $colName = $columnName ?: $this->getColumNameFromSql($sqlSelect, $caption, $id);

        $this->sqlSelector->addSelectElement('(' . $sqlSelect . ') AS `' . $colName . '`');
        $this->sqlSelectorCnt->addSelectElement('(' . $sqlSelect . ') AS `' . $colName . '`');

        $column = new \stdClass();
        $column->caption = $caption;
        $column->valueField = $colName;

        // weitere configs übernehmen
        if (\is_array($columnCnf)) {
            foreach ($columnCnf as $key => $val) {
                $column->{$key} = $val;
            }
        }

        $this->columns[] = $column;
        $this->querys[] = $sqlSelect;
    }


    /**
     * Fügt dem query eine From-Table hinzu
     * @param string $sql
     * @return void
     */
    public function addFromElement(string $sql): void {
        $this->sqlSelector->addFromElement($sql);
        $this->sqlSelectorCnt->addFromElement($sql);
    }


    public function addOrderByElement(string $sql): void {
        $this->sqlSelector->addOrderByElement($sql);
    }


    /**
     * Fügt ein default-Sortwert hinzu
     * @param string $fieldName
     * @param string $direction
     * @return void
     */
    public function addSort(string $fieldName, string $direction='ASC'): void {
        $sort = new \stdClass();
        $sort->field = $fieldName;
        $sort->direction = mb_strtoupper($direction);
        $this->sort[] = $sort;
    }


    /**
     * Fügt dem Query ein where hinzu
     * @param string $sql
     * @return void
     */
    public function addWhereElement(string $sql): void {
        $this->sqlSelector->addWhereElement($sql);
        $this->sqlSelectorCnt->addWhereElement($sql);
    }


    /**
     * @return QueryBuilderForSelect
     */
    public function getQueryBuilderForSelect(): QueryBuilderForSelect {
        return $this->sqlSelector->getQueryBuilderForSelect();
    }


    /**
     * @return QueryBuilderForSelect
     */
    public function getCntQueryBuilderForSelect(): QueryBuilderForSelect {
        return $this->sqlSelectorCnt->getQueryBuilderForSelect();
    }


    public function load(): Rpc\ResponseGrid {
        $sortInfo = null;

        // Filter
        $this->setFilters();

        // Sortierung
        foreach ($this->sort as $sort) {
            if (isset($sort->field) && \is_string($sort->field)) {
                $qry = $this->getSqlByColumnName($sort->field);
                if ($qry) {
                    $dir = \property_exists($sort, 'direction') && \in_array(\mb_strtoupper($sort->direction), ['ASC', 'DESC']) ? \mb_strtoupper($sort->direction) : 'ASC';
                    $this->sqlSelector->addOrderByElement('(' . $qry . ') ' . $dir);

                    if (!$sortInfo) {
                        $sortInfo = ['field' => $sort->field, 'direction' => $dir];
                    }
                }
            }
        }

        // Anzahl DS ermitteln
        $sql = $this->sqlSelectorCnt->getQueryBuilderForSelect()->getSql();
        $sql = "SELECT COUNT(*) As ki_RecordsCount1 FROM ({$sql}) As ki_RecordsCount2";
        $st = $this->app->getDb()->prepare($sql);
        $this->sqlSelectorCnt->getQueryBuilderForSelect()->bindUsedParams($st, $sql);
        $st->execute();
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        $results = (int)$row['ki_RecordsCount1'];
        unset($st);

        // Daten laden
        $rows = $this->sqlSelector->execute($this->app->getDb());

        // ResponseGrid response
        $response = new Rpc\ResponseGrid();
        $response->addRows($rows);
        $response->addPrimaryKeys($this->primaryKeys);
        if ($this->getMetaData) {
            $response->addColumns($this->columns);
        }

        $response->count = $results;
        $response->sort = $sortInfo;

        return $response;
    }


    /**
     * Sucht einen Wert in allen Feldern
     * @param string $searchValue
     */
    public function search(string $searchValue): void {
        $whereA = [];
        $whereB = [];
        $whereC = [];
        $whereD = [];

        $maskedSearchValue = \str_replace(['%', '_'], ['\%', '\_'], $searchValue);
        $this->sqlSelector->addParam(':searchQuery_A', $searchValue, \PDO::PARAM_STR);
        $this->sqlSelectorCnt->addParam(':searchQuery_A', $searchValue, \PDO::PARAM_STR);
        $this->sqlSelector->addParam(':searchQuery_B', $maskedSearchValue . '%', \PDO::PARAM_STR);
        $this->sqlSelectorCnt->addParam(':searchQuery_B', $maskedSearchValue . '%', \PDO::PARAM_STR);
        $this->sqlSelector->addParam(':searchQuery_C', '%' . $maskedSearchValue . '%', \PDO::PARAM_STR);
        $this->sqlSelectorCnt->addParam(':searchQuery_C', '%' . $maskedSearchValue . '%', \PDO::PARAM_STR);

        foreach ($this->querys as $query) {

            // Exakte Übereinstimmung
            $whereA[] = 'BINARY (' . $query . ') = BINARY :searchQuery_A';

            // Etwas Übereinstimmung
            $whereB[] = '(' . $query . ') = :searchQuery_A';

            // Beginn Übereinstimmung
            $whereC[] = '(' . $query . ') LIKE :searchQuery_B';

            // irgend Übereinstimmung
            $whereD[] = '(' . $query . ') LIKE :searchQuery_C';
        }

        // Sortieren nach art der Übereinstimmung
        $this->sqlSelector->addOrderByElement('IF(' . implode(' OR ', $whereA) . ', 1, 0) DESC');
        $this->sqlSelector->addOrderByElement('IF(' . implode(' OR ', $whereB) . ', 1, 0) DESC');
        $this->sqlSelector->addOrderByElement('IF(' . implode(' OR ', $whereC) . ', 1, 0) DESC');
        $this->sqlSelector->addOrderByElement('IF(' . implode(' OR ', $whereD) . ', 1, 0) DESC');

        // Where hinzufügen
        $this->sqlSelector->addWhereElement(implode(' OR ', array_merge($whereA, $whereB, $whereC, $whereD)));
        $this->sqlSelectorCnt->addWhereElement(implode(' OR ', array_merge($whereA, $whereB, $whereC, $whereD)));
    }


    // -------------------------------------------------------------------
    // Protected Functions
    // -------------------------------------------------------------------

    /**
     *
     * @param string $sql
     * @param string $caption
     * @param int $id
     * @return string
     */
    protected function getColumNameFromSql(string $sql, string $caption, int $id): string {
        $matches = [];
        $fldName = '';
        if (\preg_match('/^(?:[a-z0-9_`]+\.){0,1}([a-z0-9_]+)$/i', $sql, $matches)) {
            $fldName = $matches[1];
        } else {
            $fldName = \preg_replace('/[^a-z0-9]/', '_', \mb_strtolower($caption));
        }
        if (!$fldName) {
            $fldName = 'columnValue_' . $id;
        }

        foreach ($this->columns as $column) {
            if ($column->valueField === $fldName) {
                $fldName .= '_' . $id;
                break;
            }
        }

        return $fldName;
    }


    /**
     * Gibt das Query zu einer column zurück
     * @param string $name
     * @return string|null
     */
    protected function getSqlByColumnName(string $name): ?string {
        $id = 0;
        foreach ($this->columns as $column) {
            if ($column->valueField === $name) {
                break;
            }
            $id++;
        }

        return $this->querys[$id] ?? null;
    }


    protected function setFilters(): void {
        $filterId = 0;

        foreach ($this->filters as $filter) {
            //array(1) (
            //  [0] => stdClass object {
            //    type => (string) text
            //    valueField => (string) name
            //    search => (string) Test
            //    compare => (string) part
            //  }
            //)

            if (\is_object($filter) && \property_exists($filter, 'type') && \property_exists($filter, 'valueField')) {
                switch ($filter->type) {
                    case 'text': $this->filterText($filter, $filterId); break;
                    case 'number': $this->filterNumber($filter, $filterId); break;
                    case 'date': $this->filterDate($filter, $filterId); break;
//                    case 'checkbox': $this->filterCheckbox($filter, $filterId); break;
                }
            }

            $filterId++;
        }
    }


    protected function filterText(\stdClass $filter, int $filterId): void {
        $sql = $this->getSqlByColumnName($filter->valueField);
        if ($sql && \is_string($filter->search) && \trim($filter->search) !== '') {

            if ($filter->compare === 'begin') {

                // Textanfang
                $this->sqlSelector->addWhereElement('(' . $sql . ') LIKE :filter_' . $filterId);
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') LIKE :filter_' . $filterId);

                $this->sqlSelector->addParam(':filter_' . $filterId, $this->quoteSql(\trim($filter->search)) . '%', \PDO::PARAM_STR);
                $this->sqlSelectorCnt->addParam(':filter_' . $filterId, $this->quoteSql(\trim($filter->search)) . '%', \PDO::PARAM_STR);

            } elseif ($filter->compare === 'part') {

                // Beliebiger Teil
                $this->sqlSelector->addWhereElement('(' . $sql . ') LIKE :filter_' . $filterId);
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') LIKE :filter_' . $filterId);

                $this->sqlSelector->addParam(':filter_' . $filterId, '%' . $this->quoteSql(\trim($filter->search)) . '%', \PDO::PARAM_STR);
                $this->sqlSelectorCnt->addParam(':filter_' . $filterId, '%' . $this->quoteSql(\trim($filter->search)) . '%', \PDO::PARAM_STR);

            } elseif ($filter->compare === 'full') {

                // Vollständige Übereinstimmung
                $this->sqlSelector->addWhereElement('(' . $sql . ') = :filter_' . $filterId);
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') = :filter_' . $filterId);

                $this->sqlSelector->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);
                $this->sqlSelectorCnt->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);
            }
        }
    }


    protected function filterCheckbox(\stdClass $filter, int $filterId) : void {
        $sql = $this->getSqlByColumnName($filter->valueField);
        if ($sql && \is_string($filter->checkbox)) {

            // Ausgewählt
            if ($filter->checkbox === 'checked') {
                $this->sqlSelector->addWhereElement('(' . $sql . ') = 1');
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') = 1');

            // Nicht Ausgewählt
            } elseif ($filter->checkbox === 'unchecked') {
                $this->sqlSelector->addWhereElement('(' . $sql . ') = 0');
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') = 0');
            }
        }
    }


    protected function filterDate(\stdClass $filter, int $filterId) : void {
        $sql = $this->getSqlByColumnName($filter->valueField);
        if ($sql && \is_string($filter->search) && \trim($filter->search) !== '') {
            $date = Utilities::parseDate($filter->search);

            if ($date !== null) {
                $filter->search = date('Y-m-d', $date);
                $this->filterNumber($filter, $filterId);
            }
        }
    }


    protected function filterNumber(\stdClass $filter, int $filterId) : void {
        $sql = $this->getSqlByColumnName($filter->valueField);
        if ($sql && \is_string($filter->search) && \trim($filter->search) !== '') {

            if ($filter->compare === 'equal') {

                // Gleich
                $this->sqlSelector->addWhereElement('(' . $sql . ') = :filter_' . $filterId);
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') = :filter_' . $filterId);

                $this->sqlSelector->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);
                $this->sqlSelectorCnt->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);

            } elseif ($filter->compare === 'unequal') {

                // Ungleich
                $this->sqlSelector->addWhereElement('(' . $sql . ') <> :filter_' . $filterId);
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') <> :filter_' . $filterId);

                $this->sqlSelector->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);
                $this->sqlSelectorCnt->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);

            } elseif ($filter->compare === 'smaller') {

                // Kleiner
                $this->sqlSelector->addWhereElement('(' . $sql . ') < :filter_' . $filterId);
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') < :filter_' . $filterId);

                $this->sqlSelector->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);
                $this->sqlSelectorCnt->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);

            // Grösser
            } elseif ($filter->compare === 'bigger') {
                $this->sqlSelector->addWhereElement('(' . $sql . ') > :filter_' . $filterId);
                $this->sqlSelectorCnt->addWhereElement('(' . $sql . ') > :filter_' . $filterId);

                $this->sqlSelector->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);
                $this->sqlSelectorCnt->addParam(':filter_' . $filterId, \trim($filter->search), \PDO::PARAM_STR);
            }
        }
    }


    /**
     * Maskiert Zeichen, welche in LIKE als Platzhalter dienen.
     * @param string $str
     * @return string
     */
    protected function quoteSql(string $str) : string {
        return str_replace(['%', '_'], ['\%', '\_'], $str);
    }

}
