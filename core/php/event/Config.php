<?php
declare(strict_types = 1);

namespace biwi\edit\event;

/**
 * Class Config
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'event/Overview.js',
            'event/Event.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
