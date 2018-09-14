<?php

class QSqlTableModel extends QSqlQueryModel {

    private

    $_tableName = '',
    $_db;

    protected static

    $_cache;

    public function __construct(QSqlDatabase $db, $table = '') {
        $this->_db = $db;
       if($table){
            $this->setTable($table);
        }
        parent::__construct();
    }

    public function db(){
        return $this->_db;
    }

    public function setTable($table){
        $this->_tableName = $table;
        $stmt = $this->_db->prepare('SHOW COLUMNS FROM ' . $this->_tableName)->exec();
        while(($res = $stmt->fetch())){
            self::$_cache[$this->_tableName][$res->Field] = (object)[
                'name' => $res->Field,
                'type' => substr($res->Type, 0, ($pos = strpos($res->Type, '(')) !== false ? $pos : null),
                'defaultValue' => $res->Default,
                'auto' => $res->Extra == 'auto_increment'
            ];
        }
    }

    public function tableName(){
        return $this->_tableName;
    }
}

?>