<?php

class QBitArray extends QAbstractObject implements ArrayAccess, Iterator {
    protected
        /**
         * @access protected
         * @var string The type of the class
         */
        $_type = 'QBitArray';
    
    private 
        /**
         * @access private
         * @var string The array of bits
         */
        $_bitArray = '',
        
        /**
         * @access private
         * @var int The size of the array
         */
        $_size = 0,
        
        /**
         * @access private
         * @var int The internal pointer
         */
        $_ptr = 0;
    
    /**
     * @param int $size [optional] The size of the array
     * @param bool $value [optional] The value to store
     */
    public function __construct($size = null, $value = false) {
        parent::__construct();
        if($size instanceof QBitArray) {
            $this->_bitArray = $size->_bitArray;
            $this->_size = $size->_size;
        } else if($size !== null){
            $this->_bitArray = str_repeat(($value&1), ($this->_size = $size));
        }
    }
    
    /**
     * Returns the value of the $i bit
     * @throws QBitArrayException
     * @param $i The offset
     * @return int
     */
    public function at($i){
        if(!isset($this->_bitArray{$i}))
            throw new QBitArrayException('Out of range');
        return ($this->_bitArray{$i}&1);
    }
    
    /**
     * Reinitializes the array
     */
    public function clear(){
        $this->_bitArray = '';
        $this->_size = 0;
        return $this;
    }
    
    /**
     * Clears a bit at position $i
     * @param int $i The position of the bit
     */
    public function clearBit($i){
        if(!isset($this->_bitArray{$i}))
            throw new QBitArrayException('Out of range');
        $this->_bitArray = substr($this->_bitArray, 0, $i) . substr($this->_bitArray, $i + 1);
        $this->_size -= 1;
        return $this;
    }
    
    /**
     * Counts the number of bits stored in the array
     * @param bool $on [optional] The value of bits to count
     */
    public function count($on = true){
        return substr_count($this->_bitArray, ($on&1));
    }
    
    /**
     * Sets every bit of the array to value
     * @throws QBitArrayException
     * @param $value The value of the new bits
     * @param $begin [optional] The size of the array or the start offset if $end is set
     * @param $end [optional] The last offset
     */
    public function fill($value, $begin = null, $end = null){
        if($begin === null) {
            $this->_bitArray = str_repeat(($value&1), $this->_size);
        } else if($end === null){
            $this->_bitArray = str_repeat(($value&1), ($this->_size = $begin));
        } else if($begin > $end) {
            throw new QBitArrayException('Cannot insert a negative size');
        } else if(($begin < 0 || $begin > $this->_size) || ($end < 0 && $end > $this->_size)){
            throw new QBitArrayException('Out of range');
        } else {
            $this->_bitArray = substr($this->_bitArray, 0, $begin) . str_repeat(($value&1), ($end - $begin)) . substr($this->_bitArray, $end);
        }
        return $this;
    }
    
    /**
     * Check wether the arrat is empty or not
     * @return bool
     */
    public function isEmpty(){
        return $this->_size == 0;
    }
    
    /**
     * Return a bit array that contains the inverted bits of this bit array
     */
    public function neg(){
        $bitArray = new QBitArray($this);
        for($i = 0; $i < $bitArray->_size; ++$i)
            $bitArray->_bitArray{$i} = !$bitArray->_bitArray{$i}&1;
        return $bitArray;
    }
    
    /**
     * Perform a AND (&) binary operation
     * @param QBitArray $bitArray The other QBitArray to perform with (If it's longer than $this, then $this will be filled with 0-bit)
     */
    public function performAnd(QBitArray $bitArray){
        if($this->_size > $bitArray->_size){
            for($i = 0; $i < $bitArray->_size; ++$i)
                $this->_bitArray{$i} = $this->_bitArray{$i}&$bitArray->_bitArray{$i};
        } else {
            for($i = 0; $i < $this->_size; ++$i){
                $this->_bitArray{$i} = $this->_bitArray{$i}&$bitArray->_bitArray{$i};
            }
            $this->_bitArray .= str_repeat('0', $bitArray->_size - $this->_size);
            $this->_size = $bitArray->_size;
        }
        return $this;
    }
    
