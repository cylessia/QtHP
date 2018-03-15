<?php

class QRouter extends QAbstractObject {
    private $_routes,
            $_defaultRoute,
            $_baseUri;
    
    public function __construct($settings = null){
        if($settings !== null){
            if(!is_array($settings) || (is_object($settings) && $settings instanceof ArrayAccess)){
                throw new QRouterSettingsException('Not a valid setting object');
            }
            $this->_routes = $settings['routes'];
            if(!isset($settings['defaultRoute'])){
                if(!isset($this->_routes['default'])){
                    throw new QRouterDefaultRouteException('Default route is missing');
                }
                $this->_defaultRoute = 'default';
            } else {
                $this->setDefaultRoute($settings['defaultRoute']);
            }
            if(isset($settings['baseUri'])){
                $this->setBaseUri($settings['baseUri']);
            } else {
                $this->_baseUri = $_SERVER['SCRIPT_NAME'];
            }
        } else {
            // In that cas, set default routes
            $this->_routes = array(
                'default' => array(
                    'path' => ':controller/:action',
                    'constraint' => array(
                        'controller' => '[\w]+',
                        'action' => '([\w]+)?'
                    ),
                    'params' => array(
                        'controller' => 'index',
                        'action' => 'index'
                    )
                )
            );
            $this->_defaultRoute = 'default';
            $this->_baseUri = substr($_SERVER['SCRIPT_NAME'], 0, -16);
        }
    }
    
    public function baseUri(){
        return $this->_baseUri;
    }
    
    public function defaultRoute(){
        return $this->get($this->_defaultRoute);
    }
    
    public function get($name, $params = array()){
        return new QRoute($name, $this->_routes[$name], $params);
    }
    
    /**
     * Check if a route match, if none match, return the default route
     * @return QRoute
     */
    public function match(){
        $uri = $_SERVER['REQUEST_URI'];
        $name = $this->_baseUri;
        $i = strlen($_SERVER['REQUEST_URI']);
        $j = strlen($this->_baseUri);
        $j = $j > $i ? $i : $j;
        for($i = 0; $i < $j && $uri{$i} == $name{$i}; $i++);
        $request = substr($_SERVER['REQUEST_URI'], $i);
        if(($p = strpos($request, '?')) !== false){
            $request = substr($request, 0, $p);
        }
        $request = trim($request, '/');
        if(!$request)
            return $this->get($this->_defaultRoute);
        foreach($this->_routes as $name => $route){
            $path = array();
            foreach(explode('/', $route['path']) as $pathPart){
                if($pathPart{0} == ':'){
                    if(isset($route['constraint'][($pathPart = substr($pathPart, 1))])){
                        $path[] = '(?<' . $pathPart . '>' . (is_array($route['constraint'][$pathPart])?implode('|',$route['constraint'][$pathPart]):$route['constraint'][$pathPart]) . ')';
                    } else {
                        $path[] = '(?<' . $pathPart . '>[^/]*)';
                    }
                } else {
                    $path[] = $pathPart;
                }
            }
            if(preg_match('#^' . str_replace('#', '\#', implode('/', $path)) . '/?#', $request . '/', $matches)){
                $params = array();
                foreach($matches as $index => $match){
                    if(!is_int($index) && $match != null){
                        $params[$index] = $match;
                    }
                }
                return $this->get($name, $params);
            }
        }
        throw new QRouterMatchException('No matched route');
    }
    
    public function setBaseUri($uri){
        if(!is_string($uri)){
            throw new QRouterBaseUriException('Not a valid base uri');
        }
        $this->_baseUri = $uri;
    }
    
    public function setDefaultRoute($name){
        if(!is_string($name) && !is_numeric($name)){
            throw new QRouterDefaultRouteNameException('Not a valid route name');
        }
        $this->_defaultRoute = $name;
    }
}

class QRouterException extends QAbstractObjectException{}
class QRouterSettingsException extends QRouterException{}
class QRouterDefaultRouteException extends QRouterException{}
class QRouterMatchException extends QRouterException {}
class QRouterBaseUriException extends QRouterException {}

?>