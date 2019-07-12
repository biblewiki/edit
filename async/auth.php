<?php
require_once('settings.php');
session_start();

if ($_SESSION['login']) {
    $GLOBALS['loggedin'] = true;
} else {
    session_destroy();
    $GLOBALS['loggedin'] = false;
    header('Location: '.LOGIN_HOST.'?login=warning');
}
