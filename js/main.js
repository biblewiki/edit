// via what benachrictigungen
$(".button-email-tel").click(makeActive);

function makeActive(e) {
    var button = $(e.target).parent();

    $(".button-email-tel").removeClass("active");
    $(button).addClass("active");
}

// password generator
$(".password-change").click(generatePassword);

function generatePassword(e) {
    var input = $(".input-item.password input");

    $(input).val(randString(input));
    $(input).parent().addClass("ok");
    $(input).focus();
}

function randString(id) {
    var dataSet = $(id).attr('data-character').split(',');
    var possible = '';
    if ($.inArray('a-z', dataSet) >= 0) {
        possible += 'abcdefghkmnprstuvwxyz';
    }
    if ($.inArray('A-Z', dataSet) >= 0) {
        possible += 'ABCDEFGHJKLMNPRSTUVWXYZ';
    }
    if ($.inArray('0-9', dataSet) >= 0) {
        possible += '123456789';
    }
    if ($.inArray('#', dataSet) >= 0) {
        //possible += '![]{}()%&*$#^<>~@|';
        possible += '!.-?';
    }
    var text = '';
    for (var i = 0; i < $(id).attr('data-size'); i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

// input style
// valid or invalid
// --> give class "verified" wenn email, nummer verifiziert
$(document).ready(checkAllOk);
$(".input-item input").on("input", checkFocusOk);

function checkAllOk() {
    $(".input-item input").each(function () {
        if ($(this).val()) {
            $(this).parent().addClass("ok");
        }
    });
}

function checkFocusOk(e) {
    if ($(e.target).val()) {
        $(e.target).parent().addClass("ok");
    } else {
        $(e.target).parent().removeClass("ok");
    }
}

/*$(document).ready(checkValid);
$(".input-item input").focusout(checkValid2);

function checkValid() {
    $(".input-item input").each(function () {
        var val = $(this).val();

        if (val = ! -1) {
            if ($(this).parent().hasClass("email")) {
                if (validateEmail(val)) {
                    $(this).parent().addClass("valid");
                } else {
                    $(this).parent().addClass("invalid");
                }
            } else if ($(this).parent().hasClass("tel")) {
                if (validateTel(val)) {
                    $(this).parent().addClass("valid");
                } else {
                    $(this).parent().addClass("invalid");
                }
            } else if ($(this).parent().hasClass("password")) {
                if (validatePassword(val)) {
                    $(this).parent().addClass("valid");
                } else {
                    $(this).parent().addClass("invalid");
                }
            }
        } else {
            $(this).parent().removeClass("valid");
            $(this).parent().removeClass("invalid");
        }
    });
}

function checkValid2(e) {
    var val = $(e.target).val();

    if (val =! -1) {
        if ($(e.target).parent().hasClass("email")) {
            if (validateEmail(val)) {
                $(e.target).parent().addClass("valid");
            } else {
                $(e.target).parent().addClass("invalid");
            }
        } else if ($(e.target).parent().hasClass("tel")) {
            if (validateTel(val)) {
                $(e.target).parent().addClass("valid");
            } else {
                $(e.target).parent().addClass("invalid");
            }
        } else if ($(e.target).parent().hasClass("password")) {
            if (validatePassword(val)) {
                $(e.target).parent().addClass("valid");
            } else {
                $(e.target).parent().addClass("invalid");
            }
        }
    } else {
        $(e.target).parent().removeClass("valid");
        $(e.target).parent().removeClass("invalid");
    }
}

function validateEmail(val) {
    var re = new RegExp("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$");
    return re.test(val);
}

function validateTel(val) {
    var re = new RegExp("[0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}|[0-9]{10}");
    return re.test(val);
}

function validatePassword(val) {
    var re = new RegExp(".{6,}");
    return re.test(val);
}*/

// password visibility
$(".password .input-visible").click(changeType);


function changeType(e) {
    var eye = $(e.target).parent();
    var input = $(e.target).parent().parent().find("input");

    if ($(input).attr("type") == "password") {
        $(input).attr("type", "text");
        $(eye).find("i").addClass("fa-eye-slash").removeClass("fa-eye");
    } else if ($(input).attr("type") == "text") {
        $(input).attr("type", "password");
        $(eye).find("i").addClass("fa-eye").removeClass("fa-eye-slash");
    }
    $(input).focus();
}

// click on input --> focus
$(".input-name").click(nameSmall);
$(".input-status").click(nameSmall);

function nameSmall(e) {
    e.preventDefault();
    $(e.target).parent().find("input").focus();
}

// input full --> name top
$(".input-item input").focusout(nameToTop);
$(document).ready(checkInputs);

function nameToTop(e) {
    if ($(e.target).val() != "") {
        $(e.target).parent().find(".input-name").addClass("name-small");
    } else {
        $(e.target).parent().find(".input-name").removeClass("name-small");
    }
}

function checkInputs() {
    $(".input-item input").each(function () {
        if ($(this).val() != "") {
            $(this).parent().find(".input-name").addClass("name-small");
        } else {
            $(this).parent().find(".input-name").removeClass("name-small");
        }
    });
}

// activate current menu item
$(document).ready(activate);

function activate() {
    var pathName = window.location.pathname;
    pathName = decodeURIComponent(pathName).substr(1);
    pathName = pathName.charAt(0).toUpperCase() + pathName.slice(1);
    if (pathName == "") {
        var activate = $(".sidebar-item span:contains('Übersicht')");
    } else {
        var activate = $(".sidebar-item span:contains('" + pathName + "')");
    }
    activate.parent().addClass("active");
}

// open and close siedbar
var navBarMenu = $(".navbar-menu");
var sideBar = $(".sidebar");
var sideBarOpen = true;

navBarMenu.click(navbarMenu);
$(document).ready(setFalse);
$(window).on("resize", changeWidth);

function navbarMenu() {
    if (sideBarOpen) {
        closeSideBar()
        sideBarOpen = false;
    } else {
        openSideBar()
        sideBarOpen = true;
    }
}

function openSideBar() {
    var winWidth = $(window).width();
    if (winWidth <= "600") {
        $("html").attr("style", "--side-width: 100%; --side-font-size: 30px; --content-width: 100%");
    } else if (winWidth <= "1024") {
        $("html").attr("style", "--side-width: 250px; --side-font-size: 25px; --content-width: calc(100% - 250px);");
    } else if (winWidth > "1024") {
        $("html").attr("style", "--side-width: 300px; --side-font-size: 30px; --content-width: calc(100% - 300px);");
    }
}

function closeSideBar() {
    $("html").attr("style", "--side-width: 0; --side-font-size: 0; --content-width: 100%;");
}

function setFalse() {
    var winWidth = $(window).width();
    if (winWidth <= "600") {
        closeSideBar();
        sideBarOpen = false;
    }
}

function changeWidth() {
    if (sideBarOpen) {
        openSideBar();
        sideBarOpen = true;
    }
}