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
                    name: 'btnLogin',
                    iconChar: '&#xf00c',
                    isDefault: true,
                    caption: this._app.getText('Speichern'),
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
                elements: [
                    {
                        xtype: 'biwi.default.source.WebSourceFields',
                    }
                ],
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
                elements: [
                    {
                        xtype: 'biwi.default.source.OtherSourceFields',
                    }
                ],
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

    _deleteBibleSource(e) {
        this.down('bibleSources').remove(e.element);
    }

    _fillSources() {
        let bibleSource = [];

        for (let i=1; i<=this._sources.bible.length; i++) {
            bibleSource.push(
                {
                    xtype: 'biwi.default.source.BibleSourceFields',
                    books: this._bibleBooks,
                    on: {
                        deleteBibleSource: this._deleteBibleSource,
                        context: this
                    }
                }
            );
        }

        if (!bibleSource) {
            bibleSource.push(
                {
                    xtype: 'biwi.default.source.BibleSourceFields',
                    books: this._bibleBooks,
                    on: {
                        deleteBibleSource: this._deleteBibleSource,
                        context: this
                    }
                }
            );
        }

        this.down('bibleSources').on('add', this._onAfterAdd, this);
        this.down('bibleSources').add(bibleSource);
    }


    _getBibleBooks() {
        let params = {};

        return new Promise((resolve) => {
            this._app.rpc.do('bible.getBibleBooks', params, function(response) {
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
                books: this._bibleBooks,
                on: {
                    deleteBibleSource: this._deleteBibleSource,
                    context: this
                }
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

    _onAfterAdd(e) {
        kijs.Array.each(this.down('bibleSources').elements, function(element, i) {
            this._sources.bible[i].openTS = kijs.Date.format(new Date(), 'Y-m-d H:i:s');
            element.values = this._sources.bible[i];
        }, this);
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
            if (!error) {
                if(!formPanel.validate()) {
                    kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurden noch nicht alle Felder korrekt ausgefüllt.'));
                    error = true;
                    return;
                } else {
                    bibleSources[i] = formPanel.data;
                }
            }
        }, this);

        // Webseiten Quellen auslesen
        kijs.Array.each(this.down('webSources').elements, function(formPanel, i) {
            if (formPanel.validate()) {
               webSources[i]= formPanel.data;
            }
        }, this);

        // Andere Quellen auslesen
        kijs.Array.each(this.down('otherSources').elements, function(formPanel, i) {
            if (formPanel.validate()) {
                otherSources[i] = formPanel.data;
            }
        }, this);

        if (!error) {

            // Quellen in Array zusammenfügen
            sources.field = this._field;
            sources.values.bible = bibleSources;
            sources.values.web = webSources;
            sources.values.other = otherSources;

            // Event werfen
            this.raiseEvent('saveSource', sources);

            // Fenster schliessen
            this.close();
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