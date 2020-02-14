<?php
declare(strict_types = 1);

namespace biwi\edit\setting;

/**
 * Class Config
 *
 * @package biwi\edit\setting
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
        'setting/Setting.js'
    ];
    public $isAutoLoadJavaScriptModule = false;
}
