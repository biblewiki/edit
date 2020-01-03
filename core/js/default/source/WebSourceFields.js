/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.default.source.WebSourceFields
// --------------------------------------------------------------
kijs.createNamespace('biwi.default.source');

biwi.default.source.WebSourceFields = class biwi_default_source_WebSourceFields extends kijs.gui.FormPanel {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            cls: 'kijs-flexrow',
            style: {
                borderTop: '1px solid #ddd'
            },
            innerStyle: {
                flexDirection: 'column'
            }
        });

        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            // keine
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }

        this.add(this._createElements());
    }


    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // PROTECTED
    _createElements() {
        return [
            {
                xtype: 'kijs.gui.Container',
                innerStyle: {
                    display: 'flex'
                },
                defaults:{
                    width: 280,
                    labelWidth: 80,
                    required: true,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Text',
                        name: 'name',
                        label: this._app.getText('Name'),
                        maxLength: 191
                    },{
                        xtype: 'kijs.gui.field.Text',
                        name: 'description',
                        label: this._app.getText('Beschreibung'),
                        maxLength: 255
                    },{
                        xtype: 'kijs.gui.Button',
                        name: 'deleteBtn',
                        iconChar: '&#xf1f8',
                        width: 25,
                        on: {
                            click: this._onDeleteClick,
                            context: this
                        }
                    }
                ]
            },{
                xtype: 'kijs.gui.Container',
                defaults:{
                    labelWidth: 80,
                    required: true,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Text',
                        name: 'url',
                        label: this._app.getText('URL'),
                        maxLength: 255
                    }
                ]
            }
        ];
    }

    // Events

    _onDeleteClick(e) {
        // Element ausblenden
        e.element.parent.parent.visible = false;

        // Element Status auf gelöscht setzen
        e.element.parent.parent.data.state = 99;
    }

    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct(preventDestructEvent) {
        // Event auslösen.
        if (!preventDestructEvent) {
            this.raiseEvent('destruct');
        }

        // Basisklasse auch entladen
        super.destruct(true);

        // Variablen (Objekte/Arrays) leeren
        this._app = null;
    }
};
