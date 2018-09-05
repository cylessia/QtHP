<?php

class QLineEdit extends QAbstractFormElement {
    
    protected $_confirmation = null,
              $_echoMode = self::Normal,
              $_value,
              $_minLength,
              $_maxLength,
              $_placeholder;
    
    const Normal = 0,
          Password = 1,
            
          ErrorConfirmation = 'confirmation',
          ErrorMaxLength = 'maxLength',
          ErrorMinLength = 'minLength';
    
    public function echoMode(){
        return $this->_echoMode;
    }
    
    public function isValid(){
        if($this->_minLength && $this->_minLength > strlen($this->_value)){
            $this->_errors[] = self::ErrorMinLength;
        }
        if($this->_maxLength && strlen($this->_value) > $this->_maxLength){
            $this->_errors[] = self::ErrorMaxLength;
        }
        if($this->_confirmation){
            if($this->_confirmation->_value != $this->_value){
                $this->_errors[] = self::ErrorConfirmation;
            }
        }
        return parent::isValid();
    }
    
    public function setConfirmation(QLineEdit $confirmation){
        $this->_confirmation = $confirmation;
        return $this;
    }
    
    public function setData($data){
        $this->setValue($data);
    }
    
    public function setEchoMode($echoMode){
        if($echoMode != self::Normal && $echoMode != self::Password){
            throw new QLineEditException('Unknown echo mode "' . $echoMode . '"');
        }
        $this->_echoMode = $echoMode;
        return $this;
    }
    
    public function setMaxLength($length){
        if(!is_int($length)){
            throw new QLineEditSignatureException('Call to undefined function QLineEdit::setMaxLength(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_maxLength = $length;
        return $this;
    }
    
    public function setMinLength($length){
        if(!is_int($length)){
            throw new QLineEditSignatureException('Call to undefined function QLineEdit::setMinLength(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_minLength = $length;
        return $this;
    }
    
    public function setPlaceholder($placeholder, $force = false){
        if(!is_string($placeholder) || !is_bool($force)){
            throw new QLineEditSignatureException('Call to undefined function QLineEdit::setPlaceholder(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_placeholder = $placeholder;
        if(!isset($this->_attributes['title']) || $force){
            $this->_attributes['title'] = $placeholder;
        }
        return $this;
    }
    
    public function setValue($value){
        if($value === null){
            $value = '';
        }
        if(!is_scalar($value)){
            throw new QLineEditSignatureException('Call to undefined function QLineEdt::setValue(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_value = $value;
        return $this;
    }
    
    public function value(){
        return $this->_value;
    }
    
    public function show(){
        $this->_showTopLabel();
        echo '<input ' . $this->_styleOptionAttribute() . ' type="' . ($this->_echoMode ? 'password' : 'text') . '"' . ($this->_required ? ' required="true"' : '') . ($this->_minLength ? ' minlength="' . $this->_minLength . '"' : '') . ($this->_maxLength ? ' maxlength="' . $this->_maxLength . '"' : '') . ' name="' . $this->_parent->name() . '[' . $this->_name . ']" id="' . $this->_name . '" ' . ($this->_disabled ? 'disabled="disabled"' : '') . ($this->_value ? ' value="' . $this->_value . '"' : '') . ($this->_placeholder ? ' placeholder="' . $this->_placeholder . '"' : '') . ' />';
        $this->_showBottomLabel();
    }
    
    public function data(){
        return $this->_value;
    }
}

class QLineEditException extends QAbstractFormElementException {}
class QLineEditSignatureException extends QLineEditException implements QSignatureException {}

?>