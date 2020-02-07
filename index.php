<?php

//--------------------------------------------------------
// Config laden
//--------------------------------------------------------
require __DIR__ . '/config/config.php';

//--------------------------------------------------------
// Anpassen der Umgebung
//--------------------------------------------------------
// Lokalisation
date_default_timezone_set('Europe/Zurich');

// Multibyte Extension initialisieren
mb_internal_encoding('UTF-8');
mb_language('uni');

// RAM
ini_set('memory_limit', '512M');

// Error Reporting
if ($biwi_config['exceptionHandling']['error_reporting'] !== null) {
    ini_set('error_reporting', $biwi_config['exceptionHandling']['error_reporting']);
}

// Fehler-Ausgabe nie in den Bildschirm schreiben
ini_set('display_errors', false);

//--------------------------------------------------------
// Autoloader
//--------------------------------------------------------
require __DIR__ . '/core/php/AutoLoader.php';
$loader = new biwi\edit\AutoLoader;

// Register the autoloader
$loader->register();

$loader->addNamespace('biwi\edit', 'core/php');
$loader->addNamespace('Sabre\Event', 'core/lib/sabre/event/lib');
$loader->addNamespace('Sabre\HTTP', 'core/lib/sabre/http/lib');

//--------------------------------------------------------
// App starten
//--------------------------------------------------------
$st_app = new biwi\edit\App($biwi_config);