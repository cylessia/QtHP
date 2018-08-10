<?php

class QMySqlQuery extends QSqlQuery {

    private

    $_placeHolders,
    $_placeHoldersList,
    $_fields = array(),
    $_currentRow = -1;

    private static $_bindTypes = array(
        'integer' => 'i',
        'float' => 'd',
        'double' => 'd',
        'string' => 's',
        'NULL' => 's'
    );

    public function __construct($query = '', $database = null){
        $this->_placeHolders = new QMap();
        $this->_placeHoldersList = new QVector();
        parent::__construct($query, $database);
        $this->_fetchFunction = '_fetchObject';
    }

    public function __destruct() {
        parent::__destruct();
        if($this->_stmt){
            mysqli_stmt_free_result($this->_stmt);
            mysqli_stmt_close($this->_stmt);
        }
    }

    protected function _bind($placeHolder, $value){
        $this->_placeHolders->insert($placeHolder, $value);
        return $this;
    }

    public function bindString($placeHolder, $value, $length = 0){
        if($length > 0 && isset($value{$length})){
            throw new QMySqlQueryBindStringException('Too long string value', null);
        }
        return $this->_bind($placeHolder, $value);
    }

    public function bindFloat($placeHolder, $value){
        if(!is_numeric($value)){
            throw new QMySqlQueryBindFloatException('Not a numeric value', null);
        }
        return $this->_bind($placeHolder, (float)$value);
    }

    public function bindInt($placeHolder, $value){
        if(!is_numeric($value) || strpos($value, '.') !== false){
            throw new QMySqlQueryBindIntException('Not an integer value', null);
        }
        return $this->_bind($placeHolder, (int)$value);
    }

    public function bindBool($placeHolder, $value){
        if($value == 'true'){ $value = true;}
        else if($value == 'false'){ $value = false;}

        if(!is_bool($value) && !($value != '1' && $value != '0')){
            throw new QMySqlQueryBindBoolException('Not a boolean value', null);
        }
        return $this->_bind($placeHolder, $value !== null ? (int)($value == true) : null);
    }

    public function bindHtml($placeHolder, $value, $length = 0){
        if($length > 0 && isset($value{$length})){
            throw new QMySqlQueryBindHtmlException('Too long HTML value', null);
        }
        return $this->_bind($placeHolder, $value !== null ? htmlspecialchars($value, ENT_COMPAT|ENT_HTML401|ENT_HTML5|ENT_XHTML) : null);
    }

    public function bindDate($placeHolder, $value, $format = null) {
        if($value instanceof QDate){
            $value = $value->toString($format !== null ? $format : QDate::FormatYmd);
        } else if($value instanceof QDateTime){
            $value = $value->toString($format !== null ? $format : QDateTime::FormatTimestamp);
        } else if(is_string($value)){
            QDateTime::fromFormat($value, $format);
        } else if($value !== null) {
            throw new QMySqlQueryBindDateException('Not a valid format', null);
        }
        return $this->_bind($placeHolder, $value);
    }

    public function exec(){
        if($this->_placeHolders->size()){
            $params = array($this->_stmt, '');
            foreach($this->_placeHoldersList as $k){
                $params[1] .= self::$_bindTypes[gettype($this->_placeHolders->value($k))];
                $params[] = &$this->_placeHolders->get($k);
            }
            if(!call_user_func_array('mysqli_stmt_bind_param', $params)){
                throw new QMySqlQueryBindException('Unable to set placeholder\'s "' . $k . '" value', mysqli_error($this->_database->link()));
            }
        }
        if(!mysqli_stmt_execute($this->_stmt) || !mysqli_stmt_store_result($this->_stmt)){
            throw new QMySqlQueryExecuteException('Unable to execute query : ' . $this->_query, mysqli_error($this->_database->link()), mysqli_errno($this->_database->link()));
        }
        if(!count($this->_fields) && $this->_stmt){
            if($result = mysqli_stmt_result_metadata($this->_stmt)){
                $params = array();
                $params[] = $this->_stmt;
                foreach(mysqli_fetch_fields($result) as $field){
                    $this->_fields[$field->name] = &${$field->name};
                    $params[] = &$this->_fields[$field->name];
                }
                call_user_func_array('mysqli_stmt_bind_result', $params);
                mysqli_free_result($result);
            }
        }
        if(mysqli_errno($this->_database->link()) !== 0){
            throw new QMySqlQueryExecuteException('Unable to execute query : ' . $this->_query, mysqli_error($this->_database->link()), mysqli_errno($this->_database->link()));
        }
        return $this;
    }

