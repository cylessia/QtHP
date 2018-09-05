<?php

class QImageTag extends QAbstractTag {
    
    private $_url,
            $_alternateText = '',
            $_width = null,
            $_height = null;
    
    public static function create($imageUrl, $alternateText = ''){
        $tag = new self;
        
        if($imageUrl instanceof QUrl){
            $tag->_url = $imageUrl->toString();
        } else if(is_string($imageUrl)){
            $tag->_url = $imageUrl;
        } else {
            throw new QImageTagException('Not a valid image url');
        }
        $tag->_alternateText = $alternateText;
        return $tag;
    }
    
    public function setHeight($height){
        if(!is_numeric($height) || strpos($height, '.') !== false){
            throw new QImageTagException('"' . $height . '" is an incorrect height value');
        }
        $this->_height = $height;
        return $this;
    }
    
    public function setSize($size, $height = null){
        if($size instanceof QSize){
            $this->_height = $size->height();
            $this->_width = $size->width();
        } else if(!is_numeric($size) || !is_numeric($height) || strpos($size, '.') !== false || strpos($height, '.') !== false){
            throw new QImageTagException('It\'s not a size');
        } else {
            $this->_width = $size;
            $this->_height = $height;
        }
        return $this;
    }
    
    public function setWidth($width){
        if(!is_numeric($width) || strpos($width, '.') !== false){
            throw new QImageTagException('"' . $width . '" is an incorrect width value');
        }
    }
    
    public function __toString(){
        return '<img ' . $this->_styleOptionAttribute() . ' src="' . $this->_url . '" ' . ($this->_alternateText ? 'alt="' . $this->_alternateText . '"' : '') . ($this->_width ? ' width="' . $this->_width . '"' : '') . ($this->_height ? ' height="' . $this->_height . '"' : '') . ' />';
    }
    
}

class QImageTagException extends QAbstractTagException { protected $_type = 'QImageTagException'; }

?>