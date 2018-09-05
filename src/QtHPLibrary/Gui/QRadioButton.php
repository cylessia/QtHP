<?php

class QRadioButton extends QAbstractFormElement {
    
    protected $_buttons,
              $_value,
              $_orientation = QtHP::Horizontal,
              $_buttonLabelPosition = QtHP::Right;
    
    public function addButton($value, $label){
        if(!is_string($value) || !is_string($label)){
            throw new QRadioButtonSignatureException('Call to undefined QRadioButton::addButton(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_buttons[$value] = $label;
        return $this;
    }
    
    public function data(){
        return $this->_value;
    }

    public function setButtons($buttons){
        if(!is_array($buttons)){
            throw new QRadioButtonSignatureException('Call to undefined QRadioButton::addButton(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_buttons = $buttons;
        return $this;
    }
    
    public function setData($data) {
        $this->_value = $data;
    }
    
    public function setOrientation($orientation){
        if($orientation != QtHP::Horizontal && $orientation != QtHP::Vertical){
            throw new QRadioButtonOrientationException('Not a valid orientation');
        }
        $this->_orientation = $orientation;
        return $this;
    }
    
    public function setButtonLabelPosition($pos){
        if($pos < QtHP::Top || $pos > QtHP::Left){
            throw new QRadioButtonLabelPositionException('Not a valid position');
        }
        $this->_buttonLabelPosition = $pos;
        return $this;
    }
    
    public function setValue($value){
        $this->_value = $value;
    }
    
    public function show(){
        $labelSeparator = ($this->_buttonLabelPosition == QtHP::Top || $this->_buttonLabelPosition == QtHP::Bottom)
            ? '<br />'
            : '';
        $this->_showTopLabel();
        if($this->_buttonLabelPosition == QtHP::Top || $this->_buttonLabelPosition == QtHP::Left){
            foreach($this->_buttons as $value => $label){
                echo '<label for="' . $this->_name . '[' . $value . ']">' . $label . '</label>'
                    . $labelSeparator . '<input type="radio" name="' . $this->_parent->name() . '[' . $this->_name . ']" value="' . $value . '" id="' . $this->_name . '[' . $value . ']" ' . ($this->_value == $value ? ' checked="checked"' : '') . ($this->_disabled ? ' disabled="disabled"' : '') . ($this->_required ? ' required="true"' : '') . ' />';
            }
        } else {
            foreach($this->_buttons as $value => $label){
                echo '<input type="radio" name="' . $this->_parent->name() . '[' . $this->_name . ']" value="' . $value . '" id="' . $this->_name . '[' . $value . ']" ' . ($this->_value == $value ? ' checked="checked"' : '') . ($this->_disabled ? ' disabled="disabled"' : '') . ($this->_required ? ' required="true"' : '') . ' />'
                        . $labelSeparator . '<label for="' . $this->_name . '[' . $value . ']">' . $label . '</label>';
            }
        }
        $this->_showBottomLabel();
    }
    
    public function value(){
        return $this->_value;
    }
}

class QRadioButtonException extends QAbstractFormElementException {}
class QRadioButtonSignatureException extends QRadioButtonException implements QSignatureException {}
class QRadioButtonOrientationException extends QRadioButtonException {}
class QRadioButtonLabelPositionException extends QRadioButtonException {}

?>