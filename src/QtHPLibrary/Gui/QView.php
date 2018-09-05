<?php

class QView extends QAbstractObject {
    private $_vars = null,
            $_view = '';
    
    public function __construct($view = ''){
        if($view){
            $this->setView($view);
        }
        $this->_vars = new QMap;
    }
    
    public function set($key, $value = null){
        $this->_vars->insert($key, $value);
        return $this;
    }
    
    public function exists($var){
        return isset($this->_vars[$var]);
        }
    
    public function setView($view){
        if(!is_file($view)){
            throw new QViewPathException('"' . $view . '" is not a valid view path');
        }
        $this->_view = $view;
        return $this;
    }

    public function show(){
        foreach($this->_vars as $key => $value){
            $$key = $value;
        }
        include $this->_view;
    }
    
    public function vars(){
        return $this->_vars;
    }

    public function view(){
        return $this->_view;
    }
    
}

class QViewException extends QAbstractObjectException {}
class QViewPathException extends QViewException {}
class QViewVarException extends QViewException {}
?>