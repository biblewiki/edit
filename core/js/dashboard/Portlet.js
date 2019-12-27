/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.dashboard.Portlet
// --------------------------------------------------------------

kijs.createNamespace('biwi.dashboard');

biwi.dashboard.Portlet = class biwi_dashboard_Portlet extends kijs.gui.Panel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._sort = 0;

        // Config generieren
        Object.assign(this._defaultConfig, {
            cls: 'biwi-dashboard-portlet',
            closable: true
        });

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            sort: {target: 'sort'}
        });

        // headerBarElements
        if (kijs.isObject(config) && config.headerBarElements) {
            config.headerBarElements = this._makeHeaderBarElements(config.headerBarElements);
        }

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }

        // DragDrop-Events
        this._headerBarEl.dom.nodeAttributeSet('draggable', true);
        kijs.DragDrop.addDragEvents(this, this._headerBarEl.dom);
        kijs.DragDrop.addDropEvents(this, this.dom);
        this.on('ddOver', this._onDdOver, this);
        this.on('ddDrop', this._onDdDrop, this);

    }


    // --------------------------------------------------------------
    // GETTER/SETTER
    // --------------------------------------------------------------

    get sort() { return this._sort; }
    set sort(val) { this._sort = parseInt(val); }

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    _makeHeaderBarElements(headerBarElements) {
        if (!kijs.isArray(headerBarElements)) {
            headerBarElements = [headerBarElements];
        }

        kijs.Array.each(headerBarElements, function(headerBarElement) {
            if (kijs.isObject(headerBarElement) && !(headerBarElement instanceof kijs.gui.Element)) {
                if (!headerBarElement.xtype) {
                    headerBarElement.xtype = 'kijs.gui.Button';
                }
                if (headerBarElement.xtype === 'kijs.gui.Button') {
                    headerBarElement.on = {
                        click: this._onHeaderBarButtonClick,
                        context: this
                    };
                }
            }
        }, this);

        return headerBarElements;
    }

    // EVENT
    _onDdOver(dd) {
        if (dd.sourceElement instanceof biwi.dashboard.Portlet) {
            dd.position.allowAbove = false;
            dd.position.allowBelow = false;
            dd.position.allowOnto = false;

        // Kein Drop erlauben
        } else {
            dd.position.allowAbove = false;
            dd.position.allowBelow = false;
            dd.position.allowOnto = false;
            dd.position.allowLeft = false;
            dd.position.allowRight = false;
        }
    }

    // ein anderes Element wurde auf diesem Element abgeladen.
    _onDdDrop(dd) {
        let pos = dd.position.position, movedElement=dd.sourceElement;

        if (movedElement instanceof biwi.dashboard.Portlet) {

            // element entfernen
            this.parent.remove(movedElement);
            let thispos = this.parent.elements.indexOf(this);
            if (pos === 'right') {
                thispos += 1;
            }
            this.parent.add(movedElement, thispos);

            // Speichern
            this.parent.saveState();
        }
    }

    _onHeaderBarButtonClick(e) {
        const btn = e.element;
        if (btn.name) {
            const constr = kijs.getObjectFromNamespace(kijs.String.replaceAll(btn.name, '_', '.'));
            const inst = new constr();

            if (kijs.isFunction(inst.init)) {
                inst.init();
            }
        }
    }
};