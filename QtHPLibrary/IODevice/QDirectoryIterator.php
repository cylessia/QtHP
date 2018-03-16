<?php

/**
 * Description of DirectoryIterator
 *
 * @author rbala
 */
class QDirectoryIterator extends QAbstractObject implements SeekableIterator {
    
    private $_dir,
            $_handle = null,
            $_k,
            $_current;
    
    public function __construct(QDir $dir){
        $this->_dir = $dir;
        $this->_openIt();
    }
    
    public function current(){
        return $this->_current;
    }
    
    public function next($nameFilter = null, $filters = 0x33){
        while(
            ($entry = readdir($this->_handle)) !== false
            && !(
                (is_file($this->_dir->path() . $entry) && ($filters & QDir::Files))
                || (is_dir($this->_dir->path() . $entry) && ($filters & QDir::Dirs && !($entry == '.' && ($filters & QDir::NoDot)) && !($entry == '..' && ($filters & QDir::NoDotDot))))
            ) && (preg_match('/' . $nameFilter . '/', $entry))
        ){++$this->_k;}
        ++$this->_k;
        $this->_current = $entry ? new QFileInfo($this->_dir->path() . $entry) : null;
    }
    
    public function rewind(){
        $this->_closeIt();
        $this->_openIt();
    }
    
    public function valid(){
        $this->next();
        return $this->_current && $this->_current->exists();
    }
    
    public function key(){
        return $this->_k;
    }
    
    public function seek($k){
        if($this->_k > $k){
            $this->_closeIt();
            $this->_openIt();
        }
        while($this->_k != $k){
            $this->next();
        }
    }
    
    private function _openIt(){
        if(!$this->_dir->exists()){
            throw new QDirectoryIteratorException('Path "' . $this->_dir->path() . '" is not valid');
        }
        $this->_handle = opendir($this->_dir->path());
        $this->_k = 0;
        $this->_current = null;
    }
    
    private function _closeIt(){
        closedir($this->_handle);
    }
}

class QirectoryIteratorException extends QAbstractObjectException {}