<?php

class QRectF extends QRect {
    
    public function __construct($x, $y = null, $width = null, $height = null){
        if($x instanceof QRect){
            $this->_x1 = $x->_x1;
            $this->_x2 = $x->_x2;
            $this->_y1 = $x->_y1;
            $this->_y2 = $x->_y2;
        } else if($x instanceof QPoint){
            if($y instanceof QPoint) {
                $this->_x1 = $x->x();
                $this->_x2 = $y->x();
                $this->_y1 = $x->y();
                $this->_y2 = $y->y();
            } else if($y instanceof QSize){
                $this->_x1 = $x->x();
                $this->_y1 = $x->y();
                
                $this->_x2 = $this->_x1 + $y->width();
                $this->_y2 = $this->_y1 + $y->height();
            }
        } else if(is_numeric($x) && is_numeric($y) && is_numeric($width) && is_numeric($height)){
            $this->_x1 = $x;
            $this->_x2 = $x + $width;
            $this->_y1 = $y;
            $this->_y2 = $y + $height;
        } else {
            throw new QRectFException('Call to undefined signature QRect::__construct()');
        }
    }
    
    public function bottomLeft(){
        return new QPointF($this->_x1, $this->_y2);
    }
    
    public function bottomRight(){
        return new QPointF($this->_x2, $this->_y2);
    }
    
    public function center(){
        return new QPointF($this->_x1 + ($this->_x2 - $this->_x1) / 2, $this->_y1 + ($this->_y2 - $this->_y1) / 2);
    }
    
    public function contains($x, $y = null, $proper = false){
        if($x instanceof QPoint && is_bool($y)){
            if($y){
                return $this->_x1 < $x->x() && $this->_x2 > $x->x() && $this->_y1 < $x->y() && $this->_y2 > $x->y();
            } else {
                return $this->_x1 <= $x->x() && $this->_x2 >= $x->x() && $this->_y1 <= $x->y() && $this->_y2 >= $x->y();
            }
        } else if($x instanceof QRect && is_bool($y)){
            if($y){
                return $this->_x1 < $x->_x1 && $this->_x2 > $x->_x2 && $this->_y1 < $x->_y1 && $this->_y2 > $x->_y2;
            } else {
                return $this->_x1 <= $x->_x1 && $this->_x2 >= $x->_x2 && $this->_y1 <= $x->_y1 && $this->_y2 >= $x->_y2;
            }
        } else if(is_numeric($x) && is_numeric($y)){
            if($proper){
                return $this->_x1 < $x && $this->_x2 > $x && $this->_y1 < $y && $this->_y2 > $y;
            } else {
                return $this->_x1 <= $x && $this->_x2 >= $x && $this->_y1 <= $y && $this->_y2 >= $y;
            }
        } else {
            throw new QRectFException('Call to undefined signature QRect::contains()');
        }
    }
    
    public function moveBottom($y){
        if(!is_numeric($y)){
            throw new QRectFException('Not a valid numeric value');
        }
        $this->_x1 += $y;
        $this->_y2 += $y;
        return $this;
    }
    
    public function moveLeft($x){
        if(!is_numeric($x)){
            throw new QRectFException('Not a valid numeric value');
        }
        $this->_x1 -=  $x;
        $this->_x2 -= $x;
        return $this;
    }
    
    public function moveRight($x){
        if(!is_numeric($x)){
            throw new QRectFException('Not a valid numeric value');
        }
        $this->_x1 +=  $x;
        $this->_x2 += $x;
        return $this;
    }
    
    public function moveTo($x, $y = null){
        if($x instanceof QPoint){
            $xSize = abs($this->_x1 - $x->x());
            $ySize = abs($this->_y1 - $x->y());
            $this->_x1 = $x->x();
            $this->_x2 += $xSize;
            
            $this->_y1 = $y->y();
            $this->_y2 += $ySize;
        } else if(is_numeric($x) && is_numeric($y)){
            $xSize = abs($this->_x1 - $x);
            $ySize = abs($this->_y1 - $y);
            $this->_x1 = $x;
            $this->_x2 += $xSize;
            
            $this->_y1 = $y;
            $this->_y2 += $ySize;
        }
    }
    
    public function moveTop($y){
        if(!is_numeric($y)){
            throw new QRectFException('Not a valid numeric value');
        }
        $this->_y1 -= $y;
        $this->_y2 -= $y;
    }
    
    public function normalized(){
        if($this->_x1 > $this->_x2){
            $tmp = $this->_x1;
            $this->_x2 = $this->_x1;
            $this->_x2 = $tmp;
        }
        if($this->_y1 > $this->_y2){
            $tmp = $this->_y1;
            $this->_y2 = $this->_y1;
            $this->_y2 = $tmp;
        }
    }
    
    public function right(){
        return $this->_x2;
    }
    
    public function setBottom($y){
        if(!is_numeric($y)){
            throw new QRectFException('Not a valid numeric value');
        }
        $this->_y2 = $y;
    }
    
    public function setBottomLeft($x, $y = null){
        if($x instanceof QPoint){
            $this->_x1 = $x->x();
            $this->_y2 = $x->y();
        } else if(is_numeric($x) && is_numeric($y)){
            $this->_x1 = $x;
            $this->_y2 = $y;
        }
    }
    
