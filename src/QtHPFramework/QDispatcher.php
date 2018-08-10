<?php

class QDispatcher extends QAbstractObject implements QDependencyInjectionInterface, QDispatcherInterface {

    private $_actionName,
            $_actionSuffix = 'Action',
            $_controllerName,
            $_controllerSuffix = 'Controller',
            $_defaultActionName = 'index',
            $_defaultControllerName = 'index',
            $_defaultNamespace = '',
            $_dependencyInjector,
            $_dispatched = array(),
            $_finished = true,
            $_forwarded,
            $_eventManager,
            $_returnedValue;

    private static $_current;

    const EventBeforeExecuteRoute = 'dispatcher:beforeExecuteRoute',
          EventAfterExecuteRoute = 'dispatcher:afterExecuteRoute',
          EventBeforeThrowException = 'dispatcher:beforeThrowException',
          EventBeforeDispatch = 'application:beforeDispatch',
          EventAfterDispatch = 'application:afterDispatch',

          ExceptionControllerFileNotFound = 1,
          ExceptionControllerClassNotFound = 2,
          ExceptionActionNotFound = 3,
          ExceptionInterfaceNotFound = 4;

  public function __construct() {
      if(!self::$_current)
          self::$_current = $this;
  }

    public function actionName() {
        return $this->_actionName;
    }

    public function actionSuffix() {
        return $this->_actionSuffix;
    }

    public function controllerName() {
        return $this->_controllerName;
    }

    public function controllerSuffix() {
        return $this->_controllerSuffix;
    }

    public static function current(){
        return self::$_current;
    }

    public function defaultActionName() {
        return $this->_defaultActionName;
    }

    public function defaultControllerName() {
        return $this->_defaultControllerName;
    }

    public function defaultNamespace(){
        return $this->_defaultNamespace;
    }

    public function di(){
        return $this->_dependencyInjector;
    }

    public function dispatch() {
        if($this->_eventManager)
            $this->_eventManager->fire(self::EventBeforeDispatch, array($this, $this));
        $loops = -1;
        do {
            try {
                $this->_finished = true;
                $this->_dispatched[] = array(
                    'controller' => $cName = ($this->_defaultNamespace ? $this->_defaultNamespace . '\\' : '') . ucfirst($this->_controllerName) . $this->_controllerSuffix,
                    'action' => $mName = $this->_actionName . $this->_actionSuffix
                );
                if(++$loops > 32){
                    throw new QDispatcherInfiniteDispatchException('Too many dispatch loop, maybe a cyclic routing problem happens', $this->_dispatched);
                }
                if(!class_exists($cName)){
                    throw new QDispatcherControllerClassNotFoundException('Controller "' . $cName . '" doesn\'t exists', $this->_dispatched);
                }
                $c = new $cName($this->_dependencyInjector);
                if(!$c instanceof QDependencyInjectionInterface){
                    throw new QDispatcherControllerDIException('A controller must implements QDependencyInjectionInterface', $this->_dispatched);
                }
                if(!method_exists($c, $mName)){
                    throw new QDispatcherControllerActionNotFoundException('Controller doesn\'t have action "' . $this->_actionName . '"', $this->_dispatched);
                }
                if($this->_eventManager)
                    $this->_eventManager->fire(self::EventBeforeExecuteRoute, array($this, $c));
                if(method_exists($c, 'beforeExecuteRoute')){
                    $c->beforeExecuteRoute($this);
                }
                $this->_returnedValue = $c->{$mName}();
                if($this->_eventManager)
                    $this->_eventManager->fire(self::EventAfterExecuteRoute, array($this, $c));
                if(method_exists($c, 'afterExecuteRoute')){
                    $c->afterExecuteRoute($this);
                }
            } catch (Exception $e){
                if(!($this->_eventManager && $this->_eventManager->fire(self::EventBeforeThrowException, array($this, $e)))){
                    throw $e;
                }
            }
        } while(!$this->_finished);
        if($this->_eventManager)
            $this->_eventManager->fire(self::EventAfterDispatch, array($this, $this));
        return $c;
    }

