<?php

class QEventManager extends QAbstractObject implements QEventManagerInterface {
    
    private $_events = array();
    
    public function attach($event, $cb) {
        if(!is_string($event) || !$cb instanceof Closure){
            throw new QEventManagerSignatureException('Call to undefined function QEventManager::attach(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_events[$event] = $cb;
    }
    
    public function detach($event){
        if(!is_string($event)){
            throw new QEventManagerSignatureException('Call to undefined function QEventManager::detach(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        unset($this->_events[$event]);
    }
    
    public function detachAll() {
        $this->_events = array();
    }
    
    public function fire($event, $params = array()) {
        if(!is_string($event) ||!is_array($params)){
            throw new QEventManagerSignatureException('Call to undefined function QEventManager::fire(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if(isset($this->_events[$event])) {
            call_user_func_array($this->_events[$event], $params);
            return true;
        } else {
            return false;
        }
    }
}

class QEventManagerException extends QAbstractObjectException {}
class QEventManagerSignatureException extends QEventManagerException implements QSignatureException {}

?>