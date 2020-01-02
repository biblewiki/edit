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
            'default/source/BibleSourceFields.js',
            'default/source/WebSourceFields.js',
            'default/source/OtherSourceFields.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
    public $dashboardPortletClasses = [];
}
