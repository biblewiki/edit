/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.message.Message
// --------------------------------------------------------------

kijs.createNamespace('biwi.message');

biwi.message.Message = class biwi_message_Message extends biwi.default.DefaultGridFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'message.getGridData',
            formFnLoad: 'message.getDetailForm',
            formFnSave: 'message.saveDetailForm',
            deleteFn:   'message.deleteMessage'
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

        // Titel
        this.gridCaption = this._app.getText('Mitteilungen');
        this.formCaption = this._app.getText('Mitteilungdetails');

        // Felder hinzufügen
        formPanel.add({
            xtype:'kijs.gui.Container',
            innerStyle: {
                padding: '10px',
                overflowY: 'auto'
            },
            defaults: {
                labelWidth: 120,
                required: true,
                style: {marginBottom: '4px'}
            },
            elements: [
                {
                    xtype: 'kijs.gui.field.Text',
                    name: 'description',
                    label: this._app.getText('Beschreibung'),
                    maxLength: 191
                },{
                    xtype: 'kijs.gui.field.Memo',
                    name: 'text',
                    label: this._app.getText('Text'),
                    maxLength: 255
                },{
                    xtype: 'kijs.gui.field.DateTime',
                    name: 'dateFrom',
                    label: this._app.getText('Anzeigen von'),
                    value: Date.now()
                },{
                    xtype: 'kijs.gui.field.DateTime',
                    name: 'dateTo',
                    label: this._app.getText('Anzeigen bis'),
                    required: false
                }
            ]
        });
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};
