<?php

class QMvcUrl extends QAbstractObject {
    private $_baseUri;
    
    public function setBaseUri($base){
        if(!is_string($base)){
            throw new QMvcUrlBaseUri('Not a valid base uri');
        }
        $this->_baseUri = $base;
    }
    
    public function get($uri, $params = array(), $generateToken = false){
        $url = new QUrl;
        $url->setPath($this->_baseUri . $uri);
        if($params === true){
            $cache = new QCache('qthp_mvc_url');
            $cache->save('token', QCryptographicHash::generateToken());
            $params = array('qthp_token' => $cache->recover('token'));
        } else if($generateToken === true){
            $cache = new QCache('qthp_mvc_url');
            $cache->save('token', QCryptographicHash::generateToken());
            $params['qthp_token'] = $cache->recover('token');
        }
        $url->addQueryStringItems($params);
        return $url->toString();
    }
    
    public function checkToken($token){
        try {
            $cache = new QCache('qthp_mvc_url');
            if($token == $cache->recover('token')){
                $cache->clear();
                return;
            }
        } catch(QCacheValueException $e){}
        throw new QMvcUrlTokenException('Tokens do not match');
    }
    
    public function redirect($uri, $params = array()){
        header('Location:' . $this->get($uri, $params));
        exit;
    }
    
}

class QMvcUrlException extends QAbstractObjectException {}
class QMvcUrlTokenException extends QMvcUrlException {}

?>