<?php

// In format, "u" is used for "milliseconds" instead of "m" to prevent mixing up with "minutes"
// Is it a good or a bad idea ?!
class QTime extends QAbstractObject {
    const
        SECS_PER_DAY =           86400,
        SECS_PER_HOUR =           3600,
        SECS_PER_MIN =              60,
        MSECS_PER_DAY =       86400000,
        MSECS_PER_HOUR =       3600000,
        MSECS_PER_MIN =          60000,
        MAX_TIME =       2145916799000,
        JULIAN_DAY_FOR_EPOCH = 2440587.5;

    private $_ms = 0,
            $_format = '';

    public function __construct($h = null, $m = null, $s = null, $ms = null){
        if($h instanceof QTime){
            $this->_ms = $h->_ms;
        } else if($h instanceof QDateTime){
            // Waiting for PHP 5.6 (Oh god... -_- )
            //$this->_ms = $h->toDateTime()[1]->time();
            $_ = $h->toDateTime();
            $this->_ms = $_[1]->_ms;
        } else if(is_numeric($h) && $m === null && $s === null && $ms === null){
            $this->addMSecs($h);
        } else if($h !== null && $m !== null && $s !== null){
            $this->setHMS((string)$h, (string)$m, (string)$s, (string)$ms);
        } else if(is_string($h) && is_string($m)){
            $this->_fromFormat($h, $m);
        } else if (!($h === null && $m === null && $s === null && $ms === null)){
            $fga = func_get_args();
            throw new QTimeSignatureException('Call to undefined function ' . __CLASS__ . '::' . __METHOD__ . '(' . implode(', ', array_map('dpsGetType', $fga)) . ')');
        }
    }

    public function addMSecs($ms){
        if(abs($this->_ms += (float)$ms) > self::MSECS_PER_DAY || $this->_ms < 0){
            throw new QTimeRangeException('"' . $this->_ms . '" is out of time range');
        }
        return $this;
    }

    public function addSecs($s){
        return $this->addMSecs($s*1000);
    }

    public function hour(){
        return floor($this->_ms / self::MSECS_PER_HOUR);
    }

    public function minutes(){
        return floor(($this->_ms % self::MSECS_PER_HOUR) / self::MSECS_PER_MIN);
    }

    public function seconds(){
        return floor($this->_ms / 1000) % self::SECS_PER_MIN;
    }

    public function msecs(){
        return $this->_ms % 1000;
    }

    public function msecsTo($t){
        $t = $t instanceof QTime ? $t : new QTime($t);
        return $t->_ms - $this->_ms;
    }

    public static function now($tz = null){
        return new self(self::utc()->time() + QTimeZone::getUtcOffset(QDateTime::utc(), $tz)*1000);
    }

    public static function utc(){
        return new self(round((microtime(true) - QDate::now()->toTimestamp())*1000));
    }

    public function secsTo($t){
        $t = $t instanceof QTime ? $t : new QTime($t);
        return ($t->_ms / 1000) - ($this->_ms / 1000);
    }

    public function setFormat($format){
        if(($hour = strpos(($format = strtolower($format)), 'hh')) === false || ($min = strpos($format, 'ii')) === false || ($secs = strpos($format, 'ss')) === false || ($ms = strpos($format, 'uuu')) === false){
            throw new QTimeInvalidFormatException('Invalid time format');
        }
        $this->_format = $format;
    }

    public function setHMS($h, $m, $s, $ms){
        if(!$this->_isValid($h, $m, $s, $ms)){
            throw new QTimeInvalidTimeException('"' . $h . ':' . $m . ':' . $s . ':' . $ms . '" is not a valid time');
        }
        return $this->addMSecs($h*self::MSECS_PER_HOUR + $m*self::MSECS_PER_MIN + $s*1000 + $ms);
    }

    public function time(){
        return $this->_ms;
    }

    public function isValid($h, $m, $s, $ms){
        return $this->_isValid($h, $m, $s, $ms);
    }

    public function isNull(){
        return $this->_ms == 0;
    }

    public static function fromFormat($time, $format){
        $t = new QTime;
        $t->_fromFormat($time, $format);
        return $t;
    }
    public function toString($format){
        if($format == null)
            $format = $this->_format;
        if($format == null)
            throw new QTimeInvalidFormatException('"' . $format . '" is not a valid time format');
        return strtr($format, array('hh' => self::_toStringFormat2($this->hour()), 'ii' => self::_toStringFormat2($this->minutes()), 'ss' => self::_toStringFormat2($this->seconds()), 'uuu' => self::_toStringFormat3($this->msecs())));
    }

    private function _isValid($h, $m, $s, $u){
        // $u is just for beautiful alignment
        return (($h !== null && ctype_digit((string)$h) && $h >= 0 && $h < 0x018) || $h == null)
            && (($m !== null && ctype_digit((string)$m) && $m >= 0 && $m < 0x03C) || $m == null)
            && (($s !== null && ctype_digit((string)$s) && $s >= 0 && $s < 0x03C) || $s == null)
            && (($u !== null && ctype_digit((string)$u) && $u >= 0 && $u < 0x3E8) || $u == null)
        ;
    }

    private function _toStringFormat2($int){
        return $int < 10 ? '0'.$int:$int;
    }

    private function _toStringFormat3($int){
        return $int<10 ? '00'.$int:($int<100?'0'.$int:$int);
    }

    private function _fromFormat($time, $format){
        $t = Qtime::now();
        $hour = ($p = strpos(($format = strtolower($format)), 'hh')) !== false ? (int)substr($time, $p, 2) : $t->hour();
        $min = ($p = strpos($format, 'ii')) !== false ? (int)substr($time, $p, 2) : $t->minutes();
        $sec = ($p = strpos($format, 'ss')) !== false ? (int)substr($time, $p, 2) : $t->seconds();
        $ms = ($p = strpos($format, 'uuu')) !== false ? (int)substr($time, $p, 2) : $t->msecs();
        if(!is_string($time)){
            throw new QTimeInvalidFormatException('Invalid time format');
        }
        $this->setHMS($hour, $min, $sec, $ms);
    }
}

class QTimeException extends QAbstractObjectException {}
class QTimeSignatureException extends QTimeException {}
class QTimeRangeException extends QTimeException {}
class QTimeInvalidFormatException extends QTimeException {}
class QTimeInvalidTimeException extends QTimeException {}