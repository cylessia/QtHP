<?php

define('QTHP_APP_PATH', dirname(QTHP_PATH) . '/');
define('QTHP_PUBLIC_PATH', dirname(dirname(QTHP_LIBRARY_PATH)) . '/public/');

/**
 * Main QtHP project class
 */
class QApplication extends QAbstractObject implements QDependencyInjectionInterface {
    const
        EventBoot = 'application:boot',
        EventNoRouteMatched = 'application::noRouteMatched',
        EventBeforeHandleRequest = 'application:beforeHandleRquest',
        EventAfterHandleRequest = 'application:afterHandleRquest';
        
    private 
            /**
             * @access private
             * @var string The name of the application
             */
            $_applicationName,
            
            /**
             * @access private
             * @var string The version of the application
             */
            $_applicationVersion,
            
            /**
             * @access private
             * @var QDependencyInjector
             */
            $_dependencyInjector,
            
            /**
             * @access private
             * @var QEventManager
             */
            $_eventManager;
            
    /**
     * @access private
     * @static
     * @var QApplication The application instance
     */
    private static $_self = null;
    
    /**
     * Initializes an application
     */
    public function __construct(QDependencyInjectorInterface $di = null) {
        if(self::$_self != null)
            throw new QApplicationException('QApplication can be instanciated only once');
        if($di !== null)
            $this->setDi($di);
        if(!QAutoloader::baseDir()){
            QAutoloader::setBaseDir(QTHP_APP_PATH);
        }
        self::$_self = $this;
    }
    
    /**
     * Returns the name of the application
     * @return string The name of the application
     */
    public function applicationName(){
        return $this->_applicationName;
    }
    
    /**
     * Returns the version of the application
     * @return string The version of the application
     */
    public function applicationVersion(){
        return $this->_applicationVersion;
    }
    
    /**
     * Returns a reference to the current instance of the application
     * @return QApplication The application object
     */
    public static function &instance(){
        return self::$self;
    }
    
    /**
     * Sets the name of the application
     * @throws QApplicationException
     * @param string The name of the application
     */
    public function setApplicationName($name){
        if(!is_string($name))
            throw new QApplicationException('"' . $name . '" is not a valid name');
        $this->_applicationName = $name;
        return $this;
    }
    
    /**
     * Sets the version of the application
     * @throws QApplicationException
     * @param string The version of the application
     */
    public function setApplicationVersion($version){
        if(!is_numeric($version) && !preg_match('/^([\d]+.)*[\d]+$/', $version))
            throw new QApplicationException('"' . $version . '" is not a valid syntax');
        $this->_applicationVersion = $version;
        return $this;
    }
    
    /**
     * Returns the dependency injector object
     * @return QDependencyInjectorInterface
     */
    public function di(){
        return $this->_dependencyInjector;
    }
    
    public function eventManager(){
        return $this->_eventManager;
    }
    
    /**
     * Execute the application
     */
    public function exec(){
        if($this->_eventManager)
            $this->_eventManager->fire (self::EventBoot, array($this));
        try {
            $this->_dependencyInjector->attempt('router', 'QRouter', true);
        } catch(QDependencyInjectorAttemptException $e){
        }
        try {
            $this->_dependencyInjector->attempt('dispatcher', 'QDispatcher', true);
        } catch(QDependencyInjectorAttemptException $e){
        }
        
        if(!($router = $this->_dependencyInjector->getShared('router')) instanceof QRouter){
            throw new QApplicationRouterException('Not a valid router');
        }
        if(!($dispatcher = $this->_dependencyInjector->getShared('dispatcher')) instanceof QDispatcherInterface){
            throw new QApplicationDispatcherException('The application dispatcher must implement QDispatcherInterface');
        }
        if(!$dispatcher->di()){
            $dispatcher->setDi($this->_dependencyInjector);
        }
        
        try {
            $route = $router->match();
            $dispatcher->setControllerName($route->param('controller'));
            $dispatcher->setActionName($route->param('action'));
        } catch(QRouterMatchException $e){
            if($this->_eventManager)
                $this->_eventManager->fire(self::EventNoRouteMatched, array($dispatcher, $this));
        }
        
        if($this->_eventManager)
            $this->_eventManager->fire(self::EventBeforeHandleRequest, array($this, $dispatcher));
        if(!($controller = $dispatcher->dispatch()) instanceof QController){
            throw new QApplicationControllerException('Not a valid controller');
        }
        if($this->_eventManager)
            $this->_eventManager->fire(self::EventAfterHandleRequest, array($this, $dispatcher));
        if(!($response = $dispatcher->returnedValue())){
            $response = $this->_dependencyInjector->getShared('view');
            if($response->baseDir() == null){
                $response->setBaseDir(QTHP_APP_PATH . 'views/');
            }
            if(!$response->view()){
                $response->setView($dispatcher->controllerName() . '/' . $dispatcher->actionName() . '.phtml');
            }
            $response->show();
        } else if($response instanceof QResponseInterface) {
            echo $response->content();
        }
    }
    
    /**
     * Sets the dependency injector
     * @param QDependencyInjectorInterface $di
     */
    public function setDi(QDependencyInjectorInterface $di){
        $this->_dependencyInjector = $di;
    }
    
    public function setEventManager(QEventManager $em){
        $this->_eventManager = $em;
    }
}

class QApplicationException extends QAbstractObjectException {}
class QApplicationRouterException extends QApplicationException {}
class QApplicationDispatcherException extends QApplicationException {}
class QApplicationControllerFileNotFoundException extends QApplicationException {}
class QApplicationControllerClassNotFoundException extends QApplicationException {}
class QApplicationControllerInterfaceException extends QApplicationException {}
class QApplicationActionNotFoundException extends QApplicationException {}
class QApplicationControllerException extends QApplicationException {}
?>