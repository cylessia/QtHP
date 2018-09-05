<?php

class QParagraphLabel extends QAbstractLabel {
    
    public function show(){
        echo '<p ' . $this->_styleOptionAttribute() . '>' . $this->_text . '</p>';
    }
}

class QParagraphLabelException extends QAbstractLabelException{ protected $_type = 'QDivLabelException';}
?>