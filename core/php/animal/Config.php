<?php
declare(strict_types = 1);

namespace biwi\edit\animal;

/**
 * Class Config
 *
 * @package ki\kgweb\kg\app
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'animal/Overview.js',
            'animal/Animal.js',
        ];
    public $isAutoLoadJavaScriptModule = false;
}
