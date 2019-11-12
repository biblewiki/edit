<?php
// Config einbinden
require_once('../config/config.php');

// Authentifizierungsscript einbinden
require('../core/php/auth.php');

//session_start();

?>

<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>BibleWiki</title>
<!-- plugins:css -->
<link rel="stylesheet" href="../core/vendors/ti-icons/css/themify-icons.css">
<link rel="stylesheet" href="../core/vendors/css/vendor.bundle.base.css">
<!-- endinject -->
<!-- Plugin css for this page -->
<link rel="stylesheet" href="../core/vendors/select2/select2.min.css">
<link rel="stylesheet" href="../core/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
<link rel="stylesheet" href="../core/vendors/summernote/dist/summernote-bs4.css">
<link rel="stylesheet" href="../core/vendors/quill/quill.snow.css">
<link rel="stylesheet" href="../core/vendors/simplemde/simplemde.min.css">
<link rel="stylesheet" href="../core/vendors/ion-rangeslider/css/ion.rangeSlider.css">
<!-- End plugin css for this page -->
<!-- inject:css -->
<link rel="stylesheet" href="../core/css/vertical-layout-light/style.css">
<!-- endinject -->
<link rel="shortcut icon" href="../images/favicon.png" />
<!-- Webapp Manifest -->
<link rel="manifest" href="../manifest.json" crossorigin="use-credentials">

<!-- Include app.js-->
<script src="../core/js/app.js"></script>
<script>
    if ('serviceWorker' in navigator) {
        // Register a service worker hosted at the root of the
        // site using the default scope.
        navigator.serviceWorker.register('../serviceworker.js').then(function(registration) {
            //console.log('Service worker registration succeeded:', registration);
        }, /*catch*/ function(error) {
            console.log('Service worker registration failed:', error);
        });
    } else {
        console.log('Service workers are not supported.');
    }
</script>
