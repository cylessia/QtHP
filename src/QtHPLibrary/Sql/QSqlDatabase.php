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
                $this->setHostName($database->value('hostname'));
                $this->setPassword($database->value('password'));
                $this->setUsername($database->value('username'));
                $this->setDatabaseName($database->value('dbname'));
                $this->setPort($database->value('port'));
            } else if($database instanceof QRecursiveObject){
				isset($database->hostname) ? $this->setHostName($database->hostname) : null;
                isset($database->password) ? $this->setPassword($database->password) : null;
                isset($database->username) ? $this->setUsername($database->username) : null;
                isset($database->dbname) ? $this->setDatabaseName($database->dbname) : null;
                isset($database->port) ? $this->setPort($database->port) : null;
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

                if(!isset($tmp['hostname']) || !isset($tmp['username']) || !isset($tmp['dbname'])){
                    $missing = array_diff(array('hostname', 'username', 'dbname'), array_keys($tmp));
                    throw new QSqlDatabaseException('Missing database connection value' . (count($missing) > 1 ? 's' : '') . ' : ' . implode(', ', $missing));
                }
                $this->_hostName = $tmp['hostname'];
                $this->_port = isset($tmp['port']) ? $tmp['port'] : null;
                $this->_password = isset($tmp['password']) ? $tmp['password'] : null;
                $this->_userName = $tmp['username'];
                $this->_databaseName = $tmp['dbname'];
            } else if(is_array($database)){
                if(!isset($database['hostname']) || !isset($database['username']) || !isset($database['dbname'])){
                    $missing = array_diff(array('hostname', 'username', 'dbname'), array_keys($database));
                    throw new QSqlDatabaseException('Missing database connection value' . (count($missing) > 1 ? 's' : '') . ' : ' . implode(', ', $missing));
                }
                $this->_hostName = $database['hostname'];
                $this->_port = isset($database['port']) ? $database['port'] : null;
                $this->_password = isset($database['password']) ? $database['password'] : null;
                $this->_userName = $database['username'];
                $this->_databaseName = $database['dbname'];
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

    public function dbname(){
        return $this->_databaseName;
    }

    public function hostname(){
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

    public static function setCurrentConnection(QSqlDatabase $db){
        self::$_currentConnection = $db;
    }

    public function setDatabaseName($dbname){
        if(!is_string($dbname)){
            throw new QSqlDatabaseSignatureException('Call to undefined function DSqlDatabase::setDatabaseName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($this->_link){
            throw new QSqlDatabaseException('The database connection must be closed to change a database connection value');
        }
        $this->_databaseName = $dbname;
    }

    public function setHostName($hostname){
        if(!is_string($hostname) && $hostname != null){
            throw new QSqlDatabaseSignatureException('Call to undefined function DSqlDatabase::setHostName(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if($this->_link){
            throw new QSqlDatabaseException('The database connection must be closed to change a database connection value');
        }
        $this->_hostName = $hostname;
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

    public function username(){
        return $this->_userName;
    }

    abstract public function close();
    abstract public function commit();
    abstract public function open();
    abstract public function beginTransaction();
    abstract public function rollback($savePoint = null);
    abstract public function savePoint($savePoint);
    abstract public function release($savePoint);
    /**
     * @return \QSqlQuery
     */
    abstract public function prepare($query);
    /**
     * @return \QSqlQuery
     */
    abstract public function newQuery();

}

class QSqlDatabaseException extends QAbstractObjectException {}
class QSqlDatabaseSignatureException extends QSqlDatabaseException implements QSignatureException {}
class QSqlDatabaseUsernameException extends QSqlDatabaseException {}
?>