    public function fetch(){
        if(!count($this->_fields)){
            throw new QMySqlQueryFetchException('Not a valid statement', mysqli_error($this->_database->link()), mysqli_errno($this->_database->link()));
        }
        ++$this->_currentRow;
        $fct = $this->_fetchFunction;
        $res = mysqli_stmt_fetch($this->_stmt);
        if($res === false){
            throw new QMySqlQueryFetchException('Unable to fetch next data');
        }
        return $res === null ? null : $this->{$this->_fetchFunction}();
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
        if(!($this->_numRows = mysqli_stmt_affected_rows($this->_stmt)) === false){
            $this->_isSelect = true;
            $this->_numRows = mysqli_stmt_num_rows($this->_stmt);
        } else {
            $this->_isSelect = false;
        }
        return $this->_numRows;
    }

    public function seek($index, $relative = false){
        if($this->_stmt === false || $this->_stmt === null){
            throw new QMySqlQuerySeekException('Not a valid statement');
        }
        return mysqli_stmt_data_seek($this->_stmt, ($this->_currentRow = ($relative ? $this->_currentRow + $index : $index)));
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
                throw new QMySqlQueryFetchModeException('"' . $fetchMode . '" is not valid');
        }
        return $this;
    }

    public function prepare($query){
        parent::prepare($query);
        $this->_prepare($query);
        if(!($this->_stmt = mysqli_prepare($this->_database->link(), $this->_query))){
            throw new QMySqlQueryPrepareException('Not a valid query : ' . $this->_query, mysqli_error($this->_database->link()));
        }
    }

    private function _prepare($query){
        preg_match_all('/"([^"\\\\]*(\\\\.[^"\\\\]*)*)"|\'([^\'\\\\]*(\\\\.[^\'\\\\]*)*)\'/', $query, $m, PREG_OFFSET_CAPTURE);
        $offset = 0;
        $i = -1;
        $replacements = [];
        foreach($m[0] as $v){
            ++$i;
            $replacements[] = substr($query, $v[1]+$offset, ($diff = strlen($v[0])));
            $query = substr_replace($query, 'qthp_sqlstr_' . $i, $v[1]+$offset, $diff);
            $offset += (strlen('qthp_sqlstr_' . $i) - $diff);
        }
        preg_match_all('/:([\w_]+)/', $query, $m);
        if(isset($m[1])){
            foreach($m[1] as $ph){
                $this->_placeHoldersList->append($ph);
                $query = substr_replace($query, '?', strpos($query, ':' . $ph), strlen($ph)+1);
            }
        }
        $i = -1;
        foreach($replacements as $k => $v){
            $query = str_replace('qthp_sqlstr_' . (++$i), $v, $query);
        }
        $this->_query = $query;
    }

    private function _fetchRow(){
        return array_merge($this->_fields, array_values($this->_fields));
    }

    private function _fetchAssoc(){
        return $this->_fields;
    }

    private function _fetchObject(){
        return (object)$this->_fields;
    }
}

class QMySqlQueryException extends QSqlQueryException {}
class QMySqlQueryPrepareException extends QMySqlQueryException implements QSqlQueryPrepareException {}
class QMySqlQueryExecuteException extends QMySqlQueryException implements QSqlQueryExecuteException{}
class QMySqlQueryFetchException extends QMySqlQueryException implements QSqlQueryFetchException{}
class QMySqlQuerySeekException extends QMySqlQueryException implements QSqlQuerySeekException {}
class QMySqlQueryFetchModeException extends QMySqlQueryException implements QSqlQueryFetchModeException {}

class QMySqlQueryBindException extends QMySqlQueryException implements QSqlQueryBindException {}
class QMySqlQueryBindFloatException extends QMySqlQueryBindException implements QSqlQueryBindFloatException {}
class QMySqlQueryBindIntException extends QMySqlQueryBindException implements QSqlQueryBindIntException {}
class QMySqlQueryBindStringException extends QMySqlQueryBindException implements QSqlQueryBindStringException{}
class QMySqlQueryBindBoolException extends QMySqlQueryBindException implements QSqlQueryBindBoolException{}
class QMySqlQueryBindHtmlException extends QMySqlQueryBindException implements QSqlQueryBindHtmlException{}
class QMySqlQueryBindDateException extends QMySqlQueryBindException implements QSqlQueryBindDateException{}

?>