<?php

abstract class QValidator extends QAbstractObject {
    public static function isValid($value){}
    public function validate($value){}
}

abstract class QValidatorException extends QAbstractObjectException {}
interface QValidatorIsValidException {}
interface QValidatorValidateException {}

?>