<?php

class QVector extends QAbstractObject implements ArrayAccess, IteratorAggregate, Countable {

    protected
            /**
             * @access protected
             * @var array The containing array
             */
            $_list = array(),

            /**
             * @access protected
             * @var int Points to the last item of the array
             */
            $_endPtr = 0,

            /**
             * @access protected
             * @var int Points to the first item of the array
             */
            $_startPtr = 0;

    /**
     * Inserts an item at the end of the vector
     * @param mixed $value The value to append
     */
    public function append($value, $recursive = false){
        if($value instanceof QVector){
            $this->_list = array_merge($this->_list, $value->_list);
            $this->_endPtr = $this->_endPtr - $this->_startPtr + $value->size();
            $this->_startPtr = 0;
        } else if(is_array($value)) {
            if($recursive){
                foreach($value as $v){
                    if(is_array($v)){
                        $this->_list[$this->_endPtr++] = QVector::fromArray($v, $recursive);
                    } else {
                        $this->_list[$this->_endPtr++] = $v;
                    }
                }
            } else {
                $this->_list = array_merge($this->_list, $value);
                $this->_endPtr = $this->_endPtr - $this->_startPtr + count($value);
                $this->_startPtr = 0;
            }
        } else {
            $this->_list[$this->_endPtr++] = $value;
        }
        return $this;
    }

    /**
     * Apply callback $cb on each elements
     * @param callback $cb Callable callback
     */
    public function apply($cb){
        $this->_list = array_map($cb, $this->_list);
    }