    public function setBottomRight($x, $y){
        if($x instanceof QPoint){
            $this->_x2 = $x->x();
            $this->_y2 = $x->y();
        } else if(is_numeric($x) && is_numeric($y)){
            $this->_x2 = $x;
            $this->_y2 = $y;
        }
    }
    
    public function setCoords($x1, $y1, $x2 = null, $y2 = null){
        if($x1 instanceof QPoint && $y1 instanceof QPoint){
            $this->_x1 = $x1->x();
            $this->_y1 = $x1->y();
            $this->_x2 = $y1->x();
            $this->_y2 = $y1->y();
        } else if(is_numeric($x1) && is_numeric($y1) && is_numeric($x2) && is_numeric($y2)){
            $this->_x1 = $x1;
            $this->_y1 = $y1;
            $this->_x2 = $x2;
            $this->_y2 = $y2;
        }
    }
    
    public function setHeight($height){
        if(!is_numeric($height)){
            throw new QRectException('Not a valid height');
        }
        $this->_y2 = $this->_y1 + $height;
    }
    
    public function setLeft($x){
        if(!is_numeric($x)){
            throw new QRectException('Not a valid coordinate');
        }
        $this->_x1 = $x;
    }
    
    public function setRect($x, $y = null, $width =  null, $height = null){
        if($x instanceof QRect) {
            $this->_x1 = $x->_x1;
            $this->_y1 = $x->_y1;
            $this->_x2 = $x->_x2;
            $this->_y2 = $x->_y2;
        } else if($x instanceof QPoint && $y instanceof QSize){
            $this->_x1 = $x->x();
            $this->_y1 = $x->y();
            $this->_x2 = $this->_x1 + $y->width();
            $this->_y2 = $this->_y1 + $y->height();
        } else if(is_numeric($x) && is_numeric($y) && is_numeric($width) && is_numeric($height)){
            $this->_x1 = $x;
            $this->_y1 = $y;
            $this->_x2 = $x + $width;
            $this->_y2 = $y + $height;
        }
    }
    
    public function setRight($x){
        if(!is_numeric($x)){
            throw new QRectException('Not a valid coordinate');
        }
        $this->_x2 = $x;
    }
    
    public function setSize($width, $height = null){
        if($width instanceof QSize){
            $this->_x2 = $this->_x1 + $width->width();
            $this->_y2 = $this->_y1 + $width->height();
        } else if(is_numeric($width) && is_numeric($height)){
            $this->_x2 = $this->_x1 + $width;
            $this->_y2 = $this->_y1 + $height;
        }
    }
    
    public function setTop($y){
        if(!is_numeric($y)){
            throw new QRectException('Not a valid coordinate');
        }
        $this->_y1 = $y;
    }
    
    public function setTopLeft($x, $y = null){
        if($x instanceof QPoint){
            $this->_x1 = $x->x();
            $this->_y1 = $x->y();
        } else if(is_numeric($x) && is_numeric($y)){
            $this->_x1 = $x;
            $this->_y2 = $y;
        }
    }
    
    public function setTopRight($x, $y = null){
        if($x instanceof QPoint){
            $this->_x2 = $x->x();
            $this->_y2 = $x->y();
        } else if(is_numeric($x) && is_numeric($y)){
            $this->_x2 = $x;
            $this->_y2 = $y;
        }
    }
    
    public function setWidth($width){
        if(!is_numeric($width)){
            throw new QRectException('Not a valid width');
        }
        $this->_x2 = $this->_x1 + $width;
    }
    
    public function setX($x){
        if(!is_numeric($x)){
            throw new QRectException('Not a valid coordinate');
        }
        $this->_x1 = $x;
    }
    
    public function setY($y){
        if(!is_numeric($y)){
            throw new QRectEyception('Not a valid coordinate');
        }
        $this->_y1 = $y;
    }
    
    public function size(){
        return new QSizeF(abs($this->_x2 - $this->_x1), abs($this->_y2 - $this->_y1));
    }
    
    public function top(){
        return $this->_x1;
    }
    
    public function topLeft(){
        return new QPoint($this->_x1, $this->_y1);
    }
    
    public function topRight(){
        return new QPoint($this->_x2, $this->_y1);
    }
    
    public function united($rect){
        if(!$rect instanceof QRect){
            throw new QRectException('Not a valid QRect object');
        }
        $left = $rect->_x1 < $this->_x1 ? $rect->_x1 : $this->_x1;
        $right = $rect->_x2 > $this->_x2 ? $rect->_x2 : $this->_x2;
        $top = $rect->_y1 < $this->_y1 ? $rect->_y1 : $this->_y1;
        $bottom = $rect->_y2 > $this->_y2 ? $rect->_y2 : $this->_y2;
        return new QRect($left, $top, $right - $left, $bottom - $top);
    }
    
    public function width(){
        return $this->_x2 - $this->_x1;
    }
    
    public function x(){
        return $this->_x1;
    }
    
    public function y(){
        $this->_y1;
    }
}

class QRectFException extends QRectException {}

?>