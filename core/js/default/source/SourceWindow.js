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
        this._formPanel = null;
        this._field = '';
        this._bibleBooks = [];

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
            facadeFnLoad: true,
            facadeFnSave: { target: 'facadeFnSave', context: this._formPanel },
            rpc: { target: 'rpc', context: this._formPanel }
        });

        // Event-Weiterleitungen von this._formPanel
        this._eventForwardsAdd('afterSave', this._formPanel);

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }

        this._getBibleBooks().then(() =>{;

            // FormPanel erstellen
            this.add(this._createElements());
        });
    }


    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------
    get formPanel() { return this._formPanel; }
    set formPanel(val) { this._formPanel = val; }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // PROTECTED
    _createElements() {
        return [
            {
                xtype: 'kijs.gui.FormPanel',
                name: 'bibleSources',
                caption: this._app.getText('Bibelstellen'),
                collapsible: 'top',
                elements:[
                    {
                        xtype: 'biwi.default.source.BibleSourceField',
                        books: this._bibleBooks
                    }
                ],
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
                name: 'websites',
                caption: this._app.getText('Webseiten'),
                collapsible: 'top',
                defaults: {
                    width: 280,
                    height: 25,
                    labelWidth: 80,
                    required: true,
                    style:{
                        margin: '10px'
                   }
                },
                footerElements: [
                    {
                        xtype: 'kijs.gui.Button',
                        caption: this._app.getText('Webseite hinzufügen'),
                        on: {
                            click: this._onAddWebsiteSourceClick,
                            context: this
                        }
                    }
                ]
            },{
                xtype: 'kijs.gui.FormPanel',
                name: 'bookSources',
                caption: this._app.getText('Bücher'),
                collapsible: 'top',
                footerElements: [
                    {
                        xtype: 'kijs.gui.Button',
                        caption: this._app.getText('Buch hinzufügen'),
                        on: {
                            click: this._onAddOtherBooksClick,
                            context: this
                        }
                    }
                ]
            }
        ];
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
        this.down('bibleSources').add({
            xtype: 'biwi.default.source.BibleSourceField',
            books: this._bibleBooks
        });
    }

    _onAddOtherBooksClick() {
        this.down('bookSources').add({
            xtype: 'biwi.default.source.BookSourceField'
        });
    }

    _onAddWebsiteSourceClick() {
        this.down('websites').add(
            {
                xtype: 'kijs.gui.field.Text',
                label: this._app.getText('Url')
            }
        );
    }

    _onSaveClick(){

    }



    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct(preventDestructEvent) {
        // Event auslösen.
        if (!preventDestructEvent) {
            this.raiseEvent('destruct');
        }

        // reload timeout stoppen
        if (this._reloadAfterTimeout) {
            window.clearTimeout(this._reloadAfterTimeout);
            this._reloadAfterTimeout = null;
        }

        // Basisklasse auch entladen
        super.destruct(true);

        // Variablen (Objekte/Arrays) leeren
        this._formPanel = null;
    }
};