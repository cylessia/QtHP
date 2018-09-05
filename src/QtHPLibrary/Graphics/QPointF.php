<?php

class QPointF extends QPoint {
    
    public function __construct($x, $y = null){
        if($x instanceof QPoint){
            $this->_x = $x->_x;
            $this->_y = $x->_y;
        } else if(is_numeric($x) && is_numeric($y)){
            $this->_x = $x;
            $this->_y = $y;
        }
    }
    
    public function setX($x){
        if(!is_numeric($x)){
            throw new QPointXException('Call to undefined signature');
        }
        $this->_x = $x;
        return $this;
    }
    
    public function setY($y){
        if(!is_numeric($y)){
            throw new QPointYException('Call to undefined signature');
        }
        $this->_y = $y;
        return $this;
    }
}

class QPointFException extends QPointException {}
class QPointFXException extends QPointFException {}
class QPointFYException extends QPointFException {}

?>