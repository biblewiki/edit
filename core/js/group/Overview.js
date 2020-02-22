/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.person.Person
// --------------------------------------------------------------

kijs.createNamespace('biwi.group');

biwi.group.Overview = class biwi_group_Overview extends biwi.default.DefaultGridPanel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            gridFnLoad: 'group.getGridData',
            detailFnLoad: 'group.getDetailHtml',
            editPanel: 'biwi_group_Group',
            gridCaption: this._app.getText('Personengruppen')
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