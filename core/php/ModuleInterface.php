<?php
declare(strict_types = 1);

namespace biwi\edit;

/**
 * Interface ModuleInterface
 *
 * @package ki\kgweb\ki
 */
interface ModuleInterface {
    /**
     * Module constructor.
     *
     * @param App $app
     * @param string $prefix
     * @param string $name
     * @param string|null $group
     * @param bool $isMainModule
     */
    public function __construct(App $app, string $prefix, string $name, string $group = null, bool $isMainModule = false);


    /**
     * @return string
     */
    public function getClassName(): string;


    /**
     * @return string
     */
    public function getConfigClassName(): string;


    /**
     * @return array
     * @throws \Exception
     */
    public function getCssFiles(): array ;


    /**
     * @return string
     */
    public function getDirName(): string;


    /**
     * @return string
     */
    public function getFacadeClassName(): string;


    /**
     * Gibt die Gruppe / den Bereich zurück
     *
     * @return string
     */
    public function getGroup(): string;


    /**
     * @return array
     * @throws \Exception
     */
    public function getJsFiles(): array ;


    /**
     * @return string
     */
    public function getListenerClassName(): string;


    /**
     * @return string
     */
    public function getName(): string;


    /**
     * @return string
     */
    public function getNameSpace(): string;


    /**
     * @return bool
     */
    public function isMainModule(): bool;
}