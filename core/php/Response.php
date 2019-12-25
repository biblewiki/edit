<?php
declare(strict_types = 1);

namespace biwi\edit;

use Sabre\HTTP;

/**
 * Class Response
 *
 * http://sabre.io/http/
 *
 * @package ki\kgweb\ki
 */
class Response extends HTTP\ResponseDecorator implements ResponseInterface {


    /**
     * Removes all HTTP headers.
     *
     * This method returns true
     *
     * @return bool
     */
    public function clearHeaders(): bool {
        $headers = $this->getHeaders();

        if ($headers) {
            foreach ($headers as $header) {
                $this->removeHeader($header);
            }
            unset($header);
        }

        return true;
    }

}