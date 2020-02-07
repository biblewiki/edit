/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.user.User
// --------------------------------------------------------------

kijs.createNamespace('biwi.user');

biwi.user.User = class biwi_user_User extends biwi.default.DefaultGridFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'user.getGridData',
            formFnLoad: 'user.getFormData',
            formFnSave: 'user.saveDetailForm'
        });

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {

        });

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }
    }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // overwrite
    _populateFormPanel(formPanel) {

        // Felder hinzufügen
        formPanel.add(
            [
                {
                    xtype:'kijs.gui.Container',
                    innerStyle: {
                        padding: '10px',
                        overflowY: 'auto'
                    },
                    defaults: {
                        labelWidth: 120,
                        required: true,
                        style: {marginBottom: '4px'}
                    },
                    elements: [
                        {
                            xtype: 'kijs.gui.field.Text',
                            label: this._app.getText('Benutzername'),
                            name: 'username'
                        },{
                            xtype: 'kijs.gui.field.Text',
                            label: this._app.getText('Vorname'),
                            name: 'firstName'
                        },{
                            xtype: 'kijs.gui.field.Text',
                            label: this._app.getText('Nachname'),
                            name: 'lastName'
                        },{
                            xtype: 'kijs.gui.field.Text',
                            label: this._app.getText('Email'),
                            name: 'email'
                        },{
                            xtype: 'kijs.gui.field.Combo',
                            label: this._app.getText('Passwort Status'),
                            name: 'passwordState',
                            data: [
                                { value: null, caption: 'Unbekannt' },
                                { value: 10, caption: 'Normal' },
                                { value: 20, caption: 'Zurücksetzen angefordert' },
                                { value: 30, caption: 'Rücksetzen-Email versendet' },
                                { value: 40, caption: 'Zurücksetzen fehlgeschlagen' },
                                { value: 50, caption: 'Zurückgesetzt und noch nicht eingeloggt' },
                                { value: 60, caption: 'Zurücksetzen gesperrt' }
                            ],
                            readOnly: true
                        }
                    ]
                }
            ]
        );
    }

    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};
