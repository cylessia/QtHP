<?php

class QPreformatedLabel extends QAbstractLabel {
    
    public function show(){
        echo '<pre>' . $this->_text . '</pre>';
    }
}

?>