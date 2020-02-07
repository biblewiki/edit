<?php
declare(strict_types = 1);

namespace biwi\edit\router;

use biwi\edit;

/**
 * Class Router
 *
 * @package ki\kgweb\ki\Router
 */
class Router {
    /**
     * @var ki\App
     */
    protected $app;

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Router constructor.
     *
     * @param ki\App $app
     */
    public function __construct(edit\App $app) {
        $this->app = $app;
    }


    /**
     * Entscheidet je nach Request welcher Router diesen beantwortet
     *
     * @param ki\RequestInterface $request
     * @param ki\ResponseInterface $response
     * @return Crontab|Html|Kijs|null
     * @throws \Throwable
     */
    public function handleRequest(edit\RequestInterface $request, edit\ResponseInterface $response) {
        $router = null;
        $cliArgs = getopt("cptw"); // Hier alls möglichen Argumente für CLI angeben
        if (!\is_array($cliArgs)) {
            $cliArgs = [];
        }

        if ($this->app->getConfig('rest, enable') && $request->hasGet('path') && strpos($request->getGet('path'), 'rest/') === 0) {

            // REST Webservice
//            $router = new Rest($this->app);

        } elseif ($request->hasHeader('X-Library') && $request->getHeader('X-Library') === 'kijs') {

            // kijs-Request
            $router = new Kijs($this->app);

        } elseif ($request->hasGet('excelexport')) {

            // Als Excel exportieren
            $router = new ExcelExport($this->app);

        } elseif ($request->hasGet('pdf')) {

            // PDF generieren und an Browser senden
            $router = new PdfBook($this->app, true);

        } elseif ($request->hasGet('imageRelais')) {

            // Bild über Relais anzeigen.
            $router = new ImageRelais($this->app, true);

        } elseif (\array_key_exists('p', $cliArgs)) {

            // PDF generieren von CLI
            $router = new PdfBook($this->app, false);

        } elseif ($request->hasHeader('X-Filename')) {

            // File-Upload
            $router = new Upload($this->app);

            /*


                    } elseif ($request->hasGet('image')) {

                        // Image
                        $router = new Image($this->app);

                    } elseif ($request->hasGet('download')) {

                        // Download
                        $router = new Download($this->app);

                    } elseif ($request->hasGet('reportId') || $request->hasGet('reportName')) {

                        // Report
                        $router = new Report($this->app);
*/
        } elseif ($request->hasGet('crontab') || \array_key_exists('c', $cliArgs)) {

            // Cron-Jobs
            $router = new Crontab($this->app);

        } elseif ($request->hasGet('webhook')) {

            // Webhooks
            $router = new Webhook($this->app);
/*
                    } elseif ($request->hasGet('plupload')) {

                        // Plupload
                        $router = new Plupload($this->app);
            */

        } elseif (\array_key_exists('t', $cliArgs)) {

            // Translate
            new Translate($this->app);

        } elseif (\array_key_exists('w', $cliArgs)) {

            // Webservice Test
            new CrbTest($this->app);

        } else {

            // Default: wenn keine gültigen Argumente übergeben wurden, die HTML-Seite zurückgeben
            $router = new Html($this->app);
        }

        $router->handleRequest($request, $response);

        return $router;
    }
}

