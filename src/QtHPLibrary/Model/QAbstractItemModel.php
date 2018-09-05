<?php

/**
 * List of models
 * QAbstractItemModel
 *      | * QAbstractTableModel
 *              | * QSqlQueryModel (Read only)
 *                       | * QTableModel (Read/Write)
 *      | * QAbstractListModel
 *              | * QStringListModel
 *      | * QAbstractTreeModel
 *              | * QTreeModel
 * 
 * 
 * Comment fonctionne un modèle pour moi ?
 * Un modèle est constitué uniquement de données
 * Le but étant de ne pas pouvoir modifier la liste à partir du moment commence à fetcher
 * 
 * $model = new QStringListModel;
 * $model->setList(new QList('string'));
 * $model->append('Test');
 * $model->prepend('Test');
 * 
 * 
 * $model = new QSqlQueryModel;
 * $model->setQuery($db->prepare('SELECT * FROM users'));
 * 
 * 
 * $view->setModel($model);
 */

abstract class QAbstractItemModel extends QAbstractObject {
    
    private $_readOnly = false;
    
    protected $_data = null,
              $_columnCount = 1,
              $_headers;
    
    
    abstract public function fetch();
    abstract public function rewind();
    
    public function setReadOnly(){
        $this->_readOnly = true;
    }
    
    public function isReadOnly(){
        return $this->_readOnly;
    }
    
    public function columnCount(){
        return $this->_columnCount;
    }
    
    public function hasHeaderData(){
        return count($this->_headers) !== 0;
    }
    
    public function headerData($section){
        if($section < 0 || $section > $this->_columnCount){
            throw new QAbstractItemModelSectionHeaderIdException('Invalid section header');
        }
        return isset($this->_headers[$section]) ? $this->_headers[$section] : null;
    }
    
    public function setHeaderData($section, $value){
        if($section < 0){
            throw new QAbstractItemModelSectionHeaderIdException('Section identifier must be greater 0');
        }
        if(gettype($value) === 'object' && method_exists($value, '__toString')){
            $value = (string)$value;
        }
        if(!is_string($value)){
            throw new QAbstractItemModelSectionHeaderValueException('Section header value must be a string');
        }
        $this->_headers[$section] = $value;
    }
    
    abstract public function data($index);
    
}

class QAbstractItemModelException extends QException{}
class QAbstractItemModelSectionHeaderIdException extends QAbstractItemModelException {}
class QAbstractItemModelSectionHeaderValueException extends QAbstractItemModelException {}

?>