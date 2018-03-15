<?php

class QDependencyInjector extends QAbstractObject implements QDependencyInjectorInterface {
    
    private $_services,
            $_shared;
    
    public function __construct() {
        $this->_services = new QMap;
        $this->_shared = new QMap;
    }
    
    public function attempt($name, $def, $shared = false) {
        if($this->_services->has($name)){
            throw new QDependencyInjectorAttemptException('Service "' . $name . '" is already set');
        }
        $this->_services->insert($name, new QDependencyInjectorService($name, $def, $shared));
        return $this;
    }
    
    public function get($name){
        try {
            return $this->_services->value($name)->resolve();
        } catch(QMapException $e){
            throw new QDependencyInjectorUnexistingServiceException('Service "' . $name . '" doesn\'t exists');
        }
    }
    
    public function getShared($name) {
        try {
            return $this->_shared->value($name);
        } catch(QMapException $e){
            $this->_shared->insert($name, $this->get($name));
            return $this->_shared->value($name);
        }
    }
    
    public function has($name){
        return $this->_services->has($name);
    }
    
    public function remove($name) {
        try {
            $this->_services->clear($name);
        } catch(QMapException $e){
            throw new QDependencyInjectorUnexistingServiceException('Service "' . $name . '" doesn\'t exists');
        }
        return $this;
    }
    
    public function set($name, $def, $shared = false){
        if(!is_numeric($name) && !is_string($name)){
            throw new QDependencyInjectorSignatureException('Call to undefined function QDependencyInjector::set(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($def instanceof QDependencyInjectorServiceInterface){
            $this->_services->insert($name, $def);
        } else {
            $this->_services->insert($name, new QDependencyInjectorService($name, $def, $shared));
        }
        return $this;
    }
    
    public function setShared($name, $def){
        return $this->set($name, $def, true);
    }
    
    public function offsetExists($offset) {
        return $this->has($offset);
    }
    
    public function offsetGet($offset) {
        return $this->get($offset);
    }
    
    public function offsetSet($offset, $value) {
        $this->setShared($offset, $value);
    }
    
    public function offsetUnset($offset) {
        $this->remove($offset);
    }
}

class QDependencyInjectorException extends QAbstractObjectException {}
class QDependencyInjectorSignatureException extends QDependencyInjectorException implements QSignatureException {}
class QDependencyInjectorAttemptException extends QDependencyInjectorException {}
class QDependencyInjectorUnexistingServiceException extends QDependencyInjectorException {}
class QDependencyInjectorCallbackException extends QDependencyInjectorException {}
?>