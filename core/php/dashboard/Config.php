<?php
declare(strict_types = 1);

namespace biwi\edit\dashboard;

/**
 * Class Config
 *
 * @package ki\kgweb\kg\dashboard
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
        'dashboard/Dashboard.js',
        'dashboard/PortletContainer.js',
        'dashboard/Portlet.js',
    ];
    public $isAutoLoadJavaScriptModule = false;
}
