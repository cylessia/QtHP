<?php

class QListItemLayout extends QAbstractLayout {
            
    public function __construct($content = ''){
        parent::__construct();
        if($content){
            $this->appendChild(new QLabel($content));
        }
    }
    
    public function show(){
        echo '<li ' . $this->_styleOptionAttribute() . '>';
        foreach($this->_children as $child){
            $child->show();
        }
        echo '</li>';
    }
}

class QListLayoutItemException extends QAbstractElementException { protected $_type = 'QListLayoutItemException'; }

?>