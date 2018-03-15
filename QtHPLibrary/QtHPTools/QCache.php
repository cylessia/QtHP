<?php

class QCache extends QAbstractObject {
    
    private
            /**
             * @var QMap
             */
            $_map,
            $_name;
    
    private static $_sessions = 0;
    
    public function __construct($name = ''){
        if($name){
            session_name(($this->_name = $name));
        } else {
            $this->_name = session_name();
        }
        
        if(session_status() == PHP_SESSION_NONE){
            session_start();
        }
        $this->_map = isset($_SESSION['qthp_session'][$this->_name]) ? unserialize($_SESSION['qthp_session'][$this->_name]) : new QMap;
        ++self::$_sessions;
    }
    
    public function recover($key){
        try {
            return $this->_map->value($key);
        } catch(QMapException $e){
            throw new QCacheValueException($e->getMessage());
        }
    }
    
    public function __destruct(){
        $_SESSION['qthp_session'][$this->_name] = serialize($this->_map);
        --self::$_sessions;
        if(!self::$_sessions){
            session_write_close();
        }
    }
    
    public function save($key, $value){
        $this->_map->insert($key, $value);
    }
    
    public function clear($key = null){
        $this->_map->clear($key);
    }
    
    public static function start(){
        if(headers_sent()){
            throw new QCacheStartException('Unable to start session whereas headers already have been sent');
        }
        session_start();
    }
}

class QCacheException extends QAbstractObjectException {}
class QCacheStartException extends QCacheException {}
class QCacheValueException extends QCacheException {}

?>