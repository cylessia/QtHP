<?php

class QWindow extends QAbstractLayout {
    
    const Utf8 = 'utf-8',
          Latin1 = 'iso-8859-1';
    
    const XHtmlTransitional = 0,
          XHtmlStrict = 1,
          XHtml1 = 2;
          //Html5 = 3;
    
    protected $_charset = self::Utf8,
              $_documentType = self::XHtmlStrict,
              $_title = '',
              $_language = 'en';
    
    public function appendChild(QAbstractElement $element){
        while($element->_parent){
            $element = $element->_parent;
        }
        parent::appendChild($element);
        return $this;
    }
    
    public function charset(){
        return $this->_charset;
    }
    
    public function documentType(){
        return $this->_documentType;
    }
    
    public function language(){
        return $this->_language;
    }
    
    public function setCharset($charset){
        $this->_charset = $charset;
        return $this;
    }
    
    public function setDocumentType($type){
        if($type > self::Html5){
            throw new QAbstractWindowDocumentTypeException('Unknown document type');
        }
        $this->_documentType = $type;
        return $this;
    }
    
    public function setLanguage($language){
        $this->_language = $language;
        return $this;
    }
    
    public function setStyle(QStyle $style){
        $this->_styleOption = $style;
    }
    
    public function setWindowTitle($title){
        $this->_title = $title;
        return $this;
    }
    
    public function windowTitle(){
        return $this->_title;
    }
    
    public function show(){
        switch($this->_documentType){
            case self::XHtmlTransitional:
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
                break;
            default:
            case self::XHtmlStrict:
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                break;
            case self::XHtml1:
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
                    break;
        }
        echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $this->_language . '" lang="' . $this->_language . '"><head><title>' . $this->_title . '</title><meta http-equiv="content-type" content="text/html;charset=' . $this->_charset . '" />';
        if($this->_styleOption){
            echo '<style type="text/css">' . $this->_styleOption->build() . '</style>';
        }
        echo '</head><body>';
        foreach($this->_children as $child){
            echo  $child->show();
        }
        echo '</body></html>';
    }
    
    //////////////////////////////////////////////////////////////////////////////////////////
    
    public final function setParent(){
        throw new QAbstractWindowParentException('A QAbstractWindow can\'t have a parent');
    }
    
    public final function parent(){
        throw new QAbstractWindowParentException('A QAbstractWindow can\'t have a parent');
    }
}

class QWindowException extends QAbstractLayoutException{protected $_type = 'QWindowException';}
class QWindowDocumentTypeException extends QWindowException{ protected $_type = 'QWindowDocumentTypeException';};

?>