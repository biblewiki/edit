/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.region.GroupWindow
// --------------------------------------------------------------
kijs.createNamespace('biwi.region');

biwi.region.GroupWindow = class biwi_region_GroupWindow extends biwi.default.DefaultFormWindow {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Neue Regionengruppe hinzufügen'),
            iconChar: '&#xf0c1',
            sourceFnLoad: 'region.getSources',
            assignTable: 'regionGroup',
            primaryKey: 'regionGroupId'
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

        // FormPanel erstellen
        this.add(this._createElements());

        // FacadeFnSave zuweisen
        this.form.facadeFnSave = 'region.saveRegionGroup';

        // Formular mit Daten füllen
        if (this._dataRow) {
            this.form.data = this._dataRow;
        }
    }


    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    // PROTECTED

    // overwrite
    _populateFormPanel(formPanel) {

        // Felder hinzufügen
        formPanel.add(
            [
                {
                    xtype: 'kijs.gui.field.Combo',
                    name: 'groupId',
                    label: this._app.getText('Gruppe'),
                    captionField: 'caption',
                    valueField: 'value',
                    rpc: this._app.rpc,
                    autoLoad: true,
                    facadeFnLoad: 'group.getForCombo',
                    facadeFnArgs: { personId: this._personId },
                    elements: [
                        {
                            xtype: 'kijs.gui.Button',
                            iconChar: '&#xf039',
                            toolTip: this._app.getText('Quelle'),
                            on: {
                                click: this._onSourceClick,
                                context: this
                            }
                        }
                    ]
                }
            ]
        );
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct(preventDestructEvent) {
        // Event auslösen.
        if (!preventDestructEvent) {
            this.raiseEvent('destruct');
        }

        // Basisklasse entladen
        super.destruct(true);
    }
};