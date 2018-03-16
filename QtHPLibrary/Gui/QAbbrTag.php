<?php

class QAbbrTag extends QAbstractTag {
    
    private $_title = '',
            $_content = '';
    
    public static function create($abbreviation, $meaning){
         if(!is_scalar($meaning)){
            throw new QAbbrTagException('The meaning of an abbr tag must be a scalar value');
        }
         if(!is_scalar($abbreviation)){
            throw new QAbbrTagException('The abreviation of an abbr tag must be a scalar value');
        }
        $tag = new QAbbrTag;
        $tag->_content = $abbreviation;
        $tag->_title = $meaning;
        return $tag;
    }
    
    public function __toString(){
        return '<abbr ' . $this->_styleOptionAttribute() . ' title="' . $this->_title . '">' . $this->_content . '</abbr>';
    }
}

class QAbbrTagException extends QAbstractTagException {}

?>