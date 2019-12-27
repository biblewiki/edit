/* global kijs, biwi */

// --------------------------------------------------------------
// biwi.messgae.MessageContainer
// --------------------------------------------------------------

kijs.createNamespace('biwi.dashboard');

biwi.message.MessageContainer = class biwi_message_MessageContainer extends kijs.gui.Container {

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);

        this._app = new biwi.app.App();
        this._loaded = false;

        // Config generieren
        Object.assign(this._defaultConfig, {
            cls: 'biwi-dashboard-mitteilungcontainer',
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

        this.on('afterRender', this._onAfterRender, this);
        this.on('unrender', this._onUnrender, this);
    }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    load() {
        if (!this._loaded) {
            this._app.rpc.do('dashboard.getMessages', null, function(response) {
                if (kijs.isArray(response.messages) && response.messages.length) {
                    this.add(this._createMessageContainers(response.messages));
                }
            }, this);
            this._loaded = true;
        }
    }


    // Private

    _createMessageContainers(messages) {
        let messageContainers = [];

        kijs.Array.each(messages, function(message) {
            messageContainers.push(
                {
                    xtype: 'kijs.gui.Container',
                    cls: 'container',
                    elements: [
                        {
                            xtype: 'kijs.gui.Icon',
                            iconChar: '&#xf05a',
                            cls: 'icon'
                        },{
                            xtype: 'kijs.gui.Element',
                            html: message.text,
                            cls: 'message'
                        }
                    ]
                }
            );
        }, this);

        return messageContainers;
    }

    // Events
    _onAfterRender() {
        this.load();
    }

    _onUnrender() {
        this._loaded = false;
        this.removeAll(true);
    }


    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        super.destruct();

        this._app = null;
        this._loaded = null;
    }

};