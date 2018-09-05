<?php

class QString extends QAbstractObject {
    
    private
    
            /**
             * @access private
             * @var string The string
             */
            $_str = '',
            
            /**
             * @access private
             * @var int The size of the string
             */
            $_size = 0;
    
    protected
    
            /**
             * @access private
             * @var string The string
             */
            $_type = 'QString';
    
    /**
     * Initializes the object
     * @param string $string The string or QString object
     */
    public function __construct($string = null){
        parent::__construct();
        if($string instanceof QString){
            $this->_str = $string->_str;
            $this->_size = $string->_size;
        } else if(is_string($string)) {
            $this->_str = $string;
            $this->_size = strlen($string);
        }
    }
    
    public function append($string){
        if($string instanceof QString){
            $this->_str .= $string->_str;
            $this->_size += $string->_size;
        } else if(is_string($string)){
            $this->_str .= $string;
            $this->_size += strlen($string);
        }
    }
    
    /*public function arg($arg1, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null, $arg7 = null, $arg8 = null, $arg9 = null){
        
    }*/
    
    public function at($i){
        if($i < 0 || $i >= $this->_size){
            throw new QStringException('Out of range');
        }
        return $this->_str{$i};
    }
    
    public function chop($n){
        if($n < 0){
            throw new QStringException('Out of range');
        }
        if($i > $this->_size){
            $this->_size = 0;
            $this->_str = '';
        } else {
            $this->_str = substr($this->_str, 0, $this->_size-$n);
        }
    }
    
    public function clear(){
        $this->_size = 0;
        $this->_str = '';
    }
    
    public function compare($string, $cs = QtHP::CaseSensitive){
        if(isset($this)){
            if($string instanceof QString)
                $string = $string->_str;
            return $cs ? strcmp($this->_str, $string) : strcasecmp($this->_str, $string);
        } else {
            if(func_num_args() < 3){
                throw new QStringSignatureException('Call to undefined function QString::compare(' . implode(',', array_map('gettype', func_get_args())) . ')');
            }
            if($string instanceof QString)
                $string = $string->_str;
            if($cs instanceof QString)
                $cs = $cs->_str;
            return func_get_arg(2) ? strcmp($string, $cs) : strcasecmp($string, $cs);
        }
    }
    
    public function contains($str, $cs = QtHP::CaseSensitive){
        if(isset($this)){
            if($str instanceof QString)
                $str = $str->_str;
            return $cs ? strpos($this->_str, $str) : stripos($this->_str, $str);
        } else {
            if(func_num_args() < 3){
                throw new QStringSignatureException('Call to undefined function QString::contains(' . implode(',', array_map('gettype', func_get_args())) . ')');
            }
            if($str instanceof QString)
                $str = $str->_str;
            if($cs instanceof QString)
                $cs = $cs->_str;
            return func_get_arg(2) ? strpos($str, $cs) : stripos($str, $cs);
        }
    }
    
    public function count($str, $cs = QtHP::CaseInsensitive){
        if($str instanceof QString)
            $str = $str->_str;
        return $cs ? substr_count($this->_str, $str) : substr_count(strtolower($this->_str), strtolower($str));
    }
    
    public function endsWith($str, $cs = QtHP::CaseSensitive){
        return $str instanceof QString ? substr_compare($this->_str, $str->_str, $this->_size-$str->_size, null, $cs) == 0 : substr_compare($this->_str, $str, $this->_size-strlen($str), null, $cs) == 0;
    }
    
    public function indexOf($str, $from, $cs = QtHP::CaseSensitive){
        if($from > $this->_size){
            throw new QStringException('Out of range');
        }
        if($str instanceof QString)
            $str = $str->_str;
        return $cs ? strpos($this->_str, $str, $from) : stripos($this->_str, $str, $from);
    }
    
    public function insert($str, $position){
        if($position > $this->_size){
            throw new QStringException('Out of range');
        }
        
        if($str instanceof QString){
            $this->_size += $str->_size;
            $str = $str->_str;
        } else {
            $this->_size += strlen($str);
        }
        $this->_str = substr($this->_str, 0, $position) . $str . substr($this->_str, $position+1);
    }
    
    public function isEmpty(){
        return $this->_size == 0;
    }
    
    public function lastIndexOf($str, $from, $cs = QtHp::CaseSensitive){
        if($from > $this->_size){
            throw new QStringException('Out of range');
        }
        if($str instanceof QString)
            $str = $str->_str;
        return $cs ? strrpos($this->_str, $str, $from) : strripos($this->_str, $str, $from);
    }
    
