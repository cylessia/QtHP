<?php

class QMicroWebService extends QAbstractObject {
    private $_di;

    public function __construct(QDependencyInjectorInterface $di) {
        parent::__construct();
        $this->_di = $di;
    }

    public final function exec(){
        $router = $this->_di->getShared('router');
        if(method_exists($this, ($action = $router->match()->param('call')))){
            $result = $this->$action();
        } else {
            throw new QMicroWebServiceExcecption('No route found');
        }
        echo $result;
    }
}

class QMicroWebServiceException extends QAbstractObjectException {}