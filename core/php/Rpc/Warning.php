<?php
declare(strict_types = 1);

namespace biwi\edit\Rpc;

use biwi\edit;

/**
 * Die Warning zeigt auf dem UI eine Ok-Abbrechen-Meldung an.
 * Die Warnung darf nur geworfen werden, wenn ignoreWarnings im App
 * auf 'false' steht, ansonsten wird eine Exception geworfen.
 */
class Warning extends edit\ExceptionNotice {
    protected $title = '';

    /**
     * Erstellt eine Warnung mit einem 'ok' und 'abbrechen' Button
     * @param string $message
     * @param string $title
     */
    public function __construct(string $message, string $title='Warnung') {
        $this->title = $title;
        parent::__construct($message);
    }


    /**
     * Gibt den Titel der Warnung zurück
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }
}
