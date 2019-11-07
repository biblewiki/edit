<?php

require_once './config.php';
require_once './autoload.php';

//$data = file_get_contents('php://input');

//var_dump($data);

//var_dump(json_decode($_POST, true));
$formPacket = $_POST;
$formPacket['personId'] = 5;
$formPacket['oldVal_personId'] = 5;
$formPacket['oldVal_version'] = 5;
$formPacket['openTS'] = date('Y-m-d H:i:s');
$formPacket['version'] = 5;
$formPacket['dayBirth'] = 5;
$formPacket['monthBirth'] = 7;
$formPacket['yearBirth'] = 9;
$formPacket['state'] = 0;
$formPacket['beforeChristBirth'] = (isset($_POST['beforeChristBirth'])) ? 1 : 0;
$formPacket['believer'] = (isset($_POST['believer'])) ? 1 : 0;


var_dump($formPacket);
$app = new App($biwi_config);

//$save = new SaveData($app, 1, 'person');

// Transaktion starten
$app->getDb()->beginTransaction();

// Kommentar speichern
$save = new SaveData($app, 1, 'person');
$save->save($formPacket);

// Transaktion beenden
$app->getDb()->commit();
