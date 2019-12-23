/* global this, kijs */

// --------------------------------------------------------------
// biwi.app.ButtonTree
// --------------------------------------------------------------
kijs.createNamespace('biwi.app');
biwi.app.ButtonTree = class biwi_app_ButtonTree extends kijs.gui.ContainerStack {


    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {
        super(false);
        this._app = new biwi.app.App();
        this._buttonToPanel = [];

        // Config generieren
        config = Object.assign({}, {
            cls: 'biwi-app-buttontree'
        }, config);

         // Mapping für die Zuweisung der Config-Eigenschaften
        Object.assign(this._configMap, {

        });

        // Config anwenden
        if (kijs.isObject(config)) {
            this.applyConfig(config, true);
        }

        // Panels vom Server laden
        this._app.rpc.do('app.getNaviTree', null, function(ret) {
            this.generateButtonContainer(ret.elements);

            // Event
            this.raiseEvent('treeload', ret.elements);

        }, this);

    }


    /**
     * Erstellt für jede Hirarchiestufe ein Panel mit Buttons.
     * @param {Object} buttontree
     * @param {kijs.gui.Panel|null} parentPanel
     * @param {String|null} backCaption
     * @returns {kijs.gui.Panel}
     */
    generateButtonContainer(buttontree, parentPanel=null, backCaption=null) {
        let panelElements = [];
        let panel = new kijs.gui.Panel({
            cls:'kijs-flexcolumn',
            defaults: {
                xtype: 'kijs.gui.Button'
            },
            elements: {
                xtype: 'kijs.gui.Button',
                caption: 'test',
                on: {
                    click: this._onButtonClick,
                    context: this
                }
            }
        });

        // Button auf das Parent-Panel einfügen
        if (parentPanel) {
            let btn = {
                xtype: 'kijs.gui.Button',
                iconChar: '&#xf060',
                caption: backCaption,
                cls: 'backbutton',
                name: this._getBtnName(),
                on: {
                    click: this._onBtnClick,
                    context: this
                }
            };
            panelElements.push(btn);

            // Speichern, zu welchem Panel der Button geht.
            this._buttonToPanel.push({
                name: btn.name,
                panel: parentPanel,
                animation: 'slideRight'
            });
        }

        if (!kijs.isArray(buttontree)) {
            buttontree = [buttontree];
        }

        kijs.Array.each(buttontree, function(element) {
            if (!element.xtype || element.xtype === 'kijs.gui.Button') {
                let btn = {
                    xtype: 'kijs.gui.Button',
                    name: this._getBtnName(element.name),
                    cls: element.elements ? 'treebutton' : 'leavebutton',
                    on: {
                        click: this._onBtnClick,
                        context: this
                    }
                };

                // sämtliche propertys ausser elements und name übernehmen
                for (let keyname in element) {
                    if (keyname !== 'elements' && keyname !== 'name') {
                        btn[keyname] = element[keyname];
                    }
                }
                panelElements.push(btn);

                // subelement generieren
                if (element.elements) {
                    let subPanel = this.generateButtonContainer(element.elements, panel, btn.caption);

                    // Speichern, zu welchem Panel der Button geht.
                    this._buttonToPanel.push({
                        name: btn.name,
                        panel: subPanel,
                        animation: 'slideLeft'
                    });
                }

            // hat ein element ein xtype, einfach einfügen.
            } else {
                panelElements.push(element);
            }
        }, this);

        panel.add(panelElements);
        this.add(panel);
        return panel;
    }

    /**
     * generiert einen eindeutigen name, wenn er nicht übergeben wird.
     * @param {String} [name]
     * @returns {String} z.B.: btn_jozn4v6n_5y3
     */
    _getBtnName(name) {
        return name ? name : 'btn_' + (new Date()).getTime().toString(36) + '_' + Math.round(Math.random()*10000).toString(36);
    }

    _onBtnClick(e) {
        kijs.Array.each(this._buttonToPanel, function(btntp) {
            if (btntp.name === e.element.name) {
                this.activateAnimated(btntp.panel, btntp.animation);
                return false;
            }
        }, this);
        this.raiseEvent('btnclick', e);
    }
};