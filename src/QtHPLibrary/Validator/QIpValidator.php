<?php

class QIpValidator extends QValidator {
    
    const IpV4 = FILTER_FLAG_IPV4,
          IpV6 = FILTER_FLAG_IPV6;
    
    private $_filterType;
    
    public function __construct($filterType){
        if($filterType ^ (self::IpV4 | self::IpV6)){
            throw new QIpValidatorException('Not a valid filter');
        }
        $this->_filterType = $filterType;
    }
    
    public static function isValid($value, $filterType){
        return filter_var($value, FILTER_VALIDATE_IP, $filterType) !== false;
    }
    
    public function validate($value){
        return filter_var($value, FILTER_VALIDATE_IP, $this->_filterType) !== false;
    }
}

class QIpValidatorException extends QValidatorException {}



?>