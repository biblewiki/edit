/* global this, kijs, biwi */

// --------------------------------------------------------------
// ki.DefaultGridComponent
// --------------------------------------------------------------
kijs.createNamespace('biwi.default');

biwi.default.DefaultGridFormPanel = class biwi_default_DefaultGridFormPanel extends kijs.gui.Container {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._gridPanel = null;
        this._formPanel = null;
        this._selection = null;
        this._apertureMask = null;

        this._gridFnLoad = null;
        this._formFnLoad = null;
        this._formFnSave = null;
        this._deleteFn = null;

        this._formRemoteParams = {};

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            cls: 'kijs-flexrow',
            style: {
                flex: 1
            }
        });

        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            gridFnLoad  : true,
            formFnLoad  : true,
            formFnSave  : true,
            deleteFn    : true
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

    get grid() { return this._gridPanel.firstChild; }
    get form() { return this._formPanel.firstChild; }

    set formCaption(val) { this._formPanel.headerBar.html = val; }
    set gridCaption(val) { this._gridPanel.headerBar.html = val; }

    get isDirty() {
        return this.form.isDirty;
    }

    get formRemoteParams() { return this._formRemoteParams; }

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    refreshPanel(args) {
        if (args && args.restoreSelection){
            this.grid.reload(args.restoreSelection);
        } else {
            this.grid.reload();
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

                // kiOpenTS zurücksetzen
                if (this.form.data.openTS) {
                    this.form.data.openTS = kijs.Date.format(new Date(), 'Y-m-d H:i:s');
                }

                if (response.newId !== response.oldId){
                    this.grid.reload().then(() => {
                        this.grid.selectByIds(response.newId, false, true);

                        if (this.grid.primaryKeys.length === 1){
                            // Selection definieren mit dem neuen Eintrag
                            this._selection = {};
                            this._selection[this.grid.primaryKeys[0]] = response.newId;

                            let params = kijs.Object.clone(this._formRemoteParams);
                            params.selection = this._selection;

                            // Formular laden
                            if (this.form.facadeFnLoad) {
                                this.form.load(params, true, true);
                            }
                        } else {
                            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Mehrere Primary Keys vorhanden.'));
                        }
                    });
                } else {
                    this.grid.reload();
                }
            });
        } else {
            p = new Promise((resolve, reject) => {});
        }
        return p;
    }

    showPanel(args) {
        // Grid erstellen
        if (!this._gridPanel) {
            this.add(this._createElements());
        } else {
            if (typeof(args) !== 'undefined' && !args.restoreSelection){
                this.grid.reload(args.restoreSelection);
            } else {
                this.grid.reload();
            }
        }

        // events (selection)
        this.grid.on('selectionChange', this._onSelectionChange, this);

        let params = kijs.Object.clone(this._formRemoteParams);
        params.selection = this._selection;

        if (this.form.facadeFnLoad && this._selection) {
            this.form.load(params, true, true);
        }
    }

    // PROTECTED
    _createElements() {
        return [
            this._createTablePanel(),
            {
                xtype: 'kijs.gui.Splitter',
                targetPos: 'right'
            },
            this._createFormPanel()
        ];
    }

    _createTablePanel() {
        return this._gridPanel = new kijs.gui.Panel({
            caption: this._app.getText('Tabelle'),
            cls: 'kijs-flexcolumn',
            style: {
                flex: 1,
                minWidth: '40px'
            },
            elements: [{
                xtype: 'kijs.gui.grid.Grid',
                selectType: 'multi',
                filterable: true,
                filterVisible: false,
                facadeFnLoad: this._gridFnLoad,
                rpc: this._app.rpc,
                style: {
                    flex: 1
                }
            }],
            headerElements: this._createHeaderElements()
        });
    }

    _createFormPanel() {
        this._formPanel = new kijs.gui.Panel({
            caption: this._app.getText('Detailansicht'),
            width: 700,
            elements: [{
                xtype: 'kijs.gui.FormPanel',
                rpc: this._app.rpc,
                facadeFnLoad: this._formFnLoad,
                facadeFnSave: this._formFnSave
            }],
            innerStyle: {
                overflowY: 'auto'
            }
        });

        // FormPanel mit Elementen füllen
        this._populateFormPanel(this.form);

        // Abbrechen-Button
        this.form.add([
            {
                xtype: 'kijs.gui.Button',
                name: 'saveBtn',
                caption: this._app.getText('Speichern'),
                iconChar: '&#xf0c7',
                style: {marginTop: '16px'},
                on: {
                    click: this._onSaveClick,
                    context: this
                }
            },{
                xtype: 'kijs.gui.Button',
                name: 'cancelBtn',
                caption: this._app.getText('Abbrechen'),
                iconChar: '&#xf05e',
                style: {marginTop: '16px'},
                on: {
                    click: this._onCancelClick,
                    context: this
                }
            }
        ]);

        return this._formPanel;
    }

    _createHeaderElements() {
        return [
            {
                xtype: 'kijs.gui.Button',
                name: 'add',
                caption: this._app.getText('Neu'),
                iconChar: '&#xf055',
                on: {
                    click: this._onAddClick,
                    context: this
                }
            },{
                xtype: 'kijs.gui.Button',
                name: 'duplicate',
                caption: this._app.getText('Duplizieren'),
                iconChar: '&#xf0c5',
                on: {
                    click: this._onDuplicateClick,
                    context: this
                }
            },{
                xtype: 'kijs.gui.Button',
                caption: this._app.getText('Löschen'),
                iconChar: '&#xf1f8',
                on: {
                    click: this._onDeleteClick,
                    context: this
                }
            }
        ];
    }

    /**
     * Kann in abgeleiteter Klasse überschrieben werden,
     * um FormPanel zu füllen
     * @param {kijs.gui.FormPanel} formPanel
     * @returns {undefined}
     */
    _populateFormPanel(formPanel) {

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
        this.parent.parent.down(this.constructor.name).dom.clsRemove('active');

        super.unrender(true);
    }


    // EVENTS

    /**
     * Klick auf den 'Neu' - Button
     * @param {Object} e
     * @returns {undefined}
     */
    _onAddClick(e) {
        let params = kijs.Object.clone(this._formRemoteParams);
        params.create = true;

        // Formular laden
        if (this.form.facadeFnLoad) {
            this.form.load(params, true, true).then(() => {

            });
        }
    }

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

    /**
     * Klick auf den Löschen-Button
     * @returns {undefined}
     */
    _onDeleteClick() {
        let params = kijs.Object.clone(this._formRemoteParams);
        params.selection = this.grid.getSelectedIds();

        this._app.rpc.do(this._deleteFn, params, function() {

            // grid neu laden
            this.grid.reload();

            // form leeren
            this.form.clear();

            this._selection = null;

        }, this);
    }

    /**
     * Klick auf den Duplicate-Button
     * @returns {undefined}
     */
    _onDuplicateClick(){
        if (this.grid.getSelectedIds().length > 0){
            if (this.grid.getSelectedIds().length === 1){
                let params = kijs.Object.clone(this._formRemoteParams);
                params.create = true;
                params.selection = this._selection;

                // Formular laden
                if (this.form.facadeFnLoad) {
                    this.form.load(params, true, true).then(() => {
                        this.form.isDirty = true;
                    });
                }
            } else {
                kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es können nicht mehrere Einträge gleichzeitig dupliziert werden.'));
            }
        } else {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Kein Eintrag ausgewählt.'));
        }
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

    /**
     * Wenn eine neue Zeile ausgewählt wird.
     * @param {Object} e
     * @returns {undefined}
     */
    _onSelectionChange(e) {
        this._selection = {};
        if (e.rows && e.rows.length === 1){
            let pks = this.grid.primaryKeys, pk=null;
            for (let i=0; i<pks.length; i++) {
                pk = pks[i];
                if (e.unSelect && this.grid.getSelectedIds().length === 1){
                    this._selection[pk] = this.grid.getSelectedIds()[0];
                } else {
                    this._selection[pk] = e.rows[0].dataRow[pk];
                }
            }
        }

        if (this.grid.getSelectedIds().length === 1){

            let params = kijs.Object.clone(this._formRemoteParams);
            params.selection = this._selection;

            // Formular laden
            if (this.form.facadeFnLoad) {
                this.form.load(params, true, true);
            }
        } else {
            //this.form.disabled = true;
            this.form.clear();
        }
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
        this._gridPanel = null;
        this._apertureMask = null;
    }
};
