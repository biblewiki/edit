/* global kijs, biwi */

// --------------------------------------------------------------
// kg.App
// --------------------------------------------------------------
kijs.createNamespace('biwi.person');

biwi.person.RelationshipGridPanel = class biwi_person_RelationshipGridPanel extends kijs.gui.Panel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._personId = null;
        this._version = null;
        this._gridPanel = null;

        // Config generieren
        Object.assign(this._defaultConfig, {
            caption: this._app.getText('Beziehungen'),
            iconChar: '&#xf0c1',
            style: {
                flex: 1,
                minWidth: '40px'

            },
            headerElements: [
                {
                    xtype: 'kijs.gui.Button',
                    caption: this._app.getText('Neu'),
                    toolTip: this._app.getText('Neue Beziehung hinzufügen'),
                    iconChar: '&#xf055',
                    on: {
                        click: this._onAddClick,
                        context: this
                    }
                },{
                    xtype: 'kijs.gui.Button',
                    caption: this._app.getText('Löschen'),
                    toolTip: this._app.getText('Beziehung löschen'),
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
                facadeFnLoad: 'person.getRelationshipGrid',
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
            let win = new biwi.person.RelationshipWindow({
                personId: this._personId,
                dataRow: data
            });
            win.show();
        } else {
            kijs.gui.MsgBox.alert(
                this._app.getText('Fehler'),
                this._app.getText('Person muss gespeichert werden um Beziehungen hinzuzufügen.')
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
        this._app.rpc.do('relationship.deleteRelationship', { selection: this.down('grid').getSelectedIds() }, function() {

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
        this._app = null;
    }

};