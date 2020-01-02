/* global this, kijs, biwi */

// --------------------------------------------------------------
// kg.produkt.ZuweisungWindow
// --------------------------------------------------------------
kijs.createNamespace('biwi.person');

biwi.person.RelationshipWindow = class biwi_person_RelationshipWindow extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._personId = null;
        this._version = null;
        this._dataRow = null;

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Neue Beziehung hinzufügen'),
            iconChar: '&#xf0c1',
            width: 500,
            maximizable: false,
            resizable: false,
            modal: true,
            elements:[],
            footerElements:[{
                xtype: 'kijs.gui.Button',
                caption: this._app.getText('Hinzufügen'),
                isDefault: true,
                on: {
                    click: function() {
                        if(this.down('formPanel').validate()) {
                            this.raiseEvent('save', {dataRow: this.down('formPanel').data});
                        }
                    },
                    context: this
                }
            },{
                xtype: 'kijs.gui.Button',
                caption: this._app.getText('Abbrechen'),
                isDefault: true,
                on: {
                    click: function() {
                        this.close();
                    },
                    context: this
                }
            }]
        });

        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            personId: true,
            version: true,
            dataRow: true
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }

        // FormPanel erstellen
        this.add(this._createElements());
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
                xtype: 'kijs.gui.FormPanel',
                name: 'formPanel',
                data: this._dataRow || {},
                innerStyle: {
                    padding: '10px'
                },
                defaults: {
                    labelWidth: 120,
                    required: false,
                    maxLength: 50,
                    style: {marginBottom: '4px'}
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Combo',
                        name: 'secondPersonId',
                        label: this._app.getText('Bezugsperson'),
                        captionField: 'name',
                        valueField: 'secondPersonId',
                        rpc: this._app.rpc,
                        autoLoad: true,
                        facadeFnLoad: 'person.getPersons',
                        facadeFnArgs: {
                            personId: this._personId,
                            onlyOthers: true
                        }
                    },{
                        xtype: 'kijs.gui.field.Combo',
                        name: 'relationshipId',
                        label: this._app.getText('Beziehungsart'),
                        captionField: 'name',
                        valueField: 'relationshipId',
                        rpc: this._app.rpc,
                        autoLoad: true,
                        facadeFnLoad: 'person.getRelationships',
                        elements: [
                            {
                                xtype: 'kijs.gui.Button',
                                iconChar: '&#xf039',
                                toolTip: this._app.getText('Quelle'),
                                on: {
                                    click: this._onQuelleClick,
                                    context: this
                                }
                            }
                        ]
                    },{
                        xtype: 'kijs.gui.field.Number',
                        name: 'fatherAge',
                        label: this._app.getText('Alter Vater'),
                        elements: [
                            {
                                xtype: 'kijs.gui.Button',
                                iconChar: '&#xf039',
                                toolTip: this._app.getText('Quelle'),
                                on: {
                                    click: this._onQuelleClick,
                                    context: this
                                }
                            }
                        ]
                    }
                ]
            }
        ];
    }

    _quelleSave(form) {
        console.log(form);
    }

    // Events
    _onQuelleClick(e) {
        let quelle = new biwi.default.source.SourceWindow(
            {
                target: document.body,
                field: e.element.parent.name,
                facadeFnSave: this._quelleSave
            }
        );
        quelle.show();
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