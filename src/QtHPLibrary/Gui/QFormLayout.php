<?php


class QFormLayout extends QAbstractFormLayout {

    private $_buttonClass,
            $_elementClass,
            $_errorClass,
           /**
            * @var QList<QFormButton>
            */
            $_buttons;

    public function __construct() {
        parent::__construct();
        $this->_buttons = new QList('QFormButton');
    }

    public function buttonClass(){
        return $this->_buttonClass;
    }

    public function elementClass(){
        return $this->_elementClass;
    }

    public function errorClass(){
        return $this->_errorClass;
    }

    public function setButtonClass($class){
        if(!is_string($class)){
            throw new QFormLayoutSignatureException('Call to undefined QFormLayout::setButtonClass(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_buttonClass = trim($class);
        return $this;
    }

    public function setElementClass($class){
        if(!is_string($class)){
            throw new QFormLayoutSignatureException('Call to undefined QFormLayout::setElementClass(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_elementClass = trim($class);
        return $this;
    }

    public function setErrorClass($class){
        if(!is_string($class)){
            throw new QFormLayoutSignatureException('Call to undefined QFormLayout::setErrorClass(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_errorClass = trim($class);
        return $this;
    }

    public function addButton(QFormButton $button){
        $this->_buttons->append($button);
    }

    public function buttons(){
        return $this->_buttons;
    }

//    public function setResetButtonValue($value){
//        if(!is_scalar($value)){
//            throw new QFormLayoutException('A button value must be scalar');
//        }
//        $this->_resetValue = $value;
//        return $this;
//    }
//
//    public function setResetButtonClass($class){
//        if(!is_string($class)){
//            throw new QFormLayoutSignatureException('Call to undefined function QFormLayout::setResetButtonClass(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
//        }
//        $this->_resetClass = $class;
//        return $this;
//    }
//
//    public function setSubmitButtonValue($value){
//        if(!is_scalar($value)){
//            throw new QFormLayoutException('A button value must be scalar');
//        }
//        $this->_submitValue = $value;
//        return $this;
//    }
//
//    public function setSubmitButtonClass($class){
//        if(!is_string($class)){
//            throw new QFormLayoutSignatureException('Call to undefined function QFormLayout::setSubmitButtonClass(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
//        }
//        $this->_submitClass = $class;
//        return $this;
//    }

    public function show(){
        $eCls = $this->_elementClass ? ' class="' . $this->_elementClass . '"' : '';
        $eeCls = ($eeCls = trim($this->_errorClass . ' ' . $this->_elementClass) != '') ? ' class="' . $eeCls . '"' : '';
        $bCls = $this->_buttonClass ? ' class="' . $this->_buttonClass . '"' : '';

        echo '<form ' . $this->_styleOptionAttribute() . ($this->_enctype !== null ? ($this->_enctype == self::Multipart ? ' enctype="multipart/form-data"' : ($this->_enctype == self::PlainText ? ' enctype="text/plain"' : ' enctype="application/x-www-form-urlencoded"')) : '') . ' action="' . $this->_action . '" method="' . ($this->_method ? 'post' : 'get') . '">';
        echo '<input type="hidden" name="' . $this->_name . '[qthp_token]" value="' . $this->_token . '" />';
        foreach($this->_elements as $element){
            if(count($errors = $element->errors())){
                echo '<div' . $eeCls . '>';
                echo '<ul>';
                foreach($errors as $error){
                    if(isset($this->_defaultMessages[$element->name()][$error])){
                        echo '<li>' . $this->_defaultMessages[$element->name()][$error] . '</li>';
                    }
                }
                echo '</ul>';
            } else {
                echo '<div' . $eCls . '>';
            }
            $element->show();
            echo '</div>';
        }
        foreach($this->_buttons as $button){
            echo '<div' . $bCls . '>';
            $button->show();
            echo '</div>';
        }
//        echo '<input type="submit" ' . ($this->_submitValue?'value="' . $this->_submitValue . '"':'') . ($this->_submitClass ? ' class="' . $this->_submitClass . '"' : '') . '  />';
//        echo $this->_resetValue ? ' <input type="reset" value="' . $this->_resetValue . '"' . ($this->_resetClass ? ' class="' . $this->_resetClass . '"' : '') . ' />':'';
        echo '</form>';
    }
}

class QFormLayoutException extends QAbstractElementException {}
class QFormLayoutSignatureException extends QFormLayoutException implements QSignatureException {}

?>