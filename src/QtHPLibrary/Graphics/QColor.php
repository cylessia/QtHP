<?php

/**
 * Faire les méthodes :
 *  fromColor
 *  fromIndex 
 */

class QColor extends QAbstractObject {
    
    private $_red,
            $_green,
            $_blue,
            $_alpha;
    
    public function __construct($r = 0, $g = 0, $b = 0, $a = 0){
        if($r instanceof QColor){
            $this->_red = $r->_red;
            $this->_green = $r->_green;
            $this->_blue = $r->_blue;
            $this->_alpha = $r->_alpha;
        } else {
            $this->setRed($r);
            $this->setGreen($g);
            $this->setBlue($b);
            $this->setAlpha($a);
        }
    }
    
    public function alpha(){
        return $this->_alpha;
    }
    
    public function blue(){
        return $this->_blue;
    }
    
    public function green(){
        return $this->_green;
    }
    
    public static function fromIndex($index){
        if(!is_int($index) || $index > 0x7FFFFFFF || $index < 0)
            throw new QColorException('Color index must be a positive number lower than 2 147 483 647');
        return new self($index - ($index >> 24 << 24) >> 16, $index - ($index >> 16 << 16) >> 8, $index - ($index >> 8 << 8), $index >> 24);
    }
    
    public function index(){
        return $this->_blue + $this->_green * 0x100 + $this->_red * 0x10000 + $this->_alpha * 0x1000000;
    }
    
    public function setAlpha($a){
        if(!is_int($a) || $a < 0 || $a > 0x7F)
            throw new QColorException('Alpha must be lower than 128');
        $this->_alpha = $a;
        return $this;
    }
    
    public function setBlue($b){
        if(!is_int($b) || $b < 0 || $b > 0xFF)
            throw new QColorException('Blue must be lower than 256');
        $this->_blue = $b;
        return $this;
    }
    
    public function setGreen($g){
        if(!is_int($g) || $g < 0 || $g > 0xFF)
            throw new QColorException('Green must be lower than 256');
        $this->_green = $g;
        return $this;
    }
    
    public function setRed($r){
        if(!is_int($r) || $r < 0 || $r > 0xFF)
            throw new QColorException('Red must be lower than 256');
        $this->_red = $r;
        return $this;
    }
    
    public function red(){
        return $this->_red;
    }
}

class QColorException extends QAbstractObjectException {}

?>