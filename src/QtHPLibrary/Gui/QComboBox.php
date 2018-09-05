<?php

class QComboBox extends QAbstractFormElement {

    private
            /**
             * @var QMap
             */
            $_map,
            $_value = null;

    public function __construct(){
        parent::__construct();
        $this->_map = new QMap;
    }

    public function addItem($key, $value){
        $this->_map->insert($key, $value);
        return $this;
    }

    public function addItems($items){
        $this->_map->insert($items);
        return $this;
    }

    public function currentItem(){
        try {
            return $this->_map->value($this->_value);
        } catch(QMapException $e){
            return null;
        }
    }

    public function currentKey(){
        return $this->_value;
    }

    public function data(){
        return $this->_value !== null ? $this->_map[$this->_value] : null;
    }

    public function items(){
        return $this->_map;
    }

    public function setData($value){
        return $this->setValue($value);
    }

    public function setValue($value){
        if(isset($this->_map[$value])){
            $this->_value = $value;
        } else if($this->_map->contains($value)) {
            $this->_value = $this->_map->key($value);
        } else if($value != null) {
            throw new QComboBoxValueException('"' . $value . '" is not an index');
        }
        return $this;
    }

    public function value(){
        return $this->_value;
    }

    public function show(){
        $this->_showTopLabel();
        echo '<select ' . $this->_styleOptionAttribute() . ' name="' . ($this->_parent ? $this->_parent->name() . '[' . $this->_name . ']' : $this->_name) . '"' . ($this->_disabled ? 'disabled="disabled"' : '') . '>';
        foreach($this->_map as $k => $v){
            echo '<option value="' . $k . '" ' . ($k == $this->_value ? 'selected="selected"' : '') . '>' . $v . '</option>';
        }
        echo '</select>';
        $this->_showBottomLabel();
    }
}

class QComboBoxException extends QAbstractFormElementException {}
class QComboBoxSignatureException extends QComboBoxException implements QSignatureException {}
class QComboBoxValueException extends QComboBoxException {}

?>