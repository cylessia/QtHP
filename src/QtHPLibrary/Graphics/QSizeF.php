<?php

class QSizeF extends QSize {
    
    public function __construct($width, $height = null){
        if($width instanceof QSize){
            $this->_width = $width->width();
            $this->_height = $width->height();
        } else {
            if(!is_numeric($width) || $width < -1 || !is_numeric($height) || $height < -1){
                throw new QSizeFException('Width and height of a QSize must be a positive number');
            }
            $this->_width = $width;
            $this->_height = $height;
        }
    }
    
    public function setHeight($height){
        if(!is_numeric($height) || $height < -1){
            throw new QSizeFException('The height of a QSize must be a positive number');
        }
        $this->_height = $height;
        return $this;
    }
    
    public function setWidth($width){
        if(!is_numeric($width) || $width < -1){
            throw new QSizeFException('The width of a QSize must be a positive number');
        }
        $this->_width = $width;
        return $this;
    }
}

class QSizeFException extends QException {}
?>