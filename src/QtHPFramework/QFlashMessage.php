<?php

class QFlashMessage extends QAbstractObject {
    
    private $_msg = array(),
            $_config = array();
    
    public function __construct($config){
        $this->_config = $config;
        $c = new QCache('qthp_flash');
        try {
            $this->_msg = $c->recover('msg');
        } catch(QCacheValueException $e){}
    }
    
    public function __destruct(){
        if(count($this->_msg)){
            $cache = new QCache('qthp_flash');
            $cache->save('msg', $this->_msg);
        }
    }
    
    public function error($message){
        $this->_msg[] = array('error', $message);
    }
    
    public function info($message){
        $this->_msg[] = array('info', $message);
    }
    
    public function success($message){
        $this->_msg[] = array('success', $message);
    }
    
    public function warning($message){
        $this->_msg[] = array('warning', $message);
    }
    
    public function output(){
        $cls = array();
        foreach(array('error', 'info', 'success', 'warning') as $class){
            $cls[$class] = isset($this->_config[$class]) ? $this->_config[$class] : '';
        }
        foreach($this->_msg as &$msg){
            echo '<div class="' . $cls[$msg[0]] . '">' . $msg[1] . '</div>';
        }
        // Erase all messages
        $this->_msg = array();
        $cache = new QCache('qthp_flash');
        $cache->clear('msg');
    }
}

?>