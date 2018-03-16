<?php

class QAutoloader extends QAbstractObject {
    
    private $_namespaces = array(),
            $_classes = array(),
            $_dirs = array();
    
    private static $_baseDir = '';
    
    public function autoload($className){
        if(isset($this->_classes[$className]) && file_exists(self::$_baseDir . $this->_classes[$className] . '.php')){
            include self::$_baseDir . $this->_classes[$className] . '.php';
            return;
        }
        if(count($this->_namespaces)){
            if(($pos = strrpos($className, '\\'))){
                $ns = substr($className, 0, $pos);
                $cls = substr($className, $pos+1);
                if(isset($this->_namespaces[$ns]) && file_exists(self::$_baseDir . $this->_namespaces[$ns] . $cls . '.php')){
                    include self::$_baseDir . $this->_namespaces[$ns] . $cls . '.php';
                    return;
                }
            }
        }
        foreach($this->_dirs as $dir){
            if(file_exists(self::$_baseDir . $dir . '/' . $className . '.php')){
                include self::$_baseDir . $dir . '/' . $className . '.php';
                return;
            }
        }
        // At least, try to include the file by standard decomposition
        if(file_exists(self::$_baseDir . ($className = strtr($className, '\\', '/')) . '.php')){
            include self::$_baseDir . $className . '.php';
            return;
        }
        
        //throw new QAutoloaderAutoloadException('Class "' . $className . '" not found');
    }
    
    public static function baseDir(){
        return self::$_baseDir;
    }
    
    public function registerClass($class, $dir){
        if(!is_string($class) || !is_string($dir)){
            throw new QAutoloaderSignatureException('Call to undefined function QAutoloader::registerClass(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_classes[$class] = $dir;
        return $this;
    }
    
    public function registerClasses($dirs){
        if(!is_array($dirs)){
            throw new QAutoloaderSignatureException('Call to undefined function QAutoloader::registerClasses(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_classes = array_merge($this->_classes, $dirs);
        return $this;
    }

    public function registerDir($dir){
        if(!is_string($dir)){
            throw new QAutoloaderSignatureException('Call to undefined function QAutoloader::registerDir(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_dirs[] = $dir;
        return $this;
    }
    
    public function registerDirs($dirs){
        if(!is_array($dirs)){
            throw new QAutoloaderSignatureException('Call to undefined function QAutoloader::registerDirs(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_dirs = array_merge($this->_dirs, $dirs);
        return $this;
    }
    
    public function registerNamespace($namespace, $dir){
        if(!is_string($namespace) || !is_string($dir)){
            throw new QAutoloaderSignatureException('Call to undefined function QAutoloader::registerNamespace(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_namespaces[$namespace] = $dir;
        return $this;
    }
    
    public function registerNamespaces($namespaces){
        if(!is_array($namespaces)){
            throw new QAutoloaderSignatureException('Call to undefined function QAutoloader::registerNamespaces(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_namespaces = array_merge($this->_namespaces, $namespaces);
        return $this;
    }
    
    public function register(){
        spl_autoload_register(array($this, 'autoload'));
        return $this;
    }
    
    public static function setBaseDir($dir){
        if(!is_string($dir)){
            throw new QAutoloaderSignatureException('Call to undefined function QAutoloader::setBaseDir(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        self::$_baseDir = $dir;
    }
}

class QAutoloaderException extends QAbstractObjectException {}
class QAutoloaderSignatureException extends QAutoloaderException implements QSignatureException {}
class QAutoloaderAutoloadException extends QAutoloaderException {}
?>