/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.dashboard.PortletContainer
// --------------------------------------------------------------

kijs.createNamespace('biwi.dashboard');

biwi.dashboard.PortletContainer = class biwi_dashboard_PortletContainer extends kijs.gui.Container {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._hiddenPortlets = [];

        // Config generieren
        Object.assign(this._defaultConfig, {
            cls: 'biwi-dashboard-portletcontainer',
            elements: []
        });

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            // Keine
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            config = Object.assign({}, this._defaultConfig, config);
            this.applyConfig(config, true);
        }


        this._app.rpc.do('dashboard.getPortlets', null, function(response) {
            if (kijs.isArray(response.portlets)) {
                kijs.Array.each(response.portlets, function(portlet) {
                    const xtype = portlet.xtype || 'biwi.dashboard.Portlet';
                    const visible = portlet.visible === false ? false : true;
                    delete portlet.xtype;
                    delete portlet.visible;

                    if (!portlet.name) {
                        portlet.name = 'noname_' + (Date.now()).toString(36);
                    }

                    // Klasse abfragen
                    const constr = kijs.getObjectFromNamespace(xtype);
                    if (!constr) {
                        throw new Error(`invalid xtype "${xtype}"`);
                    }

                    // Instanz erstellen
                    const portletEl = new constr(portlet);
                    if (!(portletEl instanceof biwi.dashboard.Portlet)) {
                        throw new Error(`Portlets must be a instance of/inherit from "biwi.dashboard.Portlet", "${xtype}" given.`);
                    }

                    if (visible) {
                        // event hinzufügen
                        this.add(portletEl);

                    } else {
                        this._hiddenPortlets.push(portletEl);
                    }

                    // menu aktualisieren
                    this._updatePortletMenu();

                }, this);
            }
        }, this);

    }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    // overwrite
    add(element, index) {

        // close-event abhören
        if (element instanceof biwi.dashboard.Portlet) {
            element.off('close', this._onPortletClose, this);
            element.on('close', this._onPortletClose, this);
        }
        super.add(element, index);
    }

    saveState() {
        this._savePortletState();
    }

    // PROTECTED

    _updatePortletMenu() {
        let portletMenu = this.parent.portletMenu, toRemove=[];

        // alle Buttons entfernen
        kijs.Array.each(portletMenu.elements, function(element) {
           if (element && element.name && kijs.String.beginsWith(element.name, 'portlet_')) {
               toRemove.push(element);
           }
        });
        kijs.Array.each(toRemove, function(element) {
            this.parent.portletMenu.spinbox.remove(element);
        }, this);

        this._hiddenPortlets.sort(function(portletA, portletB) {
            if (portletA.sort < portletB.sort) return 1;
            if (portletA.sort > portletB.sort) return -1;
            return 0;
        });

        kijs.Array.each(this._hiddenPortlets, function(hiddenPortlet) {
            portletMenu.add({
                xtype: 'kijs.gui.Button',
                name: 'portlet_' + hiddenPortlet.name,
                caption: hiddenPortlet.headerBar.html,
                iconChar: hiddenPortlet.headerBar.iconChar,
                on: {
                    click: this._onPortletMenuBtnClick,
                    context: this
                }
            });
        }, this);
    }

    _savePortletState() {
        let portletStates = [], sort=0;
        kijs.Array.each(kijs.Array.concat(this.elements, this._hiddenPortlets), function(portlet) {
            if (portlet instanceof biwi.dashboard.Portlet && portlet.name) {
                sort++;
                portlet.sort = sort;
                portletStates.push({
                   portlet: portlet.name,
                   visible: kijs.Array.contains(this.elements, portlet) ? 1 : 0,
                   sort: sort
                });
            }
        }, this);

        this._app.rpc.do('dashboard.setPortletState', {portlets: portletStates}, null, this, false, 'none');
    }



    // EVENT
    _onPortletClose(e) {
        if (!kijs.Array.contains(this._hiddenPortlets, e.element)) {
            this._hiddenPortlets.push(e.element);
        }

        // Menu aktualisieren
        this._updatePortletMenu();

        // auf dem Server speichern
        kijs.defer(function() {
            this._savePortletState();
        }, 10, this);
    }



    _onPortletMenuBtnClick(e) {
        if (e.element.name && kijs.String.beginsWith(e.element.name, 'portlet_')) {
            let name = e.element.name.substr('portlet_'.length);

            // portlet zum anzeigen finden
            let portlet = null;
            kijs.Array.each(this._hiddenPortlets, function(hiddenPortlet) {
                if (hiddenPortlet.name === name) {
                    portlet = hiddenPortlet;
                    return false;
                }
            }, this);

            // Kachel aus hidden entfernen und zum Container hinzufügen.
            if (portlet) {
                this.add(portlet, 0);
                kijs.Array.remove(this._hiddenPortlets, portlet);

                // auf dem Server speichern
                kijs.defer(function() {
                    this._updatePortletMenu();
                    this._savePortletState();
                }, 10, this);
            }
        }
    }

};