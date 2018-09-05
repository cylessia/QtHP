<?php

class QOracleQuery extends QSqlQuery {

    private

    $_placeholders,
    $_currentRow;

    public function __construct($query = '', $database = null){
        $this->_placeholders = new QMap;
        parent::__construct($query, $database);
        $this->_fetchFunction = 'oci_fetch_object';
    }

    protected function _bind($placeholder, $value){
        $this->_placeholders->insert($placeholder, $value);
        return $this;
    }

    public function bindArray($placeholder, $values){
        if(count($values) > 1000){
            throw new QOracleQueryBindArrayException('Unable to bind more than 1000 items in a list', null);
        }
        $k = -1;
        foreach($values as $value){
            $this->_placeholders->insert($placeholder . (++$k), $value);
        }
        return $this;
    }

    public function bindString($placeholder, $value, $length = 0) {
        if($length > 0){
            if(isset($value{$length})){
                throw new QOracleQueryBindStringException('Too long string value', null);
            }
        }
        return $this->_bind($placeholder, $value);
    }

    public function bindFloat($placeholder, $value) {
        if(!is_numeric($value)){
            throw new QOracleQueryBindFloatException('Not a numeric value', null);
        }
        return $this->_bind($placeholder, (float)$value);
    }

    public function bindInt($placeholder, $value){
        if(!is_numeric($value) || strpos($value, '.') !== false){
            throw new QOracleQueryBindIntException('Not an integer value', null);
        }
        return $this->_bind($placeholder, (int)$value);
    }

    public function bindBool($placeholder, $value){
        if($value == 'true'){ $value = true; }
        else if($value == 'false') { $value = false;}

        if(!is_bool($value) && !($value != '1' && $value = '0')){
            throw new QOracleQueryBindBoolException('Not a boolean value', null);
        }
        return $this->_bind($placeholder, $value !== null ? (int)($value == true) : null);
    }

    public function bindHtml($placeholder, $value, $length = 0){
        if(isset($value{$length})){
            throw new QOracleQueryBindHtmlException('Too long HTML value', null);
        }
        return $this->_bind($placeholder, ($value !== null ? htmlspecialchars($value, ENT_COMPAT|ENT_HTML401|ENT_HTML5|ENT_XHTML) : null));
    }

    public function bindDate($placeholder, $value, $format = null) {
        if($value instanceof QDate){
            $value = $value->toString($format !== null ? $format : QDate::FormatYmd);
        } else if($value instanceof QDateTime) {
            $value = $value->toString($format !== null ? $format : QDateTime::FormatTimestamp);
        } else if(is_string($value)){
            QDateTime::fromFormat($value, $format);
        } else if($value !== null) {
            throw new QOracleQueryBindDateException('Not a valid format', null);
        }
        return $this->_bind($placeholder, $value);
    }

    public function exec(){
        if($this->_placeholders->size()){
            foreach($this->_placeholders as $k => $v){
                if(!oci_bind_by_name($this->_stmt, $k, $this->_placeholders->get($k))){
                    throw new QOracleQueryBindException('Unable to bind "' . $k . '"', $this->_database->lastError($this->_stmt));
                }
            }
        }
        if(!oci_execute($this->_stmt, OCI_NO_AUTO_COMMIT)){
            throw new QOracleQueryExecuteException('Unable to execute : ' . $this->_query, oci_error());
        }
        $this->_numRows = null;
        return $this;
    }

    public function fetch(){
        if($this->_stmt === false || $this->_stmt === null){
            throw new QOracleQueryFetchException('Not a valid statement', mysqli_error($this->_database->link()), mysqli_errno($this->_database->link()));
        }
        ++$this->_currentRow;
        $fct = $this->_fetchFunction;
        return ($res = $fct($this->_stmt)) ? $res : null;
    }

    public function isSelect(){
        if($this->_isSelect === null){
            $this->_isSelect = substr($this->_query, 0, 5) == 'SELECT';
        }
        return $this->_isSelect;
    }

    public function numRows() {
        if($this->_stmt === false || $this->_stmt === null){
            throw new QOracleQueryFetchException('You must execute the query before count', mysqli_error($this->_database->link()), mysqli_errno($this->_database->link()));
        }
        if($this->_numRows === null){
            $q = new self('SELECT COUNT(*) AS count FROM (' . $this->_query . ')', $this->database());
            $q->_placeholders = $this->_placeholders;
            if($r = $q->exec()->fetch()){
                $this->_numRows = $r->count;
            }
        } else {
            return $this->_numRows;
        }
    }

    public function seek($index, $relative = false) {
        if($index <= $this->_currentRow){
            $this->exec();
        }
        $i = $this->_numRows;
        while(++$i < $index){
            $this->fetch();
        }
    }

    public function setFetchMode($fetchMode){
        switch($fetchMode){
            case QSqlQuery::FETCH_ENUM:
                $this->_fetchFunction = 'oci_fetch_array';
                break;
            case QSqlQuery::FETCH_ASSOC:
                $this->_fetchFunction = 'oci_fetch_assoc';
                break;
            case QSqlQuery::FETCH_OBJECT:
                $this->_fetchFunction = 'oci_fetch_object';
            default :
                throw new QSqlQueryFetchModeException('"' . $fetchMode . '" is not valid');
        }
        return $this;
    }

    public function prepare($query){
        parent::prepare($query);
        if(!($this->_stmt = oci_parse($this->_database->link(), $query))){
            throw new QOracleQueryPrepareException('Not a valid query :' . $this->_query, $this->_database->lastError());
        }
    }

}

class QOracleQueryException extends QSqlQueryException {}
class QOracleQueryPrepareException extends QOracleQueryException implements QSqlQueryPrepareException {}
class QOracleQueryExecuteException extends QOracleQueryException implements QSqlQueryExecuteException{}
class QOracleQueryFetchException extends QOracleQueryException implements QSqlQueryFetchException{}
class QOracleQueryFetchModeException extends QOracleQueryException implements QSqlQueryFetchModeException{}
class QOracleQuerySeekException extends QOracleQueryException implements QSqlQuerySeekException {}

class QOracleQueryBindException extends QOracleQueryException implements QSqlQueryBindException {}
class QOracleQueryBindFloatException extends QOracleQueryBindException implements QSqlQueryBindFloatException {}
class QOracleQueryBindIntException extends QOracleQueryBindException implements QSqlQueryBindIntException {}
class QOracleQueryBindStringException extends QOracleQueryBindException implements QSqlQueryBindStringException{}
class QOracleQueryBindBoolException extends QOracleQueryBindException implements QSqlQueryBindBoolException{}
class QOracleQueryBindHtmlException extends QOracleQueryBindException implements QSqlQueryBindHtmlException{}
class QOracleQueryBindDateException extends QOracleQueryBindException implements QSqlQueryBindDateException{}
class QOracleQueryBindArrayException extends QOracleQueryBindException implements QSqlQueryBindException{}

?>