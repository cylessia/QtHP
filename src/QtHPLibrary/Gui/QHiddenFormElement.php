<?php

class QHiddenFormElement extends QAbstractFormElement {
    
    private $_value;
    
    public function __construct($name = null, $value = null){
        parent::__construct();
        if($name != null && $value != null){
            $this->_name = $name;
            $this->_value = $value;
        } else if(!($name == null && $value == null)){
            throw new QHiddenFormElementSignatureException('Call to undefined function QHiddenFormElement::__construct(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
    
    public function setData($data){
        $this->setValue($data);
    }
    
    public function setValue($value){
        if(!is_scalar($value)){
            throw new QHiddenFormElementSignatureException('Call to undefined function QHiddenFormElement::setValue(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_value = $value;
        return $this;
    }
    
    public function show(){
        echo '<input type="hidden" name="' . $this->_parent->name() . '[' . $this->_name . ']" id="' . $this->_name . '" ' . ($this->_disabled ? 'disabled="disabled"' : '') . ' ' . ($this->_value ? 'value="' . $this->_value . '"' : '') . ' />';
    }
    
    public function value(){
        return $this->_value;
    }
    
    public function data(){
        return $this->_value;
    }
}

class QHiddenFormElementException extends QAbstractFormElementException {}
class QHiddenFormElementSignatureException extends QHiddenFormElementException implements QSignatureException {}

?>