<?php

abstract class QAbstractFormElement extends QAbstractElement {
    
    protected $_disabled = false,
              $_required = false,
              $_name = '',
              $_validators,
              $_errors,
              $_label = '',
              $_labelPosition = QtHP::Left,
              $_form;
    
    const ErrorRequire = 'require';
    
    abstract public function data();
    abstract public function setData($data);
    
    public function __construct() {
        parent::__construct();
        $this->_validators = new QMap;
    }

    public function addValidator($name, $validator){
        if(!is_string($name) || !$validator instanceof QValidator){
            throw new QAbstractFormElementSignatureException('Call to undefined function QAbstractFormElement::addValidator(' . implode(', ', array_map('gettype', func_get_args())) . ')');
        }
        $this->_validators->insert($name, $validator);
        return $this;
    }
    
    public function errors(){
        return $this->_errors;
    }
    
    public function hasError(){
        return count($this->_errors) != 0;
    }
    
    public function isDisabled(){
        return $this->_disabled;
    }
    
    public function isEnabled(){
        return !$this->_disabled;
    }
    
    public function isRequired(){
        return $this->_required;
    }
    
    public function isValid(){
        if($this->_required && $this->data() == null){
            $this->_errors[] = self::ErrorRequire;
        }
        foreach($this->_validators as $name => $validator){
            if(!$validator->validate($this->data())){
                $this->_errors[] = $name;
            }
        }
        return count($this->_errors) == 0;
    }
    
    public function label(){
        return $this->_label;
    }
    
    public function labelPosition(){
        return $this->_labelPosition;
    }
    
    public function name(){
        return $this->_name;
    }
    
    public function setDisabled($disabled){
        if(!is_bool($disabled)){
            throw new QAbstractFormElementSignatureException('Call to undefined function QAbstractFormElement::setDisabled(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_disabled = $disabled;
        return $this;
    }
    
    public function setEnabled($enabled){
        if(!is_bool($enabled)){
            throw new QAbstractFormElementSignatureException('Call to undefined function QAbstractFormElement::setEnabled(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_disabled = !$enabled;
        return $this;
    }
    
    public function setForm(QAbstractFormLayout $form){
        $this->_form = $form;
        if(!$this->_parent)
            $this->setParent($form);
        return $this;
    }
    
    public function setLabel($label){
        if(!is_scalar($label)){
            throw new QAbstractFormElementSignatureException('Call to undefined function QAbstractFormElemnt::setLabel(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_label = $label;
        return $this;
    }
    
    public function setLabelPosition($position){
        if($position < QtHP::Top || $position > QtHP::Left){
            throw new QAbstractFormElementException('Unknown position');
        }
        $this->_labelPosition = $position;
        return $this;
    }
    
    public function setName($name){
        if(!is_scalar($name)){
            throw new QAbstractFormElementSignatureException('Call to undefined function QAbstractFormElemnt::setName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_name = $name;
        return $this;
    }
    
    public function setRequired($required){
        if(!is_bool($required)){
            throw new QAbstractFormElementSignatureException('Call to undefined function QAbstractFormElement::setRequired(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_required = $required;
        return $this;
    }
    
    public function validator($name){
        if(!is_string($name)){
            throw new QAbstractFormElementSignatureException('Call to undefined function QAbstractFormElement::validator(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return $this->_validators->value($name);
    }
    
    public function validators(){
        return $this->_validators;
    }
    
    protected function _showBottomLabel(){
        if($this->_label != '' && ($this->_labelPosition == QtHP::Bottom || $this->_labelPosition == QtHP::Right)){
            echo ($this->_labelPosition == QtHP::Bottom ? '<br />' : '') . '<label for="' . $this->_name . '">' . $this->_label . '</label>';
        }
    }
    
    protected function _showTopLabel(){
        if($this->_label != '' && ($this->_labelPosition == QtHP::Top || $this->_labelPosition == QtHP::Left)){
            echo '<label for="' . $this->_name . '">' . $this->_label . '</label>' . ($this->_labelPosition == QtHP::Top ? '<br />' : '');
        }
    }
}

class QAbstractFormElementException extends QAbstractElementException {}
class QAbstractFormElementSignatureException extends QAbstractFormElementException implements QSignatureException {}

?>