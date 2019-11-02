<?php
// Settings einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/settings.php');

// Authentifizierungsscript einbinden
require(SCRIPT_PATH.'/php/auth.php');

?>

<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>BibleWiki</title>
<!-- plugins:css -->
<link rel="stylesheet" href="vendors/ti-icons/css/themify-icons.css">
<link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
<!-- endinject -->
<!-- Plugin css for this page -->
<!-- End plugin css for this page -->
<!-- inject:css -->
<link rel="stylesheet" href="css/vertical-layout-light/style.css">
<!-- endinject -->
<link rel="shortcut icon" href="images/favicon.png" />


<!-- Include text.js-->
<script src="js/app.js"></script>

<!-- Service Worker -->
<script>
    if ('serviceWorker' in navigator) {
    // Register a service worker hosted at the root of the
    // site using the default scope.
    navigator.serviceWorker.register('serviceworker.js').then(function(registration) {
        console.log('Service worker registration succeeded:', registration);
    }, /*catch*/ function(error) {
        console.log('Service worker registration failed:', error);
    });
    } else {
    console.log('Service workers are not supported.');
    }
</script>

<!-- Webapp Manifest -->
<link rel="manifest" href="manifest.json" crossorigin="use-credentials">