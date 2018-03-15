<?php

class QCryptographicHash extends QAbstractObject {
    
    const Crypt = 1,
          Blowfish = 2,
          Sha256 = 3,
          Sha512 = 4,
          StandardDES = 5,
          ExtendedDES = 6;
    
    public static function hash($value, $algorithm){
        switch($algorithm){
            case self::Crypt:
                return crypt($value);
                break;
            case self::Blowfish:
            case self::Sha256:
            case self::Sha512:
            case self::StandardDES:
            case self::ExtendedDES:
                return crypt($value, self::generateSalt($algorithm));
                break;
        }
    }
    
    public static function checkHash($value, $hashed, $algorithm){
        switch ($algorithm){
            case self::Crypt:
            case self::Blowfish:
            case self::Sha256:
            case self::Sha512:
            case self::StandardDES:
            case self::ExtendedDES:
                return crypt($value, $hashed) == $hashed;
                break;
        }
    }
    
    public static function generateToken($length = 32){
        if(!is_int($length)){
            throw new QCryptographicsHashSignatureException('Call to undefined function QCryptographicsHash::generateToken(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return strtr(substr(base64_encode(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)), 0, $length), '+', '.');
//        $charList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
//        $i = -1;
//        $token = '';
//        while(++$i < $length){
//            $token .= $charList{mt_rand(0, 61)};
//        }
//        return $token;
    }
    
    public static function generateSalt($algorithm = null){
        switch ($algorithm){
            case self::Blowfish:
                return '$2y$13$' . self::generateToken(22);
                break;
            case self::Sha256:
                return '$5$rounds=400000$' . self::generateToken(16);
                break;
            case self::Sha512:
                return '$6$rounds=490000$' . self::generateToken(16);
                break;
            case self::StandardDES:
                return self::generateToken(2);
                break;
            case self::ExtendedDES:
                return '_' . self::generateToken(8);
                break;
            default:
                if($algorithm !== null){
                    if(!is_int($algorithm)){
                        throw new QCryptographicsHashSignatureException('Call to undefined function QCryptographicsHash::generateSalt(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
                    } else {
                        throw new QCryptographicsHashSaltException('Not a valid salt algorithm');
                    }
                } else {
                    return self::generateToken();
                }
        }
    }
}

class QCryptographicsHashException extends QAbstractObjectException {}
class QCryptographicsHashSignatureException extends QCryptographicsHashException implements QSignatureException {}
class QCryptographicsHashSaltException extends QCryptographicsHashException {}
?>