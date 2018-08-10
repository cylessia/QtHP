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
            if($result === null){
                $result = new QResponseJson;
                $result->setData([]);
            }
            $result->send();
        } else {
            throw new QMicroWebServiceException('No route found');
        }
    }
}

class QMicroWebServiceException extends QAbstractObjectException {}