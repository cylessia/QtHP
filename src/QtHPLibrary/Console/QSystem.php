<?php

/**
 * Description of QSystem
 */
class QSystem extends QAbstractObject {

    const
    Linux = 'Linux',
    Windows = 'WINNT',
    Macintosh = 'UNIX';

    private static
    $_hostname = '',
    $_macAddr = '',
    $_ipv4 = '',
    $_ipv6 = '',
    $_gateway = '',
    $_os = '',
    $_tempPath = '';

    public static function init(){
        if(!self::$_os){
            if((self::$_os = PHP_OS) == self::Windows){
                $cmd = new QCommandLine('ipconfig');
                $cmd->run(array('/all'), $outputs);
                self::$_hostname = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : substr($outputs[3], strpos($outputs[3], ':')+2);
                self::$_macAddr = substr($outputs[13], strpos($outputs[13], ':')+2);
                self::$_ipv4 = substr($outputs[17], ($p=strpos($outputs[17], ':')+2), (strpos($outputs[17], '(')-$p));
                $ipv6 = substr($outputs[16], ($p=strpos($outputs[16], ':')+2), (strpos($outputs[16], '%')-$p));
                self::$_gateway = substr($outputs[21], ($p=strpos($outputs[16], ':')+2));
                self::$_tempPath = new QDir(sys_get_temp_dir());
            } else if(self::$_os == self::Linux) {
                $cmd = new QCommandLine('ifconfig');
                $cmd->run(array(), $outputs);
                self::$_hostname = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : php_uname('n');
                self::$_macAddr = substr($outputs[0], strpos($outputs[0], 'HWaddr')+7);
                self::$_ipv4 = substr($outputs[1], ($p = strpos($outputs[1], 'inet addr:')+10), (strpos($outputs[1], ' ', $p)-$p));
                $ipv6 = substr($outputs[2], ($p = strpos($outputs[2], 'inet6 addr:')+12), (strpos($outputs[2], '/', $p)-$p));
                self::$_gateway = substr($outputs[1], ($p = strpos($outputs[1], 'Bcast:')+6), (strpos($outputs[1], ' ', $p)-$p));
                self::$_tempPath = new QDir(sys_get_temp_dir());
            } else {
                throw new QSystemException(self::$_os . ' is not handled yet !');
            }

            // "Correct" ipv6 addresses
            $_ipv6 = '';
            foreach(explode(':', $ipv6) as $part){
                $_ipv6 .= ':' . str_pad($part, 4, '0', STR_PAD_LEFT);
            }
            self::$_ipv6 = substr($_ipv6, 1);
        }
    }

    public static function hostname(){
        return self::$_hostname;
    }

    public static function macAddr(){
        return self::$_macAddr;
    }

    public static function ipv4(){
        return self::$_ipv4;
    }

    public static function ipv6(){
        return self::$_ipv6;
    }

    public static function gateway(){
        return self::$_gateway;
    }

    public static function tempPath(){
        return self::$_tempPath->path();
    }

    public static function os(){
        return self::$_os;
    }
}

class QSystemException extends QAbstractObjectException {}

QSystem::init();