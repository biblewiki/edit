<?php
require_once('settings.php');
session_start();

if (!$_SESSION['loggedin'] || !$_COOKIE['LOGGEDIN']) {
    $get = '';
    if (!$_SESSION['loggedin']){
        $get = '?login=expired';
    }
    header('Location: '.LOGIN_HOST.'/logout.php'.$get);
}

