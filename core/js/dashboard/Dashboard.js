/* global kijs,  biwi */

// --------------------------------------------------------------
// biwi.dashboard.Dashboard
// --------------------------------------------------------------

kijs.createNamespace('biwi.dashboard');

biwi.dashboard.Dashboard = class biwi_dashboard_Dashboard extends kijs.gui.Panel {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();

        // Config generieren
        config = Object.assign({}, {
            caption: this._app.getText('Start'),
            iconChar: '&#xf015',
            elements:this._createElements(),
            style: {flex: 1},
            cls: ['biwi-dashboard-dashboard', 'kijs-flexcolumn']
        }, config);

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {
        });

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }

        this._loadPanel();
    }

    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------

    get portletMenu() { return this.down('portletMenu'); }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    /**
     * Erstellt die Elemente
     * @returns {Array}
     */
    _createElements() {
        return [
            {
                xtype:'kijs.gui.Container',
                name: 'titleContainer',
                cls: 'titleContainer',
                style: {
                    flex: 'none'
                }
            },{
                xtype:'kijs.gui.Container',
                cls: 'btnContainer',
                style: {
                    flex: 'none'
                },
                elements:[{
                    xtype: 'kijs.gui.MenuButton',
                    name: 'portletMenu',
                    toolTip: this._app.getText('Kacheln hinzufügen'),
                    caption: this._app.getText('Kacheln'),
                    elements:[{
                        xtype: 'kijs.gui.MenuButton',
                        caption: this._app.getText('Neu') + '...',
                        elements:[{
                            caption: this._app.getText('Notizkachel')
                        }]
                    }, '-']
                }]
            },{
                xtype: 'biwi.message.MessageContainer'
            },{
                xtype: 'biwi.dashboard.PortletContainer',
                style: {
                    flex: 1
                }
            }
        ];
    }

    _loadPanel() {
        this._app.rpc.do('dashboard.getHeaderHtml', null, function(response) {
            this.down('titleContainer').html = response.html;
        }, this);
    }

    unrender(superCall) {
        // Event auslösen.
        if (!superCall) {
            this.raiseEvent('unrender');
        }

        if (this._apertureMask) {
            this._apertureMask.unrender();
        }

        // Button Klasse 'active' entfernen
        this.parent.parent.down(this.constructor.name).dom.clsRemove('active');

        super.unrender(true);
    }

    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        this._app = null;
        super.destruct();
    }

};