    public function dispatched(){
        return $this->_dispatched;
    }

    public function eventManager(){
        return $this->_eventManager;
    }

    public function forward($controller, $action = null) {
        if($action == null){
            if(is_array($controller)){
                if(isset($controller['controller'])){
                    $this->_controllerName = $controller['controller'];
                }
                if(isset($controller['action'])){
                    $this->_actionName = $controller['action'];
                }
            } else if(is_string($controller)){
                if(strpos($controller, '/')){
                    $tmp = explode('/', $controller);
                    if($tmp[0] !== ''){
                        $this->_controllerName = $tmp[0];
                    }
                    if($tmp[1] !== ''){
                        $this->_actionName = $tmp[1];
                    }
                } else {
                    $this->_actionName = $controller;
                }
            }
        } else if(is_string($action) && is_string($controller)){
            $this->_controllerName = $controller;
            $this->_actionName = $action;
        } else {
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::forward(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_finished = false;
        $this->_forwarded = true;
    }

    public function isFinished() {
        return $this->_finished;
    }

    public function isForwarded() {
        return $this->_forwarded;
    }

    public function returnedValue(){
        return $this->_returnedValue;
    }

    public function setActionName($name) {
        if(!is_string($name)){
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::setActionName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_actionName = $name;
    }

    public function setActionSuffix($name) {
        if(!is_string($name)){
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::setActionSuffix(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_actionSuffix = $name;
    }

    public function setControllerName($name) {
        if(!is_string($name)){
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::setControllerName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_controllerName = $name;
    }

    public function setControllerSuffix($name) {
        if(!is_string($name)){
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::setControllerSuffix(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_controllerSuffix = $name;
    }

    public static function setCurrent(\QDispatcherInterface $dispatcher) {
        self::$_current = $dispatcher;
    }

    public function setDefaultActionName($name) {
        if(!is_string($name)){
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::setDefaultActionName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_defaultActionName = $name;
    }

    public function setDefaultControllerName($name) {
        if(!is_string($name)){
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::setDefaultControllerName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_defaultControllerName = $name;
    }

    public function setDefaultNamespace($name) {
        if(!is_string($name)){
            throw new QDispatcherSignatureException('Call to undefined function QDispatcher::setDefaultNamespace(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_defaultNamespace = $name;
    }

    public function setDi(QDependencyInjectorInterface $di) {
        $this->_dependencyInjector = $di;
    }

    public function setEventManager(QEventManagerInterface $em){
        $this->_eventManager = $em;
    }
}

class QDispatcherException extends QAbstractObjectException {
    private $_loop;
    public function __construct($message, $loop) {
        parent::__construct($message);
        $this->_loop = $loop;
    }

    public function getLoop(){
        return $this->_loop;
    }


}

class QDispatcherDispatchException extends QDispatcherException {
    public function __construct($message, $loop) {
        parent::__construct($message, $loop);
        $this->code = static::$_code;
    }
}

class QDispatcherSignatureException extends QDispatcherException implements QSignatureException {}
class QDispatcherInfiniteDispatchException extends QDispatcherException {}

class QDispatcherControllerFileNotFoundException extends QDispatcherDispatchException {protected static $_code = QDispatcher::ExceptionControllerFileNotFound;}
class QDispatcherControllerClassNotFoundException extends QDispatcherDispatchException {protected static $_code = QDispatcher::ExceptionControllerClassNotFound;}
class QDispatcherControllerActionNotFoundException extends QDispatcherDispatchException {protected static $_code = QDispatcher::ExceptionActionNotFound;}
class QDispatcherControllerDIException extends QDispatcherDispatchException {protected static $_code = QDispatcher::ExceptionInterfaceNotFound;}

?>