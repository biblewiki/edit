/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.relationship.Relationship
// --------------------------------------------------------------

kijs.createNamespace('biwi.relationship');

biwi.relationship.Relationship = class biwi_relationship_Relationship extends biwi.default.DefaultGridFormPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'relationship.getGridData',
            formFnLoad: 'relationship.getDetailForm',
            formFnSave: 'relationship.saveDetailForm',
            deleteFn:   'relationship.deleteRelationship'
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
        this.gridCaption = this._app.getText('Beziehungen');
        this.formCaption = this._app.getText('Beziehungdetails');

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
                    name: 'name',
                    label: this._app.getText('Name'),
                    maxLength: 191
                },{
                    xtype: 'kijs.gui.field.Combo',
                    name: 'sex',
                    label: this._app.getText('Geschlecht'),
                    captionField: 'caption',
                    valueField: 'value',
                    data: [
                        { caption: 'Männlich', value: 1 },
                        { caption: 'Weiblich', value: 2 }
                    ]
                },{
                    xtype: 'kijs.gui.field.Combo',
                    name: 'returnMRelationshipId',
                    label: this._app.getText('Umkehrung Männlich'),
                    captionField: 'name',
                    valueField: 'relationshipId',
                    rpc: this._app.rpc,
                    autoLoad: true,
                    facadeFnLoad: 'relationship.getForCombo'
                },{
                    xtype: 'kijs.gui.field.Combo',
                    name: 'returnWRelationshipId',
                    label: this._app.getText('Umkehrung Weiblich'),
                    captionField: 'name',
                    valueField: 'relationshipId',
                    rpc: this._app.rpc,
                    autoLoad: true,
                    facadeFnLoad: 'relationship.getForCombo'
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
