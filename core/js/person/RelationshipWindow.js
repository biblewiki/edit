/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.person.RelationshipWindow
// --------------------------------------------------------------
kijs.createNamespace('biwi.person');

biwi.person.RelationshipWindow = class biwi_person_RelationshipWindow extends biwi.default.DefaultFormWindow {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._personId = null;

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Neue Beziehung hinzufügen'),
            iconChar: '&#xf0c1',
            sourceFnLoad: 'person.getSources',
            assignTable: 'personRelationship',
            primaryKey: 'personRelationshipId'
        });

        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            personId: true
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }

        // FormPanel erstellen
        this.add(this._createElements());

        // FacadeFnSave zuweisen
        this.form.facadeFnSave = 'person.saveRelationship';

        // Formular mit Daten füllen
        if (this._dataRow) {
            this.form.data = this._dataRow;
            this.form.down('relationshipId').readOnly = kijs.isEmpty(this._dataRow.relationshipId);
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
                    name: 'secondPersonId',
                    label: this._app.getText('Bezugsperson'),
                    captionField: 'comboName',
                    valueField: 'personId',
                    rpc: this._app.rpc,
                    autoLoad: true,
                    facadeFnLoad: 'person.getForCombo',
                    facadeFnArgs: {
                        personId: this._personId,
                        onlyOthers: true
                    },
                    on: {
                        change: this._onPersonChange,
                        context: this
                    }
                },{
                    xtype: 'kijs.gui.field.Combo',
                    name: 'relationshipId',
                    label: this._app.getText('Beziehungsart'),
                    captionField: 'name',
                    valueField: 'relationshipId',
                    rpc: this._app.rpc,
                    autoLoad: true,
                    facadeFnLoad: 'relationship.getForCombo',
                    readOnly: true,
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
                },{
                    xtype: 'kijs.gui.field.Number',
                    name: 'fatherAge',
                    label: this._app.getText('Alter Vater'),
                    required: false,
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

    // Events

    _onPersonChange(e) {
        let params = {
            personId: e.element.value
        };

        return new Promise((resolve) => {
            this._app.rpc.do('person.getRelationshipForCombo', params, function(response) {
                this.down('relationshipId').data = response.rows;
                this.down('relationshipId').readOnly = false;

                resolve();
            }, this, false, 'none');
        });
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

        // Variablen (Objekte/Arrays) leeren
        this._personId = null;
    }
};