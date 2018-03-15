<?php

class QCheckBox extends QAbstractFormElement {

    private $_checked = false;
    
    public function isCheckable(){
        return $this->_disabled;
    }
    
    public function isChecked(){
        return $this->_checked;
    }
    
    public function setCheckable($checkable){
        $this->_disabled = !$checkable;
        return $this;
    }
    
    public function setChecked($checked){
        if(!is_bool($checked)){
            throw new QCheckBoxSignatureException('Call to undefined function QCheckBox::setChecked(' . implode(', ', array_map('gettype', func_get_args())) . ')');
        }
        $this->_checked = $checked;
        return $this;
    }
    
    public function setData($data){
        $this->setChecked($data === 'on');
        return $this;
    }
    
    public function show(){
        $this->_showTopLabel();
        echo '<input ' . $this->_styleOptionAttribute() . ' type="checkbox" name="' . $this->_parent->name() . '[' . $this->_name . ']" id="' . $this->_name . '" ' . ($this->_disabled ? 'disabled="disabled"' : '') . ' ' . ($this->_checked ? 'checked="checked"' : '') . ' />';
        $this->_showBottomLabel();
    }
    
    public function data(){
        return $this->_checked;
    }
}

class QCheckBoxException extends QAbstractFormElementException {}
class QCheckBoxSignatureException extends QCheckBoxException implements QSignatureException {}

?>