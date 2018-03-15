<?php

interface QSignatureException {}

class QException extends Exception {
    
    /**
     * @param string $message The message exception
     */
    public function __construct($message) {
        parent::__construct($message);
    }
    
    /**
     * Returns the typeof the Object
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