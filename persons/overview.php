<!DOCTYPE html>
<html lang="en">

<head>
  <title>Personen | BibleWiki</title>
  <?php include('../php/template/header.php'); ?>

  <!-- plugin css for this page -->
  <link rel="stylesheet" href="../../../../vendors/jsgrid/jsgrid.min.css">
  <link rel="stylesheet" href="../../../../vendors/jsgrid/jsgrid-theme.min.css">
  <!-- End plugin css for this page -->
</head>

<body class="sidebar-dark">
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <?php include('../php/template/navbar.php'); ?>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_settings-panel.html -->
      <?php include('../html/settings_panel.html'); ?>
      <!-- partial -->
      <!-- partial:partials/_sidebar.html -->
      <?php include('../php/template/sidebar.php'); ?>
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
        <?php include('../php/template/footer.php'); ?>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="../vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../js/off-canvas.js"></script>
  <script src="../js/hoverable-collapse.js"></script>
  <script src="../js/template.js"></script>
  <script src="../js/settings.js"></script>
  <script src="../js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="../js/js-grid.js"></script>
  <script src="../js/db.js"></script>
  <!-- End custom js for this page-->
</body>

</html>