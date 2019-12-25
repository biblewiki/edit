<?php
declare(strict_types = 1);

namespace biwi\edit;

use Sabre\HTTP;

/**
 * Class Request
 *
 * http://sabre.io/http/
 *
 * @package ki\kgweb\ki
 */
class Request extends HTTP\RequestDecorator implements RequestInterface {

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Browsersprache ermitteln
     * Sprachcodes sind definiert in ISO 639-1
     *
     * @param $allowedLanguages array mit den Erlaubten Sprachcodes
     * @param $defaultLanguage string Sprachcode der Standardsprache
     * @return string
     */
    public function getBrowserLanguage(array $allowedLanguages, string $defaultLanguage): string {
        $langVariable = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
        $strictMode = true;

        // wurden keine Information mitgeschickt: Standardsprache zurückgeben
        if (empty($langVariable)) {
            return $defaultLanguage;
        }

        // Den Header auftrennen
        $acceptedLanguages = preg_split("/,\s*/", $langVariable);

        // Die Standardwerte einstellen
        $currentLang = $defaultLanguage;
        $currentQ = 0;

        // Nun alle mitgegebenen Sprachen abarbeiten
        foreach ($acceptedLanguages as $acceptedLanguage) {

            // Alle Infos über diese Sprache rausholen
            $res = preg_match("/^([a-z]{1,8}(?:-[a-z]{1,8})*)(?:;\s*q=(0(?:\.\d{1,3})?|1(?:\.0{1,3})?))?$/i", $acceptedLanguage, $matches);

            // wenn die Syntax nicht gültig ist: ignorieren
            if (!$res) {
                continue;
            }

            // Sprachcode holen und dann sofort in die Einzelteile trennen
            $langCode = explode("-", $matches[1]);

            // Wenn eine Qualität mitgegeben wurde, diese nehmen. Sonst Qualität 1 nehmen.
            if (isset($matches[2])) {
                $langQuality = (float)$matches[2];
            } else {
                $langQuality = 1.0;
            }

            // Bis der Sprachcode leer ist...
            while (\count($langCode)) {

                // mal sehen, ob der Sprachcode angeboten wird
                if (\in_array(strtolower(implode("-", $langCode)), $allowedLanguages, true)) {

                    // Qualität anschauen
                    if ($langQuality > $currentQ) {

                        // diese Sprache verwenden
                        $currentLang = strtolower(implode("-", $langCode));
                        $currentQ = $langQuality;

                        // Hier die innere while-Schleife verlassen
                        break;
                    }
                } elseif (\in_array(strtolower($langCode[0]), $allowedLanguages, true)) {

                    // Qualität anschauen
                    if ($langQuality > $currentQ) {

                        // diese Sprache verwenden
                        $currentLang = strtolower($langCode[0]);
                        $currentQ = $langQuality;

                        // Hier die innere while-Schleife verlassen
                        break;
                    }
                }

                // Wenn wir im strengen Modus sind, die Sprache nicht versuchen zu minimalisieren
                if ($strictMode) {
                    break;
                }

                // den rechten Teil des Sprachcodes abschneiden
                array_pop($langCode);
            }
        }

        // die gefundene Sprache zurückgeben
        return $currentLang;
    }


    /**
     * @param string|null $name
     * @return array|string
     */
    public function getGet(string $name = null) {
        $get = $this->getQueryParameters();
        if ($name) {
            if (\array_key_exists($name, $get)) {
                return $get[$name];
            }

            return '';
        }

        return $get;
    }


    /**
     * @param string|null $name
     * @return array|string
     */
    public function getPost(string $name = null) {
        $post = $this->getPostData();
        if ($name) {
            if (\array_key_exists($name, $post)) {
                return $post[$name];
            }

            return '';
        }

        return $post;
    }


    /**
     * @param string|null $name
     * @return bool
     */
    public function hasGet(string $name = null): bool {
        $get = $this->getQueryParameters();
        if ($name) {
            return \array_key_exists($name, $get);
        }

        return (bool)\count($get);
    }


    /**
     * @param string|null $name
     * @return bool
     */
    public function hasPost(string $name = null): bool {
        $post = $this->getPostData();
        if ($name) {
            return \array_key_exists($name, $post);
        }

        return (bool)\count($post);
    }

}