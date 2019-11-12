<?php

// Authentifizierungsscript einbinden
require('./core/php/auth.php');

$logdatei = fopen("log/index.txt","a");
$source = $_GET['source'] ? $_GET['source'] : 'browser';

fputs($logdatei,
    date("d.m.Y, H:i:s",time()) .
    ", " . $_SERVER['REMOTE_ADDR'] .
    ", " . $_SERVER['HTTP_USER_AGENT'] .
    ", " . $source ."\n"
    );
fclose($logdatei);

header('LOCATION: dashboard');
