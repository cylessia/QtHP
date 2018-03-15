<?php

abstract class QAbstractItemView extends QAbstractElement {
    
    protected $_model;
    
    public function setModel($model){
        if(!$model instanceof QAbstractItemModel){
            throw new QAbstractItemViewModelException('Not a valid model');
        }
        $this->_model = $model;
    }
    
}

class QAbstractItemViewException extends QAbstractElementException {}
class QAbstractItemViewModelException extends QAbstractItemViewException {}

?>