<?php

/**
 * @todo : Set all the error code (and create an exception for each (or the mostly common) ?!)
 * @todo : Work the convertFromArray method to add the right number of backslashes automatically (addshlashes(addslashes($str)) ?)
 * @todo : Make convertFromArray and convertToArray recursive
 */
class QPgSqlQuery extends QSqlQuery {

    const ErrorUniqueViolation = '23505',
          ErrorInvalidName = '26000',
          ErrorStringRightTruncation = '22001',
          ErrorUndefinedColumn = '42703',
          ErrorIndeterminateDatatype = '42P18',
          ErrorUndefinedFunction = '42883';

    private $_placeHolders,
            $_currentRow = 0;

    const BindIntArray = 6,
          BindStringArray = 7;

    public function __construct($query = '', $database = null) {
        parent::__construct($query, $database);
        $this->_fetchFunction = 'pg_fetch_object';
    }

    public function bind($type, $placeHolder, $values, $length = null) {
        switch($type){
            case self::BindIntArray:
                $this->bindIntArray($placeHolder, $values);
                break;
            case self::BindStringArray:
                $this->bindString($placeHolder, $values, $length);
                break;
            default:
            parent::bind($type, $placeHolder, $values, $length);
        }
    }

    public function bindBool($placeHolder, $value) {
        if($value == 'true' || $value == 't') $value = true;
        else if($value == 'false' || $value == 'f') $value = false;

        if($value !== null && (!is_bool($value) && !($value != '1' && $value != '0'))){
            throw new QPgSqlQueryBindBoolException('Not a boolean value for placeholder "' . $placeHolder . '"', null);
        }
        return $this->_bind($placeHolder, $value !== null ? ($value == true ? 'true' : 'false') : NULL);
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

    public function bindFloat($placeHolder, $value) {
        if(!is_numeric($value)){
            throw new QPgSqlQueryBindFloatException('Not a numeric value for placeholder "' . $placeHolder . '"', null);
        }
        return $this->_bind($placeHolder, $value);
    }

    public function bindHtml($placeHolder, $value, $length = 0) {
        if($length > 0) {
            if(isset($value{$length})){
                throw new QPgSqlQueryBindHtmlException('Too long HTML value for placeholder "' . $placeHolder . '"', null);
            }
        }
        return $this->_bind($placeHolder, $value !== null ? htmlspecialchars($value, ENT_COMPAT|ENT_HTML401|ENT_HTML5|ENT_XHTML) : NULL);
    }

    public function bindInt($placeHolder, $value) {
        if($value !== null && (!is_numeric($value) || strpos($value, '.') !== false)){
            throw new QPgSqlQueryBindIntException('Not an integer value for placeholder "' . $placeHolder . '"', null);
        }
        return $this->_bind($placeHolder, $value);
    }

    public function bindIntArray($placeHolder, $values){
        if(!is_array($values) || count(($isInt = array_unique(array_map(function($v){return ctype_digit((string)$v);}, $values)))) != 1 || !$isInt[0]){
            throw new QPgSqlQueryBindIntArrayException('Array of integer must contains only integers like', null);
        }
        return $this->_bind($placeHolder, '{' . implode(',', $values) . '}');
    }

    public function bindString($placeHolder, $value, $length = 0) {
        if($length > 0){
            if(isset($value{$length})){
                throw new QPgSqlQueryBindStringException('Too long string value for placeholder "' . $placeHolder . '"', null);
            }
        }
        return $this->_bind($placeHolder, ($value !== null ? $value : null));
    }

    public function bindStringArray($placeHolder, $values, $length = 0){
        if($length > 0){
            if(!is_array($values) || count(($isStr = array_unique(array_map(function($v)use($length){return is_scalar($v) && !isset($v{$length});}, $values)))) != 1 || !$isStr[0]){
                throw new QPgSqlQueryBindStringArrayException('Value contains at least one value which is not a string or is too long');
            }
        } else if(!is_array($values) || count(($isStr = array_unique(array_map(function($v){return is_scalar($v);}, $values)))) != 1 || !$isStr[0]){
            throw new QPgSqlQueryBindIntArrayException('Array of integer must contains only integers', null);
        }
        return $this->_bind($placeHolder, '{\'' . implode('\',\'', $values) . '\'}');
    }

    public function exec() {
        $phs = array();
        if($this->_placeHolders->size()){
            foreach($this->_placeHolders as $ph){
                $phs[] = $ph;
            }
        }
        $this->_database->savePoint('sp_' . $this->_id);
        if(!pg_send_execute($this->_database->link(), $this->_id, $phs)){
            $code = pg_result_error_field(pg_get_result($this->_database->link()), PGSQL_DIAG_SQLSTATE);
            $message = pg_result_error_field($this->_stmt, PGSQL_DIAG_MESSAGE_PRIMARY);
//            echo '<hr />';
//            var_dump('false', $error, $message);
//            echo '<hr />';
            $this->_database->rollback('sp_' . $this->_id);
            $this->_throwException($code, $message);
        }
        $this->_stmt = pg_get_result($this->_database->link());
        if(($code = pg_result_error_field($this->_stmt, PGSQL_DIAG_SQLSTATE))){
            $message = pg_result_error_field($this->_stmt, PGSQL_DIAG_MESSAGE_PRIMARY);
//            echo '<hr />';
//            var_dump('false', $error, $message);
//            echo '<hr />';
            $this->_database->rollback('sp_' . $this->_id);
            $this->_throwException($code, $message);
        }
        $this->_database->release('sp_' . $this->_id);
        return $this;
    }

    public function fetch(){
        if($this->_stmt === false || $this->_stmt === null){
            throw new QPgSqlQueryFetchException('Not a valid statement, maybe the query was not executed', null);
        }
        ++$this->_currentRow;
        $fct = $this->_fetchFunction;
        return $fct($this->_stmt);
    }

    public function isSelect(){
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
            throw new QPgSqlQueryStatementException('Not a valid statement');
        }
        if(($n = pg_num_rows($this->_stmt)) > ($a = pg_affected_rows($this->_stmt))){
            $this->_numRows = $n;
            $this->_isSelect = true;
        } else {
            $this->_numRows = $a;
            $this->_isSelect = false;
        }
//        if(($affected = pg_affected_rows($this->_stmt)) !== false){
//            $this->_numRows = $affected;
//            $this->_isSelect = false;
//        } else {
//            $this->_numRows = pg_num_rows($this->_stmt);
//            $this->_isSelect = true;
//        }
//        var_dump(pg_affected_rows($this->_stmt), pg_num_rows($this->_stmt));
        return $this->_numRows;
    }

