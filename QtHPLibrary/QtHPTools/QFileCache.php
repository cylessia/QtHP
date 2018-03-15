<?php

class QFileCache extends QCache {
    
    private $_file,
            $_map;
    
    public function __construct($filePath){
        parent::__construct();
        if(!is_dir($dirName = dirname($filePath) . '/') || is_dir($filePath)){
            throw new QFileCacheFilePathException('Not a valid path');
        }
        $baseName = $_COOKIE['PHPSESSID'] . '_' . basename($filePath);
        
        if(!is_writable($dirName)){
            throw new QFileCacheWritableException('Unable to write into "' . $dirName . '"');
        }
        
        $this->_file = new QFile($dirName . $baseName);
        
        if(file_exists($dirName . $baseName)){
            $this->_file->open(QFile::ReadWrite);
            $this->_map->insert(unserialize($this->_file->read()));
            $this->_file->rewind();
        } else {
            $this->_file->open(QFile::ReadWrite | QFile::Truncate);
        }
    }
    
    public function __destruct() {
        $this->_file->write(serialize($this->_map));
        $this->_file->close();
    }
}

class QFileCacheException extends QCacheException {}
class QFileCacheWritableException extends QFileCacheException {}
class QFileCacheValueException extends QFileCacheException {}
class QFileCacheFilePathException extends QFileCacheException {}

?>