<?php

class QSupTag extends QAbstractTag {
    
    protected $_type = 'QSubTag';
    
    private $_content = '';
    
    public static function create($content){
         if(!is_scalar($content)){
            throw new QSupTagException('The content of a sup element must be a scalar value');
        }
        $tag = new QSupTag;
        $tag->_content = $content;
        return $tag;
    }
    
    public function __toString(){
        return '<sup ' . $this->_styleOptionAttribute() . '>' . $this->_content . '</sup>';
    }
}

class QSupTagException extends QAbstractTagException {}

?>