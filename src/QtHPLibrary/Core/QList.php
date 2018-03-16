<?php

class QList extends QVector {

    const Bool = 'boolean',
          Float = 'double',
          Integer = 'integer',
          Map = 'array',
          String = 'string';

    protected
            /**
             * @access protected
             * @var string The callback to check template type
             */
            $_callback,

            /**
             * @access protected
             * @var string The template of the list
             */
            $_template;

    /**
     * @param string $template The name of the type to store
     */
    public function __construct($template){
        parent::__construct();
        if(!class_exists($template)){
            if(in_array($template, array(self::Bool, self::Integer, self::String, self::Float, self::Map))){
                $this->_template = $template;
                $this->_callback = '_isTemplateType';
            } else {
                throw new QListTemplateTypeException('Unable to find the class "' . $template. '" ');
            }
        } else {
            $this->_template = $template;
            $this->_callback = '_isTemplateInstance';
        }
    }

    /**
     * Inserts an item at the end of the vector
     * @throws QListTemplateException If $value is nor a template type nor a QList with the same template type
     * @param mixed $value The value to append
     * @param bool $recursive [optional] Wether to append the value recursively (for array, a DList will be created) [default=false]
     * @throws DListTemplateException If $value is nor a template type nor a DList with the same template type
     */
    public function append($value, $recursive = false){
        if($value instanceof QList && $value->_template == $this->_template){
            $this->_list = array_merge($this->_list, $value->_list);
            $this->_endPtr = $this->_endPtr - $this->_startPtr + $value->size();
            $this->_startPtr = 0;
        } else if($this->{$this->_callback}($value)){
            $this->_list[$this->_endPtr++] = $value;
        } else if(is_array($value)){
            if($recursive){
                foreach($value as $v){
                    $this->_list[$this->_endPtr++] = is_array($v) ? QList::fromArray($v, $recursive) : $v;
                }
            } else {
                $this->_list = array_merge($this->_list, $value);
                $this->_endPtr = $this->_endPtr - $this->_startPtr + count($value);
                $this->_startPtr = 0;
            }
        } else {
            throw new QListTemplateException('Not a ' . $this->_template);
        }
        return $this;
    }

    /**
     * Check if a value exists in the list
     * @param mixed $value The value
     * @return bool True if the value exists, false otherwise
     */
    public function contains($value){
        return in_array($value, $this->_list, true);
    }

    /**
     * Counts a value
     * @param mixed $value The value to count
     * @return int The number of items found
     */
    public function count($value = null){
        return parent::count($value, true);
    }

    /**
     * Check if $value is the last item
     * @param mixed $value The value to check
     * @return bool true if $value is last item, false otherwise
     */
    public function endsWith($value){
        return $this->_startPtr != $this->_endPtr && $this->_list[$this->_endPtr-1] === $value;
    }

    /**
     * Creates a QList from an array using the template<br />
     * type of the first item of the array
     * @access static
     * @param array The array to create a QList from
     * @return QList The created vector
     */
    public static function fromArray(array $array, $recursive = false){
        $list = new QList(self::_gettype(current($array)));
        $list->append($array, $recursive);
        return $list;
    }

    /**
     * Finds the first occurence of value in the vector
     * @throws QListException If $from is out of range
     * @param mixed $value The value to find
     * @param int $from [optional] Set it to search forward from index position $from
     * @return int The index position of the first occurence of the item $value or -1 if it was not found
     */
    public function indexOf($value, $from = null){
        if(!$this->{$this->_callback}($value))
            //throw new QListException('Not a template type ' . $this->_template);
            return -1;
        if($from === null){
            $from = $this->_startPtr;
        } else if(($from += $this->_startPtr) < $this->_startPtr || $from >= $this->_endPtr){
            throw new QListRangeException('Out of range');
        }
        for(;$from < $this->_endPtr;++$from){
            if($this->_list[$from] === $value)
                return $from - $this->_startPtr;
        }
        return -1;
    }

    /**
     * Inserts an item in the list
     * @throws QListTemplateException If $value is not a template type
     * @throws QVectorRangeException If $index is out of range
     * @param int index The index
     * @param $mixed value The value to insert
     */
    public function insert($index, $value){
        if(!$this->{$this->_callback}($value)){
            throw new QListTemplateException('Not a template type ' . $this->_template);
        }
        return parent::insert($index, $value);
    }

