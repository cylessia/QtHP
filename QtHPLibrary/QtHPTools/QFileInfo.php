<?php

class QFileInfo extends QAbstractObject {
    
    private $_path = null;
    
    public function __construct($file){
        if(is_string($file)){
            $this->_path = $file;
        } else if($file instanceof QFile){
            $this->_path = $file->fileName();
        } else {
            throw new QFileInfoException('Not a valid file name');
        }
    }
    
    public function absoluteFilePath(){
        return QDir::isAbsolutePath($this->_path) ? $this->_path : QDir::currentPath() . '/' . $this->_path;
    }
    
    public function absolutePath(){
        return QDir::isAbsolutePath($this->_path) ? dirname($this->_path) : QDir::currentPath() . '/' . dirname($this->_path);
    }
    
    public function baseName(){
        return ($pos = strpos($bn = basename($this->_path), '.')) ? substr($bn, 0, $pos) : substr($bn, 0);
    }
    
    public function canonicalFilePath($path){
        return QDir::cleanPath($this->_path);
    }
    
    public function canonicalPath(){
        return QDir::cleanPath(dirname($this->_path));
    }
    
    public function completeBaseName(){
        return substr(($bn = basename($this->_path)), 0, strrpos($bn, '.'));
    }
    
    public function completeSuffix(){
        return substr(($bn = basename($this->_path)), strpos($bn, '.'));
    }
    
    public function exists(){
        return file_exists($this->_path);
    }
    
    public function fileName($path = null){
        return basename($this->_path);
    }
    
    public function filePath(){
        return QDir::isAbsolutePath($this->_path) ? $this->_path : QDir::current() . '/' . $this->_path;
    }
    
    public function isAbsolute(){
        return QDir::isAbsolutePath($this->_path);
    }
    
    public function isDir(){
        return is_dir($this->_path);
    }
    
    public function isFile(){
        return is_file($this->_path);
    }
    
    public function isRelative(){
        return QDir::isRelativePath($this->_path);
    }
    
    public function lastModified(){
        return filemtime($this->_path);
    }
    
    public function suffix(){
        return ($pos = strrpos($this->_path, '.')) ? substr($this->_path, $pos) : '';
    }
}

class QFileInfoException extends QAbstractObjectException{}
class QFileInfoSuffixException extends QFileInfoException{}
class QFileInfoFileNameException extends QFileInfoException{}
class QFileInfoBaseNameException extends QFileInfoException{}
class QFileInfoRelativePathException extends QFileInfoException{}
class QFileInfoAbsolutePathException extends QFileInfoException{}
class QFileInfoCanonicalFilePathException extends QFileInfoException{}

?>