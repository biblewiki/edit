<?php

// Debug mode
$st_debug_mode = true;

$biwi_config = [
    // ------------------------------------------
    // DB Einstellungen
    // ------------------------------------------
    'database'  => [
        'dsn' => 'mysql:host=mwepf1gm.mysql.db.hostpoint.ch;dbname=mwepf1gm_biblewikicontent',
        'user' => 'mwepf1gm_biwico',
        'password' => 'SNkDfoNX'
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
        'error_reporting' => $st_debug_mode ? E_ALL&~E_WARNING&~E_NOTICE&~E_STRICT&~E_DEPRECATED : null,
        // Dasselbe aber für Ausgabe in Console per ChromePhp oder Breakpoint
        'error_reporting_console' => $st_debug_mode ? E_ALL&~E_NOTICE : null,
        // Datei und Zeilennummer anzeigen?
        'showDetails' => $st_debug_mode,                // D:true, P:false
        // Nur allgemeinen Fehler anzeigen (nur wenn showDetails = false möglich)
        'showGeneralErrorMsg' => true,                  // D&P:true
        // Falls Fehlermeldungen in Kurzform dem User angezeigt werden, für SQL-Fehler nur allgemeine Meldung anzeigen?
        'showSqlExceptions' => $st_debug_mode,          // D:true, P:false
        // eigene Fehlerbehandlung für unbehandelte Fehler aktivieren?
        'enableGlobalExceptionHandler' => true,         // D&P:true
        'enableFatalExceptionHandler' => true,          // D&P:true
        // Javascript-Fehler loggen?
        'logJavascriptErrors' => !$st_debug_mode,
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
            'to' => 'samuel.kipfer@kipferinformatik.ch',
            'from' => 'support@kipferinformatik.ch',
            'subject' => 'suissetec kgweb Exception'
        ]
    ],

];