/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.subject.Subject
// --------------------------------------------------------------

kijs.createNamespace('biwi.subject');

biwi.subject.Subject = class biwi_subject_Subject extends biwi.default.DefaultFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            formFnLoad: 'subject.getFormData',
            formFnSave: 'subject.saveDetailForm',
            detailFnLoad: 'subject.getDetailHtml',
            sourceFnLoad: 'subject.getSources',
            formCaption: this._app.getText('Subject')
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