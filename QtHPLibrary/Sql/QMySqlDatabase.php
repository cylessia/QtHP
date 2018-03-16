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
            throw new QMySqlDatabaseAutoCommitException('Unable to disable autocommit mode');
        }
        return $this;
    }

    public function newQuery() {
        return new QMySqlQuery;
    }

    public function beginTransaction() {
        if(!$this->isOpen()){
            throw new QMySqlDatabaseException('Database connection must be opened to commit transaction');
        }
        if(!$this->_began){
            if(!mysql_exec($this->_link, 'BEGIN')){
                throw new QMySqlDatabaseException('Unable to start transaction : ');
            }
            $this->_began = true;
        }
    }

    public function rollback($savePoint = null) {
        if($savePoint !== null && !is_scalar($savePoint)){
            throw new QMySqlDatabaseException('MySQL savepoint name must be a scalar value');
        }
        if(!mysqli_query($this->_link, 'ROLLBACK' . ($savePoint != null ? ' TO SAVEPOINT' . $savePoint : ''))){
            throw new QMySqlDatabaseException('Unable to rollback to savepoint "' . $savePoint . '"');
        }
        if(!$savePoint){
            $this->_began = false;
        }
    }

    public function savePoint($savePoint){
        if(!is_scalar($savePoint)){
            throw new QSqliteDatabaseException('SQLite savepoint name must be a scalar value');
        }
        if(!mysqli_query($this->_link, 'SAVEPOINT ' . $savePoint)){
            throw new QSqliteDatabaseException('Unable to create savepoint "' . $savePoint . '"');
        }
    }

    public function release($savePoint) {
        if(!is_scalar($savePoint)){
            throw new QSqliteDatabaseException('SQLite savepoint name must be a scalar value');
        }
        if(!mysqli_query($this->_link, 'RELEASE SAVEPOINT ' . $savePoint)){
            throw new QSqliteDatabaseException('Unable to release savepoint "' . $savePoint . '"');
        }
    }

        public function prepare($query){
        if(!$this->isOpen()){
            throw new QMySqlDatabasePrepareException('A connection is needed to prepare a query');
        }
        return new QMySqlQuery($query, $this);
    }
}

class QMySqlDatabaseException extends QSqlDatabaseException {}

class QMySqlDatabaseConnectionException extends QMySqlDatabaseException {}
class QMySqlDatabaseAutoCommitException extends QMySqlDatabaseException {}
class QMySqlDatabaseCloseException extends QMySqlDatabaseException {}
class QMySqlDatabasePrepareException extends QMySqlDatabaseException {}

?>