    public function left($n){
        if($n < 0){
            throw new QStringException('Out of range');
        }
        return new QString($n < $this->_size ? substr($this->_str, 0, $n) : $this->_str);
    }
    
    public function length(){
        return $this->_size;
    }
    
    public function levenstein($string){
        if(isset($this)){
            return levenshtein($this->_str, $string);
        } else {
            if(func_num_args() < 2){
                throw new QStringSignatureException('Call to undefined function QString::levenstein(' . implode(',', array_map('gettype', func_get_args())) . ')');
            }
            return levenshtein($string, func_get_arg(1));
        }
    }
    
    public function mid($from, $n = null){
        if($from < 0 ||$from > $this->_size){
            throw new QStringException('Out of range');
        }
        return new QString($n === null ? substr($this->_str, $from, $n) : substr($this->_str, $from));
    }
    
    public function prepend($str){
        if($str instanceof QString){
            $this->_size += $str->_size;
            $this->_str = $str->_str . $this->_str;
        } else {
            $this->_size += strlen($str);
            $this->_str = $str . $this->_str;
        }
    }
    
    public function remove($from, $n){
        if(is_int($from)){
            if($from < 0 || $from > $this->_size){
                throw new QStringException('Out of range');
            }
            $this->_str = ($from + $n) < $this->_size ? substr($this->_str, $from, $n) : substr($this->_str, $from);
        } else {
            if($from instanceof QString){
                $from = $from->_str;
            }
            $this->_str = $n ? str_replace($from, '', $this->_str) : str_ireplace($from, '', $this->_str);
        }
    }
    
    public function repeated($times){
        if(!is_int($times)){
            throw new QStringSignatureException('Call to undefined function QString::repeated(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
        return new QString(str_repeat($this->_str, $times));
    }
    
    public function replace($from, $to, $cs = QtHP::CaseSensitive){
        if(is_int($from) && is_int($to) && is_char($cs)){
            if($from < 0 || $from > $this->_size || $to < 0 ||$to > $this->_size){
                throw new QStringException('Out of range');
            }
            if($from >= $to){
                throw new QStringException($from . ' must be lower than ' . $to);
            }
            $this->_str = substr($this->_str, 0, $from) . $cs . substr($this->_str, $to);
        } else {
            if($from instanceof QString)
                $from = $from->_str;
            if($to instanceof QString)
                $to = $to->_str;
            return new QString($cs ? str_replace($from, $to, $this->_str) : str_ireplace($from, $to, $this->_str));
        }
    }
    
    public function right($n){
         if($n < 0){
             throw new QStringException('Out of range');
         }
         return new QString($n < $this->_size ? substr($this->_str, $this->_size-$n) : $this->_str);
    }
    
    public function size(){
        return $this->_size;
    }
    
    public function split($str, $n){
        if($str instanceof QString)
            $str = $str->_str;
        return QStringList::fromArray(str_split($str, $n));
    }
    
    public function startWith($str, $cs = QtHP::CaseSensitive){
        return $str instanceof QString ? substr_compare($this->_str, $str->_str, 0, $str->_size, $cs) == 0 : substr_compare($this->_str, $str, 0, strlen($str), $cs) == 0;
    }
    
    public function strip($char = '\\', $cs = QtHP::CaseSensitive){
        if(!is_char($char)){
            throw new QStringSignatureException('Call to undefined function QString::strip(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
        $this->_str = $cs ? str_replace($char.$char, $char, $this->_str) : str_ireplace($char.$char, $char, $this->_str);
    }
    
    public function swap(QString $string){
        $str = $this->_str;
        $size = $this->_size;
        $this->_str = $string->_str;
        $this->_size = $string->_size;
        $string->_str = $str;
        $string->_size = $size;
    }
    
    public function toLower(){
        $this->_str = strtolower($this->_str);
        return $this;
    }
    
    public function toUpper($onlyFirst = false){
        if($onlyFirst)
            $this->_str{0} = strtoupper($this->_str{0});
        else
        $this->_str = strtoupper($this->_str);
        return $this;
    }
    
    public function trim($charList){
        $this->_str = trim($this->_str, $charList instanceof QString ? $charList->_str : $charList);
        return $this;
    }
    
    public function truncate($position){
        if($position < $this->_size)
            $this->_str = substr($this->_str, 0, $position);
    }
}

class QStringException extends QException{protected $_type = 'QStringException';};
class QStringSignatureException extends QStringException{protected $_type = 'QStringSignatureException';};
?>