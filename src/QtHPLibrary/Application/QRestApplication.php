<?php

define('QTHP_APP_PATH', dirname(QTHP_PATH) . '/');
define('QTHP_PUBLIC_PATH', dirname(dirname(QTHP_LIBRARY_PATH)) . '/public/');

class QRestApplication extends QAbstractObject implements QDependencyInjectionInterface {

    private

    $_dependencyInjector;

    private static

    $_self;

    public function __construct(QDependencyInjectorInterface $di = null) {
        if(self::$_self != null)
            throw new QRestApplicationException('RestQApplication can be instanciated only once');
        $this->setDi($di !== null ? $di : new QDependencyInjector);
        if(!QAutoloader::baseDir()){
            QAutoloader::setBaseDir(QDir::currentPath());
        }
        self::$_self = $this;
    }

    public function di(){
        return $this->_dependencyInjector;
    }

    public function setDi(QDependencyInjectorInterface $di){
        $this->_dependencyInjector = $di;
    }

    public function exec(){
        try {
            $this->_dependencyInjector->attempt('router', 'QRouter', true);
        } catch (Exception $ex) {}
        try {
            $this->_dependencyInjector->attempt('dispatcher', function(){
                $d = new QDispatcher;
                $d->setControllerSuffix('Resource');
                $d->setActionSuffix('Method');
                return $d;
            }, true);
        } catch(QDependencyInjectorAttemptException $e){}

        if(!($router = $this->_dependencyInjector->getShared('router')) instanceof QRouter){
            throw new QApplicationRouterException('Not a valid router');
        }
        if(!($dispatcher = $this->_dependencyInjector->getShared('dispatcher')) instanceof QDispatcherInterface){
            throw new QApplicationDispatcherException('The application dispatcher must implement QDispatcherInterface');
        }
        if(!$dispatcher->di()){
            $dispatcher->setDi($this->_dependencyInjector);
        }

        $this->_dependencyInjector->set('route', $route = $router->match());
        $dispatcher->setControllerName($route->param('resource'));
        $dispatcher->setActionName($route->param('action', strtolower($router->method())));
        if(!($controller = $dispatcher->dispatch()) instanceof QController){
            throw new QApplicationControllerException('Not a valid controller');
        }
    }
}

class QRestApplicationException extends QAbstractObjectException {}
class QApplicationDispatcherException extends QRestApplicationException {}
class QApplicationRouterException extends QRestApplicationException {}

?>