<?php

class QRegExpValidator extends QValidator {
    
    private
            
            /**
             * @access private
             * @var string The regexp
             */
            $_regexp;
    
    /**
     * @param mixed $regexp
     * @throws QRegExpValidatorException If $regexp seem to be an invalid regular expression
     */
    public function __construct($regexp = null){
        if($regexp != null && substr($regexp, 0, 1) != substr($regexp, -1)){
            throw new QRegExpValidatorException('Not a valid regexp');
        }
        $this->_regexp = $regexp;
    }
    
    /**
     * Check if $value is valid
     * @throws QRegExpValidatorException If $regexp seem to be an invalid regular expression
     * @param mixed $value The value to check
     * @param string $pattern The regexp
     * @return bool
     */
    public static function isValid($value, $pattern) {
        if(($pattern != null && $pattern{0} != $pattern{strlen($pattern)-1}) || $pattern == null){
            throw new QRegExpValidatorException('Not a valid regexp');
        }
        return preg_match($pattern, $value);
    }
    
    /**
     * Checks if the value is valid
     * @param mixed $value
     * @return bool
     */
    public function validate($value){
        return preg_match($this->_regexp, $value);
    }
}

class QRegExpValidatorException extends QException{}
?>