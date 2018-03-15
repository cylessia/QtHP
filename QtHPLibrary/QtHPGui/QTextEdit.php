<?php

class QTextEdit extends QAbstractFormElement {
    protected $_value,
            $_cols,
            $_rows;
    
    public function data(){
        return $this->_value;
    }
    
    public function setData($data){
        $this->setValue($data);
    }
    
    public function setValue($value){
        if($value === null){
            $value = '';
        }
        if(!is_scalar($value)){
            throw new QTextEditValueException('A QTextEdit value must be scalar');
        }
        $this->_value = $value;
    }
    
    public function value(){
        return $this->_value;
    }
    
    public function setSize($cols, $rows = null){
        if($cols instanceof QSize){
            $this->_cols = $cols->width();
            $this->_rows = $cols->height();
        } else if(is_int($cols) && is_int($rows)){
            $this->_cols = $cols;
            $this->_rows = $rows;
        } else {
            throw new QTextEditSizeException('Call to undefined function QTextEdit::setSize(' . implode(', ', array_map('gettype', func_get_args())) . ')');
        }
        return $this;
    }
    
    public function show(){
        $this->_showTopLabel();
        echo '<textarea ' . $this->_styleOptionAttribute() . ' ' . ($this->_cols ? 'cols="' . $this->_cols . '" ' : '') . ' ' . ($this->_rows ? ' rows="' . $this->_rows . '" ' : '') . 'name="' . $this->_parent->name() . '[' . $this->_name . ']" id="' . $this->_name . '" ' . ($this->_disabled ? 'disabled="disabled"' : '') . '>' . ($this->_value ? $this->_value : '') . '</textarea>';
        $this->_showBottomLabel();
    }
}

class QTextEditException extends QAbstractFormElementException{}
class QTextEditValueException extends QTextEditException{}

?>