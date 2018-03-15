<?php

abstract class QAbstractLabel extends QAbstractElement {
    
    protected $_text;
    
    public function __construct($text = null){
        if($text){
            $this->setText($text);
        }
    }
    
    public function setText($text){
        $type = $text instanceof QAbstractObject ? $text->type() : gettype($text);
        if(!is_scalar($text) && !$text instanceof QAbstractTag && !(class_exists($type) && !method_exists($text, '__toString'))){
            throw new QAbstractLabelException('Class of type ' . $this->type() . ' can\'t contain a "' . $type . '" because it is neither a scalar value neither an object implementing the __toString magic method. ');
        }
        $this->_text = (string)$text;
        return $this;
    }
    
    public function append($text){
        $type = $text instanceof QAbstractObject ? $text->type() : gettype($text);
        if(!is_scalar($text) && !$text instanceof QAbstractTag && !(class_exists($type) && !method_exists($text, '__toString'))){
            throw new QAbstractLabelException('Class of type ' . $this->type() . ' can\'t contain a "' . $type . '" because it is neither a scalar value neither an object implementing the __toString magic method. ');
        }
        $this->_text .= $text;
        return $this;
    }
    
    public function prepend($text){
        $type = $text instanceof QAbstractObject ? $text->type() : gettype($text);
        if(!is_scalar($text) && !$text instanceof QAbstractTag && !(class_exists($type) && !method_exists($text, '__toString'))){
            throw new QAbstractLabelException('Class of type ' . $this->type() . ' can\'t contain a "' . $type . '" because it is neither a scalar value neither an object implementing the __toString magic method. ');
        }
        $this->_text = $text . $this->_text;
        return $this;
    }
}

class QAbstractLabelException extends QAbstractElementException { protected $_type = 'QAbstractLabelException';}

?>