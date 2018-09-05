<?php

abstract class QAbstractElement extends QObject {
    /**
     * 
     * @var QAbstractLayout
     */
    protected $_parent = null;
    
    public function setParent(QAbstractLayout $parent){
        $parent->appendChild($this);
        return $this;
    }
    
    public function parent(){
        return  $this->_parent;
    }
    
    abstract public function show();
}

class QAbstractElementException extends QObjectException {}
?>