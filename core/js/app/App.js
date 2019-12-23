/* global kijs, Babel, biwi */

// --------------------------------------------------------------
// app.App
// --------------------------------------------------------------
kijs.createNamespace('biwi.app');
biwi.app.App = class biwi_app_App {



    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config=null) {

        // Singleton (es wird immer die gleiche Instanz zurückgegeben)
        if (!biwi_app_App._singletonInstance) {
            biwi_app_App._singletonInstance = this;

            if (config === null) {
                throw new Error('cannot create instance of App without config.');
            }

            // Kijs getText setzen
            kijs.setGetTextFn(this.getText, this);

            this._config = config;
            this._viewport = null;

            // RPC-Instanz
            var rpcConfig = {};
            if (config.ajaxUrl) {
                rpcConfig.url = config.ajaxUrl;
            }
            if (config.ajaxTimeout) {
                rpcConfig.timeout = config.ajaxTimeout * 1000;
            }
            this._rpc = new kijs.gui.Rpc(rpcConfig);

            // Auth-token von URL entfernen
            this._authToken = this._removeParameterFromUrl('authToken');

            // Variablen
            this._texts = null;

            // Events
            window.addEventListener('beforeunload', this._onBeforeUnload.bind(this));
            window.addEventListener('error', this._onError.bind(this));
        }

        return biwi_app_App._singletonInstance;
    }

    // --------------------------------------------------------------
    // GETTERS / SETTERS
    // --------------------------------------------------------------

    get config() { return this._config; }

    get isDirty() {
        let isDirty = false;
        if (this._viewport) {
            kijs.Array.each(this._viewport.elements, function(element) {
                if (element instanceof biwi.app.MainPanel) {
                    isDirty = element.isDirty;
                }
            }, this);
        }
        return isDirty;
    }

    get languageId() { return this._config.guiLanguageId; }
    set languageId(val) { this._config.guiLanguageId = val; }

    get mainPanel() {
        let mainPanel;
        if (this._viewport) {
            kijs.Array.each(this._viewport.elements, function(element) {
                if (element instanceof kg.app.MainPanel) {
                    mainPanel = element;
                    return false;
                }
            }, this);
        }
        return mainPanel;
    }

    get rpc() { return this._rpc; }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    /**
     * Gibt einen Text in eingestellter Sprache zurück.
     * @param {String} key Der Text in Standardsprache
     * @param {String} variant
     * @param {Array} args
     * @param {String} languageId
     * @returns {String}
     */
    getText(key, variant='', args=null, languageId=null) {
        let text;
        if (languageId === null) {
            languageId = this.languageId;
        }

        // Text in objekt suchen
        if (kijs.isObject(this._texts)) {
            for (let tKey in this._texts) {
                if (tKey === key) {
                    if(kijs.isString(this._texts[key])) {
                        text = this._texts[key];

                    } else if (kijs.isObject(this._texts[key]) && kijs.isString(this._texts[key][variant])) {
                        text = this._texts[key][variant];
                    }
                    break;
                }
            }
        }

        // Wenn Text nicht gefunden, den Key nehmen
        if (text === undefined) {
            if (languageId !== 'de') {
                console.warn('Text "' + key + '" not found in translation "' + languageId + '"');
            }
            text = key;
        }

        // Argumente ersetzen
        if (args !== null) {
            args = kijs.isArray(args) ? args : [args];

            for (var i=args.length; i>0; i--) {
                text = kijs.String.replaceAll(text, '%' + i, args[i-1]);
            }
        }

        return text;
    }

    /**
     * Führt ein Log-Out durch und lädt die Seite neu.
     * @returns {undefined}
     */
    logout() {
        this._rpc.do('app.logout', null, function() {
           window.location.reload();
        });
    }

    /**
     * Startet die App. Hauptstartpunkt, wird von index aufgerufen.
     * @returns {undefined}
     */
    run() {


        // TODO
        this._config.isLoggedIn = true;
        this._config.isUser = true;
        this._config.isFachbereich = true;
        this._config.isAdmin = true;




        // ViewPort erstellen
        this._viewport = new kijs.gui.ViewPort({
            cls: 'kijs-flexcolumn'
        });
        this._viewport.render();

        // load translations
        this._rpc.do('app.getTexts', this.languageId, function(ret) {
            this._texts = ret.texts;

            // start app
//            if (!this._config.isLoggedIn) {
//
//                // Login anzeigen
//                let loginWin = new kg.app.LoginWindow({
//                    authToken: this._authToken
//                });
//                loginWin.on('login', this._onLogin, this);
//                loginWin.show();
//
//                // App anzeigen
//            } else {
//                this._startApp(false);
//            }
            this._startApp(false);
        }, this);

    }

    /**
     * Zeigt eine Meldung an, wenn die Seite verlassen wird, und noch ungespeicherte
     * Daten vorhanden sind.
     * @param {Object} e
     * @returns {Boolean}
     */
    _onBeforeUnload(e) {
        if (this.isDirty) {
             e.preventDefault();
             e.returnValue = '';
             return false;
        }
    }


    _onError(e) {
        const log = {};
        if (e.error instanceof Error) {
            const err = e.error;
            log.message = err.message || err.description || '';
            log.filename = err.fileName || err.filename || '';
            log.lineNumber = err.lineno || err.lineNumber || null;
            log.columnNumber = err.colno || err.columnNumber || null;
            log.stack = err.stack || '';

        } else {
            log.message = e.message || e.description || '';
            log.filename = e.filename || e.fileName || '';
            log.lineNumber = e.lineno || e.lineNumber || null;
            log.columnNumber = e.colno || e.columnNumber || null;
            log.stack = '';
        }

        // Auf dem Server ins Logfile schreiben
        if (this._rpc && this._rpc.do) {
            this._rpc.do('app.jsErrorLog', log);
        }
    }

    /**
     * Beim Login
     * @param {Object} e
     * @returns {undefined}
     */
    _onLogin(e) {
        e.element.destruct();
        this._config.isLoggedIn = true;
        this._config.isUser = e.response.isUser;
        this._config.isFachbereich = e.response.isFachbereich;
        this._config.isAdmin = e.response.isAdmin;

        if (e.response.guiLanguageId === this.languageId) {
            this._startApp(false);

        } else {

        // Sprache ist nicht mehr die selbe,  texte laden
        this.languageId = e.response.guiLanguageId;

        // load translations
        this._rpc.do('app.getTexts', this.languageId, function(ret) {
                this._texts = ret.texts;

                this._startApp(true);

            }, this);
        }
    }

    /**
     * Entfernt einen GET-Parameter aus der URL und gibt dessen Wert zurück
     * @param {String} parameterName
     * @returns {String}
     */
    _removeParameterFromUrl(parameterName) {
        try {
            let search = window.location.search, value=null;
            if (search) {
                if (search.substr(0,1) === '?') {
                    search = search.substr(1);
                }

                let parameters = search.split('&'), newParameters = [];
                kijs.Array.each(parameters, function(parameter) {
                    if (parameter.substr(0,parameterName.length+1) === parameterName + '=') {
                        value = parameter.substr(parameterName.length+1);
                    } else {
                        newParameters.push(parameter);
                    }
                }, this);

                search = newParameters.join('&');
                if (search) {
                    search = '?' + search;
                }

                let newUrl = window.location.origin;
                newUrl += window.location.pathname;
                newUrl += search;

                history.replaceState({}, document.title, newUrl);
                return value;
            }
        } catch(e) {
            return null;
        }
    }


    /**
     * Startet das Hauptpanel
     * @param {Boolean} showSplashscreen
     * @returns {undefined}
     */
    _startApp(showSplashscreen) {
        if (this._config.isLoggedIn && this._config.isUser) {
//            this._splashScreen(showSplashscreen, function() {
//                this._viewport.add(new biwi.app.MainPanel());
//            });

            this._viewport.add(new biwi.app.MainPanel());

        // Benutzer ist eingeloggt, hat aber keine Rechte auf das App.
        // An Suissetec Kontakt weiterleiten.
        } else {
            kijs.gui.MsgBox.error(
                this.getText('Fehler'),
                this.getText('Ihr Benutzer verfügt über keine Rechte zum Ausführen von kgweb. Bitte wenden Sie sich an suissetec.'),
                function() {
                    window.location.href = 'https://www.suissetec.ch/kontakt';
                }, this);
        }
    }

    /**
     * Zeigt den Splashscreen an und startet danach das callback.
     * @param {Boolean} show
     * @param {Function} callbackFn
     * @returns {undefined}
     */
    _splashScreen(show, callbackFn) {
        if (show) {
            let splash = new kijs.gui.Container({
                cls : 'splashscreen',
                html: '<img src="core/resources/img/splashscreen.svg" />'
            });
            this._viewport.add(splash);

            kijs.defer(function() {
                this._viewport.remove(splash);
                callbackFn.call(this);
            }, 3000, this);

        } else {
            callbackFn.call(this);
        }

    }

    // --------------------------------------------------------------
    // DESTRUCTOR
    // --------------------------------------------------------------
    destruct() {
        // RPC entladen
        this._rpc.destruct();

        // Variablen
        this._rpc = null;
    }

};
