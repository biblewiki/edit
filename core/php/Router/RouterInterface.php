<?php
declare(strict_types = 1);

namespace biwi\edit\Router;

use biwi\edit;

/**
 * Interface RouterInterface
 *
 * @package ki\kgweb\ki\Router
 */
interface RouterInterface {
    public function handleRequest(edit\RequestInterface $request, edit\ResponseInterface $response): void;
}
