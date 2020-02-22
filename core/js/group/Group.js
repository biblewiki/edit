/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.group.Group
// --------------------------------------------------------------

kijs.createNamespace('biwi.group');

biwi.group.Group = class biwi_group_Group extends biwi.default.DefaultFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            formFnLoad: 'group.getFormData',
            formFnSave: 'group.saveDetailForm',
            detailFnLoad: 'group.getDetailHtml',
            sourceFnLoad: 'group.getSources',
            formCaption: this._app.getText('Group')
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
                            xtype: 'kijs.gui.field.Combo',
                            name: 'groupType',
                            label: this._app.getText('Typ'),
                            remoteSort: true,
                            forceSelection: false,
                            rpc: this._app.rpc,
                            facadeFnLoad: 'group.getOtherType',
                            minChars: 1
                        }
                    ]
                },{
                    xtype: 'kijs.gui.Container',
                    cls: 'biwi-form-row',
                    elements: [
                        {
                            xtype: 'kijs.gui.field.Number',
                            name: 'dayFounding',
                            label: this._app.getText('Gründung Tag'),
                            minValue: 1,
                            maxValue: 31
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'monthFounding',
                            label: this._app.getText('Monat'),
                            minValue: 1,
                            maxValue: 12
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'yearFounding',
                            label: this._app.getText('Jahr')
                        },{
                            xtype: 'kijs.gui.field.Checkbox',
                            name: 'beforeChristFounding',
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
                            name: 'dayResolution',
                            label: this._app.getText('Auflösung Tag'),
                            minValue: 1,
                            maxValue: 31
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'monthResolution',
                            label: this._app.getText('Monat'),
                            minValue: 1,
                            maxValue: 12
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'yearResolution',
                            label: this._app.getText('Jahr')
                        },{
                            xtype: 'kijs.gui.field.Checkbox',
                            name: 'beforeChristResolution',
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
                            xtype: 'kijs.gui.field.Memo',
                            name: 'text',
                            label: 'Fliesstext',
                            trimValue: true,
                            height: 200
                        }
                    ]
                }
            ]
        );
    }


    // Events

    _onAfterFormLoad() {

    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};