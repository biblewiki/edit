<?php
declare(strict_types = 1);

namespace biwi\edit;

/**
 * Class Modules
 *
 * @package ki\kgweb\ki
 */
class Modules {
    /**
     * @var App
     */
    protected $app;

    protected $mainName;

    /**
     * @var array Module
     */
    protected $modules = [];


    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    /**
     * Modules constructor.
     *
     * @param App $app
     * @param string $mainName
     * @param array $config
     * @throws \Exception
     */
    public function __construct(App $app, string $mainName, array $config) {
        $this->app = $app;
        $this->mainName = $mainName;
        $this->modules[] = new Module($app, $config["prefix"], $mainName, null, true);
        $mods = $config["names"];
        foreach ($mods as $name) {
            $pos = strpos($name, '/');
            if ($pos === false) {

                // Ohne Gruppe
                $this->modules[] = new Module($app, $config["prefix"], $name);
            } else {

                // Mit Gruppe
                $this->modules[] = new Module($app, $config["prefix"], substr($name, $pos + 1), substr($name, 0, $pos));
            }
        }
    }


    /**
     * @return array Module
     */
    public function getArray(): array {
        return $this->modules;
    }


    /**
     * @return array
     */
    public function getNames(): array {
        $arr = [];
        foreach ($this->modules as $mod) {
            $arr[] = $mod->getName();
        }

        return $arr;
    }


    /**
     * @param $name
     * @return Module
     */
    public function getModule(string $name): ?Module {
        foreach ($this->modules as $mod) {
            if ($name === $mod->getName()) {
                return $mod;
            }
        }

        return null;
    }
}
