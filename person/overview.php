<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Personen | BibleWiki</title>
        <?php include('../core/php/template/header.php'); ?>

        <!-- plugin css for this page -->
        <link rel="stylesheet" href="../core/vendors/jsgrid/jsgrid.min.css">
        <link rel="stylesheet" href="../core/vendors/jsgrid/jsgrid-theme.min.css">
        <!-- End plugin css for this page -->
    </head>

    <body class="sidebar-dark">
        <div class="container-scroller">
            <!-- partial:partials/_navbar.html -->
            <?php include('../core/php/template/navbar.php'); ?>
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
                <!-- partial:partials/_settings-panel.html -->
                <?php include('../core/html/settings_panel.html'); ?>
                <!-- partial -->
                <!-- partial:partials/_sidebar.html -->
                <?php include('../core/php/template/sidebar.php'); ?>
                <!-- partial -->
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="row grid-margin">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Personen</h4>
                                        <p class="card-description">Alle erfassten Personen</p>
                                        <div id="js-grid" class="pt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- content-wrapper ends -->
                    <!-- partial:partials/_footer.html -->
                    <?php include('../core/php/template/footer.php'); ?>
                    <!-- partial -->
                </div>
                <!-- main-panel ends -->
            </div>
            <!-- page-body-wrapper ends -->
        </div>
        <!-- container-scroller -->
        <!-- plugins:js -->
        <script src="../core/vendors/js/vendor.bundle.base.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page -->
        <script src="../core/vendors/jsgrid/jsgrid.min.js"></script>
        <!-- End plugin js for this page -->
        <!-- inject:js -->
        <script src="../core/js/off-canvas.js"></script>
        <script src="../core/js/hoverable-collapse.js"></script>
        <script src="../core/js/template.js"></script>
        <script src="../core/js/settings.js"></script>
        <script src="../core/js/todolist.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page-->
        <script src="../core/js/person.js"></script>
        <script src="../core/js/db.js"></script>
        <!-- End custom js for this page-->
    </body>

</html>