<?php

class QUploadedFile extends QAbstractObject {
    
    private $_file,
            $_path;
    
    public function __construct($file){
        if(
            !is_array($file)
            || !isset($file['error'])
            || !isset($file['name'])
            || !isset($file['tmp_name'])
            || !isset($file['size'])
            || !isset($file['type'])
        ){
            throw new QUploadedFileSignatureException('Missing upload file information "' . implode(', ', array_diff(array('error', 'name', 'tmp_name', 'size', 'type'), array_keys($file))) . '"');
        }
        $this->_file = $file;
    }
    
    public function isValid(){
        return is_uploaded_file($this->_file['tmp_name']);
    }
    
    public function ext(){
        return substr($this->_file['name'], strrpos($this->_file['name'], '.')+1);
    }
    
    public function error(){
        return $this->_file['error'];
    }
    
    public function hasBeenSaved(){
        return $this->_path != null;
    }
    
    public function hasBeenUploaded(){
        return $this->_file['error'] != UPLOAD_ERR_NO_FILE;
    }
    
    public function name(){
        return $this->_file['name'];
    }
    
    public function path(){
        if(!$this->_path)
            throw new QUploadedFilePathException('The file must be save to get its new path');
        return $this->_path;
    }
    
    public function saveTo($path){
        if($this->_path)
            throw new QUploadedFileSavedException('The file has already been saved');
        if(!@move_uploaded_file($this->_file['tmp_name'], ($this->_path = $path))){
            throw new QUploadedFileSaveException('Unable to save the temp file');
        }
    }
    
    public function size(){
        return $this->_file['size'];
    }
    
    public function temporaryName(){
        return $this->_file['tmpName'];
    }
    
    public function type(){
        return $this->_file['type'];
    }
    
    
}

class QUploadedFileException extends QAbstractObjectException {}
class QUploadedFileSignatureException extends QUploadedFileException implements QSignatureException {}
class QUploadedFileSaveException extends QUploadedFileException {}
class QUploadedFileSavedException extends QUploadedFileException {}
class QUploadedFilePathException extends QUploadedFileException {}

?>