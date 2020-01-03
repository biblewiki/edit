/* global this, kijs, biwi */

// --------------------------------------------------------------
// biwi.default.source.BibleSourceFields
// --------------------------------------------------------------
kijs.createNamespace('biwi.default.source');

biwi.default.source.BibleSourceFields = class biwi_default_source_BibleSourceFields extends kijs.gui.FormPanel {
    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._books = [];

        // Standard-config-Eigenschaften
        Object.assign(this._defaultConfig, {
            cls: 'kijs-flexrow',
            style: {
                flex: 1,
                borderTop: '1px solid #ddd'
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

    get values() { return this.data; }
    set values(vals) {
        //this.down('bookId').data = this._books.names;
        this.data = vals;

        this.down('chapterId').data = this._books.chapters[this.down('bookId').value];
        this.down('verseId').data = this._books.verses[this.down('bookId').value][this.down('chapterId').value];

        this.readOnly = false;
    }

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // PROTECTED
    _createElements() {
        return [
            {
                xtype: 'kijs.gui.field.Combo',
                name: 'bookId',
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
                name: 'chapterId',
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
                name: 'verseId',
                label: this._app.getText('Vers'),
                captionField: 'value',
                valueField: 'value',
                readOnly: true
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
            this.down('chapterId').data = this._books.chapters[e.element.value];
            this.down('chapterId').readOnly = false;
        } else {
            this.down('chapterId').readOnly = true;
        }

        this.down('chapterId').value = null;

        this.down('verseId').value = null;
        this.down('verseId').readOnly = true;
    }

    /**
     * Wenn das Kapitel Combo geändert wird
     *
     * @param {type} e
     * @returns {undefined}
     */
    _onChapterChange(e) {

        if (e.element.value) {
            this.down('verseId').data = this._books.verses[this.down('bookId').value][e.element.value];
            this.down('verseId').value = null;
            this.down('verseId').readOnly = false;
        } else {
            this.down('verseId').readOnly = true;
        }

        this.down('verseId').value = null;
    }


    _onDeleteClick(e) {
        // Element ausblenden
        e.element.parent.visible = false;

        // Element Status auf gelöscht setzen
        e.element.parent.data.state = 99;
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
        this._books = null;
    }
};
