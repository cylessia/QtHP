<?php

class QTableModel extends QAbstractTableModel {
    
    private $_currentData = null,
            $_it,
            $_subIt;
    
    public function setTable($table){
        if($this->isReadOnly()){
            throw new QSqlQueryModelReadOnlyException('Cannot set a query on a read only model');
        }
        if(is_array($table)){
            $table = QList::fromArray($table, false);
        }
        
        if(!($table instanceof IteratorAggregate && $table instanceof ArrayAccess) || !($table[0] instanceof ArrayAccess || is_array($table[0]))){
            throw new QTableModelTableException('QAbstractTableModel\'s table must be an instance of ArrayIterator,ArrayAccess<ArrayIterator<scalar>>');
        }
        
        $this->_setHeaders($table[0]);
        $this->setReadOnly();
        $this->_data = $table;
        $this->_it = $table->getIterator();
        $this->_columnCount = count($table[0]);
    }
    
    public function fetch(){
        $this->_subIt = ($this->_currentData = $this->_it->current()) !== null ? new ArrayIterator($this->_currentData) : null;
        $return = $this->_it->valid();
        $this->_it->next();
        return $return;
    }
    
    public function rewind(){
        $this->_it->rewind();
    }
    
    public function data($index){
        if($this->_subIt === null){
            throw new QTableModelDataException('No more data');
        }
        if($index < 0 || $index > $this->_columnCount){
            throw new QTableModelIndexException('Out of range');
        }
        $this->_subIt->seek($index);
        return $this->_subIt->current();
    }
    
    private function _setHeaders($array){
        $it = new ArrayIterator($array);
        $i = 0;
        while($it->valid()){
            if(is_string($it->key())){
                isset($this->_headers[$i])?:($this->_headers[$i] = $it->key());
            }
            $it->next();
            ++$i;
        }
    }
}

class QTableModelException extends QAbstractTableModelException {}
class QTableModelListTypeException extends QTableModelException {}

?>