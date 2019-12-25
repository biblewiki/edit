<?php
require_once '../../config/config.php';
require_once './autoload.php';

$app = new App($biwi_config);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$timestamp = date('d.m.Y');

$database = $app->getConfig("database, db");
$user = $app->getConfig("database, user");
$pass = $app->getConfig("database, password");
$host = $app->getConfig("database, host");
$dir = "../../backup/";
$file = $timestamp.".sql";

exec("mysqldump --user={$user} --password={$pass} --host={$host} {$database} --result-file={$dir}{$file} 2>&1", $output);

var_dump($output);