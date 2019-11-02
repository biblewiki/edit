class app {
    getText(text) {

        var url_string = window.location.href; //window.location.href
        var url = new URL(url_string);
        var language = url.searchParams.get("language");


        let languageFile = "languages/" + language + ".json";


        let request = new XMLHttpRequest();
        request.open("GET", languageFile, false);
        request.send(null)
        let my_JSON_object = JSON.parse(request.responseText);

        if (typeof (my_JSON_object[text]) !== "undefined") {
            text = my_JSON_object[text];
            console.log('exist');
        } else {
            writeTxtFile(text, language);
            console.log('not exist');
        }

        return text;
    }


    writeTxtFile(text, language) {

        let xhttp = new XMLHttpRequest();
        xhttp.open("GET", "php/text.php?text=" + text + '&language=' + language, true);
        xhttp.send();

    }
}