/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.subject.Overview
// --------------------------------------------------------------

kijs.createNamespace('biwi.subject');

biwi.subject.Overview = class biwi_subject_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'subject.getGridData',
            detailFnLoad: 'subject.getDetailHtml',
            editPanel: 'biwi_subject_Subject',
            gridCaption: this._app.getText('Themen')
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