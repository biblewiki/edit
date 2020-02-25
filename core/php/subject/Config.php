<?php
declare(strict_types = 1);

namespace biwi\edit\subject;

/**
 * Class Config
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'subject/Overview.js',
            'subject/Subject.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
