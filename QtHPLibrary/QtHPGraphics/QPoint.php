<?php

class QPoint extends QAbstractObject {
    
    protected $_x,
            $_y;
    
    public function __construct($x = 0, $y = null){
        if($x instanceof QPoint){
            $this->_x = $x->_x;
            $this->_y = $x->_y;
        } else if(is_int($x) && is_int($y)){
            $this->_x = $x;
            $this->_y = $y;
        }
    }
    
    public function setX($x){
        if(!is_int($x)){
            throw new QPointXException('Call to undefined signature');
        }
        $this->_x = $x;
        return $this;
    }
    
    public function setY($y){
        if(!is_int($y)){
            throw new QPointYException('Call to undefined signature');
        }
        $this->_y = $y;
        return $this;
    }
    
    public function x(){
        return $this->_x;
    }
    
    public function y(){
        return $this->_y;
    }
}

class QPointException extends QException{}
class QPointXException extends QPointException{}
class QPointYException extends QPointException{}

?>