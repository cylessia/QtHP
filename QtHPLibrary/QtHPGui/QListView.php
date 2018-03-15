<?php

class QListView extends QAbstractItemView {
    
    public function show(){
        echo '<ul' . $this->_styleOptionAttribute() . '>';
        while($this->_model->fetch()){
            echo '<li>' . $this->_model->data(0) . '</li>';
        }
        echo '</ul>';
    }
    
}

?>