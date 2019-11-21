<?php

require_once '../../config/config.php';
require_once './autoload.php';

$app = new App($biwi_config);

$request = json_decode(file_get_contents("php://input"));

$functionExplode = explode('.', $request->function);
unset($request->function);

$class = $functionExplode[0];
$function = $functionExplode[1];
$args = $request->args;
        
if (property_exists($args, 'formPacket') && $args->formPacket !== null) {
    $requestData = $args->formPacket;
    unset($args->formPacket);
    $formPacket = [];

    foreach ($requestData as $data) {
        $formPacket[$data->name] = $data->value === 'on' ? 1 : $data->value; 
    }
    $args->formPacket = $formPacket;
}

$return = $class::$function($app, $args);

echo json_encode($return);
