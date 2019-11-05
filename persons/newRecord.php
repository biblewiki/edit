<!DOCTYPE html>
<html lang="en">

<head>
  <title>Personen | BibleWiki</title>
  <?php include('../php/template/header.php'); ?>
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
            <div class="col-lg-8 grid-margin stretch-card">
              <!--form mask starts-->
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Form mask</h4>
                  <p class="card-description">Gives a preview of input format</p>
                  <form class="forms-sample">
                    <div class="form-group row">
                      <div class="col">
                        <label>Date:</label>
                        <input class="form-control" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy" />
                      </div>
                      <div class="col">
                        <label>Date time:</label>
                        <input class="form-control" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy HH:MM:ss" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label>Date with custom placeholder:</label>
                      <input class="form-control" data-inputmask="'alias': 'datetime'" data-inputmask-placeholder="*" data-inputmask-inputformat="dd/mm/yyyy" />
                    </div>
                    <div class="form-group">
                      <label>Phone:</label>
                      <input class="form-control" data-inputmask-alias="(+99) 9999-9999" />
                    </div>
                    <div class="form-group">
                      <label>Currency:</label>
                      <input class="form-control" data-inputmask="'alias': 'currency'" />
                    </div>
                    <div class="form-group row">
                      <div class="col">
                        <label>Email:</label>
                        <input class="form-control" data-inputmask="'alias': 'email'" />
                      </div>
                      <div class="col">
                        <label>Ip:</label>
                        <input class="form-control" data-inputmask="'alias': 'ip'" />
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              <!--form mask ends-->
            </div>
            <!-- notifications starts -->
            <div class="col-md-4 stretch-card">
              <div class="card">
                <div class="card-body">
                  <p class="card-title">Notifications</p>
                  <ul class="icon-data-list">
                    <li>
                      <p class="text-primary mb-1">Isabella Becker</p>
                      <p class="text-muted">Sales dashboard have been created</p>
                      <small class="text-muted">9:30 am</small>
                    </li>
                    <li>
                      <p class="text-primary mb-1">Adam Warren</p>
                      <p class="text-muted">You have done a great job #TW11109872</p>
                      <small class="text-muted">10:30 am</small>
                    </li>
                    <li>
                      <p class="text-primary mb-1">Leonard Thornton</p>
                      <p class="text-muted">Sales dashboard have been created</p>
                      <small class="text-muted">11:30 am</small>
                    </li>
                    <li>
                      <p class="text-primary mb-1">George Morrison</p>
                      <p class="text-muted">Sales dashboard have been created</p>
                      <small class="text-muted">8:50 am</small>
                    </li>
                    <li>
                      <p class="text-primary mb-1">Ryan Cortez</p>
                      <p class="text-muted">Herbs are fun and easy to grow.</p>
                      <small class="text-muted">9:00 am</small>
                    </li>

                  </ul>
                </div>
              </div>
            </div>
            <!-- notifications ends -->
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
  <!-- End custom js for this page-->
</body>
</html>