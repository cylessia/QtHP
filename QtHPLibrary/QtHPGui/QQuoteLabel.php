<?php

class QQuoteLabel extends QAbstractLabel {
    protected
            $_type = 'QQuoteLabel';
    
    public function show(){
        echo '<blockquote>' . $this->_text . '</blockquote>';
    }
}

class QQuoteLabelException extends QAbstractLabelException{ protected $_type = 'QDivLabelException';}
?>