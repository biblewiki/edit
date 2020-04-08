<?php
declare(strict_types = 1);

namespace biwi\edit\region;

/**
 * Class Config
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'region/Overview.js',
            'region/Region.js',
            'region/NameWindow.js',
            'region/RelationshipGridPanel.js',
            'region/GroupGridPanel.js',
            'region/RelationshipWindow.js',
            'region/GroupWindow.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
