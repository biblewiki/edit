<?php
declare(strict_types = 1);

namespace biwi\edit;

use Sabre\HTTP;

/**
 * Interface ResponseInterface
 *
 * @package ki\kgweb\ki
 */
interface ResponseInterface extends HTTP\ResponseInterface {

    /**
     * Removes all HTTP headers.
     *
     * This method returns true
     *
     * @return bool
     */
    public function clearHeaders(): bool;

}