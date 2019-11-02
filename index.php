<?php
// Settings einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/settings.php');

// Authentifizierungsscript einbinden
require(SCRIPT_PATH.'/php/auth.php');

header('LOCATION: dashboard');

?>