    public function prepare($query){
        parent::prepare($query);
        $this->_analyzeQueryBindings();
        $this->_database->savepoint('sp_' . $this->_id);
        if(!pg_send_prepare($this->_database->link(), $this->_id, $this->_query)){
            throw new QPgSqlQueryPrepareException('Not a valid query', pg_last_error($this->_database->link()));
        }
        // Uncomment one of both next lines
        //pg_get_result($this->_database->link());
        $res = pg_get_result($this->_database->link());
        if(($code = pg_result_error_field($res, PGSQL_DIAG_SQLSTATE))){
            $message = pg_result_error_field($res, PGSQL_DIAG_MESSAGE_PRIMARY);
            //var_dump($res, $code, $message);
            $this->_database->rollback('sp_' . $this->_id);
            $this->_throwException($code, $message);
        }
        $this->_database->release('sp_' . $this->_id);
    }

    public function seek($index, $relative = false) {
        if($relative)
            $this->_currentRow += $index;
        else
            $this->_currentRow = $index;
        return pg_result_seek($this->_stmt, $this->_currentRow);
    }

    public function setFetchMode($fetchMode) {
        switch($fetchMode){
            case QSqlQuery::FetchAssoc:
                $this->_fetchFunction = 'pg_fetch_assoc';
                break;
            case QSqlQuery::FetchEnum:
                $this->_fetchFunction = 'pg_fetch_row';
                break;
            case QSqlQuery::FetchObject:
                $this->_fetchFunction = 'pg_fetch_object';
                break;
            default:
                throw new QPgSqlQueryFetchModeException('"' . $fetchMode . '" is not valid');
        }
        return $this;
    }

