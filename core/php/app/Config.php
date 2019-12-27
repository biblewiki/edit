<?php
declare(strict_types = 1);

namespace biwi\edit\app;

/**
 * Class Config
 *
 * @package ki\kgweb\kg\app
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'app/App.js',
            'app/MainPanel.js',
            'app/ButtonTree.js',
            'default/source/SourceWindow.js',
            'default/source/BibleSourceField.js',
            'default/source/BookSourceField.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
    public $dashboardPortletClasses = [];
}
