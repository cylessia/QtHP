<?php

class QDependencyInjectorService extends QAbstractObject {
    
    private $_def,
            $_name,
            $_shared;
    private static $_instance = array();
            public static $_test;
    
    public function __construct($name, $def, $shared) {
        if(!is_string($name) && !is_numeric($name)){
            throw new QDependencyInjectorServiceSignatureException('Call to undefined function QDependencyInjectorService::__construct(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_name = $name;
        $this->setDefinition($def);
        $this->setShared($shared);
    }

        public function getDefinition() {
        return $this->_def;
    }
    
    public function getName() {
        return $this->_name;
    }
    
    public function isShared() {
        return $this->_shared;
    }
    
    public function resolve() {
        if($this->_shared && isset(self::$_instance[$this->_name])){
            return self::$_instance[$this->_name];
        }
        if($this->_def instanceof Closure){
            $c = $this->_def;
            return ($this->_shared) ? (self::$_instance[$this->_name] = $c()) : $c();
        } else if(is_object($this->_def)){
            return ($this->_shared) ? (self::$_instance[$this->_name] = $this->_def) : $this->_def;
        } else if(is_string($this->_def)){
            if(class_exists($this->_def)){
                return ($this->_shared) ? (self::$_instance[$this->_name] = new $this->_def) : new $this->_def;
            } else if(function_exists ($this->_def)){
                $c = $this->_def;
                return ($this->_shared) ? (self::$_instance[$this->_name] = $c()) : $c();
            }
        }
        throw new QDependencyInjectorServiceDefinitionException('Service "' . $this->_name . '" is not a valid service');
    }
    
    public function setDefinition($def) {
        $this->_def = $def;
    }
    
    public function setShared($shared) {
        if(!is_bool($shared)){
            throw new QDependencyInjectorServiceSignatureException('Call to undefined function QDependencyInjectorService::setShared(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_shared = $shared;
    }
}

class QDependencyInjectorServiceException extends QAbstractObjectException {}
class QDependencyInjectorServiceSignatureException extends QDependencyInjectorServiceException implements QSignatureException{}
class QDependencyInjectorServiceDefinitionException extends QDependencyInjectorServiceException {}

?>