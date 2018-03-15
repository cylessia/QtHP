<?php

class QMySqlDatabase extends QSqlDatabase {
    
    public function close(){
        if(!$this->_link){
            throw new QMySqlDatabaseCloseException('Unable to close a non open database link');
        }
        $this->rollback();
        mysqli_close($this->_link);
        $this->_link = null;
        return $this;
    }
    
    public function commit(){
        mysqli_commit($this->_link);
        return $this;
    }
    
    public function lastError(){
        return mysqli_error($this->_link);
    }
    
    public function open(){
        if(!($this->_link = @mysqli_connect($this->_hostName, $this->_userName, $this->_password, $this->_databaseName, $this->_port))){
            throw new QMySqlDatabaseConnectionException('Unable to connect ' . $this->_userName . '@' . $this->_hostName . ($this->_port ? ':' . $this->_port : '') . ($this->_databaseName ? '/' . $this->_databaseName : '') . ($this->_password ? ' using password "' . $this->_password . '"' : ''));
        }
        if(!mysqli_autocommit($this->_link, false)){
            throw new QMysqlDatabaseAutoCommitException('Unable to disable autocommit mode');
        }
        return $this;
    }
    
    public function newQuery() {
        return new QMySqlQuery;
    }

        public function prepare($query){
        if(!$this->isOpen()){
            throw new QMySqlDatabasePrepareException('A connection is needed to prepare a query');
        }
        return new QMySqlQuery($query, $this);
    }
    
    public function rollback(){
        mysqli_rollback($this->_link);
        return $this;
    }
}

class QMySqlDatabaseException extends QSqlDatabaseException {}

class QMySqlDatabaseConnectionException extends QMySqlDatabaseException {}
class QMysqlDatabaseAutoCommitException extends QMySqlDatabaseException {}
class QMySqlDatabaseCloseException extends QMySqlDatabaseException {}
class QMySqlDatabasePrepareException extends QMySqlDatabaseException {}

?>