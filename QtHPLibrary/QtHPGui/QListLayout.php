<?php

class QListLayout extends QAbstractLayout {
    public function __construct(){
        QAbstractElement::__construct();
        $this->_children = new QList('QListItemLayout');
    }
    
    public function appendChild($child) {
        if(($child instanceof ArrayAccess) || is_array($child)){
            foreach ($child as $item) {
                $this->appendChild($item);
            }
        } else if($child instanceof QListItemLayout){
            parent::appendChild($child);
        } else if(is_scalar($child) || method_exists($child, '__toString')) {
            parent::appendChild(new QListItemLayout($child));
        } else {
            parent::appendChild(($tmp = new QListItemLayout()));
            $tmp->appendChild($child);
        }
    }
    
    public function show(){
        echo '<ul ' . $this->_styleOptionAttribute() . '>';
        foreach($this->_children as $child){
            $child->show();
        }
        echo '</ul>';
    }
}

class QListLayoutException extends QAbstractLayoutException {}
class QListLayoutAppendChildException extends QListLayoutException {}

?>