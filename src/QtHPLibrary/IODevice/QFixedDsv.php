<?php
/**
 * Description of DFixedDsv
 *
 * @author rbala
 */
class QFixedDsv extends QFile {
    
    private $_columns = null,
            $_readCB = null,
            $_writeCB = null;
    
    public function __construct($fileName, $columnLength){
        parent::__construct($fileName);
        switch(gettype($columnLength)){
            case 'array':
                /*if(count($a = array_unique(array_filter($columnLength, 'gettype'))) != 1 || $a[0] != true){
                    throw new DFixedDsvException('Not a valid array of length');
                }*/
                $this->_columns = $columnLength;
                $this->_readCB = 'array';
                $this->_writeCB = 'array';
                break;
            case 'int':
                $this->_columns = $columnLength;
                $this->_readCB = 'int';
                $this->_writeCB = 'int';
                break;
            case 'string':
            case 'object':
                throw new QFixedDsvException('Not implemented yet !');
                break;
        }
    }
    
    public function readLine($length = null) {
        $line = parent::readLine($length);
        return isset($line{0}) ? $this->{'_read'.$this->_readCB}($line) : array();
    }
    
    public function writeLine($data){
        if(!$this->isOpen()){
            throw new QDsvWriteException('Unable to write into an unopened file');
        }
        if(!($this->_openMode & self::WriteOnly || $this->_openMode & self::Append)){
            throw new QFileWriteException('File is not writable. Open mode is "' . $this->_phpOpenMode . '"');
        }
        if(!is_array($data) && !method_exists($data, 'toArray')){
            throw new QDsvWriteException('Call to undefined function DDsv::writeLine(' . dpsGetType($data) . ')');
        }
        return $this->{'_write'.$this->_writeCB}($data);
    }
    
    private function _readInt($line){
        $fields = new QStringList;
        while(strlen($line)){
            $fields[] = substr($line, 0, $this->_columns);
            $line = substr($line, $this->_columns);
        }
        return $fields;
    }
    
    private function _readArray($line){
        $fields = new QStringList;
        $start = 0;
        foreach($this->_columns as $col){
            $val = substr($line, $start, $col);
            $fields[] = $val === false ? '' : $val;
            $start += $col;
        }
        return $fields;
    }
    
    private function _writeInt(&$data){
        $str = '';
        foreach($data as $d){
            if(isset($d{$this->_columns})){
                throw new QFixedDsvLengthException('"' . $d . '" is too long');
            } else {
                $str .= str_pad($d, $this->_columns, ' ', STR_PAD_LEFT);
            }
        }
        parent::writeLine($str);
    }
    
    private function _writeArray(&$data){
        $str = '';
        foreach($this->_columns as $k => $length){
            if(isset($data[$k])){
                if(isset($data[$k]{$length})){
                    throw new QFixedDsvLengthException('"' . $data[$k] . '" is too long');
                } else {
                    $str .= str_pad($data[$k], $length, ' ', STR_PAD_LEFT);
                }
            } else {
                $str .= str_pad('', $length);
            }
        }
        parent::writeLine($str);
    }
}

class QFixedDsvException extends QFileException {}