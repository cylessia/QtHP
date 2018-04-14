<?php

/**
 * Description of QMockExpectRange
 */
class QMockExpectRange extends QAbstractMockExpectation {
    
    private
    
    $_min,
    $_max;
    
    public function __construct($min, $max){
        if(($type = qGetType($min)) != qGetType($max)){
            throw new QMockExpectBetweenException('$min and $max must be of the same type');
        }
        if($type != 'integer' && $type != 'float'){
            throw new QMockExpectRangeSignatureException('Call to undefined function QMockExpectRange::_construct(' . implode(', ', array_map('qGetType', $fga)) . ')');
        }
        $this->_min = $min;
        $this->_max = $max;
    }
    
    public function matches($times) {
        return $this->_min < $times && $times < $this->_max;
    }
}
