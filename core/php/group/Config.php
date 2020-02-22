<?php
declare(strict_types = 1);

namespace biwi\edit\group;

/**
 * Class Config
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'group/Overview.js',
            'group/Group.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
