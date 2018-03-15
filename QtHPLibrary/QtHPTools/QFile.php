<?php

class QFile extends QAbstractObject {
    
    const NotOpen = 0x00,
          ReadOnly = 0x01,
          WriteOnly = 0x02,
          ReadWrite = 0x03, // self::ReadOnly | self::WriteOnly
          Truncate = 0x04,
          Append = 0x08,
          Binary = 0x10;
            
    private $_fileName = '',
            $_handle = null,
            $_openMode = self::NotOpen,
            $_phpOpenMode = '';
    
    public function __construct($fileName){
        $this->_fileName = QDir::isAbsolutePath($fileName) ? $fileName : QDir::currentPath() . $fileName;
    }
    
    public function __destruct(){
        if($this->isOpen()){
            $this->close();
        }
    }
    
    public function close(){
        if(!$this->_handle){
            fclose($this->_handle);
            $this->_handle = null;
        }
        return $this;
    }
    
    public function exists(){
        return file_exists(QDir::isAbsolutePath($this->_fileName) ? $this->_fileName : QDir::current() . '/' . $this->_fileName);
    }
    
    public function filename(){
        return $this->_fileName;
    }
    
    public function handle(){
        return $this->_handle;
    }
    
    public function isOpen(){
        return $this->_handle != null;
    }
    
    public function open($fileName, $openMode = null){
        if(isset($this)){
            $this->_getOpenMode($fileName);
            if(QDir::isAbsolutePath($this->_fileName)){
                if(!($this->_handle = @fopen($this->_fileName, $this->_phpOpenMode))){
                    throw new QFileOpenException('Unable to open the file "' . $this->_fileName . '" with mode "' . $this->_phpOpenMode . '"');
                }
            } else {
                if(!($this->_handle = @fopen(QDir::currentPath() . $this->_fileName, $this->_phpOpenMode))){
                    throw new QFileOpenException('Unable to open the file "' . QDir::currentPath() . $this->_fileName . '" with mode "' . $this->_phpOpenMode . '"');
                }
            }
            return $this;
        } else {
            $f = new QFile($fileName);
            return $f->open($openMode);
        }
    }
    
    public function openMode(){
        if(!$this->isOpen()){
            throw new QFileException('Unable to get the open mode of an unopened file');
        }
        return $this->_openMode;
    }
    
    public function phpOpenMode(){
        if(!$this->isOpen()){
            throw new QFileException('Unable to get the open mode of an unopened file');
        }
        return $this->_phpOpenMode;
    }
    
    public function pos(){
        if(($tell = ftell($this->_handle)) === false){
            throw new QFilePosException('Unable to determine the current file position');
        }
        return $tell;
    }
    
    public function read($length = null){
        if(!$this->isOpen()){
            throw new QFileWriteException('Unable to read into an unopened file');
        }
        if($length === null){
            if(!($str = fread($this->_handle, filesize($this->_fileName))) === false){
                return $str;
            }
            throw new QFileReadException('Unable to read into file "' . $this->_fileName . '"');
        } else if($length > 0){
            if(!($str = fread($this->_handle, $length)) === false){
                return $str;
            }
            throw new QFileReadException('Unable to read into file "' . $this->_fileName . '"');
        }
        return '';
    }
    
    public function rewind(){
        if(!rewind($this->_handle)){
            throw new QFileRewindException('Unable to reach start of "' . $this->_fileName . '"');
        }
        return $this;
    }
    
    public function seek($pos){
        if($pos < 0 || $pos > $this->size()){
            throw new QFileSeekException('Out of range');
        }
        if(fseek($this->_handle, $pos, SEEK_CUR)){
            throw new QFileSeekException('Unable to reach position ' . $pos . ' inside "' . $this->_fileName . '"');
        }
        return $this;
    }
    
    public function size(){
        return filesize($this->_fileName);
    }
    
    public function write($value, $length = null){
        if(!$this->isOpen()){
            throw new QFileWriteException('Unable to write into an unopened file');
        }
        if(!is_scalar($value)){
            throw new QFileWriteException('Unable to write a non scalar value into a file');
        }
        if(fwrite($this->_handle, $value, ($length === null ? strlen($value) : $length)) === false){
            throw new QFileWriteException('Unable to write into the file "' . $this->_fileName . '"');
        }
        return $this;
    }
    
    private function _getOpenMode($openMode){
        if($openMode & self::Binary){
            $openMode &= ~self::Binary;
            $bin = true;
        } else {
            $bin = false;
        }
        switch($openMode){
            case self::ReadOnly:
                $this->_phpOpenMode = 'r';
                break;
            case self::WriteOnly:
                $this->_phpOpenMode = 'c';
                break;
            case self::ReadWrite:
                $this->_phpOpenMode = 'r+';
                break;
            case self::WriteOnly | self::Truncate:
                $this->_phpOpenMode = 'w';
                break;
            case self::ReadWrite | self::Truncate:
                $this->_phpOpenMode = 'w+';
                break;
            case self::ReadOnly | self::Append:
                $this->_phpOpenMode = 'a';
                break;
            case self::ReadWrite | self::Append || self::Append:
                $this->_phpOpenMode = 'a+';
                break;
            default:
                throw new QFileOpenException('Unknown open mode');
        }
        if($bin){
            $this->_phpOpenMode .= 'b';
        }
        $this->_openMode = $openMode;
    }
}

class QFileException extends QAbstractObjectException {}
class QFileOpenException extends QFileException {};
class QFileWriteException extends QFileException {};
class QFileReadException extends QFileException {};
class QFileMoveException extends QFileException {};
class QFileDeleteException extends QFileException {};
//class QFileCloseException extends QFileException {};
class QFileSeekException extends QFileException {};
class QFileRewindException extends QFileSeekException {};

?>