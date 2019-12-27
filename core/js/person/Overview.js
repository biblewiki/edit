/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.person.Person
// --------------------------------------------------------------

kijs.createNamespace('biwi.person');

biwi.person.Overview = class biwi_person_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'person.getGridData',
            detailFnLoad: 'person.getDetailHtml',
            editPanel: 'biwi_person_Person',
            gridCaption: this._app.getText('Personen')
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