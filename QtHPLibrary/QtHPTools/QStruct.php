<?php

class QStruct extends QAbstractObject {
    
    /**
     * QStruct object doesn't have to be initialized
     */
    public function __construct($struct = null){
        if($struct && ($struct instanceof stdClass || is_array($struct))){
            foreach($struct as $k => $v){
                $this->__set($k, $v);
            }
        } else if($struct !== null) {
            throw new QStructInitException('"' . qGetType($struct) . '" is not a valid structure');
        }
    }
    
    /**
     * Returns the value of the property
     * @param string $name The name of the property
     */
    public function __get($name){
        if(!property_exists($this, $name))
            throw new QStructException('Call to an undefined member "' . $name . '"');
        if($name{0} == '_')
            throw new QStructException('"' . $name . '" is not a valid name');
        return method_exists($this, ($method = 'get'.  ucfirst($name))) ? $this->{$method}() : $this->{$name};
    }
    
    /**
     * Sets a value to an existing property
     * @param string $name The name of the property
     * @param mixed The value of the property
     */
    public function __set($name, $value){
        if(!property_exists($this, $name))
            throw new QStructException('Trying to set undefined member "' . $name . '"');
        if($name{0} == '_')
            throw new QStructException('"' . $name . '" is not a valid name');
        method_exists($this, ($method = 'set' . ucfirst($name))) ? $this->{$method}($value) : ($this->{$name} = $value);
    }
};

class QStructException extends QException{};
class QStructInitException extends QStructException{};
?>