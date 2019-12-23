<?php
declare(strict_types = 1);

namespace person;

/**
 * Class Config
 *
 * @package ki\kgweb\kg\app
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'person/Overview.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
