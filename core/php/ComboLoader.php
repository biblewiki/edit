<?php
declare(strict_types = 1);

namespace biwi\edit;

class ComboLoader {
    protected $app;
    protected $tableName;
    protected $valueParam;
    protected $captionParam;

    protected $limit = null;
    protected $searchQuery;
    protected $currentSelectedValue;
    protected $sqlSearchQuerys = [];
    protected $valueSql;
    protected $captionSql;
    protected $castValueToInt = false;


    /**
     * QueryBuilder um das Grid zu füllen
     * @var QueryBuilderForSelect
     */
    protected $qryBld;


    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     *
     * @param \ki\kgweb\ki\App $app
     * @param \stdClass $comboArgs
     * @param string $tableName
     * @param string $valueParam
     * @param string $captionParam
     * @throws \Exception
     */
    public function __construct(App $app, \stdClass $comboArgs, string $tableName, string $valueParam='value', string $captionParam='caption') {
        $this->app = $app;

        if (!$tableName || !\preg_match("/^[A-Za-z0-9._\-ÄÖÜäöüÀÉÈàéèâÂ]+$/u", $tableName)) {
            throw new \Exception('Ungültiger Tabellenname!');
        }
        if (!$valueParam || !\preg_match("/^[A-Za-z0-9._\-ÄÖÜäöüÀÉÈàéèâÂ]+$/u", $valueParam)) {
            throw new \Exception('Ungültiger valueParam!');
        }
        if (!$captionParam || !\preg_match("/^[A-Za-z0-9._\-ÄÖÜäöüÀÉÈàéèâÂ]+$/u", $captionParam)) {
            throw new \Exception('Ungültiger captionParam!');
        }

        $this->tableName = $tableName;
        $this->valueParam = $valueParam;
        $this->captionParam = $captionParam;

        $this->qryBld = new QueryBuilderForSelect();
        $this->qryBld->addFromElement("`$tableName`");

        // remoteSort
        if (\property_exists($comboArgs, 'remoteSort') && $comboArgs->remoteSort === true) {
            $this->limit = 250;

            // Suche
            if (\property_exists($comboArgs, 'query') && \is_string($comboArgs->query) && $comboArgs->query !== '') {
                $this->searchQuery = $comboArgs->query;
            }

            // ausgewählter Wert
            if (\property_exists($comboArgs, 'value') && \is_string($comboArgs->value) && $comboArgs->value !== '') {
                $this->currentSelectedValue = (is_int($comboArgs->value)  || \strval(\intval($comboArgs->value)) === $comboArgs->value) ? (int) $comboArgs->value : $comboArgs->value;
            }
        }
    }

    /**
     * Fügt eine Suchabfrage hinzu (Where)
     * mehrere Suchen werden mit OR verbunden.
     * @param string $sql
     * @param string|null $paramName
     * @param type $paramValue
     * @param int $paramType
     */
    public function addSqlSearchQuery(string $sql, ?string $paramName=null, $paramValue=null, int $paramType=\PDO::PARAM_STR) {
        $this->sqlSearchQuerys[] = $sql;

        if ($paramName) {
            $this->qryBld->addParam($paramName, $paramValue, $paramType);
        }
    }


    /**
     * Führt die Abfrage aus.
     * @return Rpc\ResponseCombo
     */
    public function execute(): Rpc\ResponseCombo {
        // Suche
        if ($this->searchQuery) {
            $this->addSqlSearchQuery(
                    '(' . $this->captionSql . ') LIKE :searchQuery',
                    ':searchQuery',
                    $this->searchQuery . '%',
                    \PDO::PARAM_STR
                );
        }

        // aktueller Wert
        if ($this->currentSelectedValue !== null) {
            $this->addSqlSearchQuery(
                    '(' . $this->valueSql . ') = :currentSelectedValue',
                    ':currentSelectedValue',
                    $this->currentSelectedValue,
                    \is_int($this->currentSelectedValue) ? \PDO::PARAM_INT : \PDO::PARAM_STR
                );
        }

        // Suchen mit OR verknüpfen
        if ($this->sqlSearchQuerys) {
            $this->qryBld->addWhereElement('(' . implode(') OR (', $this->sqlSearchQuerys) . ')');
        }

        // aktueller Wert zuerst
        if ($this->currentSelectedValue !== null) {
            $this->qryBld->addOrderByElement('IF((' . $this->valueSql . ') = :curSelValOrd, 1, 2) ASC');
            $this->qryBld->addParam(':curSelValOrd', $this->currentSelectedValue, is_int($this->currentSelectedValue) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }

        if (\is_int($this->limit) && $this->limit > 0) {
            $this->qryBld->setLimit(0, $this->limit+1);
        }

        $st = $this->app->getDb()->prepare($this->qryBld->getSql());
        $this->qryBld->bindParams($st);
        $st->execute();
        $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
        unset ($st);

        $msg = '';

        // grösser limit?
        if (\is_int($this->limit) && $this->limit > 0 && \count($rows) > $this->limit) {
            \array_pop($rows);
            $msg = $this->app->getText('Es sind mehr als %1 Datensätze verfügbar.', '', $this->limit);
            $msg .= ' ' . $this->app->getText('Geben Sie einen genaueren Suchbegriff ein.');
        }

        // value zu int casten
        if ($this->castValueToInt) {
            foreach ($rows as &$row) {
                if (array_key_exists($this->valueParam, $row)) {
                    $row[$this->valueParam] = (int) $row[$this->valueParam];
                }
            }
            unset ($row);
        }

        // Rückgabe: Rpc Combo
        $response = new Rpc\ResponseCombo();
        $response->rows = $rows;
        if ($msg) {
            $response->addSpinboxMessage($msg);
        }

        return $response;
    }

    /**
     * Gibt den QueryBuilderForSelect zurück
     * @return QueryBuilderForSelect
     */
    public function getQueryBuilder(): QueryBuilderForSelect {
        return $this->qryBld;
    }

    /**
     * Setzt das caption SQL query
     * @param string $sql
     * @return void
     * @throws \Exception
     */
    public function setCaptionSql(string $sql): void {
        if ($this->captionSql !== null) {
            throw new \Exception('captionSql bereits gesetzt');
        }

        $this->captionSql = $sql;
        $this->qryBld->addSelectElement('(' . $sql . ') AS `' . $this->captionParam . '`');
    }

    /**
     * Setzt das Limit. Null = unbeschränkt
     * @param int|null $limit
     * @return void
     */
    public function setLimit(?int $limit): void {
        $this->limit = $limit;
    }


    /**
     * Setzt das value SQL query
     * @param string $sql
     * @param bool $castValueToInt value zu int casten?
     * @return void
     * @throws \Exception
     */
    public function setValueSql(string $sql, bool $castValueToInt=false): void {
        if ($this->valueSql !== null) {
            throw new \Exception('valueSql bereits gesetzt');
        }

        $this->valueSql = $sql;
        $this->qryBld->addSelectElement('(' . $sql . ') AS `' . $this->valueParam . '`');

        $this->castValueToInt = $castValueToInt;
    }




}
