/* global kijs, biwi, this */

// --------------------------------------------------------------
// biwi.App
// --------------------------------------------------------------
kijs.createNamespace('biwi.app');
biwi.app.MainPanel = class biwi_app_MainPanel extends kijs.gui.Container {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super();

        this._app = new biwi.app.App();
        this._panelInstances = [];

        // Config generieren
        config = Object.assign({}, {
            elements:this._createElements(),
            cls: 'kijs-flexcolumn',
            style: {
                flex: 1
            }
        }, config);

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
            // keine
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }
console.log(this.down('biwi-app-buttontreenav'));
        // Events: Klick auf ein Navi-Button
        this.down('biwi-app-buttontreenav').on('btnclick', function(e) {
            this.showPanel(e.raiseElement.name);
        }, this);

        // Laden der Navi: erstes Element anzeigen
        this.down('biwi-app-buttontreenav').on('treeload', function(elements) {
            if (elements && elements[0]) {
                this.showPanel(elements[0].name);
            }
        }, this);
    }

    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------

    get isDirty() {
        let dirty = false;
        kijs.Array.each(this.down('maincontainer').elements, function(element) {
            if (element.isDirty === true) {
                dirty = true;
            }
        }, this);
        return dirty;
    }

    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    /**
     * Zeigt ein Panel an.
     * @param {String} panelname
     * @param {array} args
     * @returns {undefined}
     */
    showPanel(panelname, args) {

        kijs.isArray(args) ? args : [args];

        let panel = this._getInstance(panelname);
        if (panel) {
            // Das Panel ist bereis das aktuelle
            if (kijs.Array.contains(this.down('maincontainer').elements, panel)) {

                if (kijs.isFunction(panel.refreshPanel)) {
                    panel.refreshPanel(args);
                }

            // Panel wechseln
            } else {
                if (kijs.isFunction(panel.showPanel)) {
                    panel.showPanel(args);
                }

                // Das Panel anzeigen.
                this.down('maincontainer').removeAll();
                this.down('maincontainer').add(panel);
                this.raiseEvent('panelchanged', {name: panelname, panel: panel});

            }
        }
    }

    /**
     * Erstellt die Elemente
     * @returns {Array}
     */
    _createElements() {
        // Haupt-Panel erstellen
        return [{
            xtype: 'kijs.gui.Panel',
            caption: 'kgweb',
            iconCls: 'icoWizard16',
            cls: 'kijs-flexcolumn',
            style: {
                flex: 1
//                        margin: '0 0 4px 0'
            },
            headerBarElements:[
                {
                    xtype: 'kijs.gui.Button',
                    name: 'help_btn',
                    iconChar: '&#xf059', // question mark
                    toolTip: this._app.getText('Hilfe'),
                    on: {
                        click: function() {
                            (new kg.app.AboutWindow()).show();
                        },
                        context: this
                    }
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'help_btn',
                    iconChar: '&#xf05a', // info
                    toolTip: this._app.getText('Über kgweb'),
                    on: {
                        click: function() {
                            (new kg.app.AboutWindow()).show();
                        },
                        context: this
                    }
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'profile_btn',
                    iconChar: '&#xf2be',
                    toolTip: this._app.getText('Benutzerkonto'),
                    on: {
                        click: function() {
                            this.showPanel('kg_user_User');
                        },
                        context: this
                    }
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'logout_btn',
                    iconChar: '&#xf08b',
                    toolTip: this._app.getText('Abmelden'),
                    on: {
                        click: function() {
                            this._app.logout();
                        },
                        context: this
                    }
                }
            ],
            elements:[{
                xtype: 'kijs.gui.Container',
                cls: 'kijs-flexrow',
                style: {
                    flex: 1
                },
                elements: [
                    // LEFT
                    {
                        xtype: 'kijs.gui.Panel',
                        caption: this._app.getText('Navigation'),
                        iconChar: '&#xf0c9',
                        collapsible: 'left',
                        width: 180,
                        cls: 'kijs-flexcolumn',
                        elements:[{
                                xtype: 'biwi.app.ButtonTree',
                                name: 'biwi-app-buttontreenav'
                            }
                        ]
                    },{
                        xtype: 'kijs.gui.Splitter',
                        targetPos: 'left'
                    },
                    // CENTER
                    {
                        xtype: 'kijs.gui.Container',
                        name: 'maincontainer',
                        cls: 'kijs-flexcolumn',
                        style: {
                            flex: 1
                        },
                        elements: []
                    }
                ]
            }]
        }];
    }

    /**
     * Gibt eine Klasse anhand ihres Namen zurück
     * @param {String} name
     * @param {String} [separator]
     * @returns {Function}
     */
    _getClassByName(name, separator='_') {
        let path = name.split(separator), cls = window;
        for (let i=0; i<path.length; i++) {
            if (cls[path[i]]) {
                cls = cls[path[i]];
            } else {
                cls = null;
                break;
            }
        }
        return kijs.isFunction(cls) ? cls : null;
    }

    /**
     *
     * @param {String} name
     * @param {Boolean} [forceNewInstance] true, falls eine neue instanz erstellt werden soll.
     * @returns {Object}
     */
    _getInstance(name, forceNewInstance=false) {
        let instance = null;
        if (forceNewInstance !== true) {
            kijs.Array.each(this._panelInstances, function(pnl) {
                if (pnl.name === name) {
                    instance = pnl.instance;
                    return false;
                }
            });
        }

        // Es wurde noch keine Instanz erstellt, neue erstellen.
        if (instance === null) {
            let classConstr  = this._getClassByName(name);
            if (classConstr) {
                instance = new classConstr();

                // merken
                this._panelInstances.push({name: name, instance: instance});
            }
        }

        return instance;
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();
    }

};