<?php

class QHBoxLayout extends QAbstractLayout {
    //private $_alignment = self::Left;
    
//    public function setAlignment($alignment){
//        if($alignment != QtHP::Left && $alignment != QtHP::Right){
//            throw new QHBoxLayoutAlignmentException('Unknow horizontal alignment');
//        }
//        $this->_alignment = $alignment;
//        return $this;
//    }
    
    public function show(){
        echo '<div>';
        foreach($this->_children as $child){
            echo $child->setAttribute('style', 'display:inline-block')->show();
        }
        echo '</div>';
    }
}

class QHBoxLayoutException extends QAbstractLayoutException {}
class QHBoxLayoutAlignmentException extends QHBoxLayoutException {}

?>