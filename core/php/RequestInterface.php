<?php
declare(strict_types = 1);

namespace biwi\edit;

use Sabre\HTTP;

/**
 * Interface RequestInterface
 *
 * @package ki\kgweb\ki
 */
interface RequestInterface extends HTTP\RequestInterface {

    /**
     * Browsersprache ermitteln
     * Sprachcodes sind definiert in ISO 639-1
     *
     * @param $allowedLanguages array mit den Erlaubten Sprachcodes
     * @param $defaultLanguage string Sprachcode der Standardsprache
     * @return string
     */
    public function getBrowserLanguage(array $allowedLanguages, string $defaultLanguage): string;


    /**
     * @param string|null $name
     * @return array|string
     */
    public function getGet(string $name = null);


    /**
     * @param string|null $name
     * @return array|string
     */
    public function getPost(string $name = null);


    /**
     * @param string|null $name
     * @return bool
     */
    public function hasGet(string $name = null): bool;


    /**
     * @param string|null $name
     * @return bool
     */
    public function hasPost(string $name = null): bool;

}