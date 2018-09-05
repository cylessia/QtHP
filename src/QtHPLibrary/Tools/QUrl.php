<?php

class QUrl extends QAbstractObject {
    private $_scheme = 0,
            $_userName = '',
            $_password = '',
            $_host = '',
            $_port = 0,
            $_fragment = '',
            $_path = '',
            $_queryItems = array(),
            $_valueDelimiter = '=',
            $_pairDelimiter = '&';
    
//    const Unknown = '',
//          Http = 'http',
//          Https = 'https',
//          Ftp = 'ftp',
//          Ftps = 'ftps',
//          Ftpes = 'ftpes';
    const None = 0,
          RemoveScheme = 0x001,
          RemovePassword = 0x002,
          RemoveUserInfo = 0x006, // self::RemovePassword | 0x04
          RemovePort = 0x008,
          RemoveAuthority = 0x01E, // self::RemoveUserInfo | self::RemovePort | 0x10
          RemovePath = 0x020,
          RemoveQuery = 0x040,
          RemoveFragment = 0x100;
    
    public function __construct($url = ''){
        if($url){
            $this->_analyze($url);
        }
    }
    
    public function addQueryStringItems($items, $value = null){
        if(is_string($items) && strpos($items, $this->_pairDelimiter)){
             $tmpQueryItems = explode($this->_pairDelimiter, $items);
             foreach($tmpQueryItems as $tmpQueryItem){
                 $tmpExplodedQueryItem = explode($this->_valueDelimiter, $tmpQueryItem, 2);
                 if(count($tmpExplodedQueryItem) != 2){
                     throw new QUrlQueryStringException('Invalid query string');
                 }
                 $this->_queryItems[$tmpExplodedQueryItem[0]] = $tmpExplodedQueryItem[1];
             }
        } else if(is_array($items)){
            $this->_queryItems = array_merge($this->_queryItems, $items);
        } else {
            $this->_queryItems[$items] = $value;
        }
    }
    
    public function fragment(){
        return $this->_fragment;
    }
    
//    public static function fromPreformated($regex, $url){
//        if(!preg_match($regex, $url, $m)){
//            throw new QUrlAnalyzeException('The preformated url doesn\'t match the regular expression');
//        }
//        $url = new self;
//        $url->setScheme($m['scheme']);
//        $url->setUserName($m['userName']);
//        $url->setPassword($m['password']);
//        $url->setHost($m['host']);
//        $url->setPort($m['port']);
//        $url->setFragment($m['fragment']);
//        $url->setPath($m['path']);
//        $url->addQueryStringItems($m['queryString']);
//        return $url;
//    }
    
    public function host(){
        return $this->_host;
    }
    
    public function password(){
        return $this->_password;
    }
    
    public function path(){
        return $this->_path;
    }
    
    public function port(){
        return $this->_port;
    }
    
    public function scheme(){
        return $this->_scheme;
    }
    
    public function setFragment($fragment){
        $this->_fragment = $fragment;
    }
    
    public function setHost($host){
        if(!preg_match('/^[\w_-]+(\.[\w_-]+)*\.[\w]{2,5}$/', $host) && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)){
            throw new QUrlHostException('Invalid host name "' . $host . '"');
        }
        $this->_host = $host;
    }
    
    public function setPassword($password){
        if(!preg_match('/^[\w_.-]+$/', $password)){
            throw new QUrlPasswordException('Invalid password');
        }
        $this->_password = $password;
    }
    
    public function setPath($path){
        if(!preg_match('#/?([\w_.-]+/)*[\w_.-](\.[\w]{2,4})?#', $path)){
            throw new QUrlPathException('Invalid path');
        }
        $this->_path = $path;
    }
    
    public function setPort($port){
        if(!is_numeric($port) || strrpos($port, '.') !== false){
            throw new QUrlPortException('Invalid port');
        }
        $this->_port = $port;
    }
    
    public function setScheme($scheme){
        $this->_scheme = $scheme;
    }
    
    public function setUserName($userName){
        if(!preg_match('/^[\w_.-]+$/', $userName)){
            throw new QUrlUserNameException('Invalid user name');
        }
        $this->_userName = $userName;
    }
    
    public function toString($formattingOptions = self::None){
        $delimiter = $this->_valueDelimiter;
        return
            ($formattingOptions & self::RemoveScheme || !$this->_scheme ? '' : $this->_scheme . '://') .
            (
                ($formattingOptions & self::RemoveAuthority) == self::RemoveAuthority
                    ? ''
                    : (
                        ($formattingOptions & self::RemoveUserInfo) == self::RemoveUserInfo || !$this->_userName && !$this->_password
                        ? ''
                        : $this->_userName . (
                            $formattingOptions & self::RemovePassword || !$this->_password
                                ? ''
                                : ':' . $this->_password
                        ) . '@'
                    )
                . $this->_host .
                ($formattingOptions & self::RemovePort || !$this->_port
                    ? ''
                    : ':' . $this->_port
                ) . '/'
            ) . ($formattingOptions & self::RemovePath ? '' : $this->_path) .
            (
                $formattingOptions & self::RemoveQuery || !count($this->_queryItems)
                ? ''
                : '?' . implode(
                    $this->_pairDelimiter, array_map(
                        function($k, $v)use($delimiter){
                            return $k . $delimiter . $v;
                        },
                        array_keys($this->_queryItems),
                        array_values($this->_queryItems)
                    )
                )
            ) . ($formattingOptions & self::RemoveFragment || !$this->_fragment ? '' : '#' . $this->_fragment);
    }
    
    public function userName(){
        return $this->_userName;
    }
    
    private function _analyze($url){
        if(!preg_match('#^(?<scheme>(https?)|(ftp(e|es)?))://((?<userName>[\w_.-]+)(:(?<password>[\w_.-]+))@)?(?<host>[\w_-]+(\.[\w_-]+)*\.[\w]{2,5})(:(?<port>[\d]+))?(/(?<path>[^?]+)(\?(?<queryString>[^\#]+))?(\#(?<fragment>.+))?)?#', $url, $m)){
            throw new QUrlAnalyzeException('The url is not formated correcttely, you may use QUrl::fromPreformated($url) instead');
        }
        
        $this->setScheme($m['scheme']);
        $this->setUserName($m['userName']);
        $this->setPassword($m['password']);
        $this->setHost($m['host']);
        $this->setPort($m['port']);
        $this->setFragment($m['fragment']);
        $this->setPath($m['path']);
        $this->addQueryStringItems($m['queryString']);
    }
}

class QUrlException extends QAbstractObjectException {protected $_type = 'QUrlException';}
class QUrlAnalyzeException extends QUrlException {protected $_type = 'QUrlAnalyzeException';}
class QUrlHostException extends QUrlException {protected $_type = 'QUrlHostNameException';}
class QUrlPasswordException extends QUrlException {protected $_type = 'QUrlHostNameException';}
class QUrlSchemeException extends QUrlException {protected $_type = 'QUrlHostNameException';}
class QUrlPortException extends QUrlException {protected $_type = 'QUrlHostNameException';}
class QUrlPathException extends QUrlException {protected $_type = 'QUrlHostNameException';}
class QUrlUserNameException extends QUrlException {protected $_type = 'QUrlHostNameException';}
class QUrlFragmentException extends QUrlException {protected $_type = 'QUrlHostNameException';}
class QUrlQueryStringException extends QUrlException {protected $_type = 'QUrlQueryStringException';}

?>