    protected function _bind($placeHolder, $value) {
        try{$this->_placeHolders->value($placeHolder);}catch(QMapException $e){
            throw new QPgSqlQueryBindException(isset($placeHolder{0}) && $placeHolder{0} == ':' ? 'No need to set ":" while binding placeholders. Error found while binding ' . $placeHolder : 'Unknown placeholder : ' . $placeHolder, null);
        }
        $this->_placeHolders->insert($placeHolder, $value);
        return $this;
    }

    public static function convertToArray($pgArray){
        if($pgArray === null)
            return null;
        if(!is_string($pgArray)){
            throw new QPgSqlQuerySignatureException('Call to undefined function QPgSqlQuery::convertToArray(' . implode(', ', array_map('qGetType', func_get_args())) . ')', null);
        }
        $length = strlen($pgArray);

        if($length == 0){
            return null;
        }

        // Is it well formed ?
        if($length < 2 || substr($pgArray, 0, 1) != '{' || substr($pgArray, -1) != '}'){
            throw new QPgSqlQueryConvertInvalidArrayException('Not a valid postgres array', null);
        }

        // Is it an empty array ?
        if($length == 2){
            return array();
        }

        // Analyze !
        $i = 0;
        $inStr = false;
        $currentValue = null;
        $array = array();
        $bs = 0;

        while(++$i < $length-1){
            $chr = substr($pgArray, $i, 1);
            if($inStr){
                if($chr == '\\'){
                    ++$bs;
                } else {
                    if($bs)
                        $bs = 0;
                    if($chr == '"' && $bs % 2 == 0){
                        $inStr = false;
                    } else {
                        $currentValue .= $chr;
                    }
                }
            } else if($chr == ','){
                $array[] = $currentValue;
                $currentValue = null;
            } else if($chr == '"'){
                $inStr = true;
            } else {
                $currentValue .= $chr;
            }
        }
        if($currentValue !== null)
            $array[] = $currentValue;
        return $array;
    }

    public static function convertFromArray($array){
        if($array === null)
            return null;
        if(!is_array($array)){
            throw new QPgSqlQuerySignatureException('Call to undefined function QPgSqlQuery::convertFromArray(' . implode(', ', array_map('qGetType', func_get_args())) . ')', null);
        }

        return '{' . implode(', ', array_map(function($v){return is_string($v) ? '"' . $v . '"' : $v;}, $array)) . '}';
    }

    /**
     * @todo Complete checking pgsql cast and bakslashes length before quote !
     */
    private function _analyzeQueryBindings(){
        $this->_placeHolders = new QMap;
        $length = strlen($this->_query);
        $i = 0;
        $ph = '';
        $inStr = '';
        $query = '';
        while($i < $length){
            if($this->_query{$i} == '\'' || $this->_query{$i} === '"'){
                if($inStr !== '' && $inStr === $this->_query{$i}){
                    $inStr = '';
                } else if($inStr === ''){
                    $inStr = $this->_query{$i};
                }
                $query .= $this->_query{$i};
            } else if($inStr === '' && $this->_query{$i} === ':' && $i >= 1 && $this->_query{$i-1} != ':'){ // PgSql type cast is ::TYPE
                // Placeholder caught !
                /*
                 * 0 => 48
                 * 9 => 57
                 * A => 65
                 * Z => 90
                 * a => 97
                 * z => 122
                 * _ => 95
                 */
                ++$i;
                while($i < $length && ((($char = ord($this->_query{$i})) > 64 && $char < 91) || $char > 96 && $char < 123 || $char > 47 && $char < 58 || $char === 95)){
                    $ph .= $this->_query{$i++};
                }
                if(!$ph)
                    throw new QPgSqlQueryParseQueryException('Empty placeholder detected at offset ' . $i, $this->_query);
                $this->_placeHolders->insert($ph, null);
                $query .= '$' . $this->_placeHolders->size() . ($i < $length ? $this->_query{$i} : '');
                $ph = null;
            } else {
                $query .= $this->_query{$i};
            }
            ++$i;
        }
        if($this->_placeHolders->size()){
            $this->_query = $query;
        }
    }

