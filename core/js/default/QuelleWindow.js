/* global this, kijs, biwi */

// --------------------------------------------------------------
// ki.DefaultGridComponent
// --------------------------------------------------------------
kijs.createNamespace('biwi.default');

biwi.default.QuelleWindow = class biwi_default_QuelleWindow extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._formPanel = null;
        this._field = '';

        // Config generieren
        config = Object.assign({}, {
            caption: this._app.getText('Quelle'),
            cls: ['kg-app-loginwindow'],
            iconChar: '&#xf039',
            width: 380,
            closable: true,
            maximizable: false,
            resizable: false,
            modal: true
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

        // FormPanel erstellen
        this.add(this._createElements());

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
    // Config definieren
    _createElements() {
        this._formPanel = this._createFormPanel();
        return [this._formPanel];
    }

    // FormPanel definieren
    _createFormPanel() {

        return new kijs.gui.FormPanel({
            name: 'loginFormPanel',
            rpc: this._app.rpc,
            facadeFnSave:'app.login',
            autoLoad: true,
            data: {
                authToken: this._authToken
            },
            elements:[
                {
                    xtype: 'kijs.gui.Container',
                    defaults:{
                        width: 280,
                        height: 25,
                        labelWidth: 80,
                        style:{
                            margin: '10px'
                        }
                    },
                    elements:[
                        {
                            xtype: 'kijs.gui.field.Text',
                            required: true,
                            name: 'userId',
                            label: this._app.getText('Benutzer')
                        },{
                            xtype: 'kijs.gui.field.Combo',
                            name: 'book',
                            label: this._app.getText('Bibelbuch'),
                            facadeFnLoad: 'bible.getBibleBooks',
                            rpc: this._app.rpc,
                            autoLoad: true
                        }
                    ]
                }
            ],
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
            ]
        });
    }



    // EVENTS
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