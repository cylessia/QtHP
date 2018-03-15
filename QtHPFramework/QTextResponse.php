<?php

class QTextResponse extends QAbstractObject implements QResponseInterface {
    
    private $_content;
    
    public function setContent($content) {
        $this->_content = $content;
        return $this;
    }
    
    public function content() {
        return $this->_content;
    }
}

?>