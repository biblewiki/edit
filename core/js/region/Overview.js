/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.region.Region
// --------------------------------------------------------------

kijs.createNamespace('biwi.region');

biwi.region.Overview = class biwi_region_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'region.getGridData',
            detailFnLoad: 'region.getDetailHtml',
            editPanel: 'biwi_region_Region',
            gridCaption: this._app.getText('Regionen')
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