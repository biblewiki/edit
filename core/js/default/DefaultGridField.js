/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.default.DefaultGridField
// --------------------------------------------------------------
kijs.createNamespace('biwi.default');

biwi.default.DefaultGridField = class biwi_default_DefaultGridField extends kijs.gui.Panel {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._personId = null;
        this._version = null;
        this._facadeFnLoad = null;
        this._saveName = null;
        this._getForGridAddFnLoad = null;
        this._getForGridAddFnArgs = null;

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Zuweisungs Grid Panel'),
            style: {
                flex: 1

            },
            headerElements: this._createHeaderElements()
        });

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            personId: true,
            version: true,
            facadeFnLoad: true,
            saveName: true,
            getForGridAddFnLoad: true,
            getForGridAddFnArgs: true
        });

        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config);
        }
    }

    // --------------------------------------------------------------
    // Getter / Setter
    // --------------------------------------------------------------

    get personId() { return this._personId; }
    set personId(val) { this._personId = parseInt(val); }

    get version() { return this._version; }
    set version(val) { this._version = parseInt(val); }

    get grid () { return this.down('grid'); };

    get getForGridAddFnArgs() { return this._getForGridAddFnArgs; }
    set getForGridAddFnArgs(val) { this._getForGridAddFnArgs = val; }

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    reload() {
        this.grid.facadeFnArgs = { personId: this._personId };
        this.grid.reload();
    }

    /**
     * Erstellt die Elemente
     *
     * @returns {Array}
     */
    _createElements() {
        return [
            {
                xtype: 'kijs.gui.grid.Grid',
                name: 'grid',
                facadeFnLoad: this._facadeFnLoad,
                autoLoad: false,
                rpc: this._app.rpc,
                style: {
                    borderLeft: '1px solid #d2d2d2',
                    borderRight: '1px solid #d2d2d2',
                    borderBottom: '1px solid #d2d2d2',
                    minHeight: '100px'
                },
                on: {
                    rowDblClick: this._onRowDoubleClick,
                    context: this
                }
            }
        ];
    }

    _createHeaderElements() {
        return [
            {
                xtype: 'kijs.gui.Button',
                caption: this._app.getText('Neu'),
                toolTip: this._app.getText('Neuer Eintrag hinzufügen'),
                iconChar: '&#xf055',
                on: {
                    click: this._onAddClick,
                    context: this
                }
            },{
                xtype: 'kijs.gui.Button',
                caption: this._app.getText('Löschen'),
                toolTip: this._app.getText('Eintrag löschen'),
                iconChar: '&#xf1f8',
                on: {
                    click: this._onDeleteClick,
                    context: this
                }
            }
        ];
    }

    // Muss in der Unterklasse überschrieben werden
    _showAddWindow(data) {

    }

    // Events
    _onAddClick() {
        let data = {
            personId: this._personId,
            version: this._version
        };
        this._showAddWindow(data);
    }

    _onDeleteClick() {
        if (this._saveName) {

            // Auslesen ob ein Eintrag ausgewählt ist
            if (this.down('grid').getSelectedIds().length) {

                // Bestätigungsfenster anzeigen
                kijs.gui.MsgBox.confirm(this._app.getText('Löschen'), this._app.getText('Wollen Sie den Eintrag wirklich löschen?'), function(response) {

                    // Wenn löschen bestätigt wurde Eintrag delete hinzufügen
                    if (response.btn === 'yes') {

                        // DataRow zwischenspeichern
                        let current = this.down('grid').current;
                        let dataRow = current.dataRow;

                        // Row aus Grid entfernen
                        this.down('grid').rowsRemove(current);

                        // Delete Status hinzufügen
                        dataRow.state = 100;

                        // Paramter zum Speichern vorbereiten
                        let ret = {
                            name: this._saveName,
                            data: dataRow
                        };

                        // Event werfen
                        this.raiseEvent('save', ret);
                    }

                }, this);

            // Fehler anzeigen
            } else {
                kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurde kein Eintrag ausgewählt'));
            }
        } else {
            kijs.gui.MsgBox.error(this._app.getText('Fehler'), this._app.getText('Es wurden kein Save Name angegeben.'));
        }

    }

    _onRowDoubleClick() {
        this._showAddWindow(this.down('grid').current.dataRow);
    }

    _onSave(data) {
        if (this._saveName) {

            // Neue Daten für Grid vom Server holen
            this._app.rpc.do(this._getForGridAddFnLoad, this._getForGridAddFnArgs, function(response) {

                kijs.Object.each(response.data, function(key, value) {
                    // Daten hinzufügen
                    data.values[key] = value;
                }, this);

                // Person dem Grid hinzufügen
                this.down('grid').rowsAdd(data.values);

            }, this);

            // Paramter zum Speichern vorbereiten
            let ret = {
                name: this._saveName,
                data: data.values
            };

            // Event werfen
            this.raiseEvent('save', ret);
        } else {
            kijs.gui.MsgBox.error(this._app.getText('Fehler'), this._app.getText('Es wurden kein Save Name angegeben.'));
        }
    }

    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();

        // Variablen (Objekte/Arrays) leeren
        this._app = null;
        this._personId = null;
        this._version = null;
        this._facadeFnLoad = null;
        this._saveName = null;
        this._getForGridAddFnLoad = null;
        this._getForGridAddFnArgs = null;
    }

};