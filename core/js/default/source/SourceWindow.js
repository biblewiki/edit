/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.default.source.SourceWindow
// --------------------------------------------------------------
kijs.createNamespace('biwi.default.source');

biwi.default.source.SourceWindow = class biwi_default_source_SourceWindow extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._field = '';
        this._id = null;
        this._version = null;
        this._facadeFnLoad = null;
        this._bibleBooks = [];
        this._sources = [];

        // Config generieren
        config = Object.assign({}, {
            caption: this._app.getText('Quellen'),
            cls: 'kg-app-loginwindow',
            iconChar: '&#xf039',
            width: 600,
            height: 900,
            closable: true,
            maximizable: false,
            resizable: false,
            modal: true,
            footerElements:[
                {
                    xtype: 'kijs.gui.Button',
                    isDefault: true,
                    caption: this._app.getText('Hinzufügen'),
                    on: {
                        click: this._onSaveClick,
                        context: this
                    }
                }
            ],
            innerStyle: {
                overflow: 'auto'
            }
        }, config);

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            field: true,
            sources: true
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }

        // Biblebücher laden
        this._getBibleBooks().then(() => {

            // FormPanel erstellen
            this.add(this._createElements());
            this._fillSources();
        });
    }


    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // PROTECTED
    _createElements() {
        return [
            {
                xtype: 'kijs.gui.Panel',
                name: 'bibleSources',
                caption: this._app.getText('Bibelstellen'),
                collapsible: 'top',
                footerElements: [
                    {
                        xtype: 'kijs.gui.Button',
                        caption: this._app.getText('Hinzufügen'),
                        on: {
                            click: this._onAddBibleSourceClick,
                            context: this
                        }
                    }
                ]
            },{
                xtype: 'kijs.gui.FormPanel',
                name: 'webSources',
                caption: this._app.getText('Webseiten'),
                collapsible: 'top',
                footerElements: [
                    {
                        xtype: 'kijs.gui.Button',
                        caption: this._app.getText('Hinzufügen'),
                        on: {
                            click: this._onAddWebsiteSourceClick,
                            context: this
                        }
                    }
                ]
            },{
                xtype: 'kijs.gui.FormPanel',
                name: 'otherSources',
                caption: this._app.getText('Andere Quellen'),
                collapsible: 'top',
                footerElements: [
                    {
                        xtype: 'kijs.gui.Button',
                        caption: this._app.getText('Hinzufügen'),
                        on: {
                            click: this._onAddOtherBooksClick,
                            context: this
                        }
                    }
                ]
            }
        ];
    }

    _fillSources() {

        // Bibelquellen
        if (this._sources && !kijs.isEmpty(this._sources.bible)){
            let bibleSource = [];

            // Für alle Quellen ein FormPanel in das Array pushen
            kijs.Object.each(this._sources.bible, function() {
                bibleSource.push(
                    {
                        xtype: 'biwi.default.source.BibleSourceFields',
                        books: this._bibleBooks
                    }
                );
            }, this);

            // Elemente hinzufügen
            this.down('bibleSources').add(bibleSource);

            // Alle neu hinzugefügten Panel mit Daten abfüllen
            kijs.Array.each(this.down('bibleSources').elements, function(element, i) {
                element.values = this._sources.bible[i];
            }, this);

        // Standardmässig ein FormPanel hinzufügen
        } else {
            this.down('bibleSources').add(
                {
                    xtype: 'biwi.default.source.BibleSourceFields',
                    books: this._bibleBooks
                }
            );
        }

        // Webquellen
        if (this._sources && !kijs.isEmpty(this._sources.web)){
            let webSource = [];

            // Für alle Quellen ein FormPanel in das Array pushen
            kijs.Object.each(this._sources.web, function() {
                webSource.push(
                    {
                        xtype: 'biwi.default.source.WebSourceFields'
                    }
                );
            }, this);

            // Elemente hinzufügen
            this.down('webSources').add(webSource);

            // Alle neu hinzugefügten Panel mit Daten abfüllen
            kijs.Array.each(this.down('webSources').elements, function(element, i) {
                element.data = this._sources.web[i];
            }, this);
        }

        // Andere Quellen
        if (this._sources && !kijs.isEmpty(this._sources.other)){
            let otherSource = [];

            // Für alle Quellen ein FormPanel in das Array pushen
            kijs.Object.each(this._sources.other, function() {
                otherSource.push(
                    {
                        xtype: 'biwi.default.source.OtherSourceFields'
                    }
                );
            }, this);

            // Elemente hinzufügen
            this.down('otherSources').add(otherSource);

            // Alle neu hinzugefügten Panel mit Daten abfüllen
            kijs.Array.each(this.down('otherSources').elements, function(element, i) {
                element.data = this._sources.other[i];
            }, this);
        }
    }


    _getBibleBooks() {
        let params = {};

        return new Promise((resolve) => {
            this._app.rpc.do('book.getForCombo', params, function(response) {
               this._bibleBooks = response.books;
               resolve();
            }, this, false, 'none');
        });
    }


    // EVENTS

    _onAddBibleSourceClick() {
        this.down('bibleSources').add(
            {
                xtype: 'biwi.default.source.BibleSourceFields',
                books: this._bibleBooks
            }
        );
    }

    _onAddOtherBooksClick() {
        this.down('otherSources').add(
            {
                xtype: 'biwi.default.source.OtherSourceFields'
            }
        );
    }

    _onAddWebsiteSourceClick() {
        this.down('webSources').add(
            {
                xtype: 'biwi.default.source.WebSourceFields'
            }
        );
    }

    _onSaveClick() {
        let sources = [];
        let error = false;
        sources.values = {};
        let bibleSources = {};
        let webSources = {};
        let otherSources = {};

        // Bibel Quellen auslesen
        kijs.Array.each(this.down('bibleSources').elements, function(formPanel, i) {

            // Wenn Formular nicht leer ist und es nicht valid ist
            if (!formPanel.isEmpty && !formPanel.validate()) {
                error = true;

            // Wenn eine ID übergeben wurde oder das Formular nicht leer ist, Daten in Array schreiben
            } else if (formPanel.data.bibleSourceId || !formPanel.isEmpty) {
                bibleSources[i] = formPanel.data;
            }
        }, this);

        // Webseiten Quellen auslesen
        kijs.Array.each(this.down('webSources').elements, function(formPanel, i) {

            // Wenn Formular nicht leer ist und es nicht valid ist
            if (!formPanel.isEmpty && !formPanel.validate()) {
                error = true;

            // Wenn eine ID übergeben wurde oder das Formular nicht leer ist, Daten in Array schreiben
            } else if (formPanel.data.webSourceId || !formPanel.isEmpty) {
               webSources[i]= formPanel.data;
            }
        }, this);

        // Andere Quellen auslesen
        kijs.Array.each(this.down('otherSources').elements, function(formPanel, i) {

            // Wenn Formular nicht leer ist und es nicht valid ist
            if (!formPanel.isEmpty && !formPanel.validate()) {
                error = true;

            // Wenn eine ID übergeben wurde oder das Formular nicht leer ist, Daten in Array schreiben
            } else if (formPanel.data.otherSourceId || !formPanel.isEmpty) {
                otherSources[i] = formPanel.data;
            }
        }, this);

        if (!error) {

            // Quellen in Objekt zusammenfügen
            sources.field = this._field;
            sources.values.bible = bibleSources;
            sources.values.web = webSources;
            sources.values.other = otherSources;

            let ret = {
                name: 'sources',
                data: sources
            };

            // Event werfen
            this.raiseEvent('saveSource', ret);

            // Fenster schliessen
            this.close();
        } else {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurden noch nicht alle Felder korrekt ausgefüllt.'));
        }
    }



    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct(preventDestructEvent) {
        // Event auslösen.
        if (!preventDestructEvent) {
            this.raiseEvent('destruct');
        }

        // Basisklasse auch entladen
        super.destruct(true);

        // Variablen (Objekte/Arrays) leeren
        this._app = null;
        this._field = null;
        this._id = null;
        this._version = null;
        this._facadeFnLoad = null;
        this._bibleBooks = null;
        this._sources = null;
    }
};