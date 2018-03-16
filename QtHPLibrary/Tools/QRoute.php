<?php

class QRoute {
    
    protected static $_urlDelimiter = '/',
                     $_urlVariable = ':';
    
    private $_config,
            $_name,
            $_params;
    
    public function __construct($name, $config, $params = array()){
        if(!is_string($name) && !is_numeric($name)){
            throw new QRouteNameException('The name of a route must be a string');
        }
        if(!is_array($config) || !isset($config['path'])){
            throw new QRouteConfigException('Route\'s config must be array and contain at least the path');
        }
        if($params !== null && !is_array($params)){
            throw new QRouteParamsException('Params must be an array');
        }
        $this->_name = $name;
        $this->_config = $config;
        $this->_params = isset($config['params']) ? array_merge($config['params'], $params) : $params;
//        if(!isset($this->_config['constraint'])){
//            $this->_config['constraint'] = array();
//        }
    }
    
    public function setParam($param, $value = null){
        if(is_array($param) || (is_object($param) && $param instanceof ArrayAccess)){
            foreach($param as $k => $v){
                if($v !== null){
                    $this->_params[$param] = $value;
                }
            }
        } else if($value !== null){
            $this->_params[$param] = $value;
        }
    }
    
    public function param($param){
        return isset($this->_params[$param]) ? $this->_params[$param] : null;
    }
    
    public function toString(){
        $path = array();
//        $allowNull = true;
        foreach(explode('/', $this->_config['path']) as $pathPart){
            if($pathPart{0} == ':'){
                if(isset($this->_params[($substr = substr($pathPart, 1))])){
                    $path[] = $this->_params[$substr];
                } else {
                    throw new QRouteMissingParamException('The parameter ' . $substr . ' is needed by the route ' . $this->_name);
                }
//                if(!isset($this->_params[($substr = substr($pathPart, 1))]) && !$allowNull){
//                    throw new QRouteMissingParamException('Route cannot have ' . $substr . ' empty');
//                } else if(isset($this->_params[$substr])){
//                    if(isset($this->_config['constraint'][$substr])){
//                        if(is_array($this->_config['constraint'][$substr]) && in_array($substr, $this->_config['constraint'][$substr])){
//                            $path[] = $this->_params[$substr];
//                        } else if(preg_match('#' . $this->_config['constraint'][$substr] . '#', $this->_params[$substr])){
//                            $path[] = $this->_params[$substr];
//                        } else {
//                            throw new QRouteMissingPramException('Route need the param ' . $substr);
//                        }
//                    } else {
//                        $path[] = $this->_params[$substr];
//                    }
//                } else {
//                    $allowNull = false;
//                }
            } else {
//                $path[] = $pathPart;
            }
        }
        foreach($this->_params as $k => $v){
            $path = str_replace(self::$_urlVariable . $k, $v, $path);
        }
        return $path;
    }
}

class QRouteException extends QAbstractObjectException{}
class QRouteMissingParamException extends QRouteException{}

?>