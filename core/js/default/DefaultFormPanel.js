/* global this, kijs, biwi */

// --------------------------------------------------------------
// ki.DefaultGridComponent
// --------------------------------------------------------------
kijs.createNamespace('biwi.default');

biwi.default.DefaultFormPanel = class biwi_default_DefaultFormPanel extends kijs.gui.Container {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._detailPanel = null;
        this._formPanel = null;
        this._selection = null;
        this._apertureMask = null;

        this._formFnLoad = null;
        this._formFnSave = null;
        this._detailFnLoad = null;
        this._sourceFnLoad = null;
        this._formCaption = this._app.getText('Formular');
        this._detailCaption = this._app.getText('Details');

        this._id = null;
        this._version = null;

        this._formRemoteParams = {};

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            cls: ['kijs-flexrow', 'ki-defaultgridcomponent'],
            style: {
                flex: 1
            }
        });

        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            formFnLoad   : true,
            formFnSave   : true,
            detailFnLoad : true,
            sourceFnLoad: true,
            formCaption: true,
            detailCaption: true
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

    get detail() { return this._detailPanel.firstChild; }
    get form() { return this._formPanel.firstChild; }

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
        params.selection = this._selection;

        if (this.form.facadeFnLoad) {
            this.form.load(params, true, true);
        }
    }

    /**
     * Speichert das Detailformular
     * @param {boolean} [force=false] true: Auch speichern, wenn nicht dirty
     * @returns {Promise}
     */
    saveData(force=false) {
        let p;
        if (force || this.form.isDirty) {
            p = this.form.save(false, kijs.Object.clone(this._formRemoteParams)).then((response) => {

                // Button reseten
                this._detailPanel.footer.down('saveBtn').disabled = true;
                this._detailPanel.footer.down('saveBtn').badgeText = '';

                // biwiOpenTS zurücksetzen
                if (this.form.data.openTS) {
                    this.form.data.openTS = kijs.Date.format(new Date(), 'Y-m-d H:i:s');
                }

                let params = kijs.Object.clone(this._formRemoteParams);
                params.id = response.id;

                // Formular laden
                if (this.form.facadeFnLoad) {
                    this.form.load(params, true, true);
                }

                // Details laden
                if (this._detailFnLoad) {
                    this._getDetailData(response.id);
                }
            });
        } else {
            p = new Promise((resolve, reject) => {});
        }
        return p;
    }

    showPanel(args) {

        // ID  und Version aus den Argumenten holen
        if (kijs.isObject(args) && args.id) {
            this._id = args.id;
            this._version = args.version ? args.version : null;
        }

        // Tabelle und Grid erstellen
        if (!this._formPanel) {
            this.add(this._createElements());
        }

        if (this._id) {
            let params = kijs.Object.clone(this._formRemoteParams);
            params.id = this._id;
            params.version = this._version;

            // Formular laden
            if (this.form.facadeFnLoad) {
                this.form.load(params, true, true);
            }

            // Details laden
            if (this._detailFnLoad) {
                this._getDetailData(args.id);
            }
        }
    }

    // PROTECTED
    _createElements() {
        return [
            this._createFormPanel(),
            {
                xtype: 'kijs.gui.Splitter',
                targetPos: 'right'
            },
            this._createDetailPanel()
        ];
    }

    _createDetailPanel() {
        return this._detailPanel = new kijs.gui.Panel({
            caption: this._detailCaption,
            width: 700,
            cls: ['kijs-flexcolumn', 'biwi-detail-panel'],
            elements: [
                {
                    xtype: 'kijs.gui.Container',
                    name: 'details'
                }
            ],
            footerElements: [
                {
                    xtype: 'kijs.gui.Button',
                    name: 'saveBtn',
                    caption: this._app.getText('Speichern'),
                    iconChar: '&#xf0c7',
                    height: 40,
                    disabled: true,
                    style: {
                      flex: 1
                    },
                    on: {
                        click: this._onSaveClick,
                        context: this
                    }
                }
            ]
        });
    }

    _createFormPanel() {
        this._formPanel = new kijs.gui.Panel({
            caption: this._formCaption,
            style: {
                flex: 1,
                minWidth: '40px'
            },
            elements: [
                {
                    xtype: 'kijs.gui.FormPanel',
                    rpc: this._app.rpc,
                    facadeFnLoad: this._formFnLoad,
                    facadeFnSave: this._formFnSave
                }
            ]
        });

        // FormPanel mit Elementen füllen
        this._populateFormPanel(this.form);

        // Event
        this.form.on('change', this._onFormChange, this);

        return this._formPanel;
    }

    /**
     * Details vom Server holen
     *
     * @param {type} id
     * @returns {undefined}
     */
    _getDetailData(id) {

        if (kijs.isEmpty(id)){
            return;
        }

        let params = {
            id: id
        };

        this._app.rpc.do(this._detailFnLoad, params, function(response) {
            this._detailPanel.down('details').html = response.html;
        }, this, false, this._detailPanel);
    }

    _getSources(fieldName) {
        return new Promise((resolve) => {
            if (this._id) {
                let params = {};
                params.id = this._id;
                params.version = this._version;
                params.field = fieldName;

                if (!this.form.data.sources) {
                    this.form.data.sources = {};
                }
                this.form.data.sources[fieldName] = {};

                this._app.rpc.do(this._sourceFnLoad, params, function(response) {

                    // Quellen in Form Data schreiben
                    kijs.Object.each(response, function(sourceType, value) {
                        this.form.data.sources[fieldName][sourceType] = value;
                    }, this);

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

        // On Form Change Funktion aufrufen, da die Formulardaten geändert haben
        this._onFormChange();

        // Form is Dirty setzen, da die Formulardaten geändert haben
        this.form.isDirty = true;

        console.log(this.form.data);
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

        // Button Klasse 'active' entfernen
        //this.parent.parent.down(this.constructor.name).dom.clsRemove('active');

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
            this.down('cancelBtn').visible = false;
            this.down('saveBtn').visible = false;
        }
    }

    _onFormChange() {
        this._detailPanel.footer.down('saveBtn').disabled = false;
        this._detailPanel.footer.down('saveBtn').badgeText = ' ';
    }

    _onSaveClick() {
        if (!this.form.validate()) {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurden noch nicht alle Felder korrekt ausgefüllt.'));
        } else {
             // Speichern & Maske ausblenden
            this.saveData().then(() => {
                if (this._apertureMask && this._apertureMask.visible === true){
                    this._apertureMask.visible = false;
                    this.down('cancelBtn').visible = false;
                    this.down('saveBtn').visible = false;
                }
            }).catch(() => {});
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
        this._detailPanel = null;
        this._formPanel = null;
        this._selection = null;
        this._apertureMask = null;

        this._formFnLoad = null;
        this._formFnSave = null;
        this._sourceFnSave = null;
        this._detailFnLoad = null;
        this._formCaption = null;
        this._detailCaption = null;

        this._id = null;
        this._version = null;

        this._formRemoteParams = null;
    }
};
