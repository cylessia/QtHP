<?php

abstract class QSqlDatabase extends QAbstractObject {
    
    protected $_hostName = '',
            $_password = '',
            $_userName = '',
            $_databaseName = '',
            $_port = 0,
            $_link = null;
    
    private static 
            /**
             * @var QSqlDatabase
             */
            $_currentConnection = null;
            
    
    public function __construct($database = null, $autoOpen = true){
        if($database != null){
            if($database instanceof QSqlDatabase){
                $this->_hostName = $database->_hostName;
                $this->_password = $database->_password;
                $this->_userName = $database->_userName;
                $this->_databaseName = $database->_databaseName;
                $this->_port = $database->_port;
            } else if($database instanceof QSettings){
                $this->setHostName($database->value('hostName'));
                $this->setPassword($database->value('password'));
                $this->setUsername($database->value('userName'));
                $this->setDatabaseName($database->value('databaseName'));
                $this->setPort($database->value('port'));
            } else if(is_string($database)) {
                //echo $database;
                if(!preg_match('/[\w_.-]+=[\w_.-]*(;[\w_.-]+=[\w_.-]*)*+/', $database)){
                    throw new QSqlDatabaseException('Invalid database connection string syntax');
                }
                
                $tmpTmp = explode(';', $database);
                $tmp = array();
                foreach($tmpTmp as $opt){
                    $tmpOpt = explode('=', $opt);
                    $tmp[$tmpOpt[0]] = $tmpOpt[1] == null ? '' : $tmpOpt[1];
                }
                
                if(!isset($tmp['hostName']) || !isset($tmp['userName']) || !isset($tmp['databaseName'])){
                    $missing = array_diff(array_keys($tmp), array('hostName', 'userName', 'databaseName'));
                    throw new QSqlDatabaseException('Missing database connection value' . (count($missing) > 1 ? 's' : '') . ' : ' . implode(', ', $missing));
                }
                $this->_hostName = $tmp['hostName'];
                $this->_port = isset($tmp['port']) ? $tmp['port'] : null;
                $this->_password = isset($tmp['password']) ? $tmp['password'] : null;
                $this->_userName = $tmp['userName'];
                $this->_databaseName = $tmp['databaseName'];
            } else if(is_array($database)){
                if(!isset($database['hostName']) || !isset($database['userName']) || !isset($database['databaseName'])){
                    $missing = array_diff(array_keys($database), array('hostName', 'userName', 'databaseName'));
                    throw new QSqlDatabaseException('Missing database connection value' . (count($missing) > 1 ? 's' : '') . ' : ' . implode(', ', $missing));
                }
                $this->_hostName = $database['hostName'];
                $this->_port = isset($database['port']) ? $database['port'] : null;
                $this->_password = isset($database['password']) ? $database['password'] : null;
                $this->_userName = $database['userName'];
                $this->_databaseName = $database['databaseName'];
            } else {
                throw new QSqlDatabaseException('Call to undefined signature QSqlDatabase::QSqlDatabase(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
            if($autoOpen === true){
                $this->open();
            }
        }
        if(self::$_currentConnection === null){
            self::$_currentConnection = $this;
        }
    }
    
    public function __destruct(){
        if($this->_link){
            $this->close();
        }
    }
    
    public static function current(){
        return self::$_currentConnection;
    }
    
    public function databaseName(){
        return $this->_databaseName;
    }
    
    public function hostName(){
        return $this->_hostName;
    }
    
    public function isOpen(){
        return $this->_link !== null;
    }
    
    public function link(){
        return $this->_link;
    }
    
    public function password(){
        return $this->_password;
    }
    
    public function port(){
        return $this->_port;
    }
    
    public function setDatabaseName($databaseName){
        if(!is_string($databaseName)){
            throw new QSqlDatabaseSignatureException('Call to undefined function QSqlDatabase::setDatabaseName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($this->_link){
            throw new QSqlDatabaseException('The database connection must be closed to change a database connection value');
        }
        $this->_databaseName = $databaseName;
    }
    
    public function setHostName($hostName){
        if(!is_string($hostName) && $hostName != null){
            throw new QSqlDatabaseSignatureException('Call to undefined function QSqlDatabase::setHostName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($this->_link){
            throw new QSqlDatabaseException('The database connection must be closed to change a database connection value');
        }
        $this->_hostName = $hostName;
    }
    
    public function setPassword($password){
        if(!is_string($password) && $password != null){
            throw new QSqlDatabaseSignatureException('Call to undefined function QSqlDatabase::setPassword(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($this->_link){
            throw new QSqlDatabaseException('The database connection must be closed to change a database connection value');
        }
        $this->_password = $password;
    }
    
    public function setPort($port){
        if($port != null && (!is_scalar($port) || !preg_match('/[\d]+/', $port))){
            throw new QSqlDatabaseSignatureException('Call to undefined function QSqlDatabase::setPort(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($this->_link){
            throw new QSqlDatabaseException('The database connection must be closed to change a database connection value');
        }
        $this->_port = $port;
    }
    
    public function setUsername($username){
        if($username != null && !is_string($username)){
            throw new QSqlDatabaseSignatureException('Call to undefined function QSqlDatabase::setUsername(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($this->_link){
            throw new QSqlDatabaseUsernameException('The database connection must be closed to change a database connection value');
        }
        $this->_userName = $username;
    }
    
    public function userName(){
        return $this->_userName;
    }
    
    abstract public function close();
    abstract public function commit();
    abstract public function open();
    abstract public function beginTransaction();
    abstract public function rollback($savePoint = null);
    abstract public function savePoint($savePoint);
    abstract public function release($savePoint);
    abstract public function prepare($query);
    abstract public function newQuery();
    
}

class QSqlDatabaseException extends QAbstractObjectException {}
class QSqlDatabaseSignatureException extends QSqlDatabaseException implements QSignatureException {}
class QSqlDatabaseUsernameException extends QSqlDatabaseException {}
?>