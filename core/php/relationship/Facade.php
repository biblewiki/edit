<?php
declare(strict_types = 1);

namespace biwi\edit\relationship;

use biwi\edit;

/**
 * Class Facade
 *
 * @package biwi\edit\relationship
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

    /**
     * Beziehung(en) löschen
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseDefault
     * @throws \Throwable
     * @throws edit\ExceptionNotice
     * @throws edit\Rpc\Warning
     */
        public function deleteRelationship(\stdClass $args): edit\Rpc\ResponseDefault {
        try {
            $ids = \property_exists($args, 'selection') ? $args->selection : [];

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
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
            $st = $this->app->getDb()->prepare('DELETE FROM relationship WHERE relationshipId = :relationshipId');

            foreach ($ids as $id) {
                $st->bindValue(':relationshipId', $id, \PDO::PARAM_INT);
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
     *
     * @param \stdClass $args
     * @return edit\Rpc\ResponseForm
     */
    public function getDetailForm(\stdClass $args): edit\Rpc\ResponseForm {

            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole() === 3) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $relationshipId = null;

            if (\property_exists($args, 'selection') && \is_object($args->selection) && \property_exists($args->selection, 'relationshipId')) {
                $relationshipId = $args->selection->relationshipId !== null ? (int)$args->selection->relationshipId : null;
            }

            $row = [];
            if ($relationshipId !== null) {
                $qryBld = new edit\SqlSelector('relationship');
                $qryBld->addSelectElement('relationship.relationshipId');
                $qryBld->addSelectElement('relationship.name');
                $qryBld->addSelectElement('relationship.sex');
                $qryBld->addSelectElement('relationship.returnMRelationshipId');
                $qryBld->addSelectElement('relationship.returnWRelationshipId');

                $qryBld->addWhereElement('relationship.relationshipId = :relationshipId');
                $qryBld->addParam(':relationshipId', $relationshipId, \PDO::PARAM_INT);

                $row = $qryBld->execute($this->app->getDb(), false);
                unset ($qryBld);

            } else {
                $row['relationshipId'] = null;
                $row['name'] = '';
                $row['sex'] = '';
                $row['returnMRelationshipId'] = null;
                $row['returnWRelationshipId'] = null;
            }

            // neuer Datensatz?
            if (\property_exists($args, 'create') && $args->create === true) {
                unset($row['relationshipId']);
            }

            $row['openTS'] = date('Y-m-d H:i:s');

            $return = new edit\Rpc\ResponseForm();
            $return->setFormData($row);
            return $return;
    }


    /**
     * Gibt die Beziehungen als Combo zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getForCombo(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $relationships = edit\relationship\Relationship::getRelationships($this->app);

        $response = new edit\Rpc\ResponseCombo();
        $response->addRows($relationships);
        return $response;
    }


    /**
     * Preis Rezepte
     * @param \stdClass $args
     * @return edit\Rpc\ResponseGrid
     */
    public function getGridData(\stdClass $args): edit\Rpc\ResponseGrid {
        $loader = new edit\GridLoader($this->app, $args, 'relationship');

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserRole() === 3) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        // primary Key
        $loader->addPrimaryColumn('relationship.relationshipId', $this->app->getText('Mitteilung') . ' ' . $this->app->getText('ID'));

        $loader->addColumn($this->app->getText('Name'), 'relationship.name');
        $loader->addColumn($this->app->getText('Geschlecht'), 'IF(relationship.sex = 1, :sex_1,  :sex_2)');
        $loader->addColumn($this->app->getText('Umkehrung Männlich'), 'relationshipM.name', ['width' => 130]);
        $loader->addColumn($this->app->getText('Umkehrung Weiblich'), 'relationshipW.name', ['width' => 130]);

        $loader->addFromElement('INNER JOIN relationship as relationshipM ON relationshipM.relationshipId = relationship.returnMRelationshipId');
        $loader->addFromElement('INNER JOIN relationship as relationshipW ON relationshipW.relationshipId = relationship.returnWRelationshipId');

        $loader->getQueryBuilderForSelect()->addParam(':sex_1', $this->app->getText('Männlich'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':sex_1', $this->app->getText('Männlich'), \PDO::PARAM_STR);
        $loader->getQueryBuilderForSelect()->addParam(':sex_2', $this->app->getText('Weiblich'), \PDO::PARAM_STR);
        $loader->getCntQueryBuilderForSelect()->addParam(':sex_2', $this->app->getText('Weiblich'), \PDO::PARAM_STR);

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
            if ($this->app->getLoggedInUserRole() !== 99) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            if ($formPacket['relationshipId']) {
                $formPacket['oldVal_relationshipId'] = $formPacket['relationshipId'];
            }

            $save = new edit\SaveData($this->app, $this->app->getLoggedInUserId(), 'relationship');
            $save->save($formPacket);
            $relationshipId = (int)$save->getPrimaryKey()->value;
            $oldrelationshipId = (int)$save->getPrimaryKey()->oldValue;
            unset ($save);

            $response = new edit\Rpc\ResponseDefault();
            $response->newId = $relationshipId;
            $response->oldId = $oldrelationshipId;
            return $response;
        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }
}
