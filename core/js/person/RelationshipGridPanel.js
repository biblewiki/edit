/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.person.RelationshipGridPanel
// --------------------------------------------------------------
kijs.createNamespace('biwi.person');

biwi.person.RelationshipGridPanel = class biwi_person_RelationshipGridPanel extends biwi.default.DefaultGridField {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Beziehungen'),
            iconChar: '&#xf0c1',
            facadeFnLoad: 'person.getRelationshipGrid',
            saveName: 'relationships',
            getForGridAddFnLoad: 'person.getForRelationshipGrid'
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
        let win = new biwi.person.RelationshipWindow({

            // Eintrags ID aus der Zuweisungstabelle
            id: data.personRelationshipId,
            version: this._version,
            personId: this._personId,
            dataRow: data
        });
        win.on('save', this._onSave, this);
        win.show();
    }


    // Events

    // overwrite
    _onSave(data) {

        this.getForGridAddFnArgs = {
            personId: data.values.secondPersonId,
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