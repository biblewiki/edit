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
        this._dataRow = null;

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
                    caption: this._app.getText('Speichern'),
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
            dataRow: true,
            id: true,
            version: true
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }

        // FormPanel erstellen
        this.add(this._createElements());
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

    refreshPanel(args) {
        if (kijs.isObject(args) && args.id) {
            this._id = args.id;
            this._version = args.version ? args.version : null;
        }

        let params = kijs.Object.clone(this._formRemoteParams);

        if (this.form.facadeFnLoad) {
            this.form.load(params, true, true);
        }
    }

    /**
     * Speichert das Formular
     * @param {boolean} [force=false] true: Auch speichern, wenn nicht dirty
     * @returns {Promise}
     */
    saveData(force=false) {
        if (force || this.form.isDirty) {
            this.form.save(false, kijs.Object.clone(this._formRemoteParams)).then((response) => {

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

    showPanel(args) {

        // ID  und Version aus den Argumenten holen
        if (kijs.isObject(args) && args.id) {
            this._id = args.id;
            this._version = args.version ? args.version : null;
        }

        // Tabelle erstellen
        if (!this._formPanel) {
            this.add(this._createElements());
        }


        let params = kijs.Object.clone(this._formRemoteParams);
        params.id = this._id;
        params.version = this._version;

        // Formular laden
        if (this.form.facadeFnLoad) {
            this.form.load(params, true, true);
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
    _getSources(fieldName) {
        return new Promise((resolve) => {

            // Überprüfen, ob Objekt "sources" bereits existiert. Sonst wird es erstellt
            if (!this.form.data.sources) {
                this.form.data.sources = {};
            }

            // Wenn schon Daten für dieses Feld im Datenpacket vorhanden sind, werden diese zurückgegeben
            if (this.form.data.sources[fieldName]) {
                resolve(this.form.data.sources[fieldName]);

            // Überprüfen ob eine ID vorhanden ist. Dies bedeutet, dass der Eintrag bereits in der DB ist.
            // Wenn ja, werden die Quellen vom Server geholt
            } else if (this._id) {

                // Argumente vorbereiten
                let params = {};
                params.id = this._id;
                params.version = this._version;
                params.field = fieldName;

                // Objekt für Feldnamen im Datenpacket erstellen
                this.form.data.sources[fieldName] = {};

                // Server Abfrage ausführen
                this._app.rpc.do(this._sourceFnLoad, params, function(response) {

                    // Quellen in Form Data schreiben
                    kijs.Object.each(response, function(sourceType, values) {
                        this.form.data.sources[fieldName][sourceType] = {};

                        // Aus dem Array ein Objekt machen
                        kijs.Array.each(values, function(value, index) {
                            this.form.data.sources[fieldName][sourceType][index] = value;
                        }, this);
                    }, this);

                    // Resolve ausführen und Quellen zurückgeben
                    resolve(this.form.data.sources[fieldName]);

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
        if (!this.form.data.sources) {
            this.form.data.sources = {};
        }
        this.form.data.sources[sources.field] = sources.values;

        // Form is Dirty setzen, da die Formulardaten geändert haben
        this.form.isDirty = true;
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
        if (!this.form.validate()) {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurden noch nicht alle Felder korrekt ausgefüllt.'));
        } else {
             // Speichern
            this.saveData();
        }
    }

    _onSourceClick(e) {
        let fieldName = e.element.parent.name;

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

        this._id = null;
        this._version = null;

        this._formRemoteParams = null;
    }
};