    private function _throwException($code, $message){
        switch($code){
            case self::ErrorUniqueViolation:
                throw new QPgSqlQueryUniqueViolationException('Unable to execute query', $message);
                break;
            case self::ErrorInvalidName:
                throw new QPgSqlQueryInvalidStatementNameException('Invalid statement name', $message);
                break;
            case self::ErrorStringRightTruncation:
                throw new QPgSqlQueryRightTruncationException('Too long value', $message);
                break;
            case self::ErrorUndefinedColumn:
                throw new QPgSqlQueryUndefinedColumnException('Undefined column', $message);
                break;
            case self::ErrorIndeterminateDatatype:
                throw new QPgSqlQueryIndeterminateDatatypeException('Indeterminate datatype', $message);
                break;
            case self::ErrorUndefinedFunction:
                throw new QPgSqlQueryUndefinedFunctionException('Undefined function', $message);
                break;
            default:
                throw new QPgSqlQueryExecuteException('Unable to execute query', $message, $code);
        }
    }

}

class QPgSqlQueryException extends QSqlQueryException{}

class QPgSqlQuerySignatureException extends QPgSqlQueryException implements QSignatureException {}

class QPgSqlQueryFetchException extends QPgSqlQueryException implements QSqlQueryFetchException{}
class QPgSqlQueryFetchModeException extends QPgSqlQueryException implements QSqlQueryFetchModeException{}
class QPgSqlQueryPrepareException extends QPgSqlQueryException implements QSqlQueryPrepareException{}

class QPgSqlQueryBindException extends QPgSqlQueryException implements QSqlQueryBindException{}
class QPgSqlQueryBindIntException extends QPgSqlQueryBindException implements QSqlQueryBindIntException{}
class QPgSqlQueryBindFloatException extends QPgSqlQueryException implements QSqlQueryBindFloatException{}
class QPgSqlQueryBindStringException extends QPgSqlQueryException implements QSqlQueryBindStringException{}
class QPgSqlQueryBindHtmlException extends QPgSqlQueryException implements QSqlQueryBindHtmlException{}
class QPgSqlQueryBindBoolException extends QPgSqlQueryException implements QSqlQueryBindBoolException{}
class QPgSqlQueryBindDateException extends QPgSqlQueryException implements QSqlQueryBindDateException{}

class QPgSqlQueryBindArrayException extends QPgSqlQueryBindException{}
class QPgSqlQueryBindIntArrayException extends QPgSqlQueryBindArrayException{}
class QPgSqlQueryBindStringArrayException extends QPgSqlQueryBindArrayException{}

class QPgSqlQueryConvertInvalidArrayException extends QPgSqlQueryException {}

class QPgSqlQueryParseQueryException extends QPgSqlQueryException {}

class QPgSqlQueryExecuteException extends QPgSqlQueryException implements QSqlQueryExecuteException{
    public function __construct($message, $sqlError, $code = null) {
        parent::__construct($message, $sqlError);
        $this->code = $code;
    }
}
class QPgSqlQueryUniqueViolationException extends QPgSqlQueryExecuteException {}
class QPgSqlQueryInvalidStatementNameException extends QPgSqlQueryExecuteException {}
class QPgSqlQueryRightTruncationException extends QPgSqlQueryExecuteException {}
class QPgSqlQueryUndefinedColumnException extends QPgSqlQueryExecuteException {}
class QPgSqlQueryUndefinedFunctionException extends QPgSqlQueryExecuteException {}
class QPgSqlQueryIndeterminateDatatypeException extends QPgSqlQueryExecuteException {}
?>
