/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.epoch.Overview
// --------------------------------------------------------------

kijs.createNamespace('biwi.epoch');

biwi.epoch.Overview = class biwi_epoch_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'epoch.getGridData',
            detailFnLoad: 'epoch.getDetailHtml',
            editPanel: 'biwi_epoch_Epoch',
            gridCaption: this._app.getText('Epochen')
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



    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};