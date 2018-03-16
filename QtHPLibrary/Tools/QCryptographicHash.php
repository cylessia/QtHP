<?php

class QCryptographicHash extends QAbstractObject {

    const Crypt = 1,
          Blowfish = 2,
          Sha256 = 3,
          Sha512 = 4,
          StandardDES = 5,
          ExtendedDES = 6,
          Md5 = 7,
          Sha1 = 8;

    public static function hash($value, $algorithm){
        switch($algorithm){
            case self::Md5:
                return md5($value);
                break;
            case self::Sha1:
                return sha1($value);
                break;
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
            case self::Md5:
                return md5($value) == $hashed;
                break;
            case self::Sha1:
                return sha1($value) == $hashed;
                break;
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
            throw new QCryptographicHashSignatureException('Call to undefined function QCryptographicHash::generateToken(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return function_exists('mcrypt_create_iv') ? strtr(substr(base64_encode(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)), 0, $length), '+/=', '.-_') : self::_generatePoorToken($length);
    }

    public static function generatePseudoRandomString($length, $prefix = ''){
        return $prefix . self::_generatePoorToken($length);
    }

    public static function generatePseudoRandomBytes($length){
        if($length < 1){
            throw new QCryptographicHashException('Cannot create a string with a size lesser than 1');
        }
        return bin2hex(function_exists('mcrypt_create_iv') ? mcrypt_create_iv($length, MCRYPT_DEV_URANDOM) : self::_generatePoorBytes($length));
    }

    public static function _generatePoorBytes($length){
        $i = $length;
        $bytes = '';
        while($i > 2){
            $i-=2;
            $bytes .= pack('n', mt_rand(0, 0xffff));
        }
        while($i-- > 0){
            $bytes .= pack('c', mt_rand(0, 0xff));
        }
        return $bytes;
    }

    private static function _generatePoorToken($length){
        $charList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $i = -1;
        $token = '';
        while(++$i < $length){
            $token .= $charList{mt_rand(0, 61)};
        }
        return $token;
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
            case self::Sha1:
            case self::Md5:
                return self::generateToken(8);
                break;
            default:
                if($algorithm !== null){
                    if(!is_int($algorithm)){
                        throw new QCryptographicHashSignatureException('Call to undefined function QCryptographicHash::generateSalt(' . implode(', ', array_map('dpsGetType', func_get_args())) . ')');
                    } else {
                        throw new QCryptographicHashSaltException('Not a valid salt algorithm');
                    }
                } else {
                    return self::generateToken();
                }
        }
    }

    /**
     * NSH : Not Secure Hash - Creates a length variable hash
     * @param string $str The string to hash
     * @param int $length The length of the resulted hash
     * @return string The hash
     * @throws QCryptographicHashException
     */
    public static function nsh($str, $length = 15){
        if($length < 2){
            throw new QCryptographicHashException('Length must be greater than 2');
}

        $_ = array(
            732584193 + $length,
            23233417 + $length*3,
            562383102 - $length,
            71733878 + $length*4
        );

        // Get the 5 first chars of each final chunks
        $max_ = ($max_ = ceil($length / 5)) > 4 ? $max_ : 4;
        for($i = 4; $i < $max_; ++$i){
            $_[$i] = abs((($_[$i-1] + $_[$i-3] + $length) ^ $_[$i-4])% 2147483647);
        }
        $chars = array();
        foreach(str_split($str) as $char){
            $chars[] = str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $l = strlen($msg = implode('', $chars));
        $msg .= str_repeat('0', 512-(($l+16)%512)) . str_pad(decbin(strlen($str)), 16, '0', STR_PAD_LEFT);
        $chunks = str_split($msg, 512);
        $__ = array();
        foreach($chunks as $chunk){
            $words = str_split($chunk, 32);
            for($i = 16; $i < 80; ++$i){
                $tmp = str_pad(decbin(((bindec($words[$i-3]) ^ bindec($words[$i-8])) ^ bindec($words[$i-14])) ^ bindec($words[$i-16])), 32, '0', STR_PAD_LEFT);
                $words[$i] = substr($tmp, 1) . $tmp{0};
            }
            for($i = 0; $i < $max_; ++$i){
                $__[$i] = $_[$i];
            }
            $i = 0;
            while($i < 20){
                $f = ($__[1] & $__[2]) | (~$__[1] & $__[2]);
                $k = 1518500249;
                $tmp = abs(($__[0] + $f + $k + bindec($words[$i])) % 2147483647);
                for($j = $max_; $j > 0; --$j){
                    $__[$j] = $__[$j-1];
                }
                $__[0] = $tmp;
                ++$i;
            }
            while($i < 40){
                $f = $__[1] ^ $__[2];
                $k = 1859775393;
                $tmp = abs(($__[0] + $f + $k + bindec($words[$i])) % 2147483647);
                for($j = $max_; $j > 0; --$j){
                    $__[$j] = $__[$j-1];
                }
                $__[0] = $tmp;
                ++$i;
            }
            while($i < 60){
                $f = ($__[1] & $__[2]) | ($__[2] & $__[3]);
                $k = 1253476061;
                $tmp = abs(($__[0] + $f + $k + bindec($words[$i])) % 2147483647);
                for($j = $max_; $j > 0; --$j){
                    $__[$j] = $__[$j-1];
                }
                $__[0] = $tmp;
                ++$i;
            }
            while($i < 80){
                $f = ($__[1] ^ $__[2]) ^ $__[3];
                $k = 1253476061;
                $tmp = abs(($__[0] + $f + $k + bindec($words[$i])) % 2147483647);
                for($j = $max_; $j > 0; --$j){
                    $__[$j] = $__[$j-1];
                }
                $__[0] = $tmp;
                ++$i;
            }
            for($i = 0; $i < $max_; ++$i){
                $_[$i] = abs(($__[$i] + $_[$i]) % 2147483647);
            }
        }
        $hash = '';
        $chunks = $length / 5;
        for($i = 0; $i < $chunks; ++$i){
            $hash .= substr(dechex(bindec(substr(str_pad(decbin($_[$i]), 20, '0', STR_PAD_RIGHT), 0, 20))), 0, $length > 5 ? 5 : $length);
            $length -= 5;
        }
        return $hash;
    }
}

class QCryptographicHashException extends QAbstractObjectException {}
class QCryptographicHashSignatureException extends QCryptographicHashException implements QSignatureException {}
class QCryptographicHashSaltException extends QCryptographicHashException {}
?>