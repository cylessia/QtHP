<?php

class QButtonLabel extends QAbstractLabel {
    public function show(){
        echo '<button ' . $this->_styleOptionAttribute() . '>' . $this->_text . '</button>';
    }
}
?>