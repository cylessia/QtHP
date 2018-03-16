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
            $this->{$name} = $value;
        }
    }

    /******************************
     * ArrayAccess implementation *
     ******************************/
    public function offsetExists($offset) {
        return true;
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