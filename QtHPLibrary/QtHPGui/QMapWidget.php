<?php

class QMapWidget extends QAbstractWidget {
    public function __construct($imageUrl = null){
        if($imageUrl){
            $this->setImage($imageUrl);
        }
    }
    
    public function setImage($url){
        if($url instanceof QUrl){
            
        }
    }
    
    public function show(){
        
    }
}

?>