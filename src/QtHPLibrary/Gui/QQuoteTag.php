<?php

class QQuoteTag extends QAbstractTag {
    
    protected $_type = 'QQuoteTag';
    
    private $_content = '';
    
    public static function create($content){
         if(!is_scalar($content)){
            throw new QSpanTagException('The content of a quoted text must be a scalar value');
        }
        $tag = new QQuoteTag;
        $tag->_content = $content;
        return $tag;
    }
    
    public function __toString(){
        return '<q ' . $this->_styleOptionAttribute() . '>' . $this->_content . '</q>';
    }
}

class QQuoteTagException extends QAbstractTagException {}

?>