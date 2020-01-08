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

        // Felder hinzufügen
        formPanel.add(
                [
                {
                    xtype: 'kijs.gui.Container',
                    cls: 'biwi-form-row',
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
                        },{
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
                },{
                    xtype: 'kijs.gui.Container',
                    cls: 'biwi-form-row',
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
                        },{
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
                    xtype: 'kijs.gui.Container',
                    cls: 'biwi-form-row',
                    elements: [
                        {
                            xtype: 'kijs.gui.field.Combo',
                            name: 'proficiency',
                            label: this._app.getText('Beruf'),
                            remoteSort: true,
                            forceSelection: false,
                            rpc: this._app.rpc,
                            facadeFnLoad: 'person.getPersonProficiency',
                            minChars: 1,
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
                            xtype: 'kijs.gui.field.Text',
                            label: this._app.getText('Beschreibung'),
                            name: 'personGroup',
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
                    xtype: 'kijs.gui.Container',
                    cls: 'biwi-form-row',
                    elements: [
                        {
                            xtype: 'kijs.gui.field.Number',
                            name: 'dayBirth',
                            label: this._app.getText('Geburt Tag'),
                            minValue: 1,
                            maxValue: 31
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'monthBirth',
                            label: this._app.getText('Monat'),
                            minValue: 1,
                            maxValue: 12
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'yearBirth',
                            label: this._app.getText('Jahr')
                        },{
                            xtype: 'kijs.gui.field.Checkbox',
                            name: 'beforeChristBirth',
                            label: this._app.getText('vor Christus'),
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
                            name: 'dayDeath',
                            label: this._app.getText('Tod Tag'),
                            minValue: 1,
                            maxValue: 31
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'monthDeath',
                            label: this._app.getText('Monat'),
                            minValue: 1,
                            maxValue: 12
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'yearDeath',
                            label: this._app.getText('Jahr')
                        },{
                            xtype: 'kijs.gui.field.Checkbox',
                            name: 'beforeChristDeath',
                            label: this._app.getText('vor Christus'),
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
                    xtype: 'kijs.gui.Container',
                    cls: 'biwi-form-row',
                    elements: [
                        {
                            xtype: 'biwi.person.RelationshipGridPanel',
                            name: 'relationshipGrid',
                            personId: this._id,
                            version: this._version
                        },{
                            xtype: 'biwi.person.GroupGridPanel',
                            name: 'groupGrid',
                            personId: this._id,
                            version: this._version
                        }
                    ]
                }
            ]
        );
    }


    // Events
    _onAfterFormLoad() {
        this.down('relationshipGrid').personId = this._id;
        this.down('relationshipGrid').version = this._version;
        this.down('relationshipGrid').reload();

        this.down('groupGrid').personId = this._id;
        this.down('groupGrid').version = this._version;
        this.down('groupGrid').reload();
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};