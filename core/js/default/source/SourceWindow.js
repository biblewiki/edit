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
        this._formPanel = null;
        this._primaryKey = null;
        this._assignTable = null;
        this._sourceFnLoad = null;

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
            id: true,
            version: true,
            field: true,
            formPanel: true,
            primaryKey: true,
            assignTable: true,
            sourceFnLoad: true
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }

        // Biblebücher laden
        this._getBibleBooks().then(() => {

            // Vorhandene Quellen laden
            this._getSources(this._field).then(() => {

                // FormPanel erstellen
                this.add(this._createElements());
                this._fillSources();
            });
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

    // Quellen vom Server holen
    _getSources() {
        return new Promise((resolve) => {

            // Überprüfen, ob Objekt "sources" bereits existiert. Sonst wird es erstellt
            if (!this._formPanel.data.sources) {
                this._formPanel.data.sources = {};
            }

            // Wenn schon Daten für dieses Feld im Datenpacket vorhanden sind, werden diese zurückgegeben
            if (this._formPanel.data.sources[this._field]) {
                resolve(this._formPanel.data.sources[this._field]);

            // Überprüfen ob eine ID vorhanden ist. Dies bedeutet, dass der Eintrag bereits in der DB ist.
            // Wenn ja, werden die Quellen vom Server geholt
            } else if (this._primaryKey || this._id) {

                // Argumente vorbereiten
                let params = {};
                params.id = this._primaryKey ? this._formPanel.data[this._primaryKey] : this._id
                params.version = this._version;
                params.field = this._field;
                params.assignTable = this._assignTable;

                // Objekt für Feldnamen im Datenpacket erstellen
                this._formPanel.data.sources[this._field] = {};

                // Server Abfrage ausführen
                this._app.rpc.do(this._sourceFnLoad, params, function(response) {

                    // Quellen in Form Data schreiben
                    kijs.Object.each(response, function(sourceType, values) {
                        this._formPanel.data.sources[this._field][sourceType] = {};

                        // Aus dem Array ein Objekt machen
                        kijs.Array.each(values, function(value, index) {
                            this._formPanel.data.sources[this._field][sourceType][index] = value;
                        }, this);
                    }, this);

                    // Resolve ausführen
                    resolve();

                }, this);
            } else {
                kijs.gui.MsgBox.error('Kein Primary Key oder ID angegeben um die Quellen zu laden');
                resolve();
            }
        });
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

    // Vorhandene Quellen abfüllen
    _fillSources() {

        // Bibelquellen
        if (this._formPanel.data.sources[this._field] && !kijs.isEmpty(this._formPanel.data.sources[this._field].bible)){

            // Für alle Quellen ein FormPanel in das Array pushen
            kijs.Object.each(this._formPanel.data.sources[this._field].bible, function(id, source) {
                if (!source.state || source.state < 100) {
                    this.down('bibleSources').add(
                        {
                            xtype: 'biwi.default.source.BibleSourceFields',
                            books: this._bibleBooks,
                            name: 'bibleSource_' + id
                        }
                    );
                    this.down('bibleSources').down('bibleSource_' + id).data = source;
                }
            }, this);

        // Standardmässig ein FormPanel hinzufügen
        } else {
            this.down('bibleSources').add(
                {
                    xtype: 'biwi.default.source.BibleSourceFields',
                    books: this._bibleBooks,
                    name: 'bibleSource_1'
                }
            );
        }

        // Webquellen
        if (this._formPanel.data.sources[this._field] && !kijs.isEmpty(this._formPanel.data.sources[this._field].web)){

            // Für alle Quellen ein FormPanel in das Array pushen
            kijs.Object.each(this._formPanel.data.sources[this._field].web, function(id, source) {
                if (!source.state || source.state < 100) {
                    this.down('webSources').add(
                        {
                            xtype: 'biwi.default.source.WebSourceFields',
                            name: 'webSource_' + id
                        }
                    );
                    this.down('webSources').down('webSource_' + id).data = source;
                }
            }, this);
        }

        // Andere Quellen
        if (this._formPanel.data.sources[this._field] && !kijs.isEmpty(this._formPanel.data.sources[this._field].other)){

            // Für alle Quellen ein FormPanel in das Array pushen
            kijs.Object.each(this._formPanel.data.sources[this._field].other, function(id, source) {
                if (!source.state || source.state < 100) {
                    this.down('otherSources').add(
                        {
                            xtype: 'biwi.default.source.OtherSourceFields',
                            name: 'otherSource_' + id
                        }
                    );
                    this.down('otherSources').down('otherSource_' + id).data = source;
                }
            }, this);
        }
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
        let sources = {};
        let error = false;
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
            sources.bible = bibleSources;
            sources.web = webSources;
            sources.other = otherSources;

            // Sources in FormPanel Data schreiben
            this._formPanel.data.sources[this._field]= sources;

            // Form is Dirty setzen, da die Formulardaten geändert haben
            this._formPanel.isDirty = true;

            // Event werfen
            this.raiseEvent('saved');

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
        this._formPanel = null;
        this._primaryKey = null;
        this._assignTable = null;
        this._sourceFnLoad = null;
    }
};