<?php
declare(strict_types = 1);

namespace biwi\edit\router;

use biwi\edit;

/**
 * Class Html
 *
 * @package ki\kgweb\ki\Router
 */
class Html implements RouterInterface {
    /**
     * @var ki\App
     */
    protected $app;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Html constructor.
     *
     * @param ki\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }


    /**
     * @param ki\RequestInterface $request
     * @param ki\ResponseInterface $response
     */
    public function handleRequest(edit\RequestInterface $request, edit\ResponseInterface $response): void {
        try {

            if (!$this->app->isLoggedIn()) {

                // Login bei interCMS
                //$login = new ki\SsoLogin($this->app);
                //$login->login($request, $response);
            }

            // Start App
            $response->setBody($this->generateAppHtml());

        } catch (\Throwable $e) {

            // Rollback
            $this->app->getDb()->rollBackIfTransaction();

            // Fehler zurückgeben
            $msg = $this->app->getExceptionHandler()->handleException($e);
            $s = '<html><head><meta charset="utf-8"><title></title></head>';
            $s .= '<body>' . $msg . '</body></html>';
            $response->addHeader('Content-Type', 'text/html; charset=utf-8');
            $response->setBody($s);
        }
    }

    // -------------------------------------------------------------------
    // Protected Functions
    // -------------------------------------------------------------------

    /**
     * @return bool|mixed|string
     */
    protected function generateAppHtml() {
        $useMinify = $this->app->getConfig('develop, use_minify');
        $debug_mode = $this->app->getConfig('develop, debug_mode');

        // URL zusammensetzen
        $url = $this->app->getUrl();

        // Template holen und aufbereiten
        $html = file_get_contents('core/ressources/templ/guiMain.html');
        $html = str_replace("#URL#", $url, $html);
        $html = str_replace("#KIJS#", '<script type="text/javascript" src="core/lib/kijs/tools/' . ($debug_mode ? 'kijs-debug.js' : 'kijs-min.js') . '"></script>', $html);
        $html = str_replace("#CSS#", $this->getCssFiles($useMinify), $html);
        $html = str_replace("#JS#", $this->getJsFiles($useMinify), $html);
        $html = str_replace("#CONFIG#", $this->getGuiData(), $html);

        return $html;
    }


    /**
     * @return string
     */
    protected function getGuiData() {
        $data = new \stdClass();

        // Relativer Pfad für AJAX-Requests
        $data->ajaxUrl = 'index.php';

        // Module
        $data->moduleNames = [];
        foreach ($this->app->getModules()->getArray() as $mod) {
            if (!$mod->isMainModule()) {
//                $rightsFunctions = $mod->getRightsFunctions();
//                if (!$rightsFunctions || $this->app->checkRights($rightsFunctions)) {
                    $data->moduleNames[] = $mod->getClassName();
//                }
            }
        }
        unset($mod);

        // Host URL zusammensetzen (für Link in Toolbar zu Hauptseite)
        $url = \array_key_exists('HTTPS', $_SERVER) ? 'https://' : 'http://';
        $url .= $_SERVER['HTTP_HOST'];
        $data->hostUrl = $url;

        // Timeout für Ajax
        $data->ajaxTimeout = (int)$this->app->getConfig('settings, ajaxTimeout');

        // Fehler Details
        $data->showExceptionDetails = (bool)$this->app->getConfig('exceptionHandling, showDetails');

        // App-Version
        $data->version = $this->app->getVersion();

        // Organisationsname
        $data->organisation = $this->app->getConfig('app, organisation');

        // isLoggedIn
        $data->isLoggedIn = $this->app->isLoggedIn();

        // !!! Die folgenden Angaben müssen auch bei Login zurückgegeben werden !!!

        // userId
        $data->loggedInUserId = $this->app->getLoggedInUserId();

        // Rechte
        $data->loggedInUserType = $this->app->getLoggedInUserType();

        // LieferantId
        //$data->loggedInLieferantId = $this->app->getLoggedInLieferantId();

        // Sprachen
        //$userCnf = kg\user\User::getUserConfig($this->app);
        //$data->guiLanguageId = empty($userCnf['languageId']) ? $this->app->getLanguageId() : $userCnf['languageId'];

        return json_encode($data);
    }


    protected function getCssFiles($useMinify): string  {
        $files = [];

        if ($useMinify) {
            $files[] = 'core/ressources/css/min.css';
        } else {

            // Zuerst die Standard-Dateien nehmen
            $standardFiles = $this->app->getConfig('module, DefaultCssFiles');
            if (\is_array($standardFiles)) {
                foreach ($standardFiles as $file) {
                    $files[] = 'core/ressources/css/' . $file;
                }
                unset ($file);
            }
            unset($file, $standardFiles);

            // Dann die Dateien der Module
            foreach ($this->app->getModules()->getArray() as $mod) {
                $files = array_merge($files, $mod->getCssFiles());
            }
            unset($mod);
        }

        // Sicherstellen, dass keine Datei zweimal geladen wird.
        $files = array_unique($files);

        // Nun die Link-Tags der CSS-Dateien erstellen.
        $ret = '';
        foreach ($files as $file) {
            if ($ret) {
                $ret .= "\n    ";
            }
            $ret .= '<link rel="stylesheet" href="' . $file . '" />';
        }
        unset ($file);

        return $ret;
    }


    /**
     * @param $useMinify
     * @return string
     */
    protected function getJsFiles($useMinify): string  {
        $files = [];

        if ($useMinify) {
            $files[] = 'core/js/min.js';
        } else {

            // Standard-Dateien
            $standardFiles = $this->app->getConfig('module, DefaultJsFiles');
            if (\is_array($standardFiles)) {
                foreach ($standardFiles as $file) {
                    $files[] = 'core/js/' . $file;
                }
                unset ($standardFiles, $file);
            }

            // Dateien der Module
            foreach ($this->app->getModules()->getArray() as $mod) {
                if (!$mod->isMainModule()) {
                    $files = array_merge($files, $mod->getJsFiles());
                }
            }
            unset($mod);

            // Dateien des Hauptmoduls erst am Schluss nehmen
            foreach ($this->app->getModules()->getArray() as $mod) {
                if ($mod->isMainModule()) {
                    $files = array_merge($files, $mod->getJsFiles());
                }
            }
            unset($mod);
        }

        // Sicherstellen, dass keine Datei zweimal geladen wird.
        $files = array_unique($files);

        // Script-Tags erstellen
        $ret = '';
        foreach ($files as $file) {
            if ($ret) {
                $ret .= "\n    ";
            }
            $ret .= '<script type="text/javascript" src="' . $file . '" charset="utf-8"></script>';
        }
        unset ($file);

        return $ret;
    }

}
