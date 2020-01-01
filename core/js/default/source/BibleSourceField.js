/* global this, kijs, biwi */

// --------------------------------------------------------------
// ki.DefaultGridComponent
// --------------------------------------------------------------
kijs.createNamespace('biwi.default.source');

biwi.default.source.BibleSourceField = class biwi_default_source_BibleSourceField extends kijs.gui.FormPanel {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._books = [];

        this._formRemoteParams = {};

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            cls: 'kijs-flexrow',
            style: {
                flex: 1
            },
            defaults:{
                width: 280,
                height: 25,
                labelWidth: 80,
                required: true,
                style:{
                    margin: '10px'
                }
            }
        });

        // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            books: true
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
                xtype: 'kijs.gui.field.Combo',
                name: 'book',
                label: this._app.getText('Bibelbuch'),
                data: this._books.names,
                captionField: 'caption',
                valueField: 'value',
                on: {
                    change: this._onBookChange,
                    context: this
                }
            },{
                xtype: 'kijs.gui.field.Combo',
                name: 'chapter',
                label: this._app.getText('Kapitel'),
                captionField: 'value',
                valueField: 'value',
                readOnly: true,
                on: {
                    change: this._onChapterChange,
                    context: this
                }
            },{
                xtype: 'kijs.gui.field.Combo',
                name: 'verse',
                label: this._app.getText('Vers'),
                captionField: 'value',
                valueField: 'value',
                readOnly: true
            }
        ];
    }


    // EVENTS

    /**
     * Wenn das Buch Combo geändert wird
     *
     * @param {type} e
     * @returns {undefined}
     */
    _onBookChange(e) {

        if (e.element.value) {
            this.down('chapter').data = this._books.chapters[e.element.value];
            this.down('chapter').readOnly = false;
        } else {
            this.down('chapter').readOnly = true;
        }

        this.down('chapter').value = null;

        this.down('verse').value = null;
        this.down('verse').readOnly = true;
    }

    /**
     * Wenn das Kapitel Combo geändert wird
     *
     * @param {type} e
     * @returns {undefined}
     */
    _onChapterChange(e) {

        if (e.element.value) {
            this.down('verse').data = this._books.verses[this.down('book').value][e.element.value];
            this.down('verse').value = null;
            this.down('verse').readOnly = false;
        } else {
            this.down('verse').readOnly = true;
        }

        this.down('verse').value = null;
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
