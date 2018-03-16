<?php

class QResource extends QAbstractObject {
    const Sql = 'sql',
          Form = 'form',
          View = 'view';
    
    private $_path;
    
    public function __construct(QSettings $settings){
        $this->_path = new QMap;
        
        try {$this->_path->insert(self::Sql, $settings->value('path/'.self::Sql));}catch(QSettingsException $e){}
        try {$this->_path->insert(self::Form, $settings->value('path/'.self::Form));}catch(QSettingsException $e){}
        try {$this->_path->insert(self::View, $settings->value('path/'.self::View));}catch(QSettingsException $e){}
    }
    
    /**
     * Prepare a sql resource
     * @param string $resource The name of the resource
     * @return QSqlDatabase
     * @throws QResourceSqlException
     */
    public function sql($resource){
        if(!file_exists(($filePath = $this->_path->value(self::Sql) . $resource . '.sql'))){
            throw new QResourceSqlException('Unknown sql resource "' . $resource . '" searching file at "' . dirname($filePath) . '"');
        }
        $f = new QFile($filePath);
        return QSqlDatabase::current()->prepare($f->open(QFile::ReadOnly)->read());
    }
    
    /**
     * Get a form
     * @param string $resource The name of the form
     * @return QFormLayout
     * @throws QResourceFormException
     */
    public function form($resource){
        if(!file_exists(($filePath = $this->_path->value(self::Form) . $resource . '.php'))){
            throw new QResourceFormException('Unknown form resource "' . $resource . '"');
        }
        $form = require $filePath;
        if(!$form instanceof QFormLayout){
            throw new QResourceFormException('Not a valid form');
        }
        return $form;
    }
    
    /**
     * Get a view
     * @param string $resource The name of the view
     * @return QView
     * @throws QResourceViewException
     */
    public function view($resource){
        if(!file_exists(($filePath = $this->_path->value(self::View) . $resource . '.tpl'))){
            throw new QResourceViewException('Unknown view resource "' . $resource . '"');
        }
        return new QView($filePath);
    }
}

class QResourceException extends QAbstractObjectException {}
class QResourceFormException extends QResourceException {}
class QResourceSqlException extends QResourceException {}
class QResourceViewException extends QResourceException {}
class QResourceTypeException extends QResourceException {}

?>