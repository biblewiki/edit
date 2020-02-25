<?php
declare(strict_types = 1);

namespace biwi\edit\lists;

/**
 * Class Config
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'lists/Overview.js',
            'lists/Lists.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
