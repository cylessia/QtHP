<?php

class QDateTime extends QAbstractObject {
    const
        SECS_PER_DAY =           86400,
        SECS_PER_HOUR =           3600,
        SECS_PER_MIN =              60,
        MSECS_PER_DAY =       86400000,
        MSECS_PER_HOUR =       3600000,
        MSECS_PER_MIN =          60000,
        MAX_TIME =       2145916799000,
        JULIAN_DAY_FOR_EPOCH = 2440588/*-.5*/;

    const FormatTimestamp = 'yyyy-mm-dd hh:ii:ss',
          FormatDMY = 'dd/mm/yyyy hh:ii:ss',
          FormatMDY = 'mm/dd/yyyy hh:ii:ss',
          FormatYMD = 'yyyy/mm/dd hh:ii:ss';

    /*private static
        $_df_dmy_ns = 'ddmmyyyy',
        $_df_ymd_ns = 'yyyymmdd',

        $_df_dmy_sl = 'dd/mm/yyyy',
        $_df_mdy_sl = 'mm/dd/yyyy',
        $_df_ymd_sl = 'yyyy/mm/dd',

        $_df_dmy_do = 'dd.mm.yyyy',
        $_df_mdy_do = 'mm.dd.yyyy',
        $_df_ymd_do = 'yyyy.mm.dd',

        $_df_dmy_sp = 'dd mm yyyy',
        $_df_ymd_sp = 'yyyy mm dd',

        $_df_dmy_hy = 'dd-mm-yyyy',
        $_df_ymd_hy = 'yyyy-mm-dd',

        $_df_dmy_ss = 'dd/mm yyyy',
        $_df_dmy_hs = 'dd. mm. yyyy. ',
        $_df_ymd_hs = 'yyyy. mm. dd. ',
        $_df_mdy_sc = 'mm dd, yyyy'
    ;*/

    private $_t = 0;

    public function __construct($d, $t = null, $tz = null){
        if((dps_is_float($d) || ctype_digit($d)) && $d < self::MAX_TIME){
            $this->_t = $d;
            $tz = $t;
        } else if($d instanceof QDateTime){
            $this->_t = $d->_t;
            $tz = $t;
        } else if($d instanceof QDate && (is_string ($t) || $t instanceof QTimeZone) && $tz === null){
            $tz = $t;
            $t = new QTime;
        } else if($d instanceof QDate && $t instanceof QTime){
            $this->setDateTime($d, $t);
        } else {
            $fga = func_get_args();
            throw new QDateTimeException('Call to undefined function ' . __METHOD__ . '(' . implode(', ', array_map('dpsGetType', $fga)) . ')');
        }
        $this->_tz = $tz instanceof QTimeZone ? $tz : new QTimeZone($tz ? $tz : QTimeZone::timezone());
    }

    public function addMSecs($u){
        $this->_t += $u;
        return $this;
    }

    public function addSecs($s){
        return $this->addMSecs($s*1000);
    }

    public function addMinutes($i){
        return $this->addMSecs($i * self::MSECS_PER_MIN);
    }

    public function addHours($h){
        return $this->addMSecs($h * self::MSECS_PER_HOUR);
    }

    public function addDays($d){
        return $this->addMSecs($d * self::MSECS_PER_DAY);
    }

    public function addMonths($m){
        list($d, $t) = $this->toDateTime();
        $d->addMonths($d);
        return $this->setDateTime($d, $t);
    }

    public function addYears($y){
        list($d, $t) = $this->toDateTime();
        $d->addYears($y);
        return $this->setDateTime($d, $t);
    }

    public static function now($tz = null){
        if($tz === null){
            $tz = QTimeZone::timezone();
        }
        return new QDateTime(QDate::now(), $tz == QTimeZone::TzUtc ? QTime::utc() : QTime::now($tz), $tz);
    }

    public static function utc(){
        return self::now(QTimeZone::TzUtc);
    }

    public function toTimestamp(){
        list($d, $t) = $this->toUtc()->toDateTime();
        return $d->toTimestamp() + round($t->time()/1000);
    }

    public static function fromTimestamp($ts){
        return new self(((self::JULIAN_DAY_FOR_EPOCH+round($ts/self::SECS_PER_DAY)))*1000000);
    }

    public function toDateTime(){
        return array(
            new QDate(($days = floor($this->_t / self::MSECS_PER_DAY))+self::JULIAN_DAY_FOR_EPOCH),
            new QTime($this->_t - ($days * self::MSECS_PER_DAY))
        );
    }

    public function t(){
        return $this->_t;
    }

    public function msecsTo(QDateTime $dt){
        return $dt->toUtc()->_t - $this->toUtc()->_t;
    }

    public function secsTo(QDateTime $dt){
        return round(($dt->toUtc()->_t - $this->toUtc()->_t)/1000);
    }

    public function toUtc(){
        $dt = self::utc();
        $dt->_t = $this->_t - $this->_tz->utcOffset($this)*1000;
        return $dt;
    }

    public function date(){
        list($d, $t) = $this->toDateTime();
        return $d;
    }

    public function time(){
        list($d, $t) = $this->toDateTime();
        return $t;
    }

    public function utcOffset(){
        return $this->_tz->utcOffset($this);
    }

    public function setDateTime(QDate $d, QTime $t){
        $this->_t = ($d->toJulianDay() - self::JULIAN_DAY_FOR_EPOCH) * self::MSECS_PER_DAY + $t->time();
        return $this;
    }

    public static function fromFormat($str, $format, $tz = null){
        try {
            $d = QDate::fromFormat($str, $format);
            $t = QTime::fromFormat($str, $format);
            return new self($d, $t, $tz);
        } catch(QTimeInvalidFormatException $e){
            throw new QDateTimeFormatException($e->getMessage());
        } catch(QDateInvalidFormatException $e){
            throw new QDateTimeFormatException($e->getMessage());
        }
    }

    public function toString($format){
        list($d, $t) = $this->toDateTime();
        return $t->toString($d->toString($format));
    }
}

class QDateTimeException extends QAbstractObjectException {}
class QDateTimeFormatException extends QDateTimeException {}
?>