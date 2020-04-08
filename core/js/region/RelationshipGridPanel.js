/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.region.RelationshipGridPanel
// --------------------------------------------------------------
kijs.createNamespace('biwi.region');

biwi.region.RelationshipGridPanel = class biwi_region_RelationshipGridPanel extends biwi.default.DefaultGridField {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Beziehungen'),
            iconChar: '&#xf0c1',
            facadeFnLoad: 'region.getRelationshipGrid',
            saveName: 'relationships',
            getForGridAddFnLoad: 'region.getForRelationshipGrid'
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
        let win = new biwi.region.RelationshipWindow({

            // Eintrags ID aus der Zuweisungstabelle
            id: data.regionRelationshipId,
            version: this._version,
            regionId: this._regionId,
            dataRow: data
        });
        win.on('save', this._onSave, this);
        win.show();
    }


    // Events

    // overwrite
    _onSave(data) {

        this.getForGridAddFnArgs = {
            regionId: data.values.secondRegionId,
            relationshipId: data.values.relationshipId
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