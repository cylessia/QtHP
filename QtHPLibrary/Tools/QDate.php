<?php

class QDate extends QAbstractObject {

    private $_jd,
            $_format = '';

    const

    FIRST_DAY = 1,
    FIRST_MONTH = 1,
    FIRST_YEAR = -4713,
    LAST_JULIAN_DAY = 2299150,
    FIRST_JULIAN_DAY_BC = 1721425,
    JULIAN_DAY_FOR_EPOCH = 2440587.5,

    FormatDMY = 'dd/mm/yyyy',
    FormatMDY = 'mm/dd/yyyy',
    FormatYmd = 'yyyy-mm-dd'
    ;

    private static

    $_now = null,
    $_holidays = array(
        array(1 => true, 121 => true, 128 => true, 195 => true, 227 => true, 305 => true, 315 => true, 359 => true),
        array(1 => true, 122 => true, 129 => true, 196 => true, 228 => true, 306 => true, 316 => true, 360 => true)
    );

    private static $_monthDays = array(1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    public function __construct($year = null, $month = null, $day = null){
        if($year instanceof QDate){
            $this->_jd = $year->_jd;
        } else if(dps_is_float($year) && $month === null && $day === null && $year > 0){
            $this->_jd = $year;
        }else if($year !== null && $month !== null && $day !== null){
            $this->setDate((int)$year, (int)$month, (int)$day);
        } else if(is_string($year) && is_string($month)){
            $this->_fromFormat($year, $month);
        } else if($year === null && $month === null && $day === null) {
            $this->_jd = null;
        } else if($year !== null) {
            $args = func_get_args();
            throw new QDateSignatureException('Call to undefined function DDate::__construct(' . implode(', ', array_map('dpsGetType', $args)) . ')');
        }
    }

    public function addDays($days){
        $this->_jd += $days;
        $oldYear = $this->year();
        $this->_dateFromJulianDay($this->_jd, $year, $month, $day);
        return $this->_fixedDate($year, $month, $day, $days > 0, $oldYear);
    }

    public function addWorkingDays($days){
        if($days > 1000){
            throw new QDateException('QDate::addWorkingDays(int) must be improved to calculate a business date longer than 1000 days');
        }
        $oldYear = $this->year();
        if($days > 0){
            for($i = $days; $i > 0; --$i){
                ++$this->_jd;
                if($this->isHoliday()){
                    ++$i;
                }
            }
        } else if($days < 0){
            for($i = abs($days); $i > 0; --$i){
                --$this->_jd;
                if($this->isHoliday()){
                    ++$i;
                }
            }
        }
        $this->_dateFromJulianDay($this->_jd, $year, $month, $day);
        return $this->_fixedDate($year, $month, $day, $days < 0, $oldYear);
    }

    public function isHoliday(){
        if(($dow = $this->dayOfWeek()) == 6 || $dow == 7){
            return true;
        }
        if(isset(self::$_holidays[$leap = $this->isLeapYear() ? 1 : 0][$doy = $this->dayOfYear()])){
            return true;
        }

        // Lundi de P?que
        if($doy == ($easterDoy = ($leap ? 82 : 81) + easter_days($this->year()))){
            return true;
        }

        // Jeudi de l'ascension / Lundi de Pentec?te
        if($doy == $easterDoy + 38 || $doy == $easterDoy + 49){
            return true;
        }
        return false;
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
        return $this->isNull() ? 0 : ($this->_jd >= 0 ? (($this->_jd % 7) + 1) : ((($this->_jd + 1) % 7) + 7));
    }

    public function dayOfYear(){
        return $this->_jd - $this->_julianDayFromDate($this->year(), 1, 1) + 1;
    }

    public function daysInMonth(){
        $this->_dateFromJulianDay($this->_jd, $year, $month);
        return $month == 2 && self::_isLeapYear($year) ? 29 : self::$_monthDays[$month];
    }

    public function daysInYear(){
        return self::_isLeapYear($this->year()) ? 366 : 365;
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

    public static function easterDate($year){
        return new self(easter_date($year));
    }

    public static function fromFormat($date, $format){
        $_date = new QDate;
        $_date->_fromFormat($date, $format);
        return $_date;
    }

    protected function _fromFormat($date, $format){
        if(!is_string($date) || ($year = strpos(($format = strtolower($format)), 'yyyy')) === false || ($month = strpos($format, 'mm')) === false || ($day = strpos($format, 'dd')) === false){
            throw new QDateInvalidFormatException('Invalid date format');
        }
        $this->setDate((int)substr($date, $year, 4), (int)substr($date, $month, 2), (int)substr($date, $day, 2));
    }

    private function isLeapYear(){
        return self::_isLeapYear($this->year());
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


    /**
     * Return an object using the current date
     * @return DDate
     */
    public static function now(){
        return self::$_now ? new self(self::$_now->_jd) : (self::$_now = new self(round(time() / 86400 + self::JULIAN_DAY_FOR_EPOCH)));
    }

    public function toTimestamp(){
        // -.5 to get midnight
        return (($this->_jd - self::JULIAN_DAY_FOR_EPOCH - 0.5) * 86400);
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

    public function toJulianDay(){
        return $this->_jd;
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
        return (int)gregoriantojd($month, $day, $year);
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

        $day = min(array($day, ($month == 2 && self::_isLeapYear($year) ? 29 : self::$_monthDays[$month])));
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
}

if(!function_exists('jdtogregorian')){
function jdtogregorian($julianDay) {
 /*
  * Math from The Calendar FAQ at http://www.tondering.dk/claus/cal/julperiod.php
  * This formula is correct for all julian days, when using mathematical integer
  * division (round to negative infinity), not c++11 integer division (round to zero)
  */
    $a = $julianDay + 32044;
    $b = floordiv(4 * $a + 3, 146097);
    $c = $a - floordiv(146097 * $b, 4);

    $d = floordiv(4 * $c + 3, 1461);
    $e = $c - floordiv(1461 * $d, 4);
    $m = floordiv(5 * $e + 2, 153);

    $day = $e - floordiv(153 * $m + 2, 5) + 1;
    $month = $m + 3 - 12 * floordiv($m, 10);
    $year = 100 * $b + $d - 4800 + floordiv($m, 10);

    // Adjust for no year 0
    if ($year <= 0)
        --$year ;
    return $month.'/'.$day.'/'.$year;
        }
    }

if(!function_exists('floordiv')){
function floordiv($a, $b){
    return floor(($a - ($a < 0 ? $b - 1 : 0)) / $b);
        }
    }

if(!function_exists('gregoriantojd')){
function gregoriantojd($month, $day, $year){
    // Adjust for no year 0
    if ($year < 0)
        ++$year;

/*
 * Math from The Calendar FAQ at http://www.tondering.dk/claus/cal/julperiod.php
 * This formula is correct for all julian days, when using mathematical integer
 * division (round to negative infinity), not c++11 integer division (round to zero)
 */
    $a = floordiv(14 - $month, 12);
    $y = (int)$year + 4800 - $a;
    $m = $month + 12 * $a - 3;
    return $day + floordiv(153 * $m + 2, 5) + 365 * $y + floordiv($y, 4) - floordiv($y, 100) + floordiv($y, 400) - 32045;
}
}

if(!function_exists('easter_date')){
function easter_date($year) {
    #First calculate the date of easter using Delambre's algorithm.
    $a = $year % 19;
    $b = floor($year / 100);
    $c = $year % 100;
    $d = floor($b / 4);
    $e = $b % 4;
    $f = floor(($b + 8) / 25);
    $g = floor(($b - $f + 1) / 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = floor($c / 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = floor(($a + 11 * $h + 22 * $l) / 451);
    $n = ($h + $l - 7 * $m + 114);
    $month = floor($n / 31);
    $day = $n % 31 + 1;
    #Return the difference between the JulianDayCount for easter and March 21'st
    #of the same year, in order to duplicate the functionality of the easter_days function
    return (int)gregoriantojd($month, $day, $year);
}
}

if(!function_exists('easter_days')){
function easter_days($year) {
    return (int)easter_date($year) - (int)gregoriantojd(3,21,$year);
}
}

class QDateException extends QAbstractObjectException {}
class QDateFormatException extends QDateException {}
class QDateSignatureException extends QDateException implements QSignatureException {}
class QDateInvalidFormatException extends QDateException {}

?>