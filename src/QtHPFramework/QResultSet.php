<?php

class QResultSet extends QAbstractObject implements Iterator, Countable {
    
    protected
            $_stmt,
            $_class,
            $_position = 0,
            /**
             * @var QList
             */
            $_array;
    
    public function __construct(QSqlQuery $stmt, QOrm $class){
        $this->_stmt = $stmt;
        $this->_class = $class;
        $this->_array = new QList('string');
    }
    
    public function current(){
        return $this->_class;
    }
    
    public function next(){}
    
    public function key() {
        return $this->_position;
    }
    
    public function rewind() {
        if($this->_position != 0){
            $this->_stmt->seek($this->_position = 0);
        }
    }
    
    public function valid() {
        if(($res = $this->_stmt->fetch())){
            $this->_class->fill($res);
            ++$this->_position;
            return true;
        } else {
            $this->_class->fill(null);
            return false;
        }
    }
    
    public function count(){
        return $this->_stmt->numRows();
    }
}

class QResultSetException extends QAbstractObjectException {}
class QResultSetSignatureException extends QResultSetException implements QSignatureException {}

?>