<?php

class QStringListModel extends QAbstractListModel {
    
    public function setStringList($stringList){
        if($this->isReadOnly()){
            throw new QStringListModelEditableException('Model is read-only');
        }
        if(!($stringList instanceof QStringList) && !($stringList->type() === 'QList<string>')){
            throw new QStringListModelTypeException('Not a valid QStringList');
        }
        $this->setReadOnly();
        $this->_data = $stringList;
        $this->_it = $stringList->getIterator();
    }
}

class QStringListModelException extends QAbstractItemModelException {}
class QStringListModelTypeException extends QStringListModelException {}

?>