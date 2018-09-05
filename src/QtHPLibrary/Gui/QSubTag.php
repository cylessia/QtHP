<?php

class QSubTag extends QAbstractTag {
    
    protected $_type = 'QSubTag';
    
    private $_content = '';
    
    public static function create($content){
         if(!is_scalar($content)){
            throw new QSubTagException('The content of a sub element must be a scalar value');
        }
        $tag = new QSubTag;
        $tag->_content = $content;
        return $tag;
    }
    
    public function __toString(){
        return '<sub ' . $this->_styleOptionAttribute() . '>' . $this->_content . '</sub>';
    }
}

class QSubTagException extends QAbstractTagException {}

?>