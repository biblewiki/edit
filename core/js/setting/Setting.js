/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.setting.Setting
// --------------------------------------------------------------

kijs.createNamespace('biwi.setting');

biwi.setting.Setting = class biwi_setting_Setting extends kijs.gui.Panel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._settings = [];

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Einstellungen'),
            cls: 'biwi-settings'
        });

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            // keine
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

    refreshPanel(args) {
        this.showPanel(args);
    }

    showPanel(args) {
        // Settings vom Server holen
        this._app.rpc.do('setting.getSettings', {}, function(response) {
            this.removeAll();
            this._settings = response.settings;
            this.add(this._createElements());
        }, this);
    }

    // overwrite
    unrender(superCall) {
        // Event auslösen.
        if (!superCall) {
            this.raiseEvent('unrender');
        }

        if (this._apertureMask) {
            this._apertureMask.unrender();
        }

        // Button Klasse 'active' entfernen
        this.parent.parent.down(this.constructor.name).dom.clsRemove('active');

        super.unrender(true);
    }

    // Protected
    _createElements() {
        let panels = [];
        let panel = null;

        kijs.Array.each(this._settings, function(setting) {

            if (setting.title) {
                panel = new kijs.gui.Panel (
                    {
                        name: setting.title,
                        cls: 'panel',
                        collapsible: 'top',
                        caption: setting.title
                    }
                );
                panels.push(panel);
            }

            if (panel) {
                let formPanel = new kijs.gui.FormPanel({
                    rpc: this._app.rpc,
                    facadeFnSave: 'setting.saveSetting',
                    cls: 'setting',
                    elements: [
                        {
                            xtype: 'kijs.gui.Element',
                            html: setting.caption,
                            cls: 'caption'
                        },{
                            xtype: 'kijs.gui.Container',
                            cls: 'setting-value',
                            elements: [
                                {
                                    xtype: 'kijs.gui.Container',
                                    cls: 'left',
                                    elements: [
                                        {
                                            xtype: 'kijs.gui.Element',
                                            html: setting.description,
                                            cls: 'description'
                                        }
                                    ]
                                },{
                                    xtype: 'kijs.gui.field.Memo',
                                    name: setting.setting,
                                    value: setting.value,
                                    required: true,
                                    cls: 'right',
                                    on: {
                                        change: this._onFieldChange,
                                        context: this
                                    }
                                }
                            ]
                        }
                    ]
                });

                formPanel.data.openTS = kijs.Date.format(new Date(), 'Y-m-d H:i:s');

                panel.add(formPanel);
            }
        }, this);

        return panels;
    }


    // Events
    _onFieldChange(e) {

        // Feld validieren
        if (e.element.validate()) {

            // Formular speichern
            e.element.parent.parent.save().then(() => {
                this.refreshPanel();
            });

            // kiOpenTS zurücksetzen
            e.element.parent.parent.data.openTS = kijs.Date.format(new Date(), 'Y-m-d H:i:s');
        } else {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurden noch nicht alle Felder korrekt ausgefüllt.'));
        }
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();

        // Variablen (Objekte/Arrays) leeren
        this._app = null;
        this._settings = null;
    }
};
