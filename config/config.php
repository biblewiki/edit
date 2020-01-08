<?php

// Debug mode
$biwi_debug_mode = true;

$biwi_config = [

    // ------------------------------------------
    // DB Einstellungen
    // ------------------------------------------

    'database'  => [
        'dsn' => 'mysql:host=mwepf1gm.mysql.db.hostpoint.ch;dbname=mwepf1gm_biblewikicontent',
        'host' => 'mwepf1gm.mysql.db.hostpoint.ch',
        'db' => 'mwepf1gm_biblewikicontent',
        'user' => json_decode(file_get_contents('../config/config.json'))->user,
        'password' => json_decode(file_get_contents('../config/config.json'))->password
    ],

    // ------------------------------------------
    // Module
    // ------------------------------------------

    'module'  => [
        // [Gruppe/]Modulnamen, Gross-/Kleinschreibung beachten, erster Buchstabe gross schreiben. Hauptmodul nicht auflisten.
        'names' => ['Animal', 'Bible', 'Dashboard', 'Group', 'Message', 'Person', 'Relationship', 'Setting', 'Source'],
        'prefix' => 'biwi',
        'DefaultCssFiles' => ['biwi_app.css'],
        'DefaultJsFiles' => ['default/DefaultFormPanel.js', 'default/DefaultFormWindow.js', 'default/DefaultGridPanel.js', 'default/DefaultGridFormPanel.js']
    ],

    // ------------------------------------------
    // Fehlerbehandlung
    // ------------------------------------------

    'exceptionHandling' => [
        // Fehler-Level (überschreibt die php.ini wenn != null
        // Konfiguration für den produktiven Einsatz ab PHP Version 5.3:
        // E_ALL&~E_WARNING&~E_NOTICE&~E_STRICT&~E_DEPRECATED&~E_USER_DEPRECATED
        // Da die Konstanten in neueren PHP-Versionen unterschiedliche Werte haben,
        // ist es bei produktiven Umgebungen besser, hier null einzutragen und
        // in der php.ini den richtigen Wert zu konfigurieren. Sonst muss
        // sichergestellt werden, dass das gewählte Fehler-Level mit der
        // PHP-Version übereinstimmt.
        'error_reporting' => $biwi_debug_mode ? E_ALL&~E_WARNING&~E_NOTICE&~E_STRICT&~E_DEPRECATED : null,
        // Dasselbe aber für Ausgabe in Console per ChromePhp oder Breakpoint
        'error_reporting_console' => $biwi_debug_mode ? E_ALL&~E_NOTICE : null,
        // Datei und Zeilennummer anzeigen?
        'showDetails' => $biwi_debug_mode,                // D:true, P:false
        // Nur allgemeinen Fehler anzeigen (nur wenn showDetails = false möglich)
        'showGeneralErrorMsg' => true,                  // D&P:true
        // Falls Fehlermeldungen in Kurzform dem User angezeigt werden, für SQL-Fehler nur allgemeine Meldung anzeigen?
        'showSqlExceptions' => $biwi_debug_mode,          // D:true, P:false
        // eigene Fehlerbehandlung für unbehandelte Fehler aktivieren?
        'enableGlobalExceptionHandler' => true,         // D&P:true
        'enableFatalExceptionHandler' => true,          // D&P:true
        // Javascript-Fehler loggen?
        'logJavascriptErrors' => !$biwi_debug_mode,
        // Errors in Exceptions umwandeln?
        'convertErrors' => true,                        // D&P:true
        // Fehler in die exceptions.log schreiben
        'logfile' => [
            'enable' => true,                           // D&P:true
            'path' => 'log/exceptions.log'
        ],
        // Fehler über E-Mail versenden
        'sendMail' => [
            'enable' => false,                          // D&P: false
            'to' => 'jk@informatex.ch',
            'from' => 'web@biblewiki.ch',
            'subject' => 'BibleWiki Exception'
        ]
    ],

    'host' => [
        'loginHost' => 'https://login.biblewiki.one',
        'editHost' => 'https://edit.joel.biblewiki.one'
    ],

    // ------------------------------------------
    // Entwicklung & Debug
    // ------------------------------------------
    'develop' => [
        'debug_mode' => $biwi_debug_mode,
        'use_chromephp' => false,                               // D:true, P:false
        'use_minify' => false,                                  // D:false, P:true
        // build_minify: D&P:false => ist nur einmal vor dem veröffentlichen nötig. Geht nicht wenn Pfad in URL. OnDemand mit ?buildmin in URL
        'build_minify' => $biwi_debug_mode ? 'onDemand' : false
    ]

];
