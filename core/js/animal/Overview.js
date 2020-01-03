/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.animal.Animal
// --------------------------------------------------------------

kijs.createNamespace('biwi.animal');

biwi.animal.Overview = class biwi_animal_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'animal.getGridData',
            detailFnLoad: 'animal.getDetailHtml',
            editPanel: 'biwi_animal_Animal',
            gridCaption: this._app.getText('Tiere')
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