
app = class app {
    static getText(text) {

        let url_string = window.location.href; //window.location.href
        let url = new URL(url_string);
        //let language = url.searchParams.get("language");
        let language = 'de-CH';

        let languageFile = "../core/languages/" + language + ".json";

        let request = new XMLHttpRequest();
        request.open("GET", languageFile, false);
        request.send(null);
        let my_JSON_object = JSON.parse(request.responseText);

        if (typeof (my_JSON_object[text]) !== "undefined") {
            text = my_JSON_object[text];
            console.log('exist');
        } else {
            //this.writeTxtFile(text, language);
            //console.log('"' + text + '" not translated in ' + language);
        }

        return text;
    }


    static writeTxtFile(text, language) {

        let xhttp = new XMLHttpRequest();
        xhttp.open("GET", "php/text.php?text=" + text + '&language=' + language, true);
        xhttp.send();

    }
}