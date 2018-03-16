<?php
            /**
 * Description of DUuid
 *
 * @author zenko
             */
class QUuid extends QAbstractObject {

    const

    FormatCollapse = 0,
    FormatHyphen = 1,
    FormatBrace = 2;

    private $_uuid;

    public function __construct($uuid = null) {
        if(is_string($uuid)){
            if(!preg_match('/^\{?[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-z]{12}\}?/i', $uuid)){
                throw new QUuidException('"' . $uuid . '" is not valid');
            }
        } else if($uuid === null) {
            $this->_uuid = self::_generateUuid();
        } else {
            throw new QUuidSignatureException('Call to undefined function ' . __METHOD__ . '(' . implode(', ', array_map('dpsGetType', func_get_args())) . ')');
        }
    }

    public static function luid($format = self::FormatHyphen, $version = 1){
        switch($version){
            case 1:
                $t = new QDateTime(new QDate(1582, 10, 15), new QTime(0,0,0), QTimeZone::TzUtc);
                $timestamp = base_convert($t->msecsTo(QDateTime::now(QTimeZone::TzUtc)), 10, 16);
                return ($format & self::FormatBrace ? '{' : '')
                . substr($timestamp, 0, 8)
                . ($format & self::FormatHyphen ? '-' : '')
                . substr($timestamp, 8)
                . 1
                . ($format & self::FormatHyphen ? '-' : '')
                . QCryptographicHash::generatePseudoRandomBytes(3)
                . ($format & self::FormatBrace ? '}' : '');
                break;
            case 2:
                return ($format & self::FormatBrace ? '{' : '')
                . QCryptographicHash::generatePseudoRandomBytes(4)
                . ($format & self::FormatHyphen ? '-' : '')
                . QCryptographicHash::generatePseudoRandomBytes(1)
                . dechex(mt_rand(0, 0xf)) . 2
                . ($format & self::FormatHyphen ? '-' : '')
                . QCryptographicHash::generatePseudoRandomBytes(3)
                . ($format & self::FormatBrace ? '}' : '');
                break;
        }
    }

    public static function uuid($format, $version = 4){
        return self::_generateUuid($version, $format);
    }

    public function toString(){
        return $this->_uuid;
    }

    private static function _generateUuid($version = null, $format = self::FormatHyphen){
        if($version == null){
            $version = 4;
        }

        $sep = $format & self::FormatHyphen ? '-' : '';
        switch($version){
            case 4:
                return ($format & self::FormatBrace ? '{' : '')
                . QCryptographicHash::generatePseudoRandomBytes(4)
                . $sep . QCryptographicHash::generatePseudoRandomBytes(2)
                . $sep . '4' . dechex(mt_rand(0, 0xf)) . QCryptographicHash::generatePseudoRandomBytes(1)
                . $sep . mt_rand(8, 9) . dechex(mt_rand(0, 0xf)) . QCryptographicHash::generatePseudoRandomBytes(1)
                . $sep . QCryptographicHash::generatePseudoRandomBytes(6)
                . ($format & self::FormatBrace ? '}' : '');
                break;
            case 5:
            case 5:
            case 2:
            case 1:
                throw new QCryptographicHashException('Please use v4 of uuid');
                break;
            case 0:
                $t = new QDateTime(new QDate(1582, 10, 15), new QTime(0,0,0), QTimeZone::TzUtc);
                $msecs = $t->msecsTo(QDateTime::now(QTimeZone::TzUtc));
                return ($format & self::FormatBrace ? '{' : '')
                . QCryptographicHash::generatePseudoRandomBytes(4)
                . $sep . QCryptographicHash::generatePseudoRandomBytes(2)
                . $sep . '0' . dechex(mt_rand(0, 0xf)) . QCryptographicHash::generatePseudoRandomBytes(1)
                . $sep . 'a' . dechex(mt_rand(0, 0xf)) . QCryptographicHash::generatePseudoRandomBytes(1)
                . $sep . base_convert($msecs, 10, 16)
                . ($format & self::FormatBrace ? '}' : '');
                break;
        }
    }
}

class QUuidException extends QAbstractObjectException{}
class QUuidSignatureException extends QUuidException implements QSignatureException {}
