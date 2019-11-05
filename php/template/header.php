<?php
// Settings einbinden
require_once($_SERVER['DOCUMENT_ROOT'] . '/php/settings.php');

// Authentifizierungsscript einbinden
require(SCRIPT_PATH.'/php/auth.php');

//session_start();

?>

<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>BibleWiki</title>
<!-- plugins:css -->
<link rel="stylesheet" href="<?php echo EDIT_HOST ?>/vendors/ti-icons/css/themify-icons.css">
<link rel="stylesheet" href="<?php echo EDIT_HOST ?>/vendors/css/vendor.bundle.base.css">
<!-- endinject -->
<!-- Plugin css for this page -->
<!-- End plugin css for this page -->
<!-- inject:css -->
<link rel="stylesheet" href="<?php echo EDIT_HOST ?>/css/vertical-layout-light/style.css">
<!-- endinject -->
<link rel="shortcut icon" href="<?php echo EDIT_HOST ?>/images/favicon.png" />
<!-- Webapp Manifest -->
<link rel="manifest" href="<?php echo EDIT_HOST ?>/manifest.json" crossorigin="use-credentials">

<!-- Include text.js-->
<script src="<?php echo EDIT_HOST ?>/js/app.js"></script>

<!-- Service Worker -->
<script>
    if ('serviceWorker' in navigator) {
    // Register a service worker hosted at the root of the
    // site using the default scope.
    navigator.serviceWorker.register('<?php echo EDIT_HOST ?>/serviceworker.js').then(function(registration) {
        //console.log('Service worker registration succeeded:', registration);
    }, /*catch*/ function(error) {
        console.log('Service worker registration failed:', error);
    });
    } else {
        console.log('Service workers are not supported.');
    }
</script>
