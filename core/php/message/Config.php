<?php
declare(strict_types = 1);

namespace biwi\edit\message;

/**
 * Class Config
 *
 * @package biwi\edit\message
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
        'message/Message.js',
        'message/MessageContainer.js',
    ];
    public $isAutoLoadJavaScriptModule = false;
}
