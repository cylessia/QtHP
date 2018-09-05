<?php

class QAcronymTag extends QAbstractTag {
    
    protected $_type = 'QAcronymTag';
    
    private $_title = '',
            $_content = '';
    
    public static function create($acronym, $meaning){
         if(!is_scalar($meaning)){
            throw new QAcronymTagException('The meaning of an abbr must be a scalar value');
        }
         if(!is_scalar($acronym)){
            throw new QAcronymTagException('The abreviation of an abbr must be a scalar value');
        }
        $tag = new QAcronymTag;
        $tag->_content = $acronym;
        $tag->_title = $meaning;
        return $tag;
    }
    
    public function __toString(){
        return '<acronym ' . $this->_styleOptionAttribute() . ' title="' . $this->_title . '">' . $this->_content . '</acronym>';
    }
}

class QAcronymTagException extends QAbstractTagException {}

?>