    /**
     * Finds the last occurence of value in the list
     * @throws QListRangeException If $from is out of range
     * @param mixed $value The value to find
     * @param int $from [optional] Set it to search forward from index position $from
     * @return int The index position of the last occurence of the item $value or -1 if it was not found
     */
    public function lastIndexOf($value, $from = null){
        if(!$this->{$this->_callback}($value))
            //throw new QListException('Not a template type ' . $this->_template);
            return -1;
        if($from === null){
            $from = $this->_endPtr-1;
        } else if(($from += $this->_startPtr) < $this->_startPtr || $from > $this->_endPtr){
            throw new QListRangeException('Out of range');
        }
        for(;$from > $this->_startPtr;--$from){
            if($this->_list[$from] === $value)
                return $from - $this->_startPtr;
        }
        return -1;
    }

    /**
     * Inserts an item at the beginning of the list
     * @param mixed $value The value to prepend
     */
    public function prepend($value){
        if($value instanceof QList && $this->_template == $value->_template){
            $this->_list = array_merge($value->_list, $this->_list);
            $this->_endPtr = $this->_endPtr - $this->_startPtr + $value->size();
            $this->_startPtr = 0;
        } else if($this->{$this->_callback}($value)){
            $this->_list[--$this->_startPtr] = $value;
        } else {
            throw new QListTemplateException('Not a template type ' . $this->_template);
        }
        return $this;
    }

    /**
     * Removes all occurences of $value
     * @param mixed $value The value to remove
     */
    public function removeAll($value){
        // end et current sont moins rapides que count avec des tableau de moins de 100 éléments !!!!
        $this->_list = array_diff_key($this->_list, array_flip(array_keys($this->_list, $value, true)));
        ksort($this->_list);
        $this->_list = array_values($this->_list);
        $this->_startPtr = 0;
        end($this->_list);
        $this->_endPtr = key($this->_list);
        return $this;
    }

    /**
     * Removes the first occurence of the list that corresponds to $value
     * @throws QListTemplateException If $value is not a template type
     * @param mixed $value The value
     */
	public function removeOne($value){
		if(!$this->{$this->_callback}($value)){
                    throw new QListTemplateException('Not a template type ' . $this->_template);
		}
		if(!count($keys = array_keys($this->_list, $value, true)))
            return false;
        unset($this->_list[$keys[0]]);
		ksort($this->_list);
        $this->_endPtr -= ($this->_startPtr+1);
        $this->_startPtr = 0;
        return true;
	}

    /*
     * Replace a value by $value
     * @throws QListTemplateException If $value is not a template type
     * @throws QVectorRangeException If $index is out of range
     * @param int $index The index position
     * @param mixed $value The value to replace with
     */
	public function replace($i, $value){
		if(!$this->{$this->_callback}($value)){
            throw new QListTemplateException('Not a template type ' . $this->_template);
		}
        return parent::replace($i, $value);
	}

    /**
     * Check if $value is the first item
     * @param mixed $value The value to check
     * @return bool true if $value is first item, false otherwise
     */
	public function startsWith($value){
		return $this->_startPtr != $this->_endPtr && $this->_list[$this->_startPtr] === $value;
	}

    /**
     * Returns a value for sure
     * @throws QListTemplateException If $value is not a template type
     * @param int $i The index position
     * @param mixed $defaultValue
     */
	public function value($i, $defaultValue){
		if(!$this->{$this->_callback}($defaultValue)){
            throw new QListTemplateException('Not a template type ' . $this->_template);
		}
		return parent::value($i, $defaultValue);
	}

//    /**
//     * Returns the complete type of the list by adding the template type<br />
//     * For example, a QList of strings would have this type : DList<string><br />
//     * It doesn't work properly for embedded DList (DList<DList<string>> for example)
//     * @return string
//     */
//    public function type(){
//        return parent::type().'<' . $this->_template . '>';
//    }

    /**
     * Get the template type
     * @return string
     */
    public function templateType(){
        return $this->_template;
    }

    /*******************
     * Private methods *
     *******************/
    private static function _gettype($value){
        if(($t = gettype($value)) == 'object'){
            return get_class($value);
        } else {
            return $t;
        }
    }

    private function _isTemplateInstance($value){
        return $value instanceof $this->_template;
    }

    private function _isTemplateType($value){
        return gettype($value) == $this->_template;
    }

    public function offsetSet ($offset, $value) {
        if(!$this->{$this->_callback}($value)){
            throw new QListTemplateException('Not a template type ' . $this->_template);

        }
        return parent::offsetSet($offset, $value);
    }
}

class QListException extends QVectorException {}
class QListTemplateTypeException extends QVectorException {}
class QListTemplateException extends QListException {}
class QListRangeException extends QListException {}
?>