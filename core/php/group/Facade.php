<?php
declare(strict_types = 1);

namespace biwi\edit\group;

use biwi\edit;

/**
 * Class Facade
 *
 * @package biwi\edit\setting
 */
class Facade {

    /**
     * @var ki\App
     */
    protected $app;

    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    /**
     * Facade constructor.
     *
     * @param ki\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }


    /**
     * Personengruppen für Combo zurückgeben
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getForCombo(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $loader = new edit\ComboLoader($this->app, $args, 'group');
        $loader->setCaptionSql('group.name');
        $loader->setValueSql('group.groupId', true);
        $loader->setDistinct(true);

        return $loader->execute();
    }
}
