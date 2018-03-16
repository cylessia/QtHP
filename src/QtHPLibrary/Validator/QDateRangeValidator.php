<?php

class QDateRangeValidator extends QValidator {
    protected $_bottom,
              $_top;
    
    public function __construct(QDate $bottom = null, QDate $top = null){
        $this->_bottom = $bottom?$bottom:new QDate;
        $this->_top = $top?$top:new QDate(date('Y'), date('m'), date('d'));
    }
    
    public function bottom(){
        return $this->_bottom;
    }
    
    public function setBottom(QDate $bottom){
        $this->_bottom = $bottom;
        return $this;
    }
    
    public function setTop(QDate $top){
        $this->_top = $top;
        return $this;
    }
    
    public function top(){
        return $this->_top;
    }
    
    public function validate($value){
        if($value !== null){
            if(!$value instanceof QDate)
                return false;
            try {
                if($value->daysTo($this->_bottom) < 0){
                    return false;
                }
            } catch(QDateException $e){}
            try {
                if($value->daysTo($this->_top) > 0){
                    return false;
                }
            } catch(QDateException $e){}
        }
        return true;
    }
    
    public static function isValid(QDate $value, QDate $bottom, QDate $top = null){
        try {
            if($value->daysTo($bottom) < 0){
                return false;
            }
        } catch(QDateException $e){}
        try {
            if($value->daysTo($top) > 0){
                return false;
            }
        } catch(QDateException $e){}
        return true;
    }
}

class QDateValidatorException extends QException{}

?>