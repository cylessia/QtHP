<?php

class QFormButton extends QAbstractElement {
    const
        Reset = 0,
        Submit = 1;
    
    private $_type = self::Submit,
            $_value = '';
    
    public function __construct($name = null) {
        parent::__construct();
        if($name)
            $this->setValue($name);
    }
    
    public function setButtonType($type){
        if($type != self::Reset && $type != self::Submit){
            throw new QFormButtonTypeException('Not a valid type');
        }
        $this->_type = $type;
        return $this;
    }
    
    public function setValue($value){
        if(!is_string($value)){
            throw new QFormButtonSignatureException('Call to undefined function QFormButton::setValue(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_value = $value;
        return $this;
    }
    
    public function value(){
        return $this->_value;
    }
    
    public function show(){
        echo '<input ' . $this->_styleOptionAttribute() . ' type="' . ($this->_type ? 'submit' : 'reset')  . '" ' . ($this->_value !== null ? 'value="' . $this->_value . '"' : '') . ' />';
    }
}

class QFormButtonException extends QAbstractElementException {}
class QFormButtonSignatureException extends QFormButtonException implements QSignatureException {}
class QFormButtonTypeException extends QFormButtonException {}

?>