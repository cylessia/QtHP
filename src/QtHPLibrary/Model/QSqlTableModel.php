<?php

class QSqlTableModel extends QSqlQueryModel {

    private

    $_tableName = '',
    $_db;

    private static

    $_cache;

    public function __construct(QSqlDatabase $db, $table = '') {
        $this->_db = $db;
        if($table){
            $this->setTable($table);
        }
        parent::__construct();
    }

    public function setTable($table){
        $this->_tableName = $table;
        $stmt = $this->_db->prepare('SHOW COLUMNS FROM ' . $this->_tableName)->exec();
        while(($res = $stmt->fetch())){
            var_dump($stmt);
        }
    }
}

?>