<?php

abstract class QAbstractLayout extends QAbstractElement {
    
    protected $_children = null;
    
    public function __construct(){
        $this->_children = new QList('QAbstractElement');
    }
    
    public function appendChild($child){
        if(!($child instanceof QAbstractElement)){
            throw new QAbstractLayoutAppendChildException('Not a instance of QAbstractElement');
        }
        if($child->_parent !== $this){
            if($child->_parent){
                $child->_parent->_children->removeOne($child);
            }
            $child->_parent = $this;
            $this->_children->append($child);
        }
        return $this;
    }
    
    public function removeChild($child){
        if($child->_parent !== $this){
            return;
        }
        $child->_parent = null;
        $this->_children->removeOne($child);
        return $this;
    }
}

class QAbstractLayoutException extends QAbstractElementException{}
class QAbstractLayoutAppendChildException extends QAbstractLayoutException {}
?>