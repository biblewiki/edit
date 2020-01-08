/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.person.GroupGridPanel
// --------------------------------------------------------------
kijs.createNamespace('biwi.person');

biwi.person.GroupGridPanel = class biwi_person_GroupGridPanel extends kijs.gui.Panel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._personId = null;
        this._version = null;

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Personengruppen'),
            iconChar: '&#xf0c0',
            style: {
                flex: 1

            },
            headerElements: [
                {
                    xtype: 'kijs.gui.Button',
                    caption: this._app.getText('Neu'),
                    toolTip: this._app.getText('Neue Gruppe hinzufügen'),
                    iconChar: '&#xf055',
                    on: {
                        click: this._onAddClick,
                        context: this
                    }
                },{
                    xtype: 'kijs.gui.Button',
                    caption: this._app.getText('Löschen'),
                    toolTip: this._app.getText('Gruppe löschen'),
                    iconChar: '&#xf1f8',
                    on: {
                        click: this._onDeleteClick,
                        context: this
                    }
                }
            ]
        });

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            personId: true,
            version: true
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

    get personId() { return this._personId; }
    set personId(val) { this._personId = parseInt(val); }

    get version() { return this._version; }
    set version(val) { this._version = parseInt(val); }

    get grid () { return this.down('grid'); };

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
                selectType: 'multi',
                name: 'grid',
                facadeFnLoad: 'person.getGroupGrid',
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

    _showAddWindow(data) {
        if (this._personId) {
            let win = new biwi.person.GroupWindow({
                id: this._personId,
                version: this._version,
                dataRow: data
            });
            win.on('afterSave', this.reload, this);
            win.show();
        } else {
            kijs.gui.MsgBox.alert(
                this._app.getText('Fehler'),
                this._app.getText('Person muss gespeichert werden um Gruppen hinzuzufügen.')
            );
        }
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
        this._app.rpc.do('person.deletePersonGroup', { selection: this.down('grid').getSelectedIds() }, function() {

            // Grid neu laden
            this.down('grid').reload();
        }, this);
    }

    _onRowDoubleClick() {
        this._showAddWindow(this.down('grid').current.dataRow);
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
    }

};