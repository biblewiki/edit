<!DOCTYPE html>
<html lang="en">

    <head>
        <title>Personen | BibleWiki</title>
        <?php include('../core/php/template/header.php'); ?>
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
                            <div class="col-lg-8 grid-margin stretch-card">
                                <!--form mask starts-->
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title"><script>document.write(app.getText('Person'));</script></h4>
                                        <p class="card-description">Personendetails erfassen</p>
                                        <form class="forms-sample">
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label>Name</label>
                                                    <input type="text" class="form-control" name="name"/><button><script>document.write(app.getText('Quelle'));</script></button>
                                                </div>
                                                <div class="col">
                                                    <label>Last Name</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <div class="form-group row">
                                                        <label class="col-sm-1 col-form-label"><script>document.write(app.getText('Geschlecht'));</script></label>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="membershipRadios" id="membershipRadios1" value="m" >
                                                                    Mann
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="membershipRadios" id="membershipRadios2" value="w">
                                                                    Frau
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="membershipRadios" id="membershipRadios3" value="u">
                                                                    Unbekannt
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <label class="col-sm-1 col-form-label"><script>document.write(app.getText('Christ'));</script></label>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="membershipRadios" id="membershipRadios1" value="1">
                                                                    Ja
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="membershipRadios" id="membershipRadios2" value="2">
                                                                    Nein
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="membershipRadios" id="membershipRadios3" value="3">
                                                                    Unbekannt
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label>Beruf</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label>Menschengruppe</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label>Vater</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label>Alter Vater</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label>Mutter</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label>Kind</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label>Alter bei Geburt</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label>Ehepartner</label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label>Beziehung mit</label>
                                                    <select class="js-example-basic-single w-100">
                                                        <option value="AL">Alabama</option>
                                                        <option value="WY">Wyoming</option>
                                                        <option value="AM">America</option>
                                                        <option value="CA">Canada</option>
                                                        <option value="RU">Russia</option>
                                                      </select>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label>Beziehungsart</label>
                                                        <select class="js-example-basic-single w-100">
                                                          <option value="AL">Alabama</option>
                                                          <option value="WY">Wyoming</option>
                                                          <option value="AM">America</option>
                                                          <option value="CA">Canada</option>
                                                          <option value="RU">Russia</option>
                                                        </select>
                                                      </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">                                            
                                                    <label>Geburt</label>
                                                    <input class="form-control" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy" />
                                                </div>
                                                <div class="col">                                            
                                                    <label>Tod</label>
                                                    <input class="form-control" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy" />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">                                            
                                                    <label>Beginn Wirkung</label>
                                                    <input class="form-control" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy" />
                                                </div>
                                                <div class="col">                                            
                                                    <label>Ende Wirkung</label>
                                                    <input class="form-control" data-inputmask="'alias': 'datetime'" data-inputmask-inputformat="dd/mm/yyyy" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Fliesstext</label></br>
                                                <textarea id='flowText'>
                                                </textarea>
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
                                        <p class="card-title">Infos</p>
                                            <div class="table-responsive">
                                                <table class="table">
                                                  <tbody>
                                                    <tr>
                                                      <td><script>document.write(app.getText('Kaegorie'));</script></td>
                                                      <td><script>document.write(app.getText('Personen'));</script></td>
                                                      <td></td>
                                                    </tr>
                                                    <tr>
                                                      <td><script>document.write(app.getText('Status'));</script></td>
                                                      <td><script>document.write(app.getText('Entwurf'));</script></td>
                                                      <td></td>
                                                    </tr>
                                                    <tr>
                                                      <td><script>document.write(app.getText('Erstellt'));</script></td>
                                                      <td>Joel Kohler</td>
                                                      <td>11.10.2019</td>
                                                    </tr>
                                                    <tr>
                                                      <td><script>document.write(app.getText('Revisionen'));</script></td>
                                                      <td>Josua</td>
                                                      <td>12.10.2019</td>
                                                    </tr>
                                                    <tr>
                                                      <td></td>
                                                      <td>Joel Kohler</td>
                                                      <td>01.11.2019</td>
                                                    </tr>
                                                  </tbody>
                                                </table>
                                              </div>
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
        <script src="../core/vendors/typeahead.js/typeahead.bundle.min.js"></script>
        <script src="../core/vendors/select2/select2.min.js"></script>
        <script src="../core/vendors/tinymce/tinymce.min.js"></script>
        <!-- End plugin js for this page -->
        <!-- inject:js -->
        <script src="../core/js/off-canvas.js"></script>
        <script src="../core/js/hoverable-collapse.js"></script>
        <script src="../core/js/template.js"></script>
        <script src="../core/js/settings.js"></script>
        <script src="../core/js/todolist.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page-->
        <script src="../core/js/file-upload.js"></script>
        <script src="../core/js/typeahead.js"></script>
        <script src="../core/js/select2.js"></script>
        <script src="../js/newPerson.js"></script>
        <!-- End custom js for this page-->
    </body>
</html>