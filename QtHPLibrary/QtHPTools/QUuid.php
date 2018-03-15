<?php

class QUuid extends QAbstractObject {
    
    private
            
            /**
             * @access private
             * @var string
             */
            $_exp1,
            
            /**
             * @access private
             * @var string
             */
            $_exp2,
            
            /**
             * @access private
             * @var string
             */
            $_exp3,
            
            /**
             * @access private
             * @var string
             */
            $_exp4,
            
            /**
             * @access private
             * @var string
             */
            $_exp5;
    
    /**
     * @param string $exp1
     * @param string $exp2
     * @param string $exp3
     * @param string $exp4
     * @param string $exp5
     * @throws QUuidException If one expression is not correct
     */
    public function __construct($exp1 = '000000000000', $exp2 = '0000', $exp3 = '0000', $exp4 = '0000', $exp5 = '000000000000'){
        if(!
            (isset($exp1{7}) &&
            !isset($exp1{8}) &&
            isset($exp2{3}) &&
            !isset($exp2{4}) &&
            isset($exp3{3}) &&
            !isset($exp3{4}) &&
            isset($exp4{3}) &&
            !isset($exp4{4}) &&
            isset($exp5{11}) &&
            !isset($exp5{12}) &&
            is_numeric('0x'.$exp1) &&
            is_numeric('0x'.$exp2) &&
            is_numeric('0x'.$exp3) &&
            is_numeric('0x'.$exp4) &&
            is_numeric('0x'.$exp5))
        ){
            throw new QUuidException('One of the Uuid expressions is not valid');
        }
        $this->_exp1 = $exp1;
        $this->_exp2 = $exp2;
        $this->_exp3 = $exp3;
        $this->_exp4 = $exp4;
        $this->_exp5 = $exp5;
    }
    
    /**
     * Creates a new QUuid with an almost unique generated identifier
     * @return QUuid
     */
    public static function createUuid(){
        return new self(self::_randomUuidExp(8), self::_randomUuidExp(4), self::_randomUuidExp(4), self::_randomUuidExp(4), self::_randomUuidExp(12));
    }
    
    /**
     * 
     * @return type 
     */
    public function isNull(){
        return $this->_exp1 == '000000000000' && $this->_exp2 == '0000' && $this->_exp3 == '0000' && $this->_exp4 == '0000' && $this->_exp5 == '000000000000';
    }
    
    /**
     * Returns the string representation of the uuid
     * @param $complete bool false to get only the expressions, true to get full representation
     * @return string
     */
    public function toString($namespace = true){
        return $namespace ? '{' . $this->_exp1 . '-' . $this->_exp2 . '-' . $this->_exp3 . '-' . $this->_exp4 . '-' . $this->_exp5 . '}' : $this->_exp1 . $this->_exp2 . $this->_exp3 . $this->_exp4 . $this->_exp5;
    }
    
    /*******************
     * Private methods *
     *******************/
    private static function _randomUuidExp($length){
        return strtoupper(substr(sha1(mcrypt_create_iv(8, MCRYPT_DEV_URANDOM)), 0, $length));
    }
}

class QUuidException extends QException{}

?>