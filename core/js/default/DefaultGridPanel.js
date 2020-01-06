/* global this, kijs, biwi */

// --------------------------------------------------------------
// ki.DefaultGridComponent
// --------------------------------------------------------------
kijs.createNamespace('biwi.default');

biwi.default.DefaultGridPanel = class biwi_default_DefaultGridPanel extends kijs.gui.Container {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._gridPanel = null;
        this._detailPanel = null;
        this._selection = null;
        this._apertureMask = null;
        this._gridFnLoad = null;
        this._detailFnLoad = null;
        this._editPanel = null;
        this._gridCaption = this._app.getText('Tabelle');
        this._detailCaption = this._app.getText('Details');

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
            gridFnLoad: true,
            detailFnLoad: true,
            editPanel: true,
            gridCaption: true,
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

    get grid() { return this._gridPanel.firstChild; }
    get detail() { return this._detailPanel; }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    refreshPanel(args) {
        if (args && args.restoreSelection){
            this.grid.reload(args.restoreSelection);
        } else {
            this.grid.reload();
        }
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
        this.grid.on('rowDblClick', this._editEntry, this);
    }

    // PROTECTED
    _createElements() {
        return [
            this._createTablePanel(),
            {
                xtype: 'kijs.gui.Splitter',
                targetPos: 'right'
            },
            this._createDetailPanel()
        ];
    }

    _createTablePanel() {
        return this._gridPanel = new kijs.gui.Panel({
            caption: this._gridCaption,
            cls: 'kijs-flexcolumn',
            style: {
                flex: 1,
                minWidth: '40px'
            },
            elements: [{
                xtype: 'kijs.gui.grid.Grid',
                selectType: 'multi',
                filterable: true,
                filterVisible: true,
                facadeFnLoad: this._gridFnLoad,
                rpc: this._app.rpc,
                style: {
                    flex: 1
                }
            }],
            headerElements: this._createHeaderElements()
        });
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
                    name: 'editBtn',
                    caption: this._app.getText('Bearbeiten'),
                    iconChar: '&#xf040;',
                    height: 40,
                    disabled: true,
                    style: {
                      flex: 1
                    },
                    on: {
                        click: this._editEntry,
                        context: this
                    }
                }
            ]
        });
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
     * Öffnet das entsprechende Panel um den Eintrag zu bearbeiten
     *
     * @returns {undefined}
     */
    _editEntry() {

        if (kijs.isEmpty(this._editPanel)) {
            kijs.gui.MsgBox.error(this._app.getText('Fehler'), this._app.getText('Es wurde kein Edit-Panel angegeben.'));
            return;
        }

        let mainPanel = this._app.mainPanel;

        let params = {};

        // Parameter vorbereiten
        kijs.Object.each(this._selection, function(key) {
            if (key.includes('Id')) {
                params.id = this._selection[key];
            } else if (key === 'version') {
                params.version = this._selection[key];
            }
        }, this);

        if (mainPanel) {
            mainPanel.showPanel(this._editPanel, params);
        }
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
     *
     * @returns {undefined}
     */
    _onAddClick() {
        let mainPanel = this._app.mainPanel;

        if (mainPanel) {
            mainPanel.showPanel(this._editPanel);
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
                        this._onFormChange();
                    });
                }
            } else {
                kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es können nicht mehrere Einträge gleichzeitig dupliziert werden.'));
            }
        } else {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Kein Eintrag ausgewählt.'));
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

        // Details laden
        kijs.Object.each(this._selection, function(key) {
            if (key.includes('Id')) {
                this._getDetailData(this._selection[key]);
            }
        }, this);

        this._detailPanel.footer.down('editBtn').disabled = false;
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
        this._gridPanel = null;
        this._apertureMask = null;
    }
};
