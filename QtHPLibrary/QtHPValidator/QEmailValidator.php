<?php

class QEmailValidator extends QValidator {
    
    public static function isValid($value){
        return filter_var($value, FILTER_VALIDATE_EMAIL)!==false;
    }
    
    public function validate($value){
        return filter_var($value, FILTER_VALIDATE_EMAIL)!==false;
    }
    
}

?>