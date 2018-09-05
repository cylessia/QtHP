<?php

class QRecursiveObject implements ArrayAccess {

    public function __construct(){}

    public function __get($name){
        if(!property_exists($this, $name)){
            $this->{$name} = new self;
        }
        return $this->{$name};
    }

    public function __set($name, $value){
        if($value === null){
            unset($this->{$name});
        } else {
            if(
                (is_array($value) || $value instanceof stdClass)
                && !(current(array_unique(array_map('qGetType', array_keys($value)))) == 'integer')
            ){
                $this->{$name} = new self;
                foreach($value as $k => $v){
                    if(!($k && !(ctype_digit($k{0}) || is_int($k)))){
                        throw new QRecursiveObjectKeyException('Key must be a string');
                    }
                    $this->{$name}->__set($k, $v);
                }
            } else {
                $this->{$name} = $value;
            }
        }
    }

    public function __isset($name){
        return property_exists($this, $name);
    }

    /******************************
     * ArrayAccess implementation *
     ******************************/
    public function offsetExists($offset) {
        return $this->__isset($offset);;
    }

    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset) {
        unset($this->{$offset});
    }
}

class QRecursiveObjectException extends QException {}
class QRecursiveObjectKeyException extends QRecursiveObjectException {}