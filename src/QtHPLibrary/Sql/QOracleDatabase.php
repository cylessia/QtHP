<?php

class QOracleDatabase extends QSqlDatabase {
    public function close(){
        if(!$this->_link){
            throw new QOracleDatabaseCloseException('Unable to close a non open database link');
        }
        $this->rollback();
        oci_close($this->_link);
        $this->_link = null;
        return $this;
    }

    public function commit(){
        oci_commit($this->_link);
        return $this;
    }

    public function lastError(){
        $err = oci_error($this->_link);
        return 'Oracle error : ' . $err['code'] . ' with message : ' . $err['message'] . ($err['offset'] != 0 ? ' near "' . substr($err['sqltext'], $err['offset']) : '');
    }

    public function open(){
        if(!$this->_link = @oci_connect($this->_userName, $this->_password, $this->_tsn)){
            throw new QOracleDatabaseConnectionException('Unable to connect ' . $this->_userName . '@' . $this->_hostName . ($this->_port ? ':' . $this->_port : '') . ($this->_databaseName ? '/' . $this->_databaseName : '') . ($this->_password ? ' using password "' . $this->_password . '"' : ''));
        }
        return $this;
    }

    public function newQuery(){
        return new QOracleQuery;
    }

    public function beginTransaction() {
        if(!$this->isOpen()){
            throw new QOracleDatabaseException('Database connection must be opened to commit transaction');
        }
        if(!$this->_began){
            if(!oci_parse($this->_link, 'BEGIN')){
                throw new QOracleDatabaseException('Unable to start transaction : ');
            }
            $this->_began = true;
        }
    }

    public function rollback($savePoint = null){
        if($savePoint !== null && !is_scalar($savePoint)){
            throw new QOracleDatabaseException('Oracle savepoint name must be a scalar value');
        }
        if(!($res = oci_parse('ROLLBACK' . ($savePoint != null ? ' TO ' . $savePoint : '')))){
            throw new QOracleDatabaseParseException($this->lastError());
        }
        if(!($res = oci_execute($res))){
            throw new QOracleQueryExecuteException($this->lastError());
        }
        return $this;
    }

    public function savePoint($savePoint){
        if(!is_scalar($savePoint)){
            throw new QOracleDatabaseException('Oracle savepoint name must be a scalar value');
        }
        if(!($res = oci_parse('SAVEPOINT ' . $savePoint))){
            throw new QOracleDatabaseParseException('Unable to create savepoint "' . $savePoint . '"');
        }
        if(!oci_execute($res)){
            throw new QOracleDatabaseExecuteException('Unable to create savepoint "' . $savePoint . '"');
        }
    }

    public function release($savePoint){
        // Juste do nothing, oracle does not support release savepoint...
        //throw new QOracleDatabaseException('Oracle does not supports savepoint release');
    }

    public function prepare($query){
        if(!$this->isOpen()){
            throw new QOracleDatabasePrepareException('A connection is needed to prepare a query');
        }
        return new QOracleQuery($query, $this);
    }
    /**
     * if(!oci_execute($this->_link, OCI_NO_AUTO_COMMIT)){
            throw new QOracleDatabaseAutoCommitException('Unable to disable autocommit mode');
        }
     */
}


class QOracleDatabaseException extends QSqlDatabaseException {}

class QOracleDatabaseConnectionException extends QMySqlDatabaseException {}
class QOracleDatabaseAutoCommitException extends QMySqlDatabaseException {}
class QOracleDatabaseCloseException extends QMySqlDatabaseException {}
class QOracleDatabaseParseException extends QMySqlDatabaseException {}
class QOracleDatabaseExecuteException extends QMySqlDatabaseException {}
class QOracleDatabasePrepareException extends QMySqlDatabaseException {}
