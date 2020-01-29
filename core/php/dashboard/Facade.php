<?php
declare(strict_types = 1);

namespace biwi\edit\dashboard;

use biwi\edit;

/**
 * Class Facade
 *
 * @package edit\kgweb\kg\dashboard
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
     * Gibt den HTML-Header für das Dashboard zurück
     * @return \edit\kgweb\edit\Rpc\ResponseDefault
     */
    public function getHeaderHtml(): edit\Rpc\ResponseDefault {
        //$user = kg\user\User::getUser($this->app);

        $html = $this->app->getText('Willkommen');
        if ($user && $user->firstname) {
            $html = \trim($this->app->getText('Willkommen') . ', ' . $user->firstname . ' ' . $user->lastname);
        }

        $response = new edit\Rpc\ResponseDefault();
        $response->html = $html;
        return $response;
    }


    /**
     * Gibt die Messages für das Dashboard zurück
     * @return \edit\kgweb\edit\Rpc\ResponseDefault
     */
    public function getMessages(): edit\Rpc\ResponseDefault {
        try {
            // Rechte überprüfen
            if (!$this->app->getLoggedInUserRole()) {
                throw new edit\ExceptionNotice($this->app->getText("Sie verfügen nicht über die benötigten Berechtigungen für diesen Vorgang."));
            }

            $messages = edit\message\Message::getActiveMessage($this->app);

            $return = new edit\Rpc\ResponseDefault;
            $return->messages = $messages;
            return $return;

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            throw $e;
        }
    }





    /**
     * Speichert den Status der Portlets (Sichtbarkeit, Sortierung)
     * @param \stdClass $args
     * @return void
     * @throws \Throwable
     */
    public function setPortletState(\stdClass $args): void {
        try {
            if (\property_exists($args, 'portlets') && \is_array($args->portlets)) {
                Dashboard::updatePortletState($this->app, $args->portlets);
            }
        } catch (\Throwable $ex) {
            $this->app->getDb()->rollBackIfTransaction();
            throw $ex;
        }
    }


    /**
     * Gibt die Portlets für das Dashboard zurück
     * @return \edit\kgweb\edit\Rpc\ResponseDefault
     */
    public function getPortlets(): edit\Rpc\ResponseDefault {
        $portlets = [];

        foreach ($this->app->getModules()->getArray() as $mod) {
            $classNames = $mod->getDashboardPortletClasses();
            foreach ($classNames as $className) {

                // Portlets laden und an Array anhängen
                $fn = [$className, 'loadPortlets'];
                if (\is_callable($fn)) {
                    $subPortlets = $fn($this->app);
                    if (\is_array($subPortlets)) {
                        $portlets = \array_merge($portlets, $subPortlets);
                        unset ($subPortlets);
                    }
                }
            }
        }

        // Status (Sichtbar, Sortierung) abfragen
        $states = Dashboard::getPortletState($this->app);

        foreach ($portlets as &$portlet) {

            if (!is_object($portlet)) {
                throw new Exception('invalid portlet');
            }

            // state suchen
            $state = null;
            if (isset($portlet->name)) {
                foreach ($states as $s) {
                    if ($s['portlet'] === $portlet->name) {
                        $state = $s;
                        break;
                    }
                }
                unset ($s);
            }

            // sortierung und sichtbarkeit von user state übernehmen, wenn sie nicht vom portlet überschrieben werden
            if ($state !== null) {
                $portlet->visible = isset($portlet->visible) && \is_bool($portlet->visible) ? $portlet->visible : !!$state['visible'];
                $portlet->sort = isset($portlet->sort) && \is_int($portlet->sort) ? $portlet->sort : ($state['sort'] + 100);
            } else {
                $portlet->visible = isset($portlet->visible) && \is_bool($portlet->visible) ? $portlet->visible : true;
                $portlet->sort = isset($portlet->sort) && \is_int($portlet->sort) ? $portlet->sort : 0;
            }

            unset ($state);
        }
        unset ($portlet);

        // sortieren
        \usort($portlets, function($a, $b) {
            if (\is_object($a) && \is_object($b) && isset($a->sort) && isset($b->sort)) {
                return ($a->sort < $b->sort) ? -1 : 1;
            }
            return 0;
        });

        // Response
        $response = new edit\Rpc\ResponseDefault();
        $response->portlets = $portlets;
        return $response;
    }

}
