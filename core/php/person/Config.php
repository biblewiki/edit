<?php
declare(strict_types = 1);

namespace biwi\edit\person;

/**
 * Class Config
 *
 * @package ki\kgweb\kg\app
 */
class Config {
    public $cssFiles = [];
    public $jsFiles = [
            'person/Overview.js',
            'person/Person.js',
            'person/RelationshipGridPanel.js',
            'person/GroupGridPanel.js',
            'person/RelationshipWindow.js',
            'person/GroupWindow.js'
        ];
    public $isAutoLoadJavaScriptModule = false;
}
