<?php

class QFileEdit extends QAbstractFormElement {
    
    private $_multiple = false,
            $_fileExts = array(),
            $_rawError = 0,
            /**
             * @var QUploadFile
             */
            $_file;
    
    private static $_noError = UPLOAD_ERR_OK, // 0
          $_errorIniFileSize = UPLOAD_ERR_INI_SIZE, // 1
          $_errorFormFileSize = UPLOAD_ERR_FORM_SIZE, // 2
          $_errorPartialUpload = UPLOAD_ERR_PARTIAL, // 3
          $_errorNoFileUploaded = UPLOAD_ERR_NO_FILE, // 4
          $_errorMissingTempDir = UPLOAD_ERR_NO_TMP_DIR, // 6
          $_errorUnwritableDir = UPLOAD_ERR_CANT_WRITE, // 7
          $_errorExtensionError = UPLOAD_ERR_EXTENSION; // 8
    
    const ErrorFileSize = 'fileSize',
          ErrorPartialUpload = 'filePartial',
          ErrorUploadDir = 'fileUploadDir',
          ErrorPhpExtension = 'phpExtension',
          ErrorFileExtension = 'fileExtension',
          ErrorHackAttempt = 'fileHackAttempt',
          ErrorHttpUpload = 'httpUpload';
    
    public function data(){
        return $this->_file;
    }
    
    public function setData($data){
        $this->setValue($data);
    }
    
    public function addAcceptedFileExt($fileExt){
        if(!is_string($fileExt)){
            throw new QFileEditSignatureException('Call to undefined function QFileEdit::addAcceptedFileType(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_fileExts[$fileExt] = true;
        return $this;
    }
    
    public function addAcceptedFileExts($fileExts){
        if(!is_array($fileExts)){
            throw new QFileEditSignatureException('Call to undefined function QFileEdit::addAcceptedFileTypes(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        foreach ($fileExts as $fileExt) {
            $this->_fileExts[$fileExt] = true;
        }
        return $this;
    }
    
    /**
     * Return the current uploaded file
     * @return QUploadFile
     */
    public function file(){
        return $this->_file;
    }
    
    public function isAcceptedFileExt($fileExt){
        if(!is_string($fileExt)){
            throw new QFileEditSignatureException('Call to undefined function QFileEdit::isAcceptedFileType(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        return isset($this->_fileExts[$fileExt]);
    }
    
    public function isMultiple(){
        return $this->_multiple;
    }
    
    public function isValid(){
        $this->_rawError = $this->_file->error();
        if($this->_rawError == UPLOAD_ERR_OK){
            // Check the name
            if(preg_match('#[\x00-\x1F\x7F-\x9F/\\\\]#', $this->_file->name())){
                $this->_errors[] = self::ErrorHackAttempt;
            }
            
            // Get the file extension
            if(!isset($this->_fileExts[substr($this->_file->name(),strrpos($this->_file->name(), '.')+1)])){
                $this->_errors[] = self::ErrorFileExtension;
            }
        } else if($this->_required && $this->_rawError == UPLOAD_ERR_NO_FILE){
            $this->_errors[] = self::ErrorRequire;
        } else if($this->_rawError == UPLOAD_ERR_INI_SIZE || $this->_rawError == UPLOAD_ERR_FORM_SIZE){
            $this->_errors[] = self::ErrorFileSize;
        } else if($this->_rawError == UPLOAD_ERR_PARTIAL){
            $this->_errors[] = self::ErrorPartialUpload;
        } else if($this->_rawError == UPLOAD_ERR_NO_TMP_DIR || $this->_rawError == UPLOAD_ERR_CANT_WRITE){
            $this->_errors[] = self::ErrorUploadDir;
        } else if($this->_rawError == UPLOAD_ERR_EXTENSION) {
            $this->_errors[] = self::ErrorPhpExtension;
        }
        return count($this->_errors) == 0;
    }
    
    public function setMultiple($multiple){
        if(!is_bool($multiple)){
            throw new QFileEditSignatureException('Call to undefined function QFileEdit::setMultiple(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_multiple = $multiple;
        return $this;
    }
    
    public function setValue($value = null){
        if($value instanceof QUploadedFile){
            $this->_file = $value;
        } else {
            $this->_file = new QUploadedFile($value);
        }
    }
    
    public function rawError(){
        return $this->_rawError;
    }
    
    public function setForm(\QAbstractFormLayout $form) {
        return parent::setForm($form->setEnctype(QAbstractFormLayout::Multipart));
    }
    
    public function show(){
        $this->_showTopLabel();
        echo '<input type="file" name="' . $this->_parent->name() . '[' . $this->_name . ']" ' . ($this->_required ? ' required="true" ' : '') . 'id="' . $this->_name . '" ' . ($this->_disabled ? 'disabled="disabled"' : '') . ' />';
        $this->_showBottomLabel();
    }
}

class QFileEditException extends QAbstractFormElementException {}
class QFileEditSignatureException extends QFileEditException implements QSignatureException {}
class QFileEditValueException extends QFileEditException {}

?>