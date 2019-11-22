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
                                        <form id="biwi-form" action="Person.saveForm" method="POST">
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Name'));</script></label>
                                                    <input type="text" class="form-control" name="name"/><button><script>document.write(app.getText('Quelle'));</script></button>
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Eindeutigkeit'));</script></label>
                                                    <input type="text" class="form-control" name="description"/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <div class="form-group row">
                                                        <label class="col-sm-1 col-form-label"><script>document.write(app.getText('Geschlecht'));</script></label>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="sex" id="membershipRadios1" value="m" >
                                                                    Mann
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="sex" id="membershipRadios2" value="w">
                                                                    Frau
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="sex" id="membershipRadios3" value="u">
                                                                    Unbekannt
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <label class="col-sm-1 col-form-label"><script>document.write(app.getText('Christ'));</script></label>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="believer" id="membershipRadios1" value="1">
                                                                    Ja
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="believer" id="membershipRadios2" value="2">
                                                                    Nein
                                                                    <i class="input-helper"></i>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="form-check">
                                                                <label class="form-check-label">
                                                                    <input type="radio" class="form-check-input" name="believer" id="membershipRadios3" value="3">
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
                                                    <label><script>document.write(app.getText('Beruf'));</script></label>
                                                    <input type="text" class="form-control" name="proficiency"/>
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Menschengruppe'));</script></label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Vater'));</script></label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Alter Vater'));</script></label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Mutter'));</script></label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Kind'));</script></label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Alter bei Geburt'));</script></label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Ehepartner'));</script></label>
                                                    <input type="text" class="form-control" name=""/>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Beziehung mit'));</script></label><br>
                                                    <select class="js-example-basic-single w-50">
                                                        <option value="AL">Alabama</option>
                                                        <option value="WY">Wyoming</option>
                                                        <option value="AM">America</option>
                                                        <option value="CA">Canada</option>
                                                        <option value="RU">Russia</option>
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Beziehungsart'));</script></label><br>
                                                    <select class="js-example-basic-single w-50">
                                                        <option value="AL">Alabama</option>
                                                        <option value="WY">Wyoming</option>
                                                        <option value="AM">America</option>
                                                        <option value="CA">Canada</option>
                                                        <option value="RU">Russia</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">                                            
                                                    <label><script>document.write(app.getText('Geburt') + ' ' + app.getText('Tag'));</script></label>
                                                    <input type="text" class="form-control" name="dayBirth" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Monat'));</script></label>
                                                    <input type="text" class="form-control" name="monthBirth" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Jahr'));</script></label>
                                                    <input type="text" class="form-control" name="yearBirth" />
                                                    <div class="form-check form-check-flat form-check-primary">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="beforeChristBirth" value="1"/>
                                                            Vor Christi Geburt
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col">                                            
                                                    <label><script>document.write(app.getText('Tod') + ' ' + app.getText('Tag'));</script></label>
                                                    <input type="text" class="form-control" name="dayDeath" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Monat'));</script></label>
                                                    <input type="text" class="form-control" name="monthDeath" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Jahr'));</script></label>
                                                    <input type="text" class="form-control" name="yearDeath" />
                                                    <div class="form-check form-check-flat form-check-primary">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="beforeChristDeath" value="1"/>
                                                            Vor Christi Geburt
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col">                                            
                                                    <label><script>document.write(app.getText('Beginn Wirkung') + ' ' + app.getText('Tag'));</script></label>
                                                    <input type="text" class="form-control" name="dayProfessionStart" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Monat'));</script></label>
                                                    <input type="text" class="form-control" name="monthProfessionStart" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Jahr'));</script></label>
                                                    <input type="text" class="form-control" name="yearProfessionStart" />
                                                    <div class="form-check form-check-flat form-check-primary">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="beforeChristProfStart" value="1"/>
                                                            Vor Christi Geburt
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col">                                            
                                                    <label><script>document.write(app.getText('Ende Wirkung') + ' ' + app.getText('Tag'));</script></label>
                                                    <input type="text" class="form-control" name="dayProfessionEnd" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Monat'));</script></label>
                                                    <input type="text" class="form-control" name="monthProfessionEnd" />
                                                </div>
                                                <div class="col">
                                                    <label><script>document.write(app.getText('Jahr'));</script></label>
                                                    <input type="text" class="form-control" name="yearProfessionEnd" />
                                                    <div class="form-check form-check-flat form-check-primary">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="beforeChristProfEnd" value="1"/>
                                                            Vor Christi Geburt
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label><script>document.write(app.getText('Fliesstext'));</script></label></br>
                                                <textarea id='flowText' name="text">
                                                </textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary mr-2"><script>document.write(app.getText('Speichern'));</script></button>
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
                                        <div class="slider-wrap">
                                            <label><script>document.write(app.getText('Sichtbarkeitslevel'));</script></label>
                                            <input type="text" id="level" name="level" value="" />
                                        </div>
                                        <div class="form-check form-check-flat form-check-primary">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">
                                                Nur in Stammbaum
                                            </label>
                                        </div>
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
        <script src="../core/vendors/ion-rangeslider/js/ion.rangeSlider.min.js"></script>
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
        <script src="../core/js/person.js"></script>
        <script src="../core/js/form/form.js"></script>
        <!-- End custom js for this page-->
    </body>
</html>