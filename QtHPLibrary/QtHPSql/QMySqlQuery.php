<?php
/**
 * @todo Start over. This class cannot work !!!!
 */
class QMySqlQuery extends QSqlQuery {
    
    private $_placeHolders,
            $_currentRow = 0;
    
    public function __construct($query = '', $database = null){
        parent::__construct($query, $database);
        $this->_placeHolders = new QStringList();
        $this->_fetchFunction = 'mysql_fetch_object';
        if($this->_query && $this->_database){
            if(!@mysqli_query($this->_database->link(), 'PREPARE ' . $this->_id . ' FROM "' . $this->_query . '"')){
                throw new QMySqlQueryPrepareException('Not a valid query', mysqli_error($this->_database->link()));
            }
        }
    }
    
    protected function _bind($placeHolder, $type, $value){
        if(!mysqli_stmt_bind_param($this->_stmt, $type, $value)){
            throw new QMySqlQueryBindException('Unable to set placeholder\'s value', mysqli_error($this->_database->link()));
        }
        $this->_placeHolders->append('@'.$placeHolder);
        return $this;
    }
    
    public function bindString($placeHolder, $value, $length = 0){
        if($length > 0){
            if(isset($value{$length})){
                throw new QMySqlQueryBindStringException('Too long string value', null);
            }
        }
        return $this->_bind($placeHolder, ($value !== null ? '"' . mysql_real_escape_string($value) . '"' : 'NULL'));
    }
    
    public function bindFloat($placeHolder, $value){
        if(!is_numeric($value)){
            throw new QMySqlQueryBindFloatException('Not a numeric value', null);
        }
        return $this->_bind($placeHolder, $value);
    }
    
    public function bindInt($placeHolder, $value){
        if(!is_numeric($value) || strpos($value, '.') !== false){
            throw new QMySqlQueryBindIntException('Not an integer value', null);
        }
        return $this->_bind($placeHolder, $value);
    }
    
    public function bindBool($placeHolder, $value){
        if($value == 'true') $value = true;
        else if($value == 'false') $value = false;
        
        if(!is_bool($value) && !($value != '1' && $value != '0')){
            throw new QMySqlQueryBindBoolException('Not a boolean value', null);
        }
        return $this->_bind($placeHolder, $value !== null ? ($value == true) : 'NULL');
    }
    
    public function bindHtml($placeHolder, $value, $length = 255){
        if(isset($value{$length})){
            throw new QMySqlQueryBindHtmlException('Too long HTML value', null);
        }
        return $this->_bind($placeHolder, $value !== null ? '"' . mysql_real_escape_string(htmlspecialchars($value, ENT_COMPAT|ENT_HTML401|ENT_HTML5|ENT_XHTML)) . '"' : 'NULL');
    }
    
    public function bindDate($placeHolder, $value, $format = null) {
        if($value instanceof QDate && $format == null){
            throw new QMySqlQueryBindDateException('Not a valid format', null);
            $value = str_replace(array('dd', 'mm', 'yyyy'), array($value->day(), $value->month(), $value->year()), $format);
        }
        $this->_bind($placeHolder, $value !== null ? '\'' . mysql_real_escape_string($value) . '\'' : 'NULL');
        return $this;
    }
    
    public function exec(){
        if(!($this->_stmt = mysql_query('EXECUTE ' . $this->_id, $this->_database->link()))){
            throw new QMySQLQueryExecuteException('Unable to execute query', mysql_error($this->_database->link()));
        }
        return $this;
    }
    
    public function fetch(){
        if($this->_stmt === false || $this->_stmt === null){
            throw new QMySqlQueryFetchException('Not a valid statement');
        }
        ++$this->_currentRow;
        $fct = $this->_fetchFunction;
        return $fct($this->_stmt);
    }
    
    public function isSelect() {
        if($this->_isSelect === null){
            $this->numRows();
        }
        return $this->_isSelect;
    }
    
    public function numRows(){
        if($this->_numRows !== null){
            return $this->_numRows;
        }
        if($this->_stmt === false || $this->_stmt === null){
            throw new QMySqlQueryStatementException('Not a valid statement');
        }
        if(($this->_numRows = @mysql_num_rows($this->_stmt)) === false){
            $this->_isSelect = false;
            $this->_numRows = mysql_affected_rows($this->_database->link());
        } else {
            $this->_isSelect = true;
        }
        return $this->_numRows;
    }
    
    public function seek($index, $relative = false){
        if($this->_stmt === false || $this->_stmt === null){
            throw new QMysqlQuerySeekException('Not a valid statement');
        }
        return mysqli_data_seek($this->_stmt, ($this->_currentRow = $relative ? $this->_currentRow + $index : $index));
    }
    
    public function setFetchMode($fetchMode){
        switch($fetchMode){
            case QSqlQuery::FETCH_ENUM:
                $this->_fetchFunction = 'mysqli_fetch_row';
                break;
            case QSqlQuery::FETCH_ASSOC:
                $this->_fetchFunction = 'mysqli_fetch_assoc';
                break;
            case QSqlQuery::FETCH_OBJECT:
                $this->_fetchFunction = 'mysqli_fetch_object';
            default :
                throw new QSqlQueryFetchModeException('"' . $fetchMode . '" is not valid');
        }
        return $this;
    }
}

class QMySqlQueryException extends QSqlQueryException {}
class QMySqlQueryPrepareException extends QSqlQueryPrepareException {}
class QMySqlQueryExecuteException extends QSqlQueryExecuteException{}
class QMySqlQueryFetchException extends QSqlQueryFetchException{}
class QSqlQueryFetchModeException extends QSqlQuerySeekException {}
class QMysqlQuerySeekException extends QSqlQuerySeekException {}

class QMySqlQueryBindException extends QMySqlQueryException implements QSqlQueryBindException {}
class QMySqlQueryBindFloatException extends QMySqlQueryBindException implements QSqlQueryBindFloatException {}
class QMySqlQueryBindIntException extends QMySqlQueryBindException implements QSqlQueryBindIntException {}
class QMySqlQueryBindStringException extends QMySqlQueryBindException implements QSqlQueryBindStringException{}
class QMySqlQueryBindBoolException extends QMySqlQueryBindException implements QSqlQueryBindBoolException{}
class QMySqlQueryBindHtmlException extends QMySqlQueryBindException implements QSqlQueryBindHtmlException{}
class QMySqlQueryBindDateException extends QMySqlQueryBindException implements QSqlQueryBindDateException{}

?>