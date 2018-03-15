<?php

class QLineF extends QLine {
    
    protected $_x1,
            $_y1,
            $_x2,
            $_y2;
    
    public function __construct($x1, $y1 = null, $x2 = null, $y2 = null){
        if($x1 instanceof QLine){
            $this->_x1 = $x1->_x1;
            $this->_y1 = $x1->_y1;
            $this->_x2 = $x1->_x2;
            $this->_y2 = $x1->_y2;
        } else if($x1 instanceof QPoint && $y1 instanceof QPoint){
            $this->_x1 = $x1->x();
            $this->_y1 = $x1->y();
            $this->_x2 = $y1->x();
            $this->_y2 = $y1->y();
        } else if(is_numeric($x1) && is_numeric($y1) && is_numeric($x2) && is_numeric($y2)){
            $this->_x1 = $x1;
            $this->_y1 = $y1;
            $this->_x2 = $x2;
            $this->_y2 = $y2;
        } else {
            throw new QLineFException('Call to invalid function QLineF::QLineF(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
    }
    
    public function p1(){
        return new QPointF($this->_x1, $this->_y1);
    }
    
    public function p2(){
        return new QPointF($this->_x2, $this->_y2);
    }
    
    public function setP1($p1){
        if(!$p1 instanceof QPoint){
            throw new QLineFException('Invalid point');
        }
        $this->_x1 = $p1->x();
        $this->_y1 = $p1->y();
    }
    
    public function setP2($p2){
        if(!$p2 instanceof QPoint){
            throw new QLineFException('Invalid point');
        }
        $this->_x2 = $p2->x();
        $this->_y2 = $p2->y();
    }
    
    public function setLine($x1, $y1 = null, $x2 = null, $y2 = null){
        if($x1 instanceof QLine){
            $this->_x1 = $x1->_x1;
            $this->_y1 = $x1->_y1;
            $this->_x2 = $x1->_x2;
            $this->_y2 = $x1->_y2;
        } else if($x1 instanceof QPoint && $y1 instanceof QPoint){
            $this->_x1 = $x1->x();
            $this->_y1 = $x1->y();
            $this->_x2 = $y1->x();
            $this->_y2 = $y1->y();
        } else if(is_numeric($x1) && is_numeric($y1) && is_numeric($x2) && is_numeric($y2)){
            $this->_x1 = $x1;
            $this->_y1 = $y1;
            $this->_x2 = $x2;
            $this->_y2 = $y2;
        } else {
            throw new QLineFException('Call to invalid function QLineF::setLine(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
    }
}

class QLineFException extends QLineException {}

?>