<?php

class QLinkTag extends QAbstractTag {
    
    protected
            $_type = 'QLinkTag';
    
    private $_href,
            $_title = '',
            $_content = '';
    
    /**
     * Create the link tag
     * @param scalar $content The content of the link
     * @param string $href [optional] The url
     * @param QStyleOption $style [optional] The style to set
     * @throws QLinkTagException If $content is not scalar
     */
    public static function create($content, $href = '#', $title = null){
        if(!is_scalar($content)){
            throw new QLinkTagException('The content of a link must be a scalar value');
        }
        $tag = new QLinkTag;
        $tag->_content = $content;
        $tag->_href = $href;
        $tag->_title = $title !== null ? $title : $href;
        return $tag;
    }
    
    public function __toString(){
        return '<a ' . $this->_styleOptionAttribute() . ' href="' . $this->_href . '" title="' . $this->_title . '">' . $this->_content . '</a>';
    }
}

class QLinkTagException extends QException{};
?>