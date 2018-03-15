<?php

class QHttpHeaders extends QAbstractObject {
    private $_referer,
            $_userAgent,
            $_queryString,
            $_queryItems,
            $_postItems,
            $_getItems,
            $_cookieItems,
            $_serverItems;
    
    const Get = 1,
          Post = 2,
          Request = 3,
          Server = 4,
            
          FilterInt = 1,
          FilterFloat = 2,
          FilterNumeric = 3,
          FilterString = 4,
          FilterBool = 5;
    
    public function __construct(){
        $this->_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        $this->_userAgent = $_SERVER['HTTP_USER_AGENT'];
        $this->_queryString = $_SERVER['QUERY_STRING'];
        $this->_serverItems = QMap::fromArray($_SERVER, true);
        $this->_queryItems = QMap::fromArray($_REQUEST, true);
        $this->_postItems = QMap::fromArray($_POST, true);
        $this->_getItems = QMap::fromArray($_GET, true);
        $this->_cookieItems = QMap::fromArray($_COOKIE, true);
    }
    
    public function get($value, $filter = null){
        try {
            return $this->_filter($this->_getItems->value($value), $filter);
        } catch(QMapException $e){
            throw new QHttpHeadersGetItemException('"' . $value . '" doesn\'t exists');
        }
    }
    
    public function post($value, $filter = null){
        try {
            return $this->_filter($this->_postItems->value($value), $filter);
        } catch(QMapException $e){
            throw new QHttpHeadersPostItemException('"' . $value . '" doesn\'t exists');
        }
    }
    
    public static function redirect($url, $exit = true){
        if($url instanceof QUrl){
            header('Location:'.$url->toString());
        } else if(is_string($url)) {
            header('Location:'.$url);
        }
        if($exit)exit;
    }
    
    public function referer(){
        return $this->_referer;
    }
    
    public function request($value, $filter = null){
        try {
            return $this->_filter($this->_queryItems->value($value), $filter);
        } catch(QMapException $e){
            throw new QHttpHeadersRequestItemException('"' . $value . '" doesn\'t exists');
        }
    }
    
    public function server($value, $filter = null){
        try {
            return $this->_filter($this->_serverItems->value($value), $filter);
        } catch(QMapException $e){
            throw new QHttpHeadersServerItemException('"' . $value . '" doesn\'t exists');
        }
    }
    
    public function isAjax(){
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest';
    }
    
    public function isPost(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    public function isGet(){
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }
    
    public function exists($name, $var = self::Request){
        switch($var){
            case self::Get:
                $array = &$this->_getItems;
                break;
            case self::Post:
                $array = &$this->_postItems;
                break;
            case self::Request:
                $array = &$this->_queryItems;
                break;
            case self::Server:
                $array = &$this->_serverItems;
                break;
            default:
                throw new QHttpHeadersQueryItemTypeException('Unknown query item "' . $var . '"');
        }
        if(is_array($name)){
            foreach($name as $key){
                try {
                    $array->value($key);
                } catch(QMapException $e){
                    return false;
                }
            }
            return true;
        } else if(is_scalar($name)) {
            try {
                $array->value($name);
                return true;
            }catch(QMapException $e){
                return false;
            }
        }
    }
    
    private function _filter($value, $filter){
        switch($filter){
            case self::FilterInt:
                if(
                   !(isset($value{0}) && $value{0} == '-' && ctype_digit(substr($value, 1)))
                   && !ctype_digit($value)
                )
                    throw new QHttpHeadersFilterIntException('"' . $value . '" is not an integer');
                   $value =(int)$value;
                break;
            case self::FilterFloat:
                if(!is_float(0+$value))
                    throw new QHttpHeadersFilterFloatException('"' . $value . '" is not a float');
                $value =(float)$value;
                break;
            case self::FilterNumeric:
                if(is_numeric($value))
                    throw new QHttpHeadersFilterNumericException('"' . $value . '" is not numeric');
                break;
            case self::FilterBool:
                if($value != 'true' && $value != 'false' && $value != 't' && $value != 'f')
                    throw new QHttpHeadersFilterBoolException('"' . $value . '" is not a boolean');
            case self::FilterString:
            case null:
                break;
            default:
                throw new QHttpHeadersFilterTypeException('Invalid filter');
                break;
        }
        return $value;
    }
    
    
}

class QHttpHeadersException extends QAbstractObjectException {}
class QHttpHeadersQueryItemTypeException extends QHttpHeadersException {}
class QHttpHeadersQueryItemException extends QHttpHeadersException {}
class QHttpHeadersGetItemException extends QHttpHeadersException {}
class QHttpHeadersPostItemException extends QHttpHeadersException {}
class QHttpHeadersRequestItemException extends QHttpHeadersException {}
class QHttpHeadersServerItemException extends QHttpHeadersException {}
class QHttpHeadersFilterTypeException extends QHttpHeadersException {}

class QHttpHeadersFilterException extends QHttpHeadersException {}
class QHttpHeadersFilterNumericException extends QHttpHeadersFilterException {}
class QHttpHeadersFilterIntException extends QHttpHeadersFilterException {}
class QHttpHeadersFilterFloatException extends QHttpHeadersFilterException {}
class QHttpHeadersFilterStringException extends QHttpHeadersFilterException {}
class QHttpHeadersFilterBoolException extends QHttpHeadersFilterException {}

?>