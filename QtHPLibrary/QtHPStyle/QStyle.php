<?php

class QStyle extends QAbstractObject {
    
    private $_counter = 0,
            $_styleOptions;
    
    protected $_type = 'QStyle';
    
    public function __construct(){
        $this->_styleOptions = new QMap('QStyleOption');
    }
    
    public function appendOption(QStyleOption $option){
        if(!$option->hasStyleName()){
            $option->setStyleName($this->_className());
        }
        $this->_styleOptions[$option->styleName(true)] = $option;
    }
    
    public function build(){
        $return = '';
        foreach ($this->_styleOptions as $option){
            if(!$option->styleName()){
                throw new QStyleException('Every style option must have a name');
            }
            $return .= $option->styleName(true) . '{' . $option->build() . '}';
        }
        return $return;
    }
    
    /**
     * Returns the options of the current style
     * @return QMap
     */
    public function options(){
        return $this->_styleOptions;
    }
    
    private function _className(){
        return '.qthp_cls_' . $this->_counter++;
    }
}

class QStyleException extends QException{}

?>