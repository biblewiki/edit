/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.event.Event
// --------------------------------------------------------------

kijs.createNamespace('biwi.event');

biwi.event.Event = class biwi_event_Event extends biwi.default.DefaultFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            formFnLoad: 'event.getFormData',
            formFnSave: 'event.saveDetailForm',
            detailFnLoad: 'event.getDetailHtml',
            sourceFnLoad: 'event.getSources',
            formCaption: this._app.getText('Ereignis')
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
                            xtype: 'kijs.gui.field.Range',
                            name: 'level',
                            label: this._app.getText('Sichtbarkeitslevel'),
                            labelWidth: 110,
                            min: 1,
                            max: 10,
                            elements: [
                                {
                                    xtype: 'kijs.gui.Element',
                                    name: 'levelValue',
                                    cls: 'rangeValue'
                                }
                            ],
                            on: {
                                change: function(e) {
                                    e.element.down('levelValue').html = e.element.value;
                                },
                                context: this
                            }
                        }
                    ]
                },{
                    xtype: 'kijs.gui.Container',
                    cls: 'biwi-form-row',
                    elements: [
                        {
                            xtype: 'kijs.gui.field.Number',
                            name: 'dayStart',
                            label: this._app.getText('Start Tag'),
                            minValue: 1,
                            maxValue: 31
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'monthStart',
                            label: this._app.getText('Monat'),
                            minValue: 1,
                            maxValue: 12
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'yearStart',
                            label: this._app.getText('Jahr')
                        },{
                            xtype: 'kijs.gui.field.Checkbox',
                            name: 'beforeChristStart',
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
                            name: 'dayEnd',
                            label: this._app.getText('End Tag'),
                            minValue: 1,
                            maxValue: 31
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'monthEnd',
                            label: this._app.getText('Monat'),
                            minValue: 1,
                            maxValue: 12
                        },{
                            xtype: 'kijs.gui.field.Number',
                            name: 'yearEnd',
                            label: this._app.getText('Jahr')
                        },{
                            xtype: 'kijs.gui.field.Checkbox',
                            name: 'beforeChristEnd',
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
        this.down('levelValue').html = this.down('level').value;
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};