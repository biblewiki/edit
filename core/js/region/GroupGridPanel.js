/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.region.GroupGridPanel
// --------------------------------------------------------------
kijs.createNamespace('biwi.region');

biwi.region.GroupGridPanel = class biwi_region_GroupGridPanel extends biwi.default.DefaultGridField {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Regionengruppen'),
            iconChar: '&#xf0c0',
            facadeFnLoad: 'region.getGroupGrid',
            saveName: 'groups',
            getForGridAddFnLoad: 'region.getForGroupGrid'
        });

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            // Keine
        });

        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config);
        }

        this.add(this._createElements());
    }

    // --------------------------------------------------------------
    // Getter / Setter
    // --------------------------------------------------------------

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // Private
    _showAddWindow(data) {
        let win = new biwi.region.GroupWindow({

            // Eintrags ID aus der Zuweisungstabelle
            id: data.personGroupId,
            personId: this._personId,
            version: this._version,
            dataRow: data
        });
        win.on('save', this._onSave, this);
        win.show();
    }

    // Events

    // overwrite
    _onSave(data) {

        this.getForGridAddFnArgs = {
            groupId: data.values.groupId
        };

        super._onSave(data);
    }

    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();

        // Variablen (Objekte/Arrays) leeren
    }

};