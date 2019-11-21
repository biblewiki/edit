<?php
declare(strict_types = 1);

namespace ki\kgweb\ki;

/**
 * Class Session
 *
 * @package ki\kgweb\ki
 */
class Session {
    public $userId = 'guest';
    public $languageId = '';
    public $userFunctions = null;
    public $lieferantId = null;
    public $captcha = null;
    public $useCaptcha = false;
    public $loginId = null;
    public $pendingImports = null;

    //--------------------------------------------------------
    // Public Functions
    //--------------------------------------------------------
    public function clear(): void {
        $this->userId = 'guest';
        $this->languageId = '';
        $this->userFunctions = null;
        $this->lieferantId = null;
        $this->captcha = null;
        $this->useCaptcha = false;
        $this->loginId = null;
        $this->pendingImports = null;
    }
}
