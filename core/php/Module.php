<?php
declare(strict_types = 1);

namespace biwi\edit;

/**
 * Class Module
 *
 * @package ki\kgweb\ki
 */
final class Module implements ModuleInterface {
    /**
     * @var App
     */
    private $app;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $group;
    /**
     * @var string
     */
    private $dirName;
    /**
     * @var bool
     */
    private $isMainModule;


    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------

    /**
     * Module constructor.
     *
     * @param App $app
     * @param string $prefix
     * @param string $name
     * @param string|null $group
     * @param bool $isMainModule
     * @throws \Exception
     */
    public function __construct(App $app, string $prefix, string $name, string $group = null, bool $isMainModule = false) {
        $this->app = $app;
        $this->prefix = $prefix;
        $this->group = $group;
        $this->isMainModule = $isMainModule;

        // Name
        $pos = strpos($name, "(");
        if ($pos === false) {
            $this->name = $name;
            $this->dirName = $name;
        } else {

            // In Klammern kann ein weiterer Name angegeben werden, dieser wird dann als Ordnername verwendet. So kann einfach zwischen Varianten eines Moduls gewechselt werden .
            $this->name = substr($name, 0, $pos);
            $this->dirName = substr($name, $pos + 1, strpos($name, ")") - $pos - 1);
        }

        // Event-Listeners
        $className = $this->getListenerClassName();
        if (class_exists($className)) {
            new $className($app);
        }
    }


    /**
     * @return string
     */
    public function getClassName(): string {
        return $this->name;
    }


    /**
     * @return string
     */
    public function getConfigClassName(): string {
        return $this->getNameSpace() . "\\Config";
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getCssFiles(): array {
        $ret = [];
        $className = $this->getConfigClassName();
        if (!class_exists($className)) {
            throw new \Exception("Die Klasse $className existiert nicht");
        }
        $class = new $className;
        foreach ($class->cssFiles as $file) {
            if (!file_exists($this->getCssDir() . DIRECTORY_SEPARATOR . $file)) {
                throw new \Exception("Die Datei '" . $this->getCssDir() . DIRECTORY_SEPARATOR . $file . "' wurde nicht gefunden.");
            }
            $ret[] = $this->getCssDir() . DIRECTORY_SEPARATOR . $file;
        }
        unset($class);

        return $ret;
    }


    /**
     * Gibt die Portlets für das Dashboard zurück
     *
     * @return array
     * @throws \Exception
     */
    public function getDashboardPortletClasses(): array {
        $ret = [];
        $className = $this->getConfigClassName();
        if (!class_exists($className)) {
            throw new \Exception("Die Klasse $className existiert nicht");
        }
        $class = new $className;
        $portletClassNames = $class->dashboardPortletClasses ?? [];
        unset($class);

        foreach ($portletClassNames as $portletClassName) {
            $class = $this->getNameSpace() . "\\" . $portletClassName;
            if (!class_exists($class)) {
                throw new \Exception("Die Klasse $class existiert nicht");
            }
            $ret[] = $class;
        }

        return $ret;
    }


    /**
     * @return string
     */
    public function getDirName(): string {
        return mb_strtolower($this->dirName);
    }


    /**
     * @return string
     */
    public function getFacadeClassName(): string {
        return $this->getNameSpace() . "\\Facade";
    }


    /**
     * Gibt die Gruppe / den Bereich zurück
     *
     * @return string
     */
    public function getGroup(): string {
        return $this->group;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getJsFiles(): array {
        $ret = [];
        $className = $this->getConfigClassName();
        if (!class_exists($className)) {
            throw new \Exception("Die Klasse $className existiert nicht");
        }
        $class = new $className;
        foreach ($class->jsFiles as $file) {
            if (!file_exists($this->getJsDir() . DIRECTORY_SEPARATOR . $file)) {
                throw new \Exception("Die Datei '" . $this->getJsDir() . DIRECTORY_SEPARATOR . $file . "' wurde nicht gefunden.");
            }
            $ret[] = $this->getJsDir() . DIRECTORY_SEPARATOR . $file;
        }

        return $ret;
    }


    /**
     * @return string
     */
    public function getListenerClassName(): string {
        return $this->getNameSpace() . "\\Listener";
    }


    /**
     * @return mixed|null|string|string[]
     */
    public function getName(): string {
        return mb_strtolower($this->name);
    }


    /**
     * @return string
     */
    public function getNameSpace(): string {
        if ($this->group) {
            return "biwi\\edit\\{$this->group}\\{$this->getName()}";
        }
        return "biwi\\edit\\{$this->getName()}";
    }


    /**
     * @return bool
     */
    public function isMainModule(): bool {
        return $this->isMainModule;
    }


    //--------------------------------------------------------
    // Private Functions
    //--------------------------------------------------------

    /**
     * @return string
     */
    private function getCssDir(): string {
        return "core/ressources/css";
    }


    /**
     * @return string
     */
    private function getJsDir(): string {
        return "core/js" . DIRECTORY_SEPARATOR;
    }
}