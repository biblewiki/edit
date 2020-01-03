<?php
declare(strict_types = 1);

namespace biwi\edit\source;

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
     * Gibt die schon vorhandenen Sprachen aus der DB zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getOtherSourceLanguage(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $comboLoader = new edit\ComboLoader($this->app, $args, 'otherSource');
        $comboLoader->setCaptionSql('otherSource.language');
        $comboLoader->setValueSql('otherSource.language');

        return $comboLoader->execute();

    }


    /**
     * Gibt die schon vorhandenen Mediums aus der DB zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getOtherSourceMedium(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $comboLoader = new edit\ComboLoader($this->app, $args, 'otherSource');
        $comboLoader->setCaptionSql('otherSource.medium');
        $comboLoader->setValueSql('otherSource.medium');

        return $comboLoader->execute();

    }


    /**
     * Gibt die schon vorhandenen Verlage aus der DB zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getOtherSourcePublishCompany(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $comboLoader = new edit\ComboLoader($this->app, $args, 'otherSource');
        $comboLoader->setCaptionSql('otherSource.publishCompany');
        $comboLoader->setValueSql('otherSource.publishCompany');

        return $comboLoader->execute();

    }


    /**
     * Gibt die schon vorhandenen Typen aus der DB zurück
     *
     * @param \stdClass $args
     * @return \biwi\edit\Rpc\ResponseCombo
     * @throws edit\ExceptionNotice
     */
    public function getOtherSourceType(\stdClass $args): edit\Rpc\ResponseCombo {

        // Rechte überprüfen
        if (!$this->app->getLoggedInUserType()) {
            throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
        }

        $comboLoader = new edit\ComboLoader($this->app, $args, 'otherSource');
        $comboLoader->setCaptionSql('otherSource.type');
        $comboLoader->setValueSql('otherSource.type');

        return $comboLoader->execute();

    }
}