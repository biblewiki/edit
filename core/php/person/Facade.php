<?php
declare(strict_types = 1);

namespace biwi\edit\person;

use biwi\edit;

/**
 * Class Facade
 */
class Facade {
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
    public function __construct(edit\App $app) {
        $this->app = $app;
    }

    public function getFormData(\stdClass $args): edit\Rpc\ResponseForm {

        $personId = null;
        $version = null;

        if (property_exists($args, 'id') && $args->id) {
            $personId = $args->id;
        }

        if (property_exists($args, 'version') && $args->version) {
            $personId = $args->version;
        }

        $row = [];
        if ($personId) {
            $qryBld = new edit\SqlSelector('person');
            $qryBld->addSelectElement('person.personId');
            $qryBld->addSelectElement('person.version');
            $qryBld->addSelectElement('person.name');
            $qryBld->addSelectElement('person.sex');
            $qryBld->addSelectElement('person.believer');

            $qryBld->addWhereElement('person.personId = :personId');
            $qryBld->addParam(':personId', $personId, \PDO::PARAM_INT);

            if ($version) {
                $qryBld->addWhereElement('person.version = :version');
                $qryBld->addParam(':version', $version, \PDO::PARAM_INT);
            }

            $row = $qryBld->execute($this->app->getDb(), false);
            unset ($qryBld);

        } else {
            $row['personId'] = null;
            $row['version'] = null;
            $row['name'] = null;
            $row['sex'] = null;
            $row['believer'] = null;
        }

        // neuer Datensatz?
        if (\property_exists($args, 'create') && $args->create === true) {
            unset($row['mitteilungId']);
        }

        $row['openTS'] = date('Y-m-d H:i:s');

        $return = new edit\Rpc\ResponseForm();
        $return->setFormData($row);
        return $return;

    }


    /**
     * Speichert das Formular
     *
     * @param \stdClass $args
     * @return edit\Rpc\ResponseDefault
     */
    public function saveDetailForm(\stdClass $args): edit\Rpc\ResponseDefault {
        $formPacket = (array)$args->formData;

        if ($formPacket['personId']) {
            $formPacket['oldVal_personId'] = $formPacket['personId'];
        }

        if ($formPacket['version']) {
            $formPacket['oldVal_version'] = $formPacket['version'];
        }

        $save = new edit\SaveData($this->app, $this->app->getSession()->userId, 'person');
        $save->save($formPacket);
        $id = (int)$save->getPrimaryKey()->value;
        $version = (int)$save->getversion();
        unset ($save);

        $response = new edit\Rpc\ResponseDefault();
        $response->id = $id;
        $response->version = $version;
        return $response;
    }

}