/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.person.GroupGridPanel
// --------------------------------------------------------------
kijs.createNamespace('biwi.person');

biwi.person.GroupGridPanel = class biwi_person_GroupGridPanel extends biwi.default.DefaultGridField {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Personengruppen'),
            iconChar: '&#xf0c0',
            facadeFnLoad: 'person.getGroupGrid',
            saveName: 'groups',
            getForGridAddFnLoad: 'person.getForGroupGrid'
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
        let win = new biwi.person.GroupWindow({

            // Eintrags ID aus der Zuweisungstabelle
            id: data.personGroupId,
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