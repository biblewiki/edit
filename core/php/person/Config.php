<?php
declare(strict_types = 1);

namespace biwi\edit\person;

/**
 * Class Config
 *
 * @package ki\kgweb\kg\app
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'person/Overview.js',
            'person/Person.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
