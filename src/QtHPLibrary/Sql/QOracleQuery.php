<?php

class QOracleQuery extends QSqlQuery {

    private

    $_placeholders,
    $_stmt,
    $_currentRow,
    $_result,
    $_count = null;

    public function __construct($query = '', $database = null){
        $this->_placeHolders = new QMap;
        parent::__construct($query, $database);
        $this->_fetchFunction = 'oci_fetch_object';
    }

    protected function _bind($placeholder, $value){
        $this->_placeholders->insert($placeholder, $value);
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

    public function bindDate($placeHolder, $value, $format = null) {
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
                oci_bind_by_name($this->_stmt, $k, $v);
            }
        }
        if(!($this->_result = oci_execute($this->_stmt, OCI_NO_AUTO_COMMIT))){
            throw new QOracleQueryExecuteException($this->_database->lastError());
        }
        $this->_count = null;
        return $this;
    }

    public function fetch(){
        if($this->_result === false || $this->_result === null){
            throw new QOracleQueryFetchException('Not a valid statement', mysqli_error($this->_database->link()), mysqli_errno($this->_database->link()));
        }
        ++$this->_currentRow;
        $fct = $this->_fetchFunction;
        return $fct($this->_result);
    }

    public function isSelect(){
        if($this->_isSelect === null){
            $this->_isSelect = substr($this->_query, 0, 5) == 'SELECT';
        }
        return $this->_isSelect;
    }

    public function numRows() {
        if($this->_result === false || $this->_result === null){
            throw new QOracleQueryFetchException('You must execute the query before count', mysqli_error($this->_database->link()), mysqli_errno($this->_database->link()));
        }
        if($this->_count === null){
            $q = new self('SELECT COUNT(*) AS count FROM (' . $this->_query . ')', $this->database());
            $q->_placeholders = $this->_placeholders;
            if($r = $q->exec()->fetch()){
                $this->_count = $r->count;
            }
        } else {
            return $this->_count;
        }
    }

    public function seek($index, $relative = false) {
        if($index <= $this->_currentRow){
            $this->exec();
        }
        $i = -2;
        while(++$i < $index){
            $this->fetch();
        }
    }

}

class QMySqlQueryException extends QSqlQueryException {}
class QMySqlQueryPrepareException extends QMySqlQueryException implements QSqlQueryPrepareException {}
class QMySqlQueryExecuteException extends QMySqlQueryException implements QSqlQueryExecuteException{}
class QMySqlQueryFetchException extends QMySqlQueryException implements QSqlQueryFetchException{}
class QSqlQueryFetchModeException extends QMySqlQueryException implements QSqlQuerySeekException {}
class QMySqlQuerySeekException extends QMySqlQueryException implements QSqlQuerySeekException {}

class QMySqlQueryBindException extends QMySqlQueryException implements QSqlQueryBindException {}
class QMySqlQueryBindFloatException extends QMySqlQueryBindException implements QSqlQueryBindFloatException {}
class QMySqlQueryBindIntException extends QMySqlQueryBindException implements QSqlQueryBindIntException {}
class QMySqlQueryBindStringException extends QMySqlQueryBindException implements QSqlQueryBindStringException{}
class QMySqlQueryBindBoolException extends QMySqlQueryBindException implements QSqlQueryBindBoolException{}
class QMySqlQueryBindHtmlException extends QMySqlQueryBindException implements QSqlQueryBindHtmlException{}
class QMySqlQueryBindDateException extends QMySqlQueryBindException implements QSqlQueryBindDateException{}

?>