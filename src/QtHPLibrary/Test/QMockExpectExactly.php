<?php
/**
 * Description of QMockExpectExactly
 */
class QMockExpectExactly extends QAbstractMockExpectation {
    private
    $_value;
    
    public function __construct($value) {
        if(!is_int($value)){
            $fga = func_get_args();
            throw new QAbstractMockExpectationSignatureException('Call to undefined function QMockExpectExactly::__construct(' . implode(', ', array_map('qGetType', $fga)) . ')');
        }
        $this->_value = $value;
    }
    
    public function matches($time){
        return $time === $this->_value;
    }
}
