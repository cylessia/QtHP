<?php

class QDateInterval extends QAbstractObject {
    
    private $_year,
            $_month,
            $_day,
            $_invert;
    
    public function __construct(QDate $date1, QDate $date2){
        if(!($this->_invert = $date1->daysTo($date2) < 0)){
            $min = $date1->toArray();
            $max = $date2->toArray();
        } else {
            $min = $date2->toArray();
            $max = $date1->toArray();
        }
        $res = array();
        foreach(array(0, 1, 2) as $k){
            $res[$k] = $max[$k] - $min[$k];
        }
        if($res[2] < 0){
            // QDate start month at 1
            $res[2] = QDate::daysInMonth($res[0], $min[1]) + $res[2];
        }
        if($res[1] < 0){
            $res[1] += 12;
            --$res[0];
        }
        $this->_year = $res[0];
        $this->_month = $res[1];
        $this->_day = $res[2];
    }
    
    public function year(){
        return $this->_year;
    }
    
    public function month(){
        return $this->_month;
    }
    
    public function day(){
        return $this->_day;
    }
    
    public function toString($format){
        return strtr($format, array('+' => $this->_invert?'+':'-', '+' => $this->_invert ?'':'-','dd' => self::_toStringFormat($this->_day), 'mm' => self::_toStringFormat($this->_month), 'yyyy' => $this->_year));
    }
    
    private static function _toStringFormat($int){
        return $int < 10 ? '0'.$int : $int;
    }
}

?>