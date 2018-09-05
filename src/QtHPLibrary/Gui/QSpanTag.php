<?php

class QSpanTag extends QAbstractTag {
    
    protected $_type = 'QSpanTag';
    
    private $_content = '';
    
    public static function create($content){
         if(!is_scalar($content)){
            throw new QSpanTagException('The content of a link must be a scalar value');
        }
        $tag = new QSpanTag;
        $tag->_content = $content;
        return $tag;
    }
    
    public function __toString(){
        return '<span ' . $this->_styleOptionAttribute() . '>' . $this->_content . '</span>';
    }
}

class QSpanTagException extends QAbstractTagException {}

?>