<?php
class QDsv extends QFile {

    private $_cb = '',
            $_sep,
            $_enclosure;
            /// Waiting for PHP 5.3
            // $_enclosure,
            // $_esc;

    /**
     *
     * @param string $filename The filename
     * @param char $sep The dsv char separator
     * @param char $enclosure The dsv char enclosure
     */
    public function __construct($filename, $sep = ',', $enclosure = '"'){
    /// Waiting for PHP 5.3
    //public function __construct($filename, $sep = ',', $enclosure = '"', $esc = '\\'){
        parent::__construct($filename);

        $this->_sep = $sep;
        $this->_enclosure = $enclosure;
        /// Waiting for PHP 5.3
        // $this->_esc = $esc;
        $this->_cb = $this->_enclosure == '' ? 'Dsv' : 'Csv';
    }

    /**
     * Read a line from filename
     * @param int $length The max length offset to read
     * @return DStringList
     * @throws DDsvReadException
     * @throws DFileReadException
     */
    public function readLine($length = null){
        if(!$this->isOpen()){
            throw new QDsvReadException('Unable to read into an unopened file');
        }
        if(!($this->_openMode & self::ReadOnly)){
            throw new QFileReadException('File is not readable. Open mode is "' . $this->_phpOpenMode . '"');
        }
        return $this->{'_read'.$this->_cb}($length);
    }

    /**
     * Write values to filename
     * @param array|DVector $values The values to write
     * @return int The number of bytes written
     * @throws DDsvWriteException
     * @throws DFileReadException
     */
    public function writeLine($values){
        if(!$this->isOpen()){
            throw new QDsvWriteException('Unable to write into an unopened file');
        }
        if(!($this->_openMode & self::WriteOnly || $this->_openMode & self::Append)){
            throw new QFileWriteException('File is not writable. Open mode is "' . $this->_phpOpenMode . '"');
        }
        if(!is_array($values) && !method_exists($values, 'toArray')){
            throw new QDsvWriteException('Call to undefined function DDsv::writeLine(' . dpsGetType($values) . ')');
        }
        return $this->{'_write'.$this->_cb}($values);
    }

    private function _readCsv($length){
        if($length === null){
            if(($array = fgetcsv($this->_handle, 0, $this->_sep, $this->_enclosure)) !== false){
                return $array[0] === null ? new QStringList : QStringList::fromArray($array);
            }
            if(feof($this->_handle)){
                return false;
            }
            throw new QDsvReadException('Unable to read into file "' . $this->_fileName . '"');
            /// Waiting for PHP 5.3
            // return DStringList::fromArray(fgetcsv($this->_handle, 0, $this->_sep, $this->_enclosure, $this->_esc));
        } else if($length > 0){
            if(($array = fgetcsv($this->_handle, $length, $this->_sep, $this->_enclosure)) !== false){
                return $array[0] === null ? new QStringList : QStringList::fromArray($array);
            }
            if(feof($this->_handle)){
                return false;
            }
            throw new QDsvReadException('Unable to read into file "' . $this->_fileName . '"');
            /// Waiting for PHP 5.3
            // return DStringList::fromArray(fgetcsv($this->_handle, $length, $this->_sep, $this->_enclosure, $this->_esc));
        }
        return new QStringList();
    }

    private function _readDsv($length){
        if($length === null){
            if(($str = fgets($this->_handle)) !== false){
                return QStringList::fromArray(explode($this->_sep, rtrim($str)));
            }
            if(feof($this->_handle)){
                return false;
            }
            throw new QDsvReadException('Unable to read into file "' . $this->_fileName . '"');
        } else if($length > 0){
            if(($str = fgets($this->_handle, $length)) !== false){
                return QStringList::fromArray(explode($this->_sep, $str));
            }
            if(feof($this->_handle)){
                return false;
            }
            throw new QDsvReadException('Unable to read into file "' . $this->_fileName . '"');
        }
        return new QStringList();
    }

    private function _writeCsv(&$values){
        foreach($values as &$v){
            $this->_codecConvert($v);
        }
        if(($written = fputcsv($this->_handle, is_array($values) ? $values : $values->toArray(), $this->_sep, $this->_enclosure)) === false){
            throw new QDsvWriteException('Unable to write into "' . $this->_filename . '"');
        }
        if($this->_eol != PHP_EOL){
            $this->seek(-1)->write($this->_eol, 1);
        }
        return $written;
    }

    private function _writeDsv(&$values){
        foreach($values as &$v){
            $this->_codecConvert($v);
        }
        if(($written = fwrite($this->_handle, (is_array($values) ? implode($this->_sep, $values) : $values->join($this->_sep)) . $this->_eol)) === false){
            throw new QDsvWriteException('Unable to write into "' . $this->_filename . '"');
        }
        return $written;
    }
}

class QDsvException extends QFileException {}
class QDsvSignatureException extends QDsvException implements QSignatureException {}
class QDsvReadException extends QFileException {}
class QDsvWriteException extends QFileException {}
?>