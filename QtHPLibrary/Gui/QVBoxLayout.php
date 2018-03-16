<?php

class QVBoxLayout extends QAbstractLayout {
    public function show(){
        echo '<div>';
        foreach($this->_children as $child){
            echo $child->show();
        }
        echo '</div>';
    }
}

?>