    /**
     * Perform a OR (|) binary operation
     * @param QBitArray $bitArray The other QBitArray to perform with (If it's longer than $this, then $this will be filled with $bitArray values)
     */
    public function performOr(QBitArray $bitArray){
        if($this->_size > $bitArray->_size){
            for($i = 0; $i < $bitArray->_size; ++$i)
                $this->_bitArray{$i} = $this->_bitArray{$i}|$bitArray->_bitArray{$i};
        } else {
            for($i = 0; $i < $this->_size; ++$i){
                $this->_bitArray{$i} = $this->_bitArray{$i}|$bitArray->_bitArray{$i};
            }
            $this->_bitArray .= substr($bitArray->_bitArray, $this->_size);
            $this->_size = $bitArray->_size;
        }
        return $this;
    }
    
    /**
     * Perform a XOR (^) binary operation
     * @param QBitArray $bitArray The other QBitArray to perform with (If if's longer than $this, then $this will be filled with 0^$bitArray[$i] values)
     */
    public function performXor(QBitArray $bitArray){
        if($this->_size > $bitArray->_size){
            for($i = 0; $i < $bitArray->_size; ++$i)
                $this->_bitArray{$i} = $this->_bitArray{$i}^$bitArray->_bitArray{$i};
        } else {
            for($i = 0; $i < $this->_size; ++$i){
                $this->_bitArray{$i} = $this->_bitArray{$i}^$bitArray->_bitArray{$i};
            }
            $this->_bitArray .= str_repeat('0', $bitArray->_size - $this->_size);
            $this->_size = $bitArray->_size;
        }
        return $this;
    }
    
    /**
     * Perform a NEG (~) binary operation
     */
    public function performNeg(){
        for($i = 0; $i < $this->_size; ++$i)
            $this->_bitArray{$i} = (!$this->_bitArray{$i}&1);
        return $this;
    }
    
    /**
     * Resizes the bit array
     * @throws QBitArrayException
     * @param int $size The new size
     */
    public function resize($size){
        if($size < 0)
            throw new QBitArrayException('Out of range');
        if($size === $this->_size)
            return $this;
        if($size < $this->_size){
            $this->_bitArray = substr($this->_bitArray, 0, $size);
        } else {
            $this->_bitArray .= str_repeat(0, ($size - $this->_size));
        }
        $this->_size = $size;
        return $this;
    }
    
    /**
     * Sets the bit
     * @throws QBitArrayException
     * @param int $i The existing position
     * @param bool $value [optional] The new value
     */
    public function setBit($i, $value = true){
        if(!isset($this->_bitArray{$i}))
            throw new QBitArrayException('Out of range');
        $this->_bitArray{$i} = ($value&1);
        return $this;
    }
    
    /**
     * Returns the size of the array
     */
    public function size(){
        return $this->_size;
    }
    
    /**
     * Invert the value of a bit
     * @throws QBitArrayException
     * @param int $i The index position
     */
    public function toggleBit($i){
        if(!isset($this->_bitArray{$i}))
            throw new QBitArrayException('Out of range');
        $this->_bitArray{$i} = (!$this->_bitArray{$i}&1);
        return $this;
    }
    
    /**
     * Returns the bit array as a string
     */
    public function toBinaryString(){
        return $this->_bitArray;
    }
    
    /**
     * Truncates the bit array
     * @throws QBitArrayException
     * @param int $i The index position
     */
    public function truncate($i){
        if(!isset($this->_bitArray{$i}))
            throw new QBitArrayException('Out of range');
        $this->_bitArray = substr($this->_bitArray, 0, ($this->_size = $i));
        return $this;
    }
    
    /*****************************
     * Interface implementations * 
     *****************************/
    public function offsetExists($i) {
        return isset($this->_bitArray{$i});
    }
    
    public function offsetGet($offset) {
        $this->at($offset);
    }
    
    public function offsetSet($offset, $value) {
        $this->setBit($offset, $value);
    }
    
    public function offsetUnset($offset) {
        $this->clearBit($offset);
    }
    
    public function current(){
        return $this->_bitArray{$this->_ptr};
    }
    
    public function key() {
        return $this->_ptr;
    }
    
    public function next(){
        ++$this->_ptr;
    }
    
    public function rewind() {
        $this->_ptr = 0;
    }
    
    public function valid() {
        return $this->_ptr < $this->_size;
    }
}

class QBitArrayException extends QException{}
?>