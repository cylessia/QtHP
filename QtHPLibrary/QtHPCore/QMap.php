<?php

class QMap extends QAbstractObject implements ArrayAccess, IteratorAggregate {
    
    protected $_map = array();
    
    public function &begin(){
        if(count($this->_map)){
            throw new QMapException('Unable to return a reference form an empty list');
        }
        reset($this->_map);
        return current($this->_map);
    }
    
    public function clear($key = null){
        if($key !== null && !is_scalar($key)){
            throw new QMapException('A key must be a scalar value');
        }
        if($key){
            unset($this->_map[$key]);
        } else {
            unset($this->_map);
            $this->_map = array();
        }
        return $this;
    }
    
    public function contains($value, $strict = true){
        return in_array($value, $this->_map, $strict);
    }
    
    public function count($value, $strict = true){
        return count(array_keys($this->_map, $value, $strict));
    }
    
    public static function fromArray(array $array, $recursive = false){
        $map = new QMap;
        $map->insert($array, $recursive);
        return $map;
    }
    
    public function has($key){
        return array_key_exists($key, $this->_map);
    }
    
    public function insert($key, $value = null, $recursive = false){
        if($key instanceof QMap){
            $this->_map += $key->_map;
        } else if(is_array($key)){
            if($value === true){
                foreach($key as $k => $v){
                    $this->insert($k, $v, $value);
                }
            } else {
                $this->_map += $key;
            }
        } else if(is_scalar($key)) {
            if(is_array($value) && $recursive === true){
                $this->_map[$key] = QMap::fromArray($value, $recursive);
            } else {
                $this->_map[$key] = $value;
            }
        } else {
            throw new QMapException('Call to undefined function QMap::append(' . implode(', ', array_map('gettype', func_get_args())) . ')');
        }
        return $this;
    }
    
    public function isEmpty(){
        return count($this->_map) == 0;
    }
    
    public function key($value, $strict = true){
        return array_search($value, $this->_map, $strict) ?: null;
    }
    
    public function size(){
        return count($this->_map);
    }
    
    public function value($key){
        if(!array_key_exists($key, $this->_map)){
            throw new QMapException('Unknown key "' . $key . '"');
        }
        return $this->_map[$key];
    }
    
    
    /*****************************
     * Interface implementations * 
     *****************************/
    public function offsetExists($offset){
        return isset($this->_map[$offset]);
    }
    
    public function offsetGet($offset){
        return isset($this->_map[$offset]) ? $this->_map[$offset] : null;
    }
    
    public function offsetSet($offset, $value){
        $this->_map[$offset] = $value;
    }
    
    public function offsetUnset($offset){
        unset($this->_map[$offset]);
    }
    
    public function getIterator(){
        return new ArrayIterator($this->_map);
    }
}

class QMapException extends QAbstractObjectException { protected $_type = 'QMapException'; }

?>