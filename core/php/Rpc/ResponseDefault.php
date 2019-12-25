<?php
declare(strict_types = 1);

namespace biwi\edit\Rpc;

/**
 * Standard-RPC-Response für individuelle Rückgaben.
 * Es können dynamisch Attribute hinzugefügt werden, welche
 * ans Javascript übermittelt werden.
 */
final class ResponseDefault extends ResponseBase {
    private $parameters = [];

    // -------------------------------------------------------------------
    // Public Functions
    // -------------------------------------------------------------------

    /**
     * overwrite
     * @return object|null
     */
    public function jsonSerialize(): ?\stdClass {
        if (\count($this->parameters) === 0) {
            return null;
        }

        return (object) $this->parameters;
    }


    /**
     * Setzt einen Rückgabewert
     * @param string $name
     * @param mixed $value
     */
    public function setResponseParameter(string $name, $value): void {
        $this->parameters[$name] = $value;
    }

    // -----------------
    // getter/setter
    // -----------------

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void {
        $this->parameters[$name] = $value;
    }


    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name) {
        return $this->parameters[$name] ?? null;
    }


    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool {
        return isset($this->parameters[$name]);
    }


    /**
     * @param string $name
     */
    public function __unset(string $name): void {
        unset($this->parameters[$name]);
    }
}
