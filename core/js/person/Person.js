/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.person.Person
// --------------------------------------------------------------

kijs.createNamespace('biwi.person');

biwi.person.Person = class biwi_person_Person extends biwi.default.DefaultFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            formFnLoad: 'person.getFormData',
            formFnSave: 'person.saveDetailForm',
            detailFnLoad: 'person.getDetailHtml',
            sourceFnLoad: 'person.getSources',
            formCaption: this._app.getText('Person')
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
                                label: this._app.getText('Name'),
                                name: 'name',
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
                                xtype: 'kijs.gui.field.OptionGroup',
                                name: 'sex',
                                label: this._app.getText('Geschlecht'),
                                cls: 'kijs-inline',
                                valueField: 'id',
                                captionField: 'caption',
                                required: true,
                                data: [
                                    { id: 1, caption: this._app.getText('Mann') },
                                    { id: 2, caption: this._app.getText('Frau') },
                                    { id: 3, caption: this._app.getText('Unbekannt') }
                                ],
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
                                xtype: 'kijs.gui.field.OptionGroup',
                                name: 'believer',
                                label: this._app.getText('Christ'),
                                cls: 'kijs-inline',
                                valueField: 'id',
                                captionField: 'caption',
                                required: true,
                                data: [
                                    { id: 1, caption: this._app.getText('Ja') },
                                    { id: 2, caption: this._app.getText('Nein') },
                                    { id: 3, caption: this._app.getText('Unbekannt') }
                                ],
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
                    },{
                        xtype: 'biwi.person.RelationshipGridPanel',
                        name: 'relationshipGrid',
                        personId: this._id,
                        version: this._version,
                        on: {
                            afterRender: this._onAfterRender,
                            context: this
                        }
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
//            personId: this._id,
//            version: this._version
//        };
        //this.down('relationshipGrid').version = this._version;
    }

    _onAfterRender(e) {

//        e.element.grid.facadeFnArgs = {
//            personId: this._id,
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