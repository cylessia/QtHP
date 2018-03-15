<?php

class QDateEdit extends QLineEdit {
    
    private $_format = '';
    
    public function displayFormat(){
        return $this->_format;
    }
    
    public function setDisplayFormat($format){
        if(!is_string($format) || strpos($format, 'yyyy') === false || strpos($format, 'mm') === false || strpos($format, 'dd') === false){
            throw new QDateEditException('Invalid format type');
        }
        $this->_format = $format;
        return $this;
    }
    
    public function setValue($value){
        if($value instanceof QDate){
            $this->_value = $value;
        } else {
            try {
                $this->_value = QDate::fromFormat($value, $this->_format);
            } catch(QDateFormatException $e){
                throw new QDateEditFormatException('Invalid format');
            } catch(QDateException $e){
                $this->_value = null;
            }
        }
        return $this;
    }
    
    public function show(){
        $this->_showTopLabel();
        echo '<input ' . $this->_styleOptionAttribute() . ' type="' . ($this->_echoMode ? 'password' : 'date') . '"' . ($this->_required ? ' required="true"' : '') . ($this->_minLength ? ' minlength="' . $this->_minLength . '"' : '') . ($this->_maxLength ? ' maxlength="' . $this->_maxLength . '"' : '') . ' name="' . $this->_parent->name() . '[' . $this->_name . ']" id="' . $this->_name . '" ' . ($this->_disabled ? 'disabled="disabled"' : '') . ' ' . ($this->_value ? 'value="' . $this->_value->toString($this->_format) . '"' : '') . ($this->_placeholder ? ' placeholder="' . $this->_placeholder . '"' : '') . ' />';
        $this->_showBottomLabel();
    }
}

class QDateEditException extends QLineEditException {}
class QDateEditFormatException extends QDateEditException {}
?>