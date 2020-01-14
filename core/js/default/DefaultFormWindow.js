/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.default.DefaultFormWindow
// --------------------------------------------------------------
kijs.createNamespace('biwi.default');

biwi.default.DefaultFormWindow = class biwi_default_DefaultFormWindow extends kijs.gui.Window {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._formPanel = null;
        this._apertureMask = null;

        this._formFnLoad = null;
        this._formFnSave = null;
        this._sourceFnLoad = null;
        this._sourceFnArgs = null;
        this._dataRow = null;
        this._activeFormPanel = null;

        this._id = null;
        this._version = null;

        this._formRemoteParams = {};

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            cls: ['kijs-flexrow'],
            style: {
                flex: 1
            },
            width: 500,
            maximizable: false,
            resizable: false,
            modal: true,
            footerElements:[
                {
                    xtype: 'kijs.gui.Button',
                    caption: this._app.getText('Hinzufügen'),
                    isDefault: true,
                    on: {
                        click: this._onSaveClick,
                        context: this
                    }
                },{
                    xtype: 'kijs.gui.Button',
                    caption: this._app.getText('Abbrechen'),
                    isDefault: true,
                    on: {
                        click: function() {
                            this.close();
                        },
                        context: this
                    }
                }
            ]
        });

        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            formFnLoad   : true,
            formFnSave   : true,
            sourceFnLoad: true,
            sourceFnArgs: true,
            dataRow: true,
            id: true,
            version: true
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }
    }


    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------

    get form() { return this._formPanel; }

    get isDirty() {
        return this.form.isDirty;
    }

    get formRemoteParams() { return this._formRemoteParams; }

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    /**
     * Speichert das Formular
     * @param {boolean} [force=false] true: Auch speichern, wenn nicht dirty
     * @returns {Promise}
     */
    saveData(force=false) {
        if (force || this.form.isDirty) {
            this.form.save(false, kijs.Object.clone(this._formRemoteParams)).then(() => {

                // Event werfen
                this.raiseEvent('afterSave');

                // Fenster Schliessen
                this.close();
            });
        } else {

            // Fenster Schliessen
            this.close();
        }
    }


    // PROTECTED
    _createElements() {
        this._formPanel = new kijs.gui.FormPanel({
            rpc: this._app.rpc,
            facadeFnLoad: this._formFnLoad,
            facadeFnSave: this._formFnSave,
            name: 'formPanel',
            style: {
                flex: 1
            },
            innerStyle: {
                padding: '10px'
            },
            defaults: {
                labelWidth: 120,
                required: true,
                maxLength: 50,
                style: {marginBottom: '4px'}
            }
        });

        // FormPanel mit Elementen füllen
        this._populateFormPanel(this.form);

        return this._formPanel;
    }

    // Quellen vom Server holen
    _getSources(fieldName, formPanel) {
        return new Promise((resolve) => {

            if (kijs.isEmpty(this._sourceFnLoad)) {
                kijs.gui.MsgBox.error(this._app.getText('Fehler'), this._app.getText('Es wurden keine Source Load Funktion angegeben.'));
            }

            if (kijs.isEmpty(this._sourceFnLoad) || kijs.isEmpty(this._sourceFnArgs.assignTable)) {
                kijs.gui.MsgBox.error(this._app.getText('Fehler'), this._app.getText('Es wurde keine assignTable in den sourceFnArgs angegeben.'));
            }

            // Überprüfen, ob Objekt "sources" bereits existiert. Sonst wird es erstellt
            if (!this._activeFormPanel.data.sources) {
                this._activeFormPanel.data.sources = {};
            }

            // Wenn schon Daten für dieses Feld im Datenpacket vorhanden sind, werden diese zurückgegeben
            if (this._activeFormPanel.data.sources[fieldName]) {
                resolve(this._activeFormPanel.data.sources[fieldName]);

            // Überprüfen ob eine ID vorhanden ist. Dies bedeutet, dass der Eintrag bereits in der DB ist.
            // Wenn ja, werden die Quellen vom Server geholt
            } else if (this._id) {

                // Argumente vorbereiten
                let params = Object.assign(this._sourceFnArgs, {
                    id: this._id,
                    version: this._version,
                    field: fieldName
                });

                // Objekt für Feldnamen im Datenpacket erstellen
                this._activeFormPanel.data.sources[fieldName] = {};

                // Server Abfrage ausführen
                this._app.rpc.do(this._sourceFnLoad, params, function(response) {

                    // Quellen in Form Data schreiben
                    kijs.Object.each(response, function(sourceType, values) {
                        this._activeFormPanel.data.sources[fieldName][sourceType] = {};

                        // Aus dem Array ein Objekt machen
                        kijs.Array.each(values, function(value, index) {
                            this._activeFormPanel.data.sources[fieldName][sourceType][index] = value;
                        }, this);
                    }, this);

                    // Resolve ausführen und Quellen zurückgeben
                    resolve(this._activeFormPanel.data.sources[fieldName]);

                }, this);
            } else {
                resolve();
            }
        });
    }

    /**
     * Kann in abgeleiteter Klasse überschrieben werden,
     * um FormPanel zu füllen
     *
     * @param {kijs.gui.FormPanel} formPanel
     * @returns {undefined}
     */
    _populateFormPanel(formPanel) {

    }

    _addSourceToFormData(sources) {

        // Formular Quellenarray hinzufügen
        if (!this._activeFormPanel.data.sources) {
            this._activeFormPanel.data.sources = {};
        }
        this._activeFormPanel.data.sources[sources.data.field] = sources.data.values;

        // Form is Dirty setzen, da die Formulardaten geändert haben
        this._activeFormPanel.isDirty = true;
    }

    // overwrite
    unrender(superCall) {
        // Event auslösen.
        if (!superCall) {
            this.raiseEvent('unrender');
        }

        if (this._apertureMask) {
            this._apertureMask.unrender();
        }
        super.unrender(true);
    }


    // EVENTS

    /**
     * Klick auf den Abbrechen-Button
     * @returns {undefined}
     */
    _onCancelClick() {
        this.form.reset();
        this.form.resetValidation();

        if (this._apertureMask && this._apertureMask.visible === true) {
            this._apertureMask.visible = false;
        }
    }

    _onSaveClick() {

        // Überprüfen ob das Formular valid ist
        if (this._formPanel.validate()) {
            let data = [];
            data.values = this._formPanel.data;

            // Event werfen mit den Daten
            this.raiseEvent('save', data);

            // Fenster schliessen
            this.close();

        // Fehler anzeigen
        } else {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurden noch nicht alle Felder korrekt ausgefüllt.'));
        }
    }

    _onSourceClick(e) {
        let fieldName = e.element.parent.name;

        if (e.element.parent.parent instanceof kijs.gui.FormPanel) {
            this._activeFormPanel = e.element.parent.parent;
        } else if (this.form) {
            this._activeFormPanel = this.form;
        }

        // Vorhandene Quellen laden
        this._getSources(fieldName).then((sources) => {
            let sourceWindow = new biwi.default.source.SourceWindow(
                {
                    target: document.body,
                    field: fieldName,
                    sources: sources
                }
            );
            sourceWindow.show();

            sourceWindow.on('saveSource', this._addSourceToFormData, this);
        });
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct(preventDestructEvent) {
        // Event auslösen.
        if (!preventDestructEvent) {
            this.raiseEvent('destruct');
        }

        // Maske entfernen
        this._apertureMask.destruct();

        // Basisklasse auch entladen
        super.destruct(true);

        // Variablen (Objekte/Arrays) leeren
        this._app = null;
        this._formPanel = null;
        this._apertureMask = null;

        this._formFnLoad = null;
        this._formFnSave = null;
        this._sourceFnLoad = null;
        this._sourceFnArgs = null;
        this._dataRow = null;
        this._activeFormPanel = null;

        this._id = null;
        this._version = null;

        this._formRemoteParams = null;
    }
};
