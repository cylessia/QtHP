<?php

abstract class QGraphicsItem extends QAbstractObject {
//    const NoCache = 0x00,
//          CoordinateCache = 0x01,
//          BrushCache = 0x02,
//          ItemCache = 0x03;
    
    protected $_x,
              $_y,
              $_children,
              $_parent,
              $_visibility,
              $_opacity,
              $_pos = null,
              $_rotation = 0,
              $_scale = 1.0;
    
    public function __construct() {
        //$this->_children = new QList('QGraphicsItem');
    }
    
    public function addChild($child){
        if($child->_parent === $this){
            return;
        }
        if($child->_parent){
            $child->_parent->children()->removeOne($child);
        }
        $child->_parent = $this;
        $this->_children->append($child);
    }
      
    abstract public function boundingRect();
    
//    public function childItems(){
//        return $this->_children;
//    }
    
//    public function commonAncestorItem($other){
//        if(!$other instanceof QGraphicsItem){
//            throw new QGraphicsItemCommonAncestorException('Not a QGraphicsItem');
//        }
//        $me = $this;
//        while($me){
//            $_other = $other;
//            while($_other){
//                if($me->_parent === $_other->parent){
//                    return $me->_parent;
//                }
//                $_other = $_other->_parent;
//            }
//            $me = $me->_parent;
//        }
//        return null;
//    }
    
    public function hide(){
        $this->_visibility = false;
    }
    
    public function isVisible(){
        return $this->_visibility;
    }
    
    public function opacity(){
        return $this->_opacity;
    }
    
//    public function parent(){
//        return $this->_parent;
//    }
    
    public function pos(){
        return $this->_pos;
    }
    
    public function rotation(){
        return $this->_rotation;
    }
    
    public function scale(){
        return $this->_scale;
    }
    
    public function setOpacity($opacity){
        if(!is_numeric($opacity) || $opacity < 0){
            throw new QGraphicsItemOpacityException('Not a valid opacity');
        }
        $this->_opacity = $opacity;
        return $this;
    }
    
    public function setParent($parent){
        $parent->addChild($this);
        return $this;
    }
    
    public function setPos($x, $y = null){
        if($x instanceof QPoint){
            $this->_pos = new QPointF($x);
        } else if(is_numeric($x) && is_numeric($y)){
            $this->_pos = new QPointF($x, $y);
        } else {
            throw new QGraphicsItemPosException('Not a valid pos');
        }
        return $this;
    }
    
    public function setRotation($rotation){
        if(!is_int($rotation) || $rotation < 0 || $rotation > 360){
            throw new QGraphicsItemRotationException('Not valid');
        }
        $this->_rotation = $rotation;
        return $this;
    }
    
    public function setScale($scale){
        if(!is_numeric($scale)){
            throw new QGraphicsItemScaleException('No a valid scale');
        }
        $this->_scale = $scale;
        return $this;
    }
    
    public function setVisible($visible){
        if(!is_bool($visible)){
            throw new QGraphicsItemVisibilityException('Not a valid value');
        }
        $this->_visibility = $visible;
        return $this;
    }
    
    public function setX($x){
        $this->_pos->setX($x);
    }
    
    public function setY($y){
        $this->_pos->setY($y);
    }
    
    public function show(){
        $this->_visibility = true;
        return $this;
    }
    
    public function x(){
        return $this->_pos->x();
    }
    
    public function y(){
        return $this->_pos->y();
    }
    
}

class QGraphicsItemException extends QAbstractObjectException {}
class QGraphicsItemCommonAncestorException extends QGraphicsItemException {}

?>