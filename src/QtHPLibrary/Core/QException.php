<?php

interface QSignatureException {}

/**
 * Base of all exceptions
 */
class QException extends Exception {

    /**
     * @param string $message The message exception
     */
    public function __construct($message) {
        parent::__construct($message);
    }

    /**
     * Returns the type of the object
     * @return string The type of the exception
     */
    public function type(){
        return get_class($this);
    }

    /**
     * Shows information about the object
     */
    public function dumpInfo(){
        var_dump($this);
        return $this;
    }
}
?>