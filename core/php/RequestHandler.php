<?php

require_once '../../config/config.php';
require_once './autoload.php';

// App-Instanz erstelllen
$app = new App($biwi_config);

// Request abfragen
$request = json_decode(file_get_contents("php://input"));

// Funktion aufsplitten in Klasse und Funktionsnamen
$functionExplode = explode('.', $request->function);
unset($request->function);

$class = $functionExplode[0];
$function = $functionExplode[1];
$args = $request->args;

// Formpacket formatieren in Array
if (property_exists($args, 'formPacket') && $args->formPacket !== null) {
    $requestData = $args->formPacket;
    unset($args->formPacket);

    $formPacket = [];

    // Alle Einträge in Array schreiben
    foreach ($requestData as $data) {
        $formPacket[$data->name] = $data->value === 'on' ? 1 : $data->value; 
    }

    $args->formPacket = $formPacket;
}

// Klasse mit Funktion aufrufen
$return = $class::$function($app, $args);

// Rückgabewert von Funktion an JS zurückgeben
echo json_encode($return);
