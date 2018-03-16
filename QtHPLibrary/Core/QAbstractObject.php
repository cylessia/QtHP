<?php

/**
 * Base of all QtHP classes
 */
abstract class QAbstractObject extends QtHP {

    private
        /**
         * @access private
         * @var string The name of the object
         */
        $_objectName = '';

    /**
     * Initializes the object
     */
    public function __construct(){}

    /**
     * Uninitializes the object
     */
    public function __destruct(){}

    /**
     * Returns the type of the class
     * @return string The type of the class
     */
    public function type(){
        return get_class($this);
    }

    /**
     * Returns the name of the object
     * @return string The name of the object
     */
    public function objectName(){
        return $this->_objectName;
    }

    /**
     * Sets the name of the object
     * @throws QAbstractObjectException
     */
    public function setObjectName($objectName){
        if(!is_string($objectName) && !is_numeric($objectName)){
            throw new QAbstractObjectSignatureException('Not a valid object name');
        }
        $this->_objectName = $objectName;
        return $this;
    }

    /**
     * Shows information about the object
     */
    public function dumpInfo(){
        var_dump($this);
        return $this;
    }
};

class QAbstractObjectException extends QException {}
class QAbstractObjectSignatureException extends QAbstractObjectException implements QSignatureException {}
?>