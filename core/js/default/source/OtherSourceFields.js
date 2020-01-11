/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.default.source.BookSourceFields
// --------------------------------------------------------------
kijs.createNamespace('biwi.default.source');

biwi.default.source.OtherSourceFields = class biwi_default_source_OtherSourceFields extends kijs.gui.FormPanel {
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
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Text',
                        name: 'title',
                        label: this._app.getText('Titel'),
                        maxLength: 45,
                        required: true
                    },{
                        xtype: 'kijs.gui.field.Text',
                        name: 'name',
                        label: this._app.getText('Name'),
                        maxLength: 45,
                        required: true
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
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Text',
                        name: 'description',
                        label: this._app.getText('Beschreibung'),
                        maxLength: 255,
                        required: true
                    }
                ]
            },{
                xtype: 'kijs.gui.Container',
                innerStyle: {
                    display: 'flex'
                },
                defaults:{
                    width: 280,
                    labelWidth: 80,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Combo',
                        name: 'type',
                        label: this._app.getText('Typ'),
                        remoteSort: true,
                        forceSelection: false,
                        rpc: this._app.rpc,
                        facadeFnLoad: 'source.getOtherSourceType',
                        minChars: 1
                    },{
                        xtype: 'kijs.gui.field.Text',
                        name: 'workName',
                        label: this._app.getText('Name des Werks'),
                        maxLength: 45
                    }
                ]
            },{
                xtype: 'kijs.gui.Container',
                innerStyle: {
                    display: 'flex'
                },
                defaults:{
                    width: 280,
                    labelWidth: 80,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Combo',
                        name: 'medium',
                        label: this._app.getText('Medium'),
                        remoteSort: true,
                        forceSelection: false,
                        rpc: this._app.rpc,
                        facadeFnLoad: 'source.getOtherSourceMedium',
                        minChars: 1
                    },{
                        xtype: 'kijs.gui.field.Number',
                        name: 'number',
                        label: this._app.getText('Nummer')
                    }
                ]
            },{
                xtype: 'kijs.gui.Container',
                innerStyle: {
                    display: 'flex'
                },
                defaults:{
                    width: 280,
                    labelWidth: 80,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Number',
                        name: 'edition',
                        label: this._app.getText('Edition')
                    },{
                        xtype: 'kijs.gui.field.Text',
                        name: 'locality',
                        label: this._app.getText('Lokalität'),
                        maxLength: 45
                    }
                ]
            },{
                xtype: 'kijs.gui.Container',
                innerStyle: {
                    display: 'flex'
                },
                defaults:{
                    width: 280,
                    labelWidth: 80,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Combo',
                        name: 'publishCompany',
                        label: this._app.getText('Verlag'),
                        remoteSort: true,
                        forceSelection: false,
                        rpc: this._app.rpc,
                        facadeFnLoad: 'source.getOtherSourcePublishCompany',
                        minChars: 1
                    },{
                        xtype: 'kijs.gui.field.DateTime',
                        name: 'publishDate',
                        label: this._app.getText('Veröffentlichungsdatum'),
                        hasTime: false
                    }
                ]
            },{
                xtype: 'kijs.gui.Container',
                innerStyle: {
                    display: 'flex'
                },
                defaults:{
                    width: 280,
                    labelWidth: 80,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Combo',
                        name: 'language',
                        label: this._app.getText('Sprache'),
                        remoteSort: true,
                        forceSelection: false,
                        rpc: this._app.rpc,
                        facadeFnLoad: 'source.getOtherSourceLanguage',
                        minChars: 1
                    },{
                        xtype: 'kijs.gui.field.Number',
                        name: 'isbnDoiIssn',
                        label: this._app.getText('ISBN')
                    }
                ]
            },{
                xtype: 'kijs.gui.Container',
                defaults:{
                    labelWidth: 80,
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
            },{
                xtype: 'kijs.gui.Container',
                innerStyle: {
                    display: 'flex'
                },
                defaults:{
                    width: 280,
                    labelWidth: 80,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.DateTime',
                        name: 'downloadDate',
                        label: this._app.getText('Downloaddatum'),
                        hasTime: false
                    },{
                        xtype: 'kijs.gui.field.Text',
                        name: 'rights',
                        label: this._app.getText('Rechte'),
                        maxLength: 255,
                        required: true
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
        e.element.parent.parent.data.state = 100;
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
