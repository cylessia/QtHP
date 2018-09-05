<?php

class QJson extends QMap {
    
    
    public function __construct(){}
    
    public static function parse($json){
        return self::fromArray(json_decode($json, true), true);
    }
    
    public static function fromArray(array $array, $recursive = false){
        $a = new QJson;
        $a->insert($array, true);
        return $a;
    }
    
    public function insert($key, $value = null, $recursive = false){
        if($key instanceof QJson || $key instanceof QMap){
            $this->_map = array_merge($this->_map, $key->_map);
        } else if(is_array($key)){
            if($value === true){
                foreach($key as $k => $v){
                    $this->insert($k, $v, $value);
                }
            } else {
                $this->_map = array_merge($this->_map, $key);
            }
        } else if(is_scalar($key)) {
            if(is_array($value) && $recursive === true){
                $this->_map[$key] = QJson::fromArray($value, $recursive);
            } else {
                $this->_map[$key] = $value;
            }
        } else {
            throw new QMapException('Call to undefined function QMap::append(' . implode(', ', array_map('gettype', func_get_args())) . ')');
        }
        return $this;
    }
    
    public function encode(){
        return json_encode($this->_encode($this->_map));
    }
    
    private function _encode($array){
        $_array = array();
        foreach($array as $k => $v){
            $_array[$k] = is_array($v) || $v instanceof ArrayAccess ? $this->_encode($v) : $v;
        }
        return $_array;
    }
}

class QJsonException extends QAbstractObjectException {}

?>