<?php

abstract class QMvcForm extends QFormLayout {
    public function __construct() {
        parent::__construct();
        $this->init();
    }
    
    abstract public function init();
    
    public function setEntity($entity){
        if(is_object($entity)){
            foreach ($this->_elements as $e){
                $e->setData($entity->{$e->name()});
            }
        } else if(is_array($entity)){
            foreach ($this->_elements as $e){
                $e->setData($entity[$e->name()]);
            }
        } else {
            throw new QMvcFormSignatureException('Call to undefined QMvcForm::setEntity(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return $this;
    }
    
    public function populate($entity){
        if(is_object($entity)){
            foreach($this->_elements as $e){
                $entity->{$e->name()} = $e->data();
            }
        } else if(is_array($entity)) {
            foreach($this->_elements as $e){
                $entity[$e->name()] = $e->data();
            }
        } else {
            throw new QMvcFormSignatureException('Call to undefined QMvcForm::populate(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return $this;
    }
}

class QMvcFormException extends QFormLayoutException {}
class QMvcFormSignatureException extends QMvcFormException implements QSignatureException {}


?>