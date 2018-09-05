<?php
/**
 * Description of DSqliteDatabase
 *
 * @author rbala
 */
class QSqliteDatabase extends QSqlDatabase {

    private $_path,
            $_began = false;

    public function __construct($database = null, $autoOpen = true){
        if($database != null){
            if($database instanceof QSqliteDatabase){
                $this->_path = $database->_path;
                $this->_databaseName = $database->_databaseName;
            } else if($database instanceof QSettings){
                $this->setPath($database->value('path'));
                $this->setDatabaseName($database->value('databaseName'));
            } else if(is_string($database)) {
                if(!preg_match('/[\w_.-]+=[\w_.-]*(;[\w_.-]+=[\w_.-]*)*+/', $database)){
                    throw new QSqlDatabaseException('Invalid database connection string syntax');
                }

                $tmpTmp = explode(';', $database);
                $tmp = array();
                foreach($tmpTmp as $opt){
                    $tmpOpt = explode('=', $opt);
                    $tmp[$tmpOpt[0]] = $tmpOpt[1] == null ? '' : $tmpOpt[1];
                }
                if(!isset($tmp['path']) || !isset($tmp['databaseName'])){
                    $missing = array();
                    foreach(array('path', 'databaseName') as $k){
                        if(!isset($database[$k])){
                            $missing[] = $k;
                        }
                    }
                    throw new QSqlDatabaseException('Missing database connection value' . (count($missing) > 1 ? 's' : '') . ' : ' . implode(', ', $missing));
                }
                $this->_path = $tmp['path'];
                $this->_databaseName = $tmp['databaseName'];
            } else if(is_array($database)){

                if(!isset($database['path']) || !isset($database['databaseName'])){
                    $missing = array();
                    foreach(array('path', 'databaseName') as $k){
                        if(!isset($database[$k])){
                            $missing[] = $k;
                        }
                    }
                    throw new QSqlDatabaseException('Missing database connection value' . (count($missing) > 1 ? 's' : '') . ' : ' . implode(', ', $missing));
                }
                $this->_path = $database['path'];
                $this->_databaseName = $database['databaseName'];
            } else {
                throw new QSqlDatabaseException('Call to undefined signature DSqliteDatabase::DSqliteDatabase(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
            }
            if($autoOpen === true){
                $this->open();
            }
        }
        // Call parent with null, null because
        // we override the connection part !
        parent::__construct();
    }

    public function setPath($path){
        if($this->_link){
            throw new QSqliteDatabaseException('The database connection must be closed to change a database connection value');
        }
        if($path instanceof QDir){
            $this->_path = $path->path();
        } else if(is_string($path)){
            $this->_path = QDir::cleanPath($path);
        } else {
            throw new QSqliteDatabaseSignatureException('Call to undefined function DSqlDatabase::setPassword(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }

    public function path(){
        return $this->_path;
    }

    public function close(){
        if(!$this->isOpen()){
            throw new QSqliteDatabaseException('Database connection must be opened to be closed');
        }
        if($this->_began){
            $this->rollback();
        }
        if($this->_link){
            @sqlite_close($this->_link);
        }
    }

    public function commit(){
        if(!$this->isOpen()){
            throw new QSqliteDatabaseException('Database connection must be opened to commit transaction');
        }
        if(sqlite_exec($this->_link, 'COMMIT', $str)){
            throw new QSqliteDatabaseException('Unable to commit transaction : ' . $str);
        }
        $this->_began = false;
    }

    public function open(){
        if(!($this->_link = @sqlite_open($this->_path . $this->_databaseName, 0666, $str))){
            throw new QSqliteDatabaseException('Unable to connect to ' . $this->_path . $this->_databaseName);
        }
    }

    public function beginTransaction() {
        if(!$this->isOpen()){
            throw new QSqliteDatabaseException('Database connection must be opened to commit transaction');
        }
        if(!$this->_began){
            if(!sqlite_exec($this->_link, 'BEGIN', $str)){
                throw new QSqliteDatabaseException('Unable to start transaction : ' . $str);
            }
            $this->_began = true;
        }
    }

    public function lastInsertId($tableName){
        $stmt = sqlite_query($this->_link, 'SELECT rowid FROM ' . $tableName);
        $res = sqlite_fetch_array($stmt, SQLITE_ASSOC);
        return $res['rowid'];
    }

    /**
     *
     * @param type $queryName
     * @return \DSqliteQuery
     */
    public function load($queryName){
        return new QSqliteQuery(parent::load($queryName), $this);
    }

    public function rollback($savePoint = null) {
        if(!is_scalar($savePoint)){
            throw new QSqliteDatabaseException('SQLite savepoint name must be a scalar value');
        }
        if(!sqlite_exec($this->_link, 'ROLLBACK' . ($savePoint != null ? ' TO SAVEPOINT' . $savePoint : ''))){
            throw new QSqliteDatabaseException('Unable to create savepoint "' . $savePoint . '"');
        }
        if(!$savePoint){
            $this->_began = false;
        }
    }

    public function savePoint($savePoint){
        if(!is_scalar($savePoint)){
            throw new QSqliteDatabaseException('SQLite savepoint name must be a scalar value');
        }
        if(!sqlite_exec($this->_link, 'SAVEPOINT ' . $savePoint)){
            throw new QSqliteDatabaseException('Unable to create savepoint "' . $savePoint . '"');
        }
    }

    public function release($savePoint) {
        if(!is_scalar($savePoint)){
            throw new QSqliteDatabaseException('SQLite savepoint name must be a scalar value');
        }
        if(!sqlite_exec($this->_link, 'RELEASE SAVEPOINT ' . $savePoint)){
            throw new QSqliteDatabaseException('Unable to create savepoint "' . $savePoint . '"');
        }
    }

    public function prepare($query){
        return new QSqliteQuery($query, $this);
    }

    public function newQuery(){
        return new QSqliteQuery($this);
    }

    public function __wakeup() {
        $this->open();
    }
}

class QSqliteDatabaseException extends QSqlDatabaseException {}