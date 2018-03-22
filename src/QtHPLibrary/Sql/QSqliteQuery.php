<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DSqliteQuery
 *
 * @author rbala
 */
class QSqliteQuery extends QSqlQuery {

    private $_placeHolders = null;

    public function __construct($query = '', $database = null){
        parent::__construct($query, $database);

        $this->_fetchFunction = '_fetchObject';
        if(is_string($query) && $query != ''){
            $this->_prepare($query);
        }
    }

    public function bindBool($placeHolder, $value) {
        if($value == 'true'){ $value = true; }
        else if($value == 'false'){ $value = false; }

        if(!is_bool($value) && !($value != '1' && $value != '0') && !is_int($value)){
            throw new QSqliteQueryBindBoolException('Not a boolean value', null);
        }
        return $this->_bind($placeHolder, $value !== null ? ($value == true) : 'NULL');
    }

    public function bindDate($placeHolder, $value, $format = null) {
        if($value instanceof QDate){
            $value = $value->toString($format !== null ? $format : QDate::FormatYmd);
        } else if($value instanceof QDateTime){
            $value = $value->toString($format !== null ? $format : QDateTime::FormatTimestamp);
        } else if(is_string($value)){
            QDateTime::fromFormat($value, $format);
        } else if($value !== null) {
            throw new QSqliteQueryBindDateException('Not a valid format', null);
        }
        $this->_bind($placeHolder, $value !== null ? '\'' . sqlite_escape_string($value) . '\'' : 'NULL');
        return $this;
    }

    public function bindFloat($placeHolder, $value){
        if(!is_numeric($value)){
            throw new QSqliteQueryBindFloatException('Not a numeric value', null);
        }
        return $this->_bind($placeHolder, $value);
    }

    public function bindHtml($placeHolder, $value, $length = 255){
        if(isset($value{$length})){
            throw new QSqliteQueryBindHtmlException('Too long HTML value', null);
        }
        return $this->_bind($placeHolder, $value !== null ? '\'' . sqlite_escape_string(htmlspecialchars($value, ENT_COMPAT|ENT_HTML401|ENT_HTML5|ENT_XHTML)) . '\'' : 'NULL');
    }

    public function bindInt($placeHolder, $value){
        if(!is_numeric($value) || strpos($value, '.') !== false){
            throw new QSqliteQueryBindIntException('Not an integer value', null);
        }
        return $this->_bind($placeHolder, $value);
    }

    public function bindString($placeHolder, $value, $length = 0){
        if($length > 0){
            if(isset($value{$length})){
                throw new QSqliteQueryBindStringException('Too long string value', null);
            }
        }
        return $this->_bind($placeHolder, ($value !== null ? '\'' . sqlite_escape_string($value) . '\'' : 'NULL'));
    }

    public function exec(){
        $_query = $this->_query;
        foreach($this->_placeHolders as $ph => $val){
            if($val === null){
                throw new QSqliteQueryExecuteException('Unable to execute query because of unbinded placeholder "' . $ph . '"', '');
            }
            $_query = str_replace(':' . $ph, $val, $_query);
        }
        $this->_generatedQuery = $_query;
        if($this->isSelect()){
            if(!($this->_stmt = @sqlite_query($this->_database->link(), $_query))){
                throw new QSqliteQueryExecuteException('Unable to execute query', sqlite_error_string(sqlite_last_error($this->_database->link())));
            }
        } else {
            if(!(sqlite_exec($this->_database->link(), $_query))){
                throw new QSqliteQueryExecuteException('Unable to execute query', sqlite_error_string(sqlite_last_error($this->_database->link())));
            }
        }
        return $this;
    }

    public function fetch(){
        if(!$this->_stmt){
            throw new QSqlQueryException('Unable to fetch non executed query', '');
        }
        return $this->{$this->_fetchFunction}();
    }

    public function isSelect(){
        return stripos($this->_query, 'SELECT') === 0;
    }

    public function numRows(){
        return sqlite_num_rows($this->_stmt);
    }

    public function prepare($query){
        parent::prepare($query);
        $this->_prepare($query);
    }

    public function seek($index, $relative = false){
        if($relative == false){
            if($index == 0){
                sqlite_rewind($this->_stmt);
            } else {
                sqlite_seek($this->_stmt, $index);
            }
        }
    }

    public function setFetchMode($fetchMode){
        switch($fetchMode){
            case QSqlQuery::FetchEnum:
                $this->_fetchFunction = '_fetchRow';
                break;
            case QSqlQuery::FetchAssoc:
                $this->_fetchFunction = '_fetchAssoc';
                break;
            case QSqlQuery::FetchObject:
                $this->_fetchFunction = '_fetchObject';
            default :
                throw new QSqlQueryFetchModeException('"' . $fetchMode . '" is not valid');
        }
        return $this;
    }

    protected function _bind($placeHolder, $value){
        $this->_placeHolders[$placeHolder] = $value;
    }

    private function _prepare($query){
        $this->_placeHolders = new QMap();
        preg_match_all('/:([\w]+)/', $query, $m);
        if(isset($m[1])){
            foreach($m[1] as $ph){
                $this->_placeHolders->insert($ph, null);
            }
        }
    }

    private function _fetchRow(){
        return sqlite_fetch_array($this->_stmt, SQLITE_NUM);
    }

    private function _fetchAssoc(){
        return sqlite_fetch_array($this->_stmt, SQLITE_ASSOC);
    }

    private function _fetchObject(){
        return sqlite_fetch_object($this->_stmt);
    }

    private function _isCreate(){
        return stripos($this->_query, 'CREATE TABLE') === 0;
    }
}

class QSqliteQueryException extends QSqlQueryException {}
class QSqliteQueryPrepareException extends QSqliteQueryException implements QSqlQueryPrepareException {}
class QSqliteQueryExecuteException extends QSqliteQueryException implements QSqlQueryExecuteException{}
class QSqliteQueryFetchException extends QSqliteQueryException implements QSqlQueryFetchException{}
class QSqliteQueryFetchModeException extends QSqliteQueryException implements QSqlQuerySeekException {}
class QSqliteQuerySeekException extends QSqliteQueryException implements QSqlQuerySeekException {}

class QSqliteQueryBindException extends QSqliteQueryException implements QSqlQueryBindException {}
class QSqliteQueryBindFloatException extends QSqliteQueryBindException implements QSqlQueryBindFloatException {}
class QSqliteQueryBindIntException extends QSqliteQueryBindException implements QSqlQueryBindIntException {}
class QSqliteQueryBindStringException extends QSqliteQueryBindException implements QSqlQueryBindStringException{}
class QSqliteQueryBindBoolException extends QSqliteQueryBindException implements QSqlQueryBindBoolException{}
class QSqliteQueryBindHtmlException extends QSqliteQueryBindException implements QSqlQueryBindHtmlException{}
class QSqliteQueryBindDateException extends QSqliteQueryBindException implements QSqlQueryBindDateException{}

?>