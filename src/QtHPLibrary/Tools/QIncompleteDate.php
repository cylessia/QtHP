<?php

class QIncompleteDate extends QAbstractObject {
    
    private $_year = null,
            $_month = null,
            $_day = null;
    
    public function __construct($year = null, $month = null, $day = null){
        if(is_string($month)){
            $this->_fromFormat($year, $month);
        } else {
            $this->setDate($year, $month, $day);
        }
    }
    
    public function day(){
        return $this->_day;
    }
    
    public function isComplete(){
        return $this->_year !== null && $this->_month !== null && $this->_day !== null;
    }
    
    public function isNull(){
        return $this->_year === null;
    }

    public function isValid(){
        return QDate::isValid($this->_year ?: 1, $this->_month ?: 1, $this->_day ?: 1);
    }
    
    public function month(){
        return $this->_month;
    }
    
    public function toDate(){
        if($this->isValid()){
            return new QDate($this->_year ?: date('Y'), $this->_month ?: date('m'), $this->_day ?: date('d'));
        }
    }
    
    public static function fromFormat($date, $format){
        $d = new self;
        return $d->_fromFormat($date, $format);
    }
    
    private function _fromFormat($date, $format){
        if(preg_match('/^[\d]{4}$/', $date)){
            $this->setDate($date);
        } else {
            if(($year = strpos(($format = strtolower($format)), 'yyyy')) === false || ($month = strpos($format, 'mm')) === false || ($day = strpos($format, 'dd')) === false){
                throw new QIncompleteDateFormatException('"' . $format . '" is not a valid date format');
            }
            $this->setDate(($year = substr($date, $year, 4)) !== false ? (int)$year : null, ($month = substr($date, $month, 2)) !== false ? (int)$month : null, ($day = substr($date, $day, 2)) !== false ? (int)$day : null);
        }
        return $this;
    }
    
    public function setDate($year, $month = null, $day = null){
        if(!is_int($year) && !(is_int($month) || $month == null) && !(is_int($day) || $day == null)){
            throw new QIncompleteDateSignatureException('Call to undefined function QIncompleteDate::setDate(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_year = $year;
        if($month){
            $this->_month = $month;
            if($day){
                $this->_day = $day;
            }
        } else if($day){
            throw new QIncompleteDateException('$month must be set to set $day');
        }
    }
    
    public function toString($format, $separator= null){
        if($separator === null){
            if($this->_day && ($pos = strpos($format, 'dd')) !== false){
                $format = substr_replace($format, self::_toStringFormat($this->_day), $pos, 2);
            }
            if($this->_month && ($pos = strpos($format, 'mm')) !== false){
                $format = substr_replace($format, self::_toStringFormat($this->_month), $pos, 2);
            }
            $format = substr_replace($format, $this->_year, strpos($format, 'yyyy'), strlen($this->_year));
            return $format;
        } else {
            $array = array();
            if(strlen($format) == 8){
                $yearPos = strpos($format, 'yyyy');
                $monthPos = strpos($format, 'mm');
                $dayPos = strpos($format, 'dd');
                // A partir de la, on récup le premier
                // Ensuite le second puis le dernier
                if($this->_day){
                    if($dayPos < $monthPos && $dayPos < $yearPos){
                        $array[] = self::_toStringFormat($this->_day);
                        if($monthPos < $yearPos){
                            $array[] = self::_toStringFormat($this->_month);
                            $array[] = $this->_year;
                        } else {
                            $array[] = $this->_year;
                            $array[] = self::_toStringFormat($this->_month);
                        }
                    } else if($monthPos < $dayPos && $monthPos < $yearPos) {
                        $array[] = self::_toStringFormat($this->_month);
                        if($dayPos < $yearPos){
                            $array[] = self::_toStringFormat($this->_day);
                            $array[] = $this->_year;
                        } else {
                            $array[] = $this->_year;
                            $array[] = self::_toStringFormat($this->_day);
                        }
                    } else {
                        $array[] = $this->_year;
                        if($dayPos < $monthPos){
                            $array[] = self::_toStringFormat($this->_day);
                            $array[] = self::_toStringFormat($this->_month);
                        } else {
                            $array[] = self::_toStringFormat($this->_month);
                            $array[] = self::_toStringFormat($this->_day);
                        }
                    }
                } else if($this->_month){
                    if($monthPos < $yearPos){
                        $array[] = self::_toStringFormat($this->_month);
                        $array[] = $this->_year;
                    } else {
                        $array[] = $this->_year;
                        $array[] = self::_toStringFormat($this->_month);
                    }
                } else {
                    return $this->_year;
                }
                return implode($separator, $array);
            } else {
                throw new QIncompleteDateFormatException('Not a valid format. Format must be complete date format without separator');
            }
        }
    }
    
    public function year(){
        return $this->_year;
    }
    
    private static function _toStringFormat($int){
        return $int < 10 ? '0'.$int : $int;
    }
}

class QIncompleteDateException extends Exception {}
class QIncompleteDateSignatureException extends Exception implements QSignatureException {}
class QIncompleteDateFormatException extends QIncompleteDateException {}

?>