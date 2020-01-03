/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.animal.Animal
// --------------------------------------------------------------

kijs.createNamespace('biwi.animal');

biwi.animal.Animal = class biwi_animal_Animal extends biwi.default.DefaultFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            formFnLoad: 'animal.getFormData',
            formFnSave: 'animal.saveDetailForm',
            detailFnLoad: 'animal.getDetailHtml',
            sourceFnLoad: 'animal.getSources',
            formCaption: this._app.getText('Tiere')
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

        formPanel.on('afterLoad', this._onAfterFormLoad, this);
        //formPanel.on('add', this._onAfterAdd, this);

        // Felder hinzufügen
        formPanel.add(
            {
                xtype:'kijs.gui.Container',
                innerStyle: {
                    padding: '10px',
                    overflowY: 'auto'
                },
                defaults: {
                    width: 800,
                    labelWidth: 120,
                    style: {marginBottom: '4px'}
                },
                elements: [
                    {
                        xtype: 'kijs.gui.Container',
                        cls: 'biwi-form-row',
                        defaults: {
                            width: 800,
                            labelWidth: 120,
                            style: {marginBottom: '4px'}
                        },
                        elements: [
                            {
                                xtype: 'kijs.gui.field.Text',
                                label: this._app.getText('AnimalSpecies (Name)'),
                                name: 'animalSpecies',
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
                            },
                            {
                                xtype: 'kijs.gui.field.Text',
                                label: this._app.getText('Beschreibung'),
                                name: 'description',
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
                    },
                    {
                        xtype: 'kijs.gui.Container',
                        cls: 'biwi-form-row',
                        defaults: {
                            width: 800,
                            labelWidth: 120,
                            style: {marginBottom: '4px'}
                        },
                        elements: [
                            {
                                xtype: 'kijs.gui.field.Number',
                                label: this._app.getText('Anzahl'),
                                name: 'number',
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
                            },
                            {
                                xtype: 'kijs.gui.field.Number',
                                label: this._app.getText('Alter'),
                                name: 'age',
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
                    },
                    {
                        xtype: 'kijs.gui.Container',
                        cls: 'biwi-form-row',
                        defaults: {
                            width: 400,
                            labelWidth: 120,
                            style: {marginBottom: '4px'}
                        },
                        elements: [
                            {
                                xtype: 'kijs.gui.field.Combo',
                                name: 'personId',
                                label: this._app.getText('Bezugsperson'),
                                captionField: 'name',
                                valueField: 'personId',
                                rpc: this._app.rpc,
                                autoLoad: true,
                                facadeFnLoad: 'person.getPersons',
                            }
                        ]
                    }
                ]
            }
        );
    }

    _createHeaderElements() {
        return [
            {
                xtype: 'kijs.gui.Button',
                name: 'add',
                caption: this._app.getText('Neu'),
                iconChar: '&#xf055',
                on: {
                    click: this._onAddClick,
                    context: this
                }
            },{
                xtype: 'kijs.gui.Button',
                caption: this._app.getText('Löschen'),
                iconChar: '&#xf1f8',
                on: {
                    click: this._onDeleteClick,
                    context: this
                }
            }
        ];
    }

    // Events
    _onAfterFormLoad() {
        //this.down('relationshipGrid').reload(this._id, this._version);
    }

    _onAfterAdd(e) {
        //console.log(this._id);
//        e.element.grid.facadeFnArgs = {
//            animalId: this._id,
//            version: this._version
//        };
        //this.down('relationshipGrid').version = this._version;
    }

    _onAfterRender(e) {

//        e.element.grid.facadeFnArgs = {
//            animalId: this._id,
//            version: this._version
//        };
        console.log(e.element.grid.facadeFnArgs);
        //this.down('relationshipGrid').reload(this._id, this._version);
    }



    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};