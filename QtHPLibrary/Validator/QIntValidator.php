<?php

class QIntValidator extends QValidator {
    protected
            /**
             * @access protected
             * @var int The bottom 
             */
            $_bottom,
            
            /**
             * @access protected
             * @var int The top 
             */
            $_top;
    
    /**
     * @param int $bottom [optional] The bottom
     * @param int $top [optional] The top
     * @throws QIntValidatorException If $bottom or $top are not integer like numeric
     */
    public function __construct($bottom = -2147483648, $top = 2147483647){
        parent::__construct();
        if((!is_numeric($bottom) || strpos($bottom, '.') !== false)){
            throw new QIntValidatorException('"' . $bottom . '" is not a valid integer');
        }
        if((!is_numeric($top) || strpos($top, '.') !== false)){
            throw new QIntValidatorException('"' . $top . '" is not a valid integer');
        }
        $this->_bottom = (int)$bottom;
        $this->_top = (int)$top;
    }
    
    /**
     * Returns the validator's lower valid value
     * @return type 
     */
    public function bottom(){
        return $this->_bottom;
    }
    
    /**
     * Check if $value is valid
     * @param mixed $value The value to check
     * @param float $bottom The lower valid value
     * @param float $top The higher valid value
     * @return bool
     * @throws QFloatValidatorException 
     */
    public static function isValid($value, $bottom = -2147483648, $top = 2147483647){
        if((!is_numeric($bottom) || strpos($bottom, '.') !== false)){
            throw new QIntValidatorException('"' . $bottom . '" is not a valid integer');
        }
        if((!is_numeric($top) || strpos($top, '.') !== false)){
            throw new QIntValidatorException('"' . $top . '" is not a valid integer');
        }
        return is_numeric($value) && strpos($value, '.') === false && $value >= $bottom && $value <= $top;
    }
    
    /**
     * Sets the validator's lower valid value
     * @param int $bottom
     * @throws QIntValidatorException 
     */
    public function setBottom($bottom){
        if(!is_numeric($bottom) || strpos($bottom, '.') !== false){
            throw new QIntValidatorException('"' . $bottom . '" is not a valid integer');
        }
        $this->_bottom = (int)$bottom;
    }
    
    /**
     * Sets the range of the validator to only accepts interger values between bottom and top including
     * @param int $bottom The bottom
     * @param int $top The top
     * @throws QIntValidatorException If $bottom or $top are not integer like numeric
     */
    public function setRange($bottom, $top){
        if(!is_numeric($bottom) || strpos($bottom, '.') !== false){
            throw new QIntValidatorException('"' . $bottom . '" is not a valid integer');
        }
        if(!is_numeric($top) || strpos($top, '.') !== false){
            throw new QIntValidatorException('"' . $top . '" is not a valid integer');
        }
        $this->_bottom = (int)$bottom;
        $this->_top = (int)$top;
    }
    
    /**
     * Sets the validator's higher valid value
     * @param int $top
     * @throws QIntValidatorException 
     */
    public function setTop($top){
        if(!is_numeric($top) || strpos($top, '.') !== false){
            throw new QIntValidatorException('"' . $top . '" is not a valid integer');
        }
        $this->_top = (int)$top;
    }
    
    /**
     * Returns the validator's higher valid value
     * @return int
     */
    public function top(){
        return $this->_top;
    }
    
    /**
     * Checks if $value is valid 
     * @param int $value The value to check
     * @return bool The value
     */
    public function validate($value){
        return is_numeric($value) && !strpos($value, '.') === false && $value >= $this->_bottom && $value <= $this->_top;
    }
}

class QIntValidatorException extends QException{}
?>