<?php
declare(strict_types = 1);

namespace Rpc;

/**
 * Die RpcResponse-Klasse dient zur Daten-Rückgabe vom Server an den Browser.
 */
abstract class ResponseBase implements \JsonSerializable {
    protected $jsonType = 'RpcResponse';

    protected $errorTitle = 'Fehler';
    protected $errorMsgs = [];
    protected $errorCancelCallback = true;

    protected $infoTitle = 'Info';
    protected $infoMsgs = [];

    protected $cornerTipTitle = 'Info';
    protected $cornerTipMsgs = [];

    protected $warningTitle = 'Warnung';
    protected $warningMsgs = [];

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Gibt die Nachrichten als stdClass zurück.
     * Funktion wird vom Router aufgerufen, um die Nachrichten
     * an den RPC zu übergeben.
     * @return \stdClass
     */
    public function getMessages(): \stdClass {
        $messages = new \stdClass();
        if ($this->cornerTipMsgs) {
            $messages->cornerTipMsg = $this->_getMessageArray($this->cornerTipMsgs, $this->cornerTipTitle);
        }

        if ($this->errorMsgs) {
            $messages->errorMsg = $this->_getMessageArray($this->errorMsgs, $this->errorTitle, $this->errorCancelCallback);
        }

        if ($this->infoMsgs) {
            $messages->infoMsg = $this->_getMessageArray($this->infoMsgs, $this->infoTitle);
        }

        if ($this->warningMsgs) {
            $messages->warningMsg = $this->_getMessageArray($this->warningMsgs, $this->warningTitle);
        }

        return $messages;
    }


    /**
     * Zeigt eine Meldung als Tiptext unten Links an.
     * @param string $message
     * @param string|null $title
     */
    public function showCornerTipMsg(string $message, ?string $title=null): void {
        $this->cornerTipMsgs[] = $message;
        if ($title) {
            $this->cornerTipTitle = $title;
        }
    }


    /**
     * Zeigt eine Fehlermeldung an. Die Callback-Fn wird nicht aufgerufen.
     * @param string $message
     * @param string|null $title
     * @param bool $cancelCallback false, falls die callback-Fn trotzdem aufgerufen werden soll.
     */
    public function showErrorMsg(string $message, ?string $title=null, bool $cancelCallback=true): void {
        $this->errorMsgs[] = $message;
        if ($title) {
            $this->errorTitle = $title;
        }
        $this->errorCancelCallback = $cancelCallback;
    }


    /**
     * Zeigt eine Info-Meldung mit einem 'ok' Button an
     * @param string $message
     * @param string|null $title
     */
    public function showInfoMsg(string $message, ?string $title=null): void {
        $this->infoMsgs[] = $message;
        if ($title) {
            $this->infoTitle = $title;
        }
    }


    /**
     * Zeigt eine Warnung mit einem 'ok' und einem 'Abbrechen' Button an.
     * @param string $message
     * @param string|null $title
     */
    public function showWarningMsg(string $message, ?string $title=null): void {
        $this->warningMsgs[] = $message;
        if ($title) {
            $this->warningTitle = $title;
        }
    }


    // -----------------
    // Implementierung
    // -----------------

    /**
     * Bereitet Argumente für die Rückgabe an die callback-Funktion auf.
     * Methode kann in abgeleiteter Klasse überschrieben werden, falls
     * Daten an die Callback-Funktion übergeben werden sollen.
     * @return null|object
     */
    public function jsonSerialize() {
        return null;
    }

    // -----------------
    // Implementierung
    // -----------------

    /**
     * Gibt das Nachrichtenarray zurück.
     * @param mixed $messages
     * @param string $title
     * @param ?boolean $cancelCb
     * @return array
     */
    private function _getMessageArray($messages, $title, $cancelCb = null): array {
        $return = [];
        if (\count($messages) === 1) {
            $return['msg'] = $messages[0];
            $return['title'] = $title;
        } else {
            $return['msg'] = $messages;
            $return['title'] = $title;
        }
        if ($cancelCb !== null) {
            $return['cancelCb'] = $cancelCb;
        }
        return $return;
    }
}
