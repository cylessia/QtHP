<?php

class QResponseJson extends QAbstractObject implements QResponseInterface, QDependencyInjectionInterface {

    private

    $_content,
    $_di;


    public function __construct($data = ''){
        if($data){
            $this->setContent($data);
        }
    }

    public function setDi(QDependencyInjectorInterface $di){
        $this->_di = $di;
    }

    public function di(){
        return $this->_di;
    }

    public function setContent($data){
        if(is_string($data)){
            if(!json_decode($data)){
                throw new QResponseInvalidJsonException('Invalid json data');
            }
            $this->_content = $data;
        } else if(is_array($data) || is_object($data)){
            $this->_content = json_encode($data);
        }
    }

    public function content(){
        return $this->_content;
    }

    public function show(){
        QHttpHeaders::setHeader('Content-Type', 'application/json; charset=UTF-8');
        echo $this->_content;
    }
}


?>