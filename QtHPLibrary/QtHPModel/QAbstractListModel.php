<?php

abstract class QAbstractListModel extends QAbstractItemModel {
    
    protected /**
           * @var ArrayIterator
           */
            $_it = null,
            $_currentData = null;
    
    public function rewind(){
        $this->_it->rewind();
    }
    
    public function fetch(){
        if($this->_data === null){
            throw new QAbstractListModelFetchException('Cannot fetch null data');
        }
        $this->_currentData = $this->_it->current();
        $return = $this->_it->valid();
        $this->_it->next();
        return $return;
    }
    
    public function data($index){
        return $this->_currentData;
    }
}

class QAbstractListModelException extends QAbstractItemModelException {}
class QAbstractListModelFetchException extends QAbstractListModelException {}

?>