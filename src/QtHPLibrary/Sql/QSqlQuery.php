<?php

abstract class QSqlQuery extends QAbstractObject {

    const

    BindInt = 0,
    BindFloat= 1,
    BindString = 2,
    BindBool = 3,
    BindHtml = 4,
    BindDate = 5,

    FetchObject = 0,
    FetchAssoc = 1,
    FetchEnum = 2;

    protected

    $_query = null,
    $_database = null,
    $_fetchFunction,
    $_stmt,
    $_lastError,
    $_isSelect = null,
    $_numRows = null,
    $_id;

    public function __construct($query = '', $database = null){
        $this->_id = uniqid('qthp_sql_');
        if($query instanceof QSqlQuery){
            $this->_query = $query->_query;
            $this->_database = $query->_database;
            $this->_fetchFunction = $query->_fetchFunction;
        } else if(is_string($query) && $query != '' && $database instanceof QSqlDatabase){
            $this->setDatabase($database);
            $this->prepare($query);
        } else if($query instanceof QSqlDatabase && $database == null){
            $this->setDatabase($query);
        } else if($query !== null && $database !== null) {
            throw new QSqlQuerySignatureException('Call to undefined function QSqlQuery::__construct(' . implode(', ', array_map('qGetType', func_get_args ())) . ')');
        }
    }

    public function bind($type, $placeHolder, $value, $length = null){
        switch($type){
            case self::BindInt:
                return $this->bindInt($placeHolder, $value);
                break;
            case self::BindFloat:
                return $this->bindFloat($placeHolder, $value);
                break;
            case self::BindBool:
                return $this->bindBool($placeHolder, $value);
                break;
            case self::BindString:
                return $this->bindString($placeHolder, $value, $length);
                break;
            case self::BindHtml:
                return $this->bindHtml($placeHolder, $value, $length);
                break;
            case self::BindDate:
                return $this->bindDate($placeHolder, $value, $length);
                break;
            default:
                throw new QSqlQueryBindTypeException('Unknown binding type "' . $type . '"');
        }
    }

    public function database(){
        return $this->_database;
    }

    public function query(){
        return $this->_query;
    }

    public function setDatabase($db){
        if(!$db instanceof QSqlDatabase)
            throw new QSqlQuerySignatureException('Call to undefined function QSqlQuery::setDatabase(' . implode(', ', array_map('qGetType', func_get_args ())) . ')');
        $this->_database = $db;
    }

    public function prepare($query){
        if(!is_string($query)){
            throw new QSqlQuerySignatureException('Call to undefined function QSqlQuery::prepare(' . implode(', ', array_map('qGetType', func_get_args ())) . ')');
        }
        if(!$this->_database)
            throw new QSqlQueryDatabaseException('A database connection is needed to prepare a query', null);
        if($this->_query !== null){
            throw new QSqlQueryException('Query is already set !');
        }
        $this->_query = $query;
    }

    /**
     * @param string $placeholder
     * @param float $value
     * @return QSqlQuery
     */
    abstract public function bindFloat($placeHolder, $value);

    /**
     * @param string $placeholder
     * @param string $value
     * @param int $length
     * @return QSqlQuery
     */
    abstract public function bindString($placeHolder, $value, $length = 0);

    /**
     * @param string $placeholder
     * @param int $value
     * @return QSqlQuery
     */
    abstract public function bindInt($placeHolder, $value);

    /**
     * @param string $placeholder
     * @param mixed $value
     * @return QSqlQuery
     */
    abstract public function bindBool($placeHolder, $value);

    /**
     * @param string $placeholder
     * @param string $value
     * @return QSqlQuery
     */
    abstract public function bindHtml($placeHolder, $value, $length = 0);

    /**
     * @param string $placeholder
     * @param QDate|string $value
     * @return QSqlQuery
     */
    abstract public function bindDate($placeHolder, $value, $format = null);

    /**
     * @return QSqlQuery
     */
    abstract public function exec();

    /**
     * @return QSqlQuery
     */
    abstract public function fetch();

    /**
     * @return bool
     */
    abstract public function isSelect();

    /**
     * @return int
     */
    abstract public function numRows();

    /**
     * @return bool
     */
    abstract public function seek($index, $relative = false);

    /**
     * @return QSqlQuery
     */
    abstract public function setFetchMode($fetchMode);

    /**
     * @param string $placeholder
     * @param mixed $value
     * @return QSqlQuery
     */
    abstract protected function _bind($placeHolder, $value);

}

class QSqlQueryException extends QAbstractObjectException {
    private $_sqlError;
    public function __construct($message, $sqlError, $sqlCode = 0){
        parent::__construct($message);
        $this->_sqlError = $sqlError;
        $this->code = $sqlCode;
    }

    public function sqlError(){
        return $this->_sqlError;
    }
}

class QSqlQuerySignatureException extends QSqlQueryException implements QSignatureException {}
class QSqlQueryBindTypeException extends QSqlQueryException {}

interface QSqlQueryPrepareException {}
interface QSqlQueryExecuteException {}
interface QSqlQueryFetchException {}
interface QSqlQueryFetchModeException {}
interface QSqlQuerySeekException {}
interface QSqlQueryDatabaseException {}
interface QSqlQueryBindException {}
interface QSqlQueryBindIntException{}
interface QSqlQueryBindFloatException{}
interface QSqlQueryBindStringException{}
interface QSqlQueryBindDateException{}
interface QSqlQueryBindBoolException{}
interface QSqlQueryBindHtmlException{}
?>