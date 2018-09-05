<?php

class QGroupLayout extends QAbstractElement {
    
    private $_title;
    
    public function __construct(){
        $this->_name = 'qthp_group_' . (++self::$_formId);
        parent::__construct();
    }
    
    public function setTitle($title){
        if(!is_scalar($title)){
            throw new QGroupLayoutException('A group layout must be a scalar value');
        }
        $this->_title = $title;
        return $this;
    }
    
    public function show(){
        echo '<fieldset' . $this->_styleOptionAttribute() . '>';
        if($this->_title != ''){
            echo '<legend>' . $this->_title . '</legend>';
        }
        foreach($this->_elements as $element){
            $element->show();
        }
        echo '</fieldset>';
    }
    
    public function title(){
        return $this->_title;
    }
}

class QGroupLayoutException extends QAbstractElementException { protected $_type = 'QGroupLayoutException'; }

?>