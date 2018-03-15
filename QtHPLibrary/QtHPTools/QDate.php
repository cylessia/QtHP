<?php

class QDate extends QAbstractObject {
    
    private $_jd,
            $_format = '';
    
    const FIRST_DAY = 1,
          FIRST_MONTH = 1,
          FIRST_YEAR = -4713,
          LAST_JULIAN_DAY = 2299150,
          FIRST_JULIAN_DAY_BC = 1721425;
    
    private static $_now = null,
            $_overloadedFunctions = array(
                'isLeapYear', 'daysInMonth', 'daysInYear'
            );
    
    private static $_monthDays = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    
    public function __construct($year = null, $month = null, $day = null){
        if($year instanceof QDate){
            $this->_jd = $year->_jd;
        } else if(is_int($year) && $month === null && $day === null && $year > 0){
            $this->_jd = $year;
        }else if($year !== null && $month !== null && $day !== null){
            $this->setDate((int)$year, (int)$month, (int)$day);
        } else if(is_string($year) && is_string($month)){
            $this->_fromFormat($year, $month);
        } else if($year === null && $month === null && $day === null) {
            $this->_jd = null;
        } else if($year !== null) {
            throw new QDateSignatureException('Call to undefined function QDate::__construct(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
    
    public function addDays($days){
        $this->_jd += $days;
    }
    
    public function addMonths($months){
        if(!$months){
            return $this;
        }
        
        $this->_dateFromJulianDay($this->_jd, $year, $month, $day);
        $oldYear = $year;
        if($months < 0){
            if($months < 12){
                $year += ceil($months / 12);
                $months %= 12;
            }
            $month += $months;
            if($month < 1){
                --$year;
                $month += 12;
            }
        } else {
            if($months > 12){
                $year += floor($months / 12);
                $months %= 12;
            }
            $month += $months;
            if($month > 12){
                ++$year;
                $month -= 12;
            }
        }
        
        return $this->_fixedDate($year, $month, $day, $months > 0, $oldYear);
    }
    
    public function addYears($years){
        if(!$years)
            return $this;
        $this->_dateFromJulianDay($this->_jd, $year, $month, $day);
        $oldYear = $year;
        $year += $years;
        
        return $this->_fixedDate($year, $month, $day, $years > 0, $oldYear);
    }
    
    public function day(){
        $this->_dateFromJulianDay($this->_jd, $year, $month, $day);
        return $day;
    }
    
    public function dayOfWeek(){
        return ($this->_jd <= self::FIRST_JULIAN_DAY_BC ? (($this->_jd-2)%7) : ($this->_jd <= self::LAST_JULIAN_DAY?(($this->_jd+3)%7):($this->_jd%7)))+1;
    }
    
    public function dayOfYear(){
        return $this->_jd - $this->_julianDayFromDate($this->year(), 1, 1) + 1;
    }
    
    private function daysInMonth(){
        $this->_dateFromJulianDay($this->_jd, $year, $month);
        return $month == 2 && self::_isLeapYear($year) ? 29 : self::$_monthDays[$month];
    }
    
    private static function daysInMonthStatic($year, $month){
        return $month == 2 && self::_isLeapYear($year) ? 29 : self::$_monthDays[$month];
    }
    
    public function daysInYear(){
        return self::_isLeapYear($this->year());
    }
    
    public static function daysInYearStatic($year){
        return self::_isLeapYear($year) ? 366 : 365;
    }
    
    public function daysTo($year, $month = null, $day = null){
        if($year instanceof QDate){
            return $year->_jd - $this->_jd;
        } else {
            if(!self::_isValid($year, $month, $day)){
                throw new QDateException('Invalid date');
            }
            return $this->_julianDayFromDate($year, $month, $day) - $this->_jd;
        }
    }
    
    public static function fromFormat($date, $format){
        $_date = new QDate;
        $_date->_fromFormat($date, $format);
        return $_date;
    }
    
    protected function _fromFormat($date, $format){
        if(($year = strpos(($format = strtolower($format)), 'yyyy')) === false || ($month = strpos($format, 'mm')) === false || ($day = strpos($format, 'dd')) === false){
            throw new QDateInvalidFormatException('Invalid date format');
        }
        $this->setDate((int)substr($date, $year, 4), (int)substr($date, $month, 2), (int)substr($date, $day, 2));
    }

    private function isLeapYear(){
        return self::_isLeapYear($this->year());
    }
    
    private static function isLeapYearStatic($year){
        return self::_isLeapYear($year);
    }
    
    public function isNull(){
        return $this->_jd === null;
    }
    
    public static function isValid($year = null, $month = null, $day = null){
        return self::_isValid($year, $month, $day);
    }
    
    public function month(){
        $this->_dateFromJulianDay($this->_jd, $year, $month);
        return $month;
    }
    
    public static function now(){
        return self::$_now ? self::$_now : (self::$_now = new self(date('Y'), date('m'), date('d')));
    }
    
    public function setDate($year, $month, $day){
        if($year && $month && $day){
            if(!self::_isValid($year, $month, $day)){
                throw new QDateException('Invalid date');
            }
            $this->_jd = $this->_julianDayFromDate($year, $month, $day);
        }
        return $this;
    }
    
    public function setFormat($format){
        if(!is_string($format) || strpos(($format = strtolower($format)), 'yyyy') === false || strpos($format, 'mm') === false || strpos($format, 'dd') === false){
            throw new QDateInvalidFormatException('Invalid format');
        }
        $this->_format = $format;
    }
    
    public function toString($format = null){
        if($format == null)
            $format = $this->_format;
        if($format == null)
            throw new QDateInvalidFormatException('"' . $format . '" is not a valid date format');
        if($this->_jd !== null){
            $this->_dateFromJulianDay($this->_jd, $year, $month, $day);
            return strtr($format, array('dd' => self::_toStringFormat($day), 'mm' => self::_toStringFormat($month), 'yyyy' => $year));
        } else {
            return null;
        }
    }
    
    public function toArray(){
        $this->_dateFromJulianDay($this->_jd, $year, $month, $day);
        return array($year, $month, $day);
    }
    
    public function year(){
        $this->_dateFromJulianDay($this->_jd, $year);
        return $year;
    }
    
    protected static function _isValid($year, $month, $day){
        return !(
            $year < self::FIRST_YEAR
            || ($year == self::FIRST_YEAR && ($month < self::FIRST_MONTH || ($month == self::FIRST_MONTH && $day < self::FIRST_DAY)))
            || $year == 0
            || ($year == 1582 && $month == 10 && $day > 4 && $day < 15)
            || !(
                ($day > 0 && $month > 0 && $month <= 12)
                && ($day <= self::$_monthDays[$month] || $day == 29 && $month == 2 && self::_isLeapYear($year))
            )
        );
    }
    
    private function _julianDayFromDate($year, $month, $day){
        return gregoriantojd($month, $day, $year);
    }
    
    private function _dateFromJulianDay($jd, &$year, &$month = null, &$day = null){
        if($this->_jd !== null){
            $date = explode('/', jdtogregorian($jd));
            $year = $date[2];
            $month = $date[0];
            $day = $date[1];
        }
    }
    
    private function _fixedDate($year, $month, $day, $increasing, $oldYear){
      
        // Did we change the sign ?
        if(($oldYear < 0 && $year >= 0) || ($oldYear > 0 && $year <= 0)){
            $year += $increasing ? 1 : -1;
        }
        
        // Did we end up in the Julian/Gregorian hole ?
        if($year == 1582 && $month == 10 && $day > 4 && $day < 15){
            $day = $increasing ? 15 : 4;
        }
        
        $day = min(array($day, $this->daysInMonth()));
        return $this->setDate($year, $month, $day);
    }
    
    private static function _toStringFormat($int){
        return $int < 10 ? '0'.$int : $int;
    }
    
    private static function _isLeapYear($year){
        if($year < 1582){
            if($year < 1) // No year 0 in julian calendar
                ++$year;
            return $year % 4 == 0;
        } else {
            return ($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0;
        }
    }
    
    public function __call($name, $arguments) {
        if(in_array($name, self::$_overloadedFunctions)){
            return call_user_func_array(array($this, $name), $arguments);
        } else {
            throw new QDateSignatureException('Call to undefined function QDate::' . $name . '(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
    
    public static function __callStatic($name, $arguments) {
        if(in_array($name, self::$_overloadedFunctions)){
            return call_user_func_array(array('QDate', $name . 'Static'), $arguments);
        } else {
            throw new QDateSignatureException('Call to undefined function QDate::' . $name . '(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
}

class QDateException extends QAbstractObjectException {}
class QDateFormatException extends QDateException {}
class QDateSignatureException extends QDateException implements QSignatureException {}
class QDateInvalidFormatException extends QDateException {}

?>