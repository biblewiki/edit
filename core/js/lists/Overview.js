/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.lists.Overview
// --------------------------------------------------------------

kijs.createNamespace('biwi.lists');

biwi.lists.Overview = class biwi_lists_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'lists.getGridData',
            detailFnLoad: 'lists.getDetailHtml',
            editPanel: 'biwi_lists_Lists',
            gridCaption: this._app.getText('Listen')
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