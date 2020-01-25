/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.default.person.NameWindow
// --------------------------------------------------------------
kijs.createNamespace('biwi.person');

biwi.person.NameWindow = class biwi_person_NameWindow extends biwi.default.DefaultFormWindow {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._names = [];

        // Config generieren
        config = Object.assign({}, {
            sourceFnLoad: 'person.getSources',
            assignTable: 'personName',
            primaryKey: 'personNameId',
            caption: this._app.getText('Namen hinzufügen'),
            cls: 'kg-app-person-nameWindow',
            iconChar: '&#xf2bc',
            width: 600,
            height: 300,
            closable: true,
            maximizable: false,
            resizable: false,
            modal: true,
            elements: this._createElements(),
            footerElements:[
                {
                    xtype: 'kijs.gui.Button',
                    isDefault: true,
                    caption: this._app.getText('Hinzufügen'),
                    on: {
                        click: this._onSaveClick,
                        context: this
                    }
                }
            ],
            innerStyle: {
                overflow: 'auto'
            }
        }, config);

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            names: true
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }

        // Vorhandene Namen aus der DB laden
        if (this._names) {
            this._fillNames(this._names);
        } else {
            this._getExistingNames();
        }
    }


    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // PROTECTED
    _addPanel() {
        this.down('names').add([
            {
                xtype: 'kijs.gui.FormPanel',
                style: {
                    borderTop: '1px solid #ddd'
                },
                defaults:{
                    height: 25,
                    labelWidth: 80,
                    style:{
                        margin: '10px'
                    }
                },
                elements: [
                    {
                        xtype: 'kijs.gui.field.Text',
                        label: this._app.getText('Name'),
                        name: 'name',
                        required: true,
                        elements: [
                            {
                                xtype: 'kijs.gui.Button',
                                iconChar: '&#xf039',
                                toolTip: this._app.getText('Quelle'),
                                on: {
                                    click: this._onSourceClick,
                                    context: this
                                }
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
                        xtype: 'kijs.gui.field.Text',
                        label: this._app.getText('Beschreibung'),
                        name: 'description'
                    },{
                        xtype: 'kijs.gui.field.Checkbox',
                        label: this._app.getText('Sichtbar'),
                        name: 'visible'
                    }
                ]
            }
        ]);
    }

    _createElements() {
        return [
            {
                xtype: 'kijs.gui.Panel',
                name: 'names',
                footerElements: [
                    {
                        xtype: 'kijs.gui.Button',
                        caption: this._app.getText('Name hinzufügen'),
                        on: {
                            click: this._onAddClick,
                            context: this
                        }
                    }
                ]
            }
        ];
    }

    _fillNames(names) {
        if (names.length) {
            kijs.Array.each(names, function(name) {
                this._addPanel();
                let count = this.down('names').elements.length - 1;

                this.down('names').elements[count].data = name;
            }, this);
        } else {
            this._addPanel();
        }
    }

    _getExistingNames() {
        let params = {
            personId: this._id,
            version: this._version
        };

        this._app.rpc.do('person.getNames', params, function(response) {

            // Namen in Formular abfüllen
            this._fillNames(response.names);
        }, this);
    }


    // EVENTS

    _onAddClick() {
        this._addPanel();
    }

    _onDeleteClick(e) {
        // Element ausblenden
        e.element.parent.parent.visible = false;

        // Element Status auf gelöscht setzen
        e.element.parent.parent.data.state = 100;
    }

    _onSaveClick() {
        let names = [];
        let error = false;
        names.values = {};

        // Namen auslesen
        kijs.Array.each(this.down('names').elements, function(formPanel, i) {

            // Wenn Formular nicht leer ist und es nicht valid ist
            if (!formPanel.isEmpty && !formPanel.validate()) {
                error = true;

            // Wenn eine ID übergeben wurde oder das Formular nicht leer ist, Daten in Array schreiben
            } else if (formPanel.data.bibleNameId || !formPanel.isEmpty) {
                names[i] = formPanel.data;
            }
        }, this);


        if (!error) {

            let ret = {
                name: 'names',
                data: names
            };

            // Event werfen
            this.raiseEvent('saveName', ret);

            // Fenster schliessen
            this.close();
        } else {
            kijs.gui.MsgBox.alert(this._app.getText('Fehler'), this._app.getText('Es wurden noch nicht alle Felder korrekt ausgefüllt.'));
        }
    }

    _onSourceClick(e) {
        this._id = e.element.parent.parent.data.personNameId;

        super._onSourceClick(e);
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
        this._names = null;
    }
};