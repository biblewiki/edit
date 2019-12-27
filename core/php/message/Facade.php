<?php
declare(strict_types = 1);

namespace biwi\edit\message;

use biwi\edit;

/**
 * Class Facade
 *
 * @package edit\kgweb\kg\message
 */
class Facade {
    /**
     * @var edit\App
     */
    protected $app;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Facade constructor.
     * @param edit\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }


    public function deleteMessage(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $ids = \property_exists($args, 'selection') ? $args->selection : [];

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserType() === 3) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            if (!$ids) {
                throw new edit\ExceptionNotice($this->app->getText('Es wurde kein Datensatz ausgewählt.'));
            }

            if (!$this->app->isIgnoreWarnings()) {
                throw new edit\Rpc\Warning($this->app->getText('Möchten Sie die ausgewählten Datensätze wirklich löschen?'), $this->app->getText('Löschen') . '?');
            }

            // Transaktion starten
            $this->app->getDb()->beginTransaction();

            // sql
            $st = $this->app->getDb()->prepare('DELETE FROM message WHERE messageId = :messageId');

            foreach ($ids as $id) {
                $st->bindValue(':messageId', $id, \PDO::PARAM_INT);
                $st->execute();
            }
            unset ($st);

            // Transaktion beenden
            $this->app->getDb()->commit();

            $response = new edit\Rpc\ResponseDefault();
            $response->return = $ids;
            return $response;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }


    /**
     * Gibt das Detailformular zurück
     * @param \stdClass $args
     * @return edit\Rpc\ResponseForm
     */
    public function getDetailForm(\stdClass $args): edit\Rpc\ResponseForm {

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserType() === 3) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $messageId = null;

            if (\property_exists($args, 'selection') && \is_object($args->selection) && \property_exists($args->selection, 'messageId')) {
                $messageId = $args->selection->messageId !== null ? (int)$args->selection->messageId : null;
            }

            $row = [];
            if ($messageId !== null) {
                $qryBld = new edit\SqlSelector('message');
                $qryBld->addSelectElement('message.messageId');
                $qryBld->addSelectElement('message.description');
                $qryBld->addSelectElement('message.text');
                $qryBld->addSelectElement('dateFrom');
                $qryBld->addSelectElement('dateTo');

                $qryBld->addWhereElement('message.messageId = :messageId');
                $qryBld->addParam(':messageId', $messageId, \PDO::PARAM_INT);

                $row = $qryBld->execute($this->app->getDb(), false);
                unset ($qryBld);

            } else {
                $row['messageId'] = null;
                $row['description'] = '';
                $row['text'] = '';
                $row['dateFrom'] = date('Y-m-d H:i');
                $row['dateTo'] = null;
            }

            // neuer Datensatz?
            if (\property_exists($args, 'create') && $args->create === true) {
                unset($row['messageId']);
            }

            $row['openTS'] = date('Y-m-d H:i:s');

            $return = new edit\Rpc\ResponseForm();
            $return->setFormData($row);
            return $return;
    }


    /**
     * Preis Rezepte
     * @param \stdClass $args
     * @return edit\Rpc\ResponseGrid
     */
    public function getGridData(\stdClass $args): edit\Rpc\ResponseGrid {
        $loader = new edit\GridLoader($this->app, $args, 'message');

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType() === 3) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // primary Key
        $loader->addPrimaryColumn('message.messageId', $this->app->getText('Mitteilung') . ' ' . $this->app->getText('ID'));

        $loader->addColumn($this->app->getText('Beschreibung'), 'message.description');
        $loader->addColumn($this->app->getText('Mitteilung'), 'message.text' );
        $loader->addColumn($this->app->getText('Anzeigen von'), 'message.dateFrom', ['width' => 110, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);
        $loader->addColumn($this->app->getText('Anzeigen bis'), 'message.dateTo', ['width' => 110, 'xtype' => 'kijs.gui.grid.columnConfig.Date', 'format' => 'd.m.Y H:i']);

        return $loader->load();
    }


    /**
     * Speichert das Formular
     * @param \stdClass $args
     * @return edit\Rpc\ResponseDefault
     */
    public function saveDetailForm(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $formPacket = (array)$args->formData;

            // Rechte überprüfen
            if ($this->app->getLoggedInUserType() !== 99) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            if ($formPacket['messageId']) {
                $formPacket['oldVal_messageId'] = $formPacket['messageId'];
            }

            $save = new edit\SaveData($this->app, $this->app->getSession()->userId, 'message');
            $save->save($formPacket);
            $messageId = (int)$save->getPrimaryKey()->value;
            $oldmessageId = (int)$save->getPrimaryKey()->oldValue;
            unset ($save);

            $response = new edit\Rpc\ResponseDefault();
            $response->newId = $messageId;
            $response->oldId = $oldmessageId;
            return $response;
        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}
