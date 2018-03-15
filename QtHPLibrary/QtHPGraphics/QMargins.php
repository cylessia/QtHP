<?php

class QMargins extends QAbstractObject {
    protected
            $_type = 'QMargins';
    private 
            $_bottom = 0,
            $_left = 0,
            $_right = 0,
            $_top = 0;
    
    public function __construct($left, $top, $right, $down){
        if(!is_numeric($left) || !is_numeric($top) || !is_numeric($right) || !is_numeric($down)){
            throw new QMarginsException('All the margins must be numeric values');
        }
        $this->_bottom = $bottom;
        $this->_left = $left;
        $this->_right = $right;
        $this->_top = $top;
    }
    
    public function bottom(){
        return $this->_bottom;
    }
    
    public function isNull(){
        return $this->_left == 0 && $this->_top == 0 && $this->_right == 0 && $this->_bottom == 0;
    }
    
    public function left(){
        return $this->_left;
    }
    
    public function right(){
        return $this->_right;
    }
    
    public function setBottom($bottom){
        if(!is_numeric($bottom)){
            throw new QMarginsBottomException('The bottom of a margin must be a numeric value');
        }
        $this->_bottom = $bottom;
        return $this;
    }
    
    public function setLeft($left){
        if(!is_numeric($left)){
            throw new QMarginsLeftException('The left of a margin must be a numeric value');
        }
        $this->_left = $left;
        return $this;
    }
    
    public function setrRight($right){
        if(!is_numeric($right)){
            throw new QMarginsRightException('The right of a margin must be a numeric value');
        }
        $this->_right= $right;
        return $this;
    }
    
    public function setTop($top){
        if(!is_numeric($top)){
            throw new QMarginsTopException('The top of the margin must be a numeric value');
        }
        $this->_top = $top;
        return $this;
    }
    
    public function top(){
        return $this->_top;
    }
}

class QMarginsException extends QException{}
class QMarginsHorizontalException extends QMarginsException{}
class QMarginsVerticalException extends QMarginsException{}
class QMarginsRightException extends QMarginsVerticalException{}
class QMarginsLeftException extends QMarginsVerticalException{}
class QMarginsTopException extends QMarginsHorizontalException{}
class QMarginsBottomException extends QMarginsHorizontalException{}

?>