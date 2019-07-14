$(function() {
    var urlVars = getUrlVars();
    var login = urlVars["login"];

    if (typeof login !== 'undefined') {
        if (login != 'error' && login != 'success' && login != 'warning' && login != 'info') {
            notification('info', 'login_' + login);
        } else {
            notification(login, 'login');
        }
    }

    var logout = urlVars["logout"];

    if (typeof logout !== 'undefined') {
        if (logout != 'error' && logout != 'success' && logout != 'warning' && logout != 'info') {
            notification('info', 'logout_' + logout);
        } else {
            notification(logout, 'logout');
        }
    }

    var confirmed = urlVars["email_confirmed"];

    if (typeof confirmed !== 'undefined') {
        if (confirmed != 'error' && confirmed != 'success' && confirmed != 'warning' && confirmed != 'info') {
            notification('info', 'confirmed_' + confirmed);
        } else {
            notification(confirmed, 'confirmed');
        }
    }


});

// URL Parameter auslesen
function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
        vars[key] = value;
    });

    //GET-Paramter löschen
    window.history.pushState("", "", '/');

    return vars;
}

// Notifications anzeigen
function notification(type, code) {

    // JSON einlesen
    $.getJSON("lang/notifications_DE.json", function(data) {

        // Notification Parameter auslesen
        var notification = data[type][code];

        if (type === 'error') {
            //Notification anzeigen. Wird nicht von selbst ausgeblendet
            toastr[type](notification.text, notification.title, {
                "timeOut": "0",
                "extendedTimeout": "0"
            });
        } else {
            //Notification anzeigen. Wird von selbst ausgeblendet
            toastr[type](notification.text, notification.title);
        }


    });


}