<?php
declare(strict_types = 1);

namespace biwi\edit\epoch;

/**
 * Class Config
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'epoch/Overview.js',
            'epoch/Epoch.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
