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
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Sortable table</h4>
                  <p class="card-description">Perform sorting action</p>
                  <div class="sort-panel d-flex align-items-ceter mb-4 pt-3">
                    <label class="d-flex justify-content-start mb-0">
                      Sorting Field:
                      <select id="sortingField" class="form-control form-control-sm mr-2 ml-2 w-25">
                        <option>Name</option>
                        <option>Age</option>
                        <option>Address</option>
                        <option>Country</option>
                        <option>Married</option>
                      </select>
                    </label>
                    <button type="button" id="sort" class="btn btn-info btn-sm">Sort</button>
                  </div>
                  <div id="js-grid-sortable"></div>
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
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../core/js/off-canvas.js"></script>
  <script src="../core/js/hoverable-collapse.js"></script>
  <script src="../core/js/template.js"></script>
  <script src="../core/js/settings.js"></script>
  <script src="../core/js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="../core/js/js-grid.js"></script>
  <script src="../core/js/db.js"></script>
  <!-- End custom js for this page-->
</body>

</html>