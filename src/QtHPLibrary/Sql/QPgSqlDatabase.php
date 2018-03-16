<?php

class QPgSqlDatabase extends QSqlDatabase {
    
    private $_began = false;
    
    public function close(){
        if(!$this->isOpen()){
            throw new QPgSqlDatabaseCloseException('A connection is needed to rollback a transaction');
        }
        if($this->_began){
            $this->rollback();
        }
        if(!@pg_close($this->_link)){
            throw new QPgSqlDatabaseCloseException('Unable to close the connection');
        }
        $this->_link = null; // Otherwise it's an empty resource O_o
        return $this;
    }
    
    public function commit(){
        if(!$this->isOpen()){
            throw new QPgSqlDatabaseCommitException('A connection is needed to commit a transaction');
        }
        if(!@pg_query($this->_link, 'COMMIT')){
            throw new QPgSqlDatabaseCommitException('Unable to commit the transaction');
        }
        $this->_began = false;
        return $this;
    }
    
    public function open(){
        if(!($this->_link = @pg_connect(
            ($this->_hostName ? 'host=' . $this->_hostName : '')
            . ($this->_port ? ' port=' . $this->_port : '')
            . ($this->_databaseName ? ' dbname=' . $this->_databaseName : '')
            . ($this->_userName ? ' user=' . $this->_userName : '')
            . ($this->_password ? ' password=' . $this->_password : '')
        ))){
            throw new QPgSqlDatabaseConnectionException('Unable to connect ' . $this->_userName . '@' . $this->_hostName . ($this->_port ? ':' . $this->_port : '') . ($this->_databaseName ? '/' . $this->_databaseName : '') . ($this->_password ? ' using password "' . $this->_password . '"' : ''));
        }
        return $this;
    }
    
    public function newQuery() {
        return new QPgSqlQuery;
    }
    
    public function prepare($query){
        if(!$this->isOpen()){
            throw new QPgSqlDatabasePrepareException('A connection is needed to prepare a query');
        }
        $this->beginTransaction();
        return new QPgSqlQuery($query, $this);
    }
    
    public function beginTransaction(){
        if(!$this->_began){
            if(!pg_send_query($this->_link, 'BEGIN')){
                throw new QPgSqlDatabaseBeginException('Unable to start the transaction');
            }
            pg_get_result($this->_link);
            $this->_began = false;
        }
    }
    
//    public function rollback(){
//        pg_query($this->_link, 'ROLLBACK');
//        $this->_began = false;
//        return $this;
//    }
    
    public function release($savePoint){
        if(!is_scalar($savePoint)){
            throw new QPgSqlDatabaseSignatureException('Call to undefined function QPgSqlDatabase::release(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if(!$this->isOpen()){
            throw new QPgSqlDatabaseReleaseException('A connection is needed to release a savepoint');
        }
        if(!pg_send_query($this->_link, 'RELEASE ' . $savePoint)){
            throw new QPgSqlDatabaseReleaseException('Unable to release savepoint "' . $savePoint . '"');
        }
        pg_get_result($this->_link);
        return $this;
    }

    public function rollback($savepoint = null){
        if($savepoint !== null){
            if(!is_scalar($savepoint)){
                throw new QPgSqlDatabaseSignatureException('Call to undefined function QPgSqlDatabase::release(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
            if(!$this->isOpen()){
                throw new QPgSqlDatabaseRollbackException('A connection is needed to rollback a transaction');
            }
            if(!pg_send_query($this->_link, 'ROLLBACK TO ' . $savepoint)){
                throw new QPgSqlDatabaseRollbackException('Unable to rollback to savepoint "' . $savepoint . '"');
            }
        } else {
            if(!$this->isOpen()){
                throw new QPgSqlDatabaseRollbackException('A connection is needed to rollback a transaction');
            }
            if(!pg_send_query($this->_link, 'ROLLBACK')){
                throw new QPgSqlDatabaseRollbackException('Unable to rollback the current transaction');
            }
            $this->_began = false;
        }
        pg_get_result($this->_link);
        return $this;
    }
    
    public function savepoint($savepoint){
        if(!is_string($savepoint)){
            throw new QPgSqlDatabaseSignatureException('Call to undefined function QPgSqlDatabase::savePoint(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if(!$this->isOpen()){
            throw new QPgSqlDatabaseSavepointException('A connection is needed to set a savepoint');
        }
        //var_dump(pg_get_result($this->_link));
        if(!pg_send_query($this->_link, 'SAVEPOINT ' . $savepoint)){
            throw new QPgSqlDatabaseSavepointException('Unable to set savepoint "' . $savepoint . '"');
        }
        pg_get_result($this->_link);
        //var_dump('sv', pg_result_error_field(pg_get_result($this->_link), PGSQL_DIAG_MESSAGE_PRIMARY));
        return $this;
    }
    
//    public function isBusy(){
//        return pg_connection_busy($this->_link);
//    }
}
class QPgSqlDatabaseException extends QSqlDatabaseException {}
class QPgSqlDatabaseSignatureException extends QPgSqlDatabaseException implements QSignatureException {}
class QPgSqlDatabaseCloseException extends QPgSqlDatabaseException {}
class QPgSqlDatabaseConnectionException extends QPgSqlDatabaseException {}
class QPgSqlDatabasePrepareException extends QPgSqlDatabaseException {}
class QPgSqlDatabaseBeginException extends QPgSqlDatabaseException {}
class QPgSqlDatabaseRollbackException extends QPgSqlDatabaseException {}
class QPgSqlDatabaseCommitException extends QPgSqlDatabaseException {}
class QPgSqlDatabaseSavepointException extends QPgSqlDatabaseException {}
class QPgSqlDatabaseReleaseException extends QPgSqlDatabaseException {}

?>