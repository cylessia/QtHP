<?php

class QMap extends QAbstractObject implements ArrayAccess, IteratorAggregate {

    protected $_map = array();

    /**
     *
     * @param QMap|array $map
     */
    public function __construct($map = null){
        if($map instanceof QMap){
            $this->_map = $map->_map;
        } else if($map != null){
            $this->insert($map);
        }
    }

    public function apply($cb){
        $this->_map = array_map($cb, $this->_map);
        return $this;
    }

    /**
     * Returns a reference of the first element
     * @return mixed
     * @throws DMapException
     */
    public function &begin(){
        if(count($this->_map)){
            throw new QMapException('Unable to return a reference form an empty list');
        }
        reset($this->_map);
        return current($this->_map);
    }

    /**
     * Removes an index
     * @param string $key
     * @return \QMap
     * @throws QMapException
     */
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

    /**
     * Checks if a value exists in the map
     * @param mixed $value The value to search
     * @param boolean $strict [optional] true to check type (default = true)
     * @return boolean
     */
    public function contains($value, $strict = true){
        return in_array($value, $this->_map, $strict);
    }

    /**
     * Count the $value contained in the map
     * @param mixed $value The value to count
     * @param boolean $strict [optional] true to check type (default = true)
     * @return type
     */
    public function count($value, $strict = true){
        return count(array_keys($this->_map, $value, $strict));
    }

    /**
     * Create a QMap object from an array
     * @param array $array
     * @param boolean $recursive [optional] true to create DMap recursively (default = false)
     * @return \QMap
     */
    public static function fromArray(array $array, $recursive = false){
        $map = new QMap;
        $map->insert($array, $recursive);
        return $map;
    }

    /**
     * Checks if an index exists in the map
     * @param string $key
     * @return boolean
     */
    public function has($key){
        return array_key_exists($key, $this->_map);
    }

    /**
     * Insert one or more value in the map
     * @param QMap|array|scalar $key The map or array to merge, or the index of the element
     * @param mixed $value The element to add
     * @param boolean $recursive [optional] true to create map of $value recursively (default = false)
     * @return \QMap
     * @throws QMapException
     */
    public function insert($key, $value = null, $recursive = false){
        if($key instanceof QMap){
            foreach($key->_map as $k => $v){
                $this->_map[$k] = $v;
            }
        } else if(is_array($key)){
            if($value === true){
                foreach($key as $k => $v){
                    $this->insert($k, $v, $value);
                }
            } else {
                foreach($key as $k => $v){
                    is_int($k) ? $this->_map[] = $v : $this->_map[$k] = $v;
                }
            }
        } else if(is_scalar($key)) {
            if(is_array($value) && $recursive === true){
                $this->_map[$key] = QMap::fromArray($value, $recursive);
            } else {
                $this->_map[$key] = $value;
            }
        } else {
            $fga = func_get_args();
            throw new QMapException('Call to undefined function DMap::insert(' . implode(', ', array_map('gettype', $fga)) . ')');
        }
        return $this;
    }

    /**
     * Checks if the map is empty (eg : has no element)
     * @return boolean
     */
    public function isEmpty(){
        return count($this->_map) == 0;
    }

    /**
     * Get the key of a value<br />
     * If $value is found more than once,<br />
     * the first matching key is returned.<br />
     * If $value is not found, null is returned
     * @param mixed $value The value to search
     * @param booelan $strict [optional] true to check type (default = true)
     * @return mixed
     */
    public function key($value, $strict = true){
        return array_search($value, $this->_map, $strict) ?: null;
    }

    /**
     * Get all keys of the map
     * @return DStringList
     */
    public function keys(){
        return QStringList::fromArray(array_keys($this->_map));
    }

    /**
     * Count the number of elements contained in the map
     * @return int
     */
    public function size(){
        return count($this->_map);
    }

    /**
     * Get the map as array
     * @return array
     */
    public function data(){
        return $this->_map;
    }

    /**
     * Get a value matching the key $key
     * @param string $key The index
     * @return mixed
     * @throws DMapException
     */
    public function value($key){
        if(!array_key_exists($key, $this->_map)){
            throw new QMapException('Unknown key "' . $key . '"');
        }
        return $this->_map[$key];
    }

    public function &get($key){
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
        if(is_array($value)){
            $value = self::fromArray($value);
        }
        if($offset == null){
            $this->_map[] = $value;
        } else {
            $this->_map[$offset] = $value;
        }
    }

    public function offsetUnset($offset){
        unset($this->_map[$offset]);
    }

    public function getIterator(){
        return new ArrayIterator($this->_map);
    }
}

class QMapException extends QAbstractObjectException {}

?>