    /**
     * Returns an item
     * @throws QVectorRangeException If $i is out of range
     * @param int $i The position of the item
     * @return mixed The item
     */
    public function at($i){
        if(($i += $this->_startPtr) < $this->_startPtr || $i >= $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $i . ') is out of range');
        }
        return $this->_list[$i];
    }

    /**
     * Remove all items stored in the list
     */
    public function clear(){
        $this->_list = array();
        $this->_startPtr = $this->_endPtr = 0;
        return $this;
    }

    /**
     * Check if a value exists in the list
     * @param mixed $value The value
     * @param bool strict [optional] Set it to true to check the type of the value too
     * @return bool True if the value exists, false otherwise
     */
    public function contains($value, $strict = true){
        return in_array($value, $this->_list, $strict);
    }

    /**
     * Counts a value
     * @param mixed $value The value to count
     * @param bool $strict [optional] Set it to true to check the typeof the value too
     * @return int The number of items found
     */
    public function count($value = null, $strict = true){
        return $value ? count(array_keys($this->_list, $value, $strict)) : ($this->_endPtr - $this->_startPtr);
    }

    /**
     * Check if $value is the last item<br />
     * This member doesn't check the types
     */
    public function endsWith($value){
        return $this->_startPtr != $this->_endPtr && $this->_list[$this->_endPtr-1] == $value;
    }

    /**
     * Returns a reference to the first item
     * @throws QVectorEmptyException If the vector is empty
     * @return mixed A reference to the first item
     */
    public function &first(){
        if($this->_endPtr == $this->_startPtr){
            throw new QVectorEmptyException('Unable to return a reference from an empty list');
        }
        return $this->_list[$this->_startPtr];
    }

    /**
     * Creates a QVector from an array
     * @access static
     * @param array The array to create a QVector from
     * @return QVector The created vector
     */
    public static function fromArray(array $array, $recursive = false){
        $vector = new QVector;
        $vector->append($array, $recursive);
        return $vector;
    }

    /**
     * Finds the first occurence of value in the vector
     * @throws QVectorRangeException If $from is out of range
     * @param mixed $value The value to find
     * @param int $from [optional] Set it to search forward from index position $from
     * @return int The index position of the first occurence of the item $value or -1 if it was not found
     */
    public function indexOf($value, $from = null){
        if($from === null){
            $from = $this->_startPtr;
        } else if(($from += $this->_startPtr) < $this->_startPtr || $from >= $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $from . ') is out of range');;
        }
        for(;$from < $this->_endPtr;++$from){
            if($this->_list[$from] == $value)
                return $from - $this->_startPtr;
        }
        return -1;
    }

    /**
     * Inserts an item in the vector
     * @throws QVectorRangeException If $index is out of range
     * @param int index The index
     * @param $mixed value The value to insert
     */
    public function insert($index, $value){
        if(($index += $this->_startPtr) < $this->_startPtr || $index > $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $index . ') is out of range');
        }
        array_splice($this->_list, $index, 1, array($value, $this->_list[$index]));
        return $this;
    }

    /**
     * Returns true if the list is empty
     */
    public function isEmpty(){
        return $this->_startPtr == $this->_endPtr;
    }

    /**
     * Returns a reference of an item
     * @throws DVectorRangeException If $i is out of range
     * @param int $i The position of the item
     * @return mixed The item
     */
    public function &item($i){
        if(($i += $this->_startPtr) < $this->_startPtr || $i >= $this->_endPtr){
            throw new QVectorRangeException('&' . __METHOD__ . '(' . $i . ') is out of range');
        }
        return $this->_list[$i];
    }

    /**
     * Join all element
     * @param string $glue The string used to join elements
     * @return string
     */
    public function join($glue){
        return implode($glue, $this->_list);
    }

    /**
     * Returns a reference to the last item
     * @throws QVectorRmptyException If the vector is empty
     * @return mixed A reference to the last item
     */
    public function &last(){
        if($this->_endPtr == $this->_startPtr){
            throw new QVectorEmptyException('Unable to return a reference from an empty list');
        }
        return $this->_list[$this->_endPtr-1];
    }

    /**
     * Finds the last occurence of value in the vector
     * @throws QVectorRangeException If $from is out of range
     * @param mixed $value The value to find
     * @param int $from [optional] Set it to search forward from index position $from
     * @return int The index position of the last occurence of the item $value or -1 if it was not found
     */
    public function lastIndexOf($value, $from = null){
        if($from === null){
            $from = $this->_endPtr-1;
        } else if(($from += $this->_startPtr) < $this->_startPtr || $from > $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $from . ') is out of range');
        }
        for(;$from > $this->_startPtr;--$from){
            if($this->_list[$from] == $value){
                return $from - $this->_startPtr;
            }
        }
        return -1;
    }

    /**
     * Inserts an item at the beginning of the vector
     * @param mixed $value The value to prepend
     */
    public function prepend($value){
        if($value instanceof QVector){
            $this->_list = array_merge($value->_list, $this->_list);
            $this->_endPtr = $this->_endPtr - $this->_startPtr + $value->size();
            $this->_startPtr = 0;
        } else if(is_array($value)) {
            $this->_list = array_merge($value, $this->_list);
            $this->_endPtr = $this->_endPtr - $this->_startPtr + count($value);
            $this->_startPtr = 0;
        } else {
            $this->_list[--$this->_startPtr] = $value;
        }
        return $this;
    }

    /**
     * Removes all occurences of $value
     * @param mixed $value The value to remove
     */
    public function removeAll($value){
        // end and current are slower than count for array of 100 and less
        $this->_list = array_diff_key($this->_list, array_flip(array_keys($this->_list, $value)));
        ksort($this->_list);
        $this->_list = array_values($this->_list);
        $this->_startPtr = 0;
        end($this->_list);
        $this->_endPtr = current($this->_list);
        return $this;
    }

    /**
     * Remove the item a index position $i
     * @throws QVectorRangeException If $i is out of range
     * @param int $i The index position
     */
    public function removeAt($i){
        if(($i + $this->_startPtr) < $this->_startPtr || $i >= $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $i . ') is out of range');
        }
        ksort($this->_list);
        $this->_endPtr -= ($this->_startPtr+1);
        $this->_startPtr = 0;
        array_splice($this->_list, $i, 1);
        return $this;
    }

    /**
     * Removes the first item of the list
     * @throws QVectorEmptyException If the list is empty
     */
    public function removeFirst(){
        if($this->_startPtr == $this->_endPtr){
            throw new QVectorEmptyException('Unable to remove items from empty lists');
        }
        unset($this->_list[$this->_startPtr++]);
        return $this;
    }

    /**
     * Removes the last item of the list
     * @throws QVectorEmptyException If the list is empty
     */
    public function removeLast(){
        if($this->_startPtr == $this->_endPtr){
            throw new QVectorEmptyException('Unable to remove items from empty lists');
        }
        unset($this->_list[--$this->_endPtr]);
        return $this;
    }

    /**
     * Removes the first occurence of the list that corresponds to $value
     * @param mixed $value The value
     */
    public function removeOne($value){
        if(count($keys = array_keys($this->_list, $value)))
            return false;
        unset($this->_list[$keys[0]]);
		ksort($this->_list);
        $this->_endPtr -= ($this->_startPtr+1);
        $this->_startPtr = 0;
        return true;
    }

    /*
     * Replace a value by $value
     * @throws QVectorRangeException If $index is out of range
     * @param int $index The index position
     * @param mixed $value The value to replace with
     */
    public function replace($index, $value){
        if(($index += $this->_startPtr) < $this->_startPtr || $index >= $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $index . ') is out of range');
        }
        $this->_list[$index] = $value;
        return $this;
    }

    /**
     * Sort items in the list
     * @return \DVector
     */
    public function sort(){
        sort($this->_list);
        return $this;
    }

    /**
     * Returns the size of the vector
     * @return int The size
     */
    public function size(){
        return $this->_endPtr - $this->_startPtr;
    }

    /**
     * Return a vector filled with values from $start to $end
     * @param int $start
     * @param int $end
     */
    public function mid($start, $end = null){
        if($end === null){
            if(($start + $this->_startPtr) < $this->_startPtr || $start > $this->_endPtr){
            throw new QVectorRangeException(__CLASS__ . '::' . __METHOD__ . '(' . $start . ') is out of range');
            }
            $end = $this->_endPtr;
        } else {
            if(
                (($start + $this->_startPtr) < $this->_startPtr || $start > $this->_endPtr)
                || (($end + $this->_startPtr) < $this->_startPtr || $end > $this->_endPtr)
            ) {
            throw new QVectorRangeException(__CLASS__ . '::' . __METHOD__ . '(' . $end . ') is out of range');
            }
        }
        return QVector::fromArray(array_slice($this->_list, $start, $end-$start+1));
    }

    /**
     * Check if $value is the first item<br />
     * This member doesn't check the types
     * @param $value The value to check
     * @return bool
     */
    public function startsWith($value){
        return $this->_startPtr != $this->_endPtr && $this->_list[$this->_startPtr] == $value;
    }

    /**
     * Swaps two values
     * @throws QVectorRangeException If $i or $j is out of range
     * @param int $i An index position
     * @param int $j The index position to swap with
     */
    public function swap($i, $j){
        if(($i += $this->_startPtr) < $this->_startPtr || $i >= $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $i . ') is out of range');
        }
        if(($j += $this->_startPtr) < $this->_startPtr || $j >= $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $j . ') is out of range');
        }
        $tmp = $this->_list[$i];
        $this->_list[$i] = $this->_list[$j];
        $this->_list[$j] = $tmp;
        return $this;
    }

    /**
     * Removes an itemand returns it
     * @throws QVectorRangeException If $i is out of range
     * @param int $i The index position of the item
     * @return mixed The value stored at $i
     */
    public function takeAt($i){
        if(($i += $this->_startPtr) < $this->_startPtr || $i >= $this->_endPtr){
            throw new QVectorRangeException(__METHOD__ . '(' . $i . ') is out of range');
        }
        ksort($this->_list);
        $this->_endPtr -= ($this->_startPtr+1);
        $this->_startPtr = 0;
        $tmp = array_splice($this->_list, $i, 1);
        return $tmp[0];
    }

    /**
     * Removes the first item and return it
     * @throws QVectorEmptyException If the vector is empty
     * @return mixed The first value
     */
    public function takeFirst(){
        if($this->_startPtr == $this->_endPtr){
            throw new QVectorEmptyException('Unable to remove items from empty lists');
        }
        $tmp = $this->_list[$this->_startPtr];
        unset($this->_list[$this->_startPtr++]);
        return $tmp;
    }

    /**
     * Removes the last item and return it
     * @throws QVectorEmptyException If the vector is empty
     * @return mixed The last value
     */
    public function takeLast(){
        if($this->_startPtr == $this->_endPtr){
            throw new QVectorEmptyException('Unable to remove items from empty lists');
        }
        $tmp = $this->_list[--$this->_endPtr];
        unset($this->_list[$this->_endPtr]);
        return $tmp;
    }

    /**
     * Returns the internal array
     * @return array
     */
    public function toArray(){
        return $this->_list;
    }

    /**
     * Returns a value for sure
     * @param int $i The index position
     * @param mixed $defaultValue
     */
    public function value($i, $defaultValue){
        return ($i += $this->_startPtr) < $this->_startPtr || $i >= $this->_endPtr ? $this->_list[$i] : $defaultValue;
    }

    /****************************
     * Interface implementation *
     ****************************/
    public function offsetExists($offset){
        return ($offset+=$this->_startPtr) > $this->_startPtr && $offset < $this->_endPtr;
    }

    public function offsetGet($offset) {
        return $this->at($offset);
    }

    public function offsetSet($offset, $value) {
        if($offset === null) {
            $this->_list[$this->_endPtr++] = $value;
        } else if(is_int($offset) && $offset > $this->_startPtr && $offset < $this->_endPtr) {
            $this->_list[$offset] = $value;
        } else if($offset === $this->_endPtr) {
            $this->_list[$this->_endPtr++] = $value;
        } else {
            throw new QVectorRangeException('Out of range');
        }
    }

    public function offsetUnset($offset) {
        if(($offset += $this->_startPtr) < $this->_startPtr || $offset > $this->_endPtr){
            throw new QVectorRangeException('Out of range');
        }
        unset($this->_list[$offset]);
        --$this->_endPtr;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(){
        ksort($this->_list);
        return new ArrayIterator($this->_list);
    }
}

class QVectorException extends QAbstractObjectException{}
class QVectorRangeException extends QVectorException {}
class QVectorEmptyException extends QVectorException {}
?>