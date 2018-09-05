<?php
// All the signals and slots system
class QObject extends QAbstractObject {
    
    protected
            /**
             * @var QStyleOption The style
             */
            $_styleOption = null,
            
            /**
             * @var QStyleOption The inner style
             */
            $_innerStyle = null,
            
            $_connections,
            
            $_attributes = array();
    
//    private static $_javascriptObject = null;
    
#if VERSION >= 1.0
    public static function tr($label){
        return $label;
    }
#endif
    
    public static function connect($sender, $signal, $receiver, $slot){
        $sender->_connections[$signal] = array($receiver, $slot);
    }
    
//    public function __construct(){
//        parent::__construct();
//        if(!self::$_javascriptObject)
//            self::$_javascriptObject = new QJavascriptObject();
//    }
    
    public function setStyle($style){
        if($style instanceof QStyleOption){
            $style = $style->build();
        }
        if(!is_string($style)){
            throw new QObjectSignatureExceptionException('Call to undefined function QObject::setStyle(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_innerStyle = $style;
        return $this;
    }
    
    public function setAttribute($attrName, $attrValue){
        $this->_attributes[$attrName] = $attrValue;
        return $this;
    }
    
    public function setStyleOption(QStyleOption $style){
        $this->_styleOption = $style;
        return $this;
    }
    
    public function setToolTip($tip){
        if(!is_string($tip)){
            throw new QObjectSignatureExceptionException('Call to undefined function QObject::setToolTip(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_attributes['title'] = $tip;
        return $this;
    }
    
    public function isLabel(){
        return $this instanceof QAbstractLabel;
    }
    
    public function isLayout(){
        return $this instanceof QAbstractLayout;
    }
    
    public function isTag(){
        return $this instanceof QAbstractTag;
    }
    
    public function isWidget(){
        return $this instanceof QAbstractWidget;
    }
    
    public function isWindow(){
        return $this instanceof QAbstractWindow;
    }
    
    protected function _styleOptionAttribute(){
        return $this->_attributes() . ' ' . ($this->_innerStyle ? 'style="' . $this->_innerStyle . '"' : '') . ($this->_styleOption ? ($this->_styleOption->styleType() != QStyleOption::StyleTag ? ($this->_styleOption->styleType() == QStyleOption::StyleId ? 'id' : 'class') . '="' . $this->_styleOption->styleName() . '"' : '') : '');
    }
    
    protected function _attributes(){
        $return = '';
        foreach($this->_attributes as $name => $value){
            $return .= ' ' . $name . '="' . $value . '"';
        }
        return $return;
    }


//    protected function _setUpConnections(){
//        foreach($this->_connections as $signal => $connection){
//            '$(\'#' . $this->_id . '\').' . $signal . '(function(){
//                
//            })';
//        }
//    }
}

class QObjectException extends QAbstractObjectException{}
class QObjectSignatureException extends QObjectException implements QSignatureException {}

?>