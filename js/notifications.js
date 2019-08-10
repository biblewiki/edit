$(function() {
    var urlVars = getUrlVars();
    var notif = urlVars["notif"];
    var type = urlVars["type"];

    if (typeof notif !== 'undefined') {
        notification(type, notif);
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
function notification(type = 'info', code) {

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