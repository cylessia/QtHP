<?php

class QController extends QAbstractObject implements QDependencyInjectionInterface {
    protected $di;
    
    public final function __construct(QDependencyInjectorInterface $di) {
        $this->di = $di;
        if(!$this->view->di())
            $this->view->setDi($this->di);
    }
    
    public function setDi(QDependencyInjectorInterface $di){
        $this->di = $di;
    }
    
    public function di(){
        return $this->di;
    }
    
    public function __get($service){
        return $this->di->getShared($service);
    }
}

?>