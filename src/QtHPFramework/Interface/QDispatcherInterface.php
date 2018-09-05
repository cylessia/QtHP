<?php

interface QDispatcherInterface {
    public static function current();
    public static function setCurrent(QDispatcherInterface $dispatcher);
    
    public function actionName();
    public function actionSuffix();
    public function controllerName();
    public function controllerSuffix();
    public function defaultActionName();
    public function defaultControllerName();
    public function defaultNamespace();
    public function dispatch();
    public function forward($controller, $action = null);
    public function isFinished();
    public function isForwarded();
    public function setActionName($name);
    public function setActionSuffix($name);
    public function setControllerName($name);
    public function setControllerSuffix($name);
    public function setDefaultActionName($name);
    public function setDefaultControllerName($name);
    public function setDefaultNamespace($name);
}

?>