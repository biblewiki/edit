<?php
declare(strict_types = 1);

//namespace bible;


/**
 * Class Bible
 *
 */
class Bible {
    /**
     * @var ki\App
     */
    protected $app;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Facade constructor.
     * @param ki\App $app
     */
    public function __construct(ki\App $app) {
        $this->app = $app;
    }

    public function deleteKapitel(\stdClass $args): ki\Rpc\ResponseDefault {
        try {

            // Rechte überprüfen
            if (!$this->app->checkRights($this->app->getConfig('rights, fachbereichFunction'))) {
                throw new ki\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $ids = \property_exists($args, 'selection') ? $args->selection : [];

            if (!$ids) {
                throw new ki\ExceptionNotice($this->app->getText('Es wurde kein Datensatz ausgewählt.'));
            }

            // Transaktion starten
            $this->app->getDb()->beginTransaction();

            // sql
            $st = $this->app->getDb()->prepare('DELETE FROM kapitel WHERE kapitelNr = :kapitelNr');

            foreach ($ids as $id) {
                if (strpos((string)$id, '9') === false) {
                    throw new ki\ExceptionNotice($this->app->getText('CRB Kapitel können nicht gelöscht werden.'));
                }

                $row = kg\position\Position::getPositionByKapitel($this->app, $id, 1);

                if ($row !== NULL) {
                    throw new ki\ExceptionNotice($this->app->getText('Kapitel ist noch mit Positionen verknüpft.'));
                }

                $st->bindValue(':kapitelNr', $id, \PDO::PARAM_INT);
                $st->execute();
            }
            unset ($st);

            if (!$this->app->isIgnoreWarnings()) {
                throw new ki\Rpc\Warning($this->app->getText('Möchten Sie die ausgewählten Datensätze wirklich löschen?'), $this->app->getText('Löschen?'));
            }

            // Transaktion ausführen
            $this->app->getDb()->commit();

            $response = new ki\Rpc\ResponseDefault();
            $response->return = $ids;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }


    /**
     * Gibt die Antwort für ein Combo
     * @param \stdClass $args
     * @return ki\Rpc\ResponseCombo
     */
    public function getBibleBooks(\stdClass $args): ki\Rpc\ResponseCombo {
        $brancheId = property_exists($args, 'brancheId') && $args->brancheId !== null ? (int)$args->brancheId : null;

        $comboLoader = new ComboLoader($this->app, $args, 'kapitel');
        $comboLoader->setCaptionSql('CONCAT(kapitel.kapitelNr, \' - \', kapitel_text.bezeichnung)');
        $comboLoader->setValueSql('kapitel.kapitelNr', true);

        $comboLoader->getQueryBuilder()->addFromElement('INNER JOIN kapitel_text ON kapitel.kapitelNr = kapitel_text.kapitelNr AND kapitel_text.languageId = :languageId');
        $comboLoader->getQueryBuilder()->addParam(':languageId', $this->app->getLanguageId(), \PDO::PARAM_STR);

        if ($brancheId) {
            $comboLoader->getQueryBuilder()->addWhereElement('kapitel.brancheId = :brancheId');
            $comboLoader->getQueryBuilder()->addParam(':brancheId', $brancheId, \PDO::PARAM_INT);
        }

        return $comboLoader->execute();
    }


    public function getDetailForm(\stdClass $args): ki\Rpc\ResponseForm {
        $kapitelNr = null;
        if (\property_exists($args, 'selection') && \is_object($args->selection) && \property_exists($args->selection, 'kapitelNr')) {
            $kapitelNr = $args->selection->kapitelNr !== null ? (int) $args->selection->kapitelNr : null;
        }

        // Rechte überprüfen
        if (!$this->app->checkRights()) {
            throw new ki\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $row = [];
        if ($kapitelNr !== null) {
            $qryBld = new ki\SqlSelector('kapitel');

            $qryBld->addSelectElement('kapitel.kapitelNr');

            $qryBld->addSelectElement('kapitel.versionsJahr');
            $qryBld->addSelectElement('kapitel.gesperrt');
            $qryBld->addSelectElement('kapitel.brancheId');

            $qryBld->addSelectElement('txt_de.bezeichnung AS bezeichnung_de');
            $qryBld->addSelectElement('txt_fr.bezeichnung AS bezeichnung_fr');
            $qryBld->addSelectElement('txt_it.bezeichnung AS bezeichnung_it');

            $qryBld->addFromElement('LEFT JOIN kapitel_text AS txt_de ON kapitel.kapitelNr = txt_de.kapitelNr AND txt_de.languageId = \'de\'');
            $qryBld->addFromElement('LEFT JOIN kapitel_text AS txt_fr ON kapitel.kapitelNr = txt_fr.kapitelNr AND txt_fr.languageId = \'fr\'');
            $qryBld->addFromElement('LEFT JOIN kapitel_text AS txt_it ON kapitel.kapitelNr = txt_it.kapitelNr AND txt_it.languageId = \'it\'');

            $qryBld->addWhereElement('kapitel.kapitelNr = :kapitelNr');
            $qryBld->addParam(':kapitelNr', $kapitelNr, \PDO::PARAM_INT);

            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);
        } else {
            $row['kapitelNr'] = null;
            $row['versionsJahr'] = null;
            $row['gesperrt'] = false;
            $row['brancheId'] = null;
            $row['bezeichnung_de'] = '';
            $row['bezeichnung_fr'] = '';
            $row['bezeichnung_it'] = '';
        }

        // neuer Datensatz?
        if (\property_exists($args, 'create') && $args->create === true) {
            $row['kapitelNr'] = NULL;
        }

        $row['kiOpenTS'] = date('Y-m-d H:i:s');

        $return = new ki\Rpc\ResponseForm();
        $return->setFormData($row);
        return $return;
    }


    /**
     * Gibt die Tabelle zurück.
     * @param \stdClass $args
     * @return ki\Rpc\ResponseGrid
     */
    public function getGridData(\stdClass $args): ki\Rpc\ResponseGrid {
        $loader = new ki\GridLoader($this->app, $args, 'kapitel');

        // Rechte überprüfen
        if (!$this->app->checkRights()) {
            throw new ki\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $loader->addPrimaryColumn('kapitel.kapitelNr', $this->app->getText('Kapitel-Nr.'), ['visible' => true, 'width' => 76, 'xtype' => 'kijs.gui.grid.columnConfig.Number', 'decimalThousandSep' => '']);

        $loader->addColumn($this->app->getText('Versionsjahr'), 'kapitel.versionsJahr', ['xtype' => 'kijs.gui.grid.columnConfig.Number', 'decimalThousandSep' => '']);
        $loader->addColumn($this->app->getText('Branche'), 'branche_text.bezeichnung', ['width' => 70]);
        $loader->addColumn($this->app->getText('Gesperrt'), 'IF(kapitel.gesperrt = 1, :ja, :nein)', ['width' => 71], 'kapitelGesperrt');
        $loader->addColumn($this->app->getText('Deutsch'), 'txt_de.bezeichnung', ['width' => 220]);
        $loader->addColumn($this->app->getText('Französisch'), 'txt_fr.bezeichnung', ['width' => 220]);
        $loader->addColumn($this->app->getText('Italienisch'), 'txt_it.bezeichnung', ['width' => 220]);

        $loader->addFromElement('LEFT JOIN kapitel_text AS txt_de ON kapitel.kapitelNr = txt_de.kapitelNr AND txt_de.languageId = \'de\'');
        $loader->addFromElement('LEFT JOIN kapitel_text AS txt_fr ON kapitel.kapitelNr = txt_fr.kapitelNr AND txt_fr.languageId = \'fr\'');
        $loader->addFromElement('LEFT JOIN kapitel_text AS txt_it ON kapitel.kapitelNr = txt_it.kapitelNr AND txt_it.languageId = \'it\'');

        $loader->addFromElement('LEFT JOIN branche_text ON kapitel.brancheId = branche_text.brancheId AND branche_text.languageId = :languageId');

        $loader->getQueryBuilderForSelect()->addParam(':ja', $this->app->getText('Ja'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':ja', $this->app->getText('Ja'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':nein', $this->app->getText('Nein'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':nein', $this->app->getText('Nein'), \PDO::PARAM_STR);

        $loader->getQueryBuilderForSelect()->addParam(':languageId', $this->app->getLanguageId());
        $loader->getCntQueryBuilderForSelect()->addParam(':languageId', $this->app->getLanguageId());

        return $loader->load();
    }


    /**
     * Speichert das Formular
     *
     * @param \stdClass $args
     * @return ki\Rpc\ResponseDefault
     */
    public function saveDetailForm(\stdClass $args): ki\Rpc\ResponseDefault {
        $formPacket = (array)$args->formData;

        if ($formPacket['kapitelNr']) {
            $formPacket['kiOldVal_kapitelNr'] = $formPacket['kapitelNr'];
        }

        $save = new ki\SaveData($this->app, $this->app->getSession()->userId, 'kapitel');
        $save->save($formPacket);
        $kapitelNr = (int)$save->getPrimaryKey()->value;
        $oldKapitelNr = (int)$save->getPrimaryKey()->oldValue;
        unset ($save);

        $response = new ki\Rpc\ResponseDefault();
        $response->newId = $kapitelNr;
        $response->oldId = $oldKapitelNr;
        return $response;
    }
}
