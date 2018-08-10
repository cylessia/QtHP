<?php

class QSqlQueryModel extends QAbstractTableModel {

    private
            /**
             * @var QSqlQuery
             */
            $_query;

    public function __construct($query) {
        parent::__construct();
        if($query){
            $this->setQuery($query);
        }
    }

    public function setQuery($query){
        if($this->isReadOnly()){
            throw new QSqlQueryModelReadOnlyException('Cannot set a query on a read only model');
        }
        if(!$query instanceof QSqlQuery){
            throw new QSqlQueryModelQueryException('Must be an instance of QSqlQuery');
        }
        $this->setReadOnly();
        $this->_query = $query;
        $this->_prefetch();
    }

    public function fetch(){
        return ($this->_data = $this->_query->fetch()) !== null;
    }

    public function data($index){
        if($index < 0 || $index > $this->_columnCount){
            throw new QSqlQueryModelColumnIndexException('Not a valid column index');
        }
        return $this->_data[$index];
    }

    public function rewind(){
        $this->_query->seek(0);
    }

    private function _prefetch(){
        $data = $this->_query->exec()->setFetchMode(QSqlQuery::FetchAssoc)->fetch();
        $this->_columnCount = count($data);
        foreach($data as $k => $v){
            !isset($this->_headers[$k])?:($this->_headers[$k] = $v);
        }
        $this->_query->setFetchMode(QSqlQuery::FetchEnum)->seek(0);
    }
}

class QSqlQueryModelException extends QAbstractItemModelException {}
class QSqlQueryModelQueryException extends QSqlQueryModelException {}
class QSqlQueryModelReadOnlyException extends QSqlQueryModelException {}
class QSqlQueryModelColumnIndexException extends QSqlQueryModelException {}

?>