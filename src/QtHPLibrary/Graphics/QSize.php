<?php

class QSize extends QAbstractObject {
    
    protected $_width = 0,
            $_height = 0;
    
    public function __construct($width, $height = null){
        if($width instanceof QSize){
            $this->_width = $width->width();
            $this->_height = $width->height();
        } else {
            if(!is_int($width) || $width < -1 || !is_int($height) || $height < -1){
                throw new QSizeException('Width and height of a QSize must be a positive integer');
            }
            $this->_width = $width;
            $this->_height = $height;
        }
    }
    
    public function height(){
        return $this->_height;
    }
    
    public function isEmpty(){
        return $this->_width == 0 && $this->_height == 0;
    }
    
    public function setHeight($height){
        if(!is_int($height) || $height < -1){
            throw new QSizeException('The height of a QSize must be a positive integer');
        }
        $this->_height = $height;
        return $this;
    }
    
    public function setWidth($width){
        if(!is_int($width) || $width < -1){
            throw new QSizeException('The width of a QSize must be a positive integer');
        }
        $this->_width = $width;
        return $this;
    }
    
    public function transpose(){
        $tmp = $this->_width;
        $this->_width = $this->_height;
        $this->_height = $tmp;
        return $this;
    }
    
    public function width(){
        return $this->_width;
    }
}

class QSizeException extends QException {}
?>