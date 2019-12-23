<?php
declare(strict_types = 1);

namespace ki\kgweb\ki\Rpc;

/**
 * Klasse für ein RPC-Response an ein Formular
 */
final class ResponseForm extends ResponseBase {
    public $formItems = [];
    public $formData = [];
    public $fieldErrors = [];

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * Fügt ein Item zum Form hinzu
     * @param array|\stdClass $formItems Ein Item oder ein array von formItems anhängen
     */
    public function addFormItems($formItems): void {

        // array von formItems: alle formItems anhängen
        if (\is_array($formItems) && $this->isSequential($formItems)) {
            foreach ($formItems as $item) {
                $this->formItems[] = $item;
            }

        // Einzelnes Item: anfügen
        } else {
            $this->formItems[] = $formItems;
        }
    }


    /**
     * overwrite: Werte für callback-Funktion aufbereiten
     * @return \stdClass
     */
    public function jsonSerialize(): \stdClass {
        $cbData = new \stdClass();
        if ($this->formItems) {
            $cbData->form = $this->formItems;
        }
        if ($this->formData) {
            $cbData->formData = $this->formData;
        }
        if ($this->fieldErrors) {
            $cbData->fieldErrors = $this->fieldErrors;
        }
        return $cbData;
    }


    /**
     * Setzt das value eines Forms
     * @param array $values array mit key => value
     */
    public function setFormData(array $values): void {
        foreach ($values as $key => $value) {
            $this->formData[$key] = $value;
        }
    }


    /**
     * Setzt die Fehlermeldungen bei einem Form
     * @param array $errors
     */
    public function setFieldErrors(array $errors): void {
        foreach ($errors as $key => $error) {
            $this->fieldErrors[$key] = $error;
        }
    }


    // -------------------------------------------------------------------
    // Private Functions
    // -------------------------------------------------------------------

    /**
     * prüft ob ein array numerische Schlüssel hat.
     * @param array $arr
     * @return bool
     */
    private function isSequential(array $arr): bool {
        if (\count($arr) === 0) {
            return true;
        }
        return \array_keys($arr) === \range(0, \count($arr) - 1);
    }

}
