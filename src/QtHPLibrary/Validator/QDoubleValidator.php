<?php

class QDoubleValidator extends QIntValidator {
    protected

            /**
             * @access protected
             * @var int The maximum number of digits after the decimal point
             */
            $_decimals;

    /**
     * @param float $bottom [optional] The bottom
     * @param float $top [optional] The top
     * @param int $decimals The maximum number of digits after the decimal point
     * @throws QFloatValidatorException If $bottom or $top are not integer like numeric
     */
    public function __construct($bottom = -2147483648, $top = 2147483647, $decimals = 13){
        QValidator::__construct();
        if(!is_numeric($bottom)){
            throw new QDoubleValidatorException('"' . $bottom . '" is not a valid floating point number');
        }
        if(!is_numeric($top)){
            throw new QDoubleValidatorException('"' . $top . '" is not a valid floating point number');
        }
        if(!is_numeric($decimals) || strpos($decimals, '.') !== false || $decimals > 13){
            throw new QDoubleValidatorException('"' . $decimals . '" is not a valid decimal number');
        }
        $this->_bottom = (float)$bottom;
        $this->_top = (float)$top;
        $this->_decimals = (int)$decimals;
    }

    /**
     * Returns the max size of numbers after the floating point
     * @return int
     */
    public function decimals(){
        return $this->_decimals;
    }

    /**
     * Check if $value is valid
     * @param mixed $value The value to check
     * @param float $bottom The lower valid value
     * @param float $top The higher valid value
     * @param int $decimal The maximum number of digits after the decimal point
     * @return bool
     * @throws QFloatValidatorException
     */
    public static function isValid($value, $bottom = -2147483648, $top = 2147483647, $decimals = 13){
        if(!is_numeric($bottom)){
            throw new QDoubleValidatorException('"' . $bottom . '" is not a valid floating point number');
        }
        if(!is_numeric($top)){
            throw new QDoubleValidatorException('"' . $top . '" is not a valid floating point number');
        }
        if(!is_numeric($decimals) || strpos($decimals, '.') !== false || $decimals > 13){
            throw new QDoubleValidatorException('"' . $decimals . '" is not a valid decimal number');
        }
        return preg_match('/^[+-]?[0-9]*(\.([0-9]*))?([eE][+-]?[0-9]{0,3})?$/', $value, $m) && $value >= $bottom && $value <= $top && (!isset($m[2]) || strlen($m[2]) <= $decimals);
    }

    /**
     * Sets the validator's lower valid value
     * @param float $bottom
     * @throws QIntValidatorException
     */
    public function setBottom($bottom){
        if(!is_numeric($bottom)){
            throw new QDoubleValidatorException('"' . $bottom . '" is not a valid integer');
        }
        $this->_bottom = (float)$bottom;
    }

    public function setDecimals($decimals){
        if(!is_numeric($decimals) || strpos($decimals, '.') !== false || $decimals > 13){
            throw new QDoubleValidatorException('"' . $decimals . '" is not a valid decimal number');
        }
        $this->_decimals = (int)$decimals;
    }

    /**
     * Sets the range of the validator to only accepts interger values between bottom and top including
     * @param float $bottom The bottom
     * @param float $top The top
     * @throws QIntValidatorException If $bottom or $top are not integer like numeric
     */
    public function setRange($bottom, $top){
        if(!is_numeric($bottom)){
            throw new QDoubleValidatorException('"' . $bottom . '" is not a valid integer');
        }
        if(!is_numeric($top)){
            throw new QDoubleValidatorException('"' . $top . '" is not a valid integer');
        }
        $this->_bottom = (float)$bottom;
        $this->_top = (float)$top;
    }

    /**
     * Sets the validator's higher valid value
     * @param int $top
     * @throws QIntValidatorException
     */
    public function setTop($top){
        if(!is_numeric($top)){
            throw new QDoubleValidatorException('"' . $top . '" is not a valid integer');
        }
        $this->_top = (float)$top;
    }

    /**
     * Checks if $value is valid
     * @param float $value The value to check
     * @return bool The value
     */
    public function validate($value){
        return is_numeric($value) && $value >= $this->_bottom && $value <= $this->_top;
    }

}

class QDoubleValidatorException extends QException{}

?>