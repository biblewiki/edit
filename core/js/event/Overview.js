/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.event.Overview
// --------------------------------------------------------------

kijs.createNamespace('biwi.event');

biwi.event.Overview = class biwi_event_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'event.getGridData',
            detailFnLoad: 'event.getDetailHtml',
            editPanel: 'biwi_event_Event',
            gridCaption: this._app.getText('Ereignisse')
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