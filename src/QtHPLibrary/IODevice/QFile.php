<?php
class QFile extends QAbstractObject {

    const

    NotOpen = 0x00,
    ReadOnly = 0x01,
    WriteOnly = 0x02,
    ReadWrite = 0x03, // self::ReadOnly | self::WriteOnly
    Truncate = 0x04,
    Append = 0x08,
    Binary = 0x10,

    EolWindows = "\r\n",
    EolLinux = "\n",
    EolMac = "\r",

    Ascii = 1,
    Latin1 = 2,
    Utf8 = 3;

    protected

    $_filename = '',
    $_handle = null,
    $_openMode = self::NotOpen,
    $_phpOpenMode = '',
    $_eol = PHP_EOL,
    $_codec = self::Ascii;

    /**
     *
     * @param string $fileName
     */
    public function __construct($fileName){
        $this->_filename = $fileName instanceof QFileInfo
            ? $fileName->canonicalFilePath()
            : (
                $fileName instanceof QFile
                ? $fileName->_filename
                : (QDir::isAbsolutePath($fileName)
                    ? $fileName
                    : QDir::currentPath() . $fileName
                )
            )
        ;
    }

    public function __destruct(){
        if($this->isOpen()){
            $this->close();
        }
    }

    /**
     * Close the handle
     * @return \DFile
     */
    public function close(){
        if($this->_handle){
            fclose($this->_handle);
            $this->_handle = null;
        }
        return $this;
    }

    /**
     * Copy a file
     * @param string|DFile $from
     * @param string|DFile $to
     * @return boolean true on success, false otherwise
     * @throws DFileSignatureException
     * @throws DFileCopyException
     */
    public static function copy($from, $to){
        $from = (!$from instanceof QFileInfo) ? new QFileInfo($from) : $from;
        $to = (!$to instanceof QFileInfo) ? new QFileInfo($to) : $to;
        
        if(!$from->isFile()){
            throw new QFileCopyException('"' . $from . '" is not a valid file');
        }
        if($from != $to){
            if($to->isDir()){
                if(@copy($from->canonicalFilePath(), $to->canonicalFilePath() . '/' . $from->filename())){
                    return;
                }
            } else {   
                if(@copy($from->canonicalFilePath(), $to->canonicalFilePath())){
                    return;
                }
            }
            throw new QFileCopyException('Unable to copy file "' . $from . '" to "' . $to . '"');
        }
    }

    /**
     * Create a file<br />
     * Also creates recursively the directories
     * @param string $path The file path
     * @throws DFileException
     */
    public static function create($path, $mode = 0777){
        if(!file_exists($path)){
            if(!file_exists(dirname($path))){
                QDir::mkPath(dirname($path));
            }
            if(!touch($path)){
                throw new QFileException('Unable to create file "' . $path . '"');
            }
            @chmod($path, $mode);
        }
    }

    /**
     * Checke if a file exists
     * @param string $path the file path
     * @return boolean
     */
    public static function fileExists($path){
        return file_exists(QDir::isAbsolutePath($path) ? $path : QDir::currentPath() . $path);
    }

    /**
     * Checks if the file exists
     * @return boolean
     */
    public function exists(){
        return file_exists(QDir::isAbsolutePath($this->_filename) ? $this->_filename : QDir::currentPath() . $this->_filename);
    }

    /**
     * Return the file path
     * @return string
     */
    public function filename(){
        return $this->_filename;
    }

    /**
     * Return the handle to this file
     * @return resource|null
     */
    public function handle(){
        return $this->_handle;
    }

    /**
     * Checks if the file is already open
     * @return boolean
     */
    public function isOpen(){
        return $this->_handle != null;
    }

    /**
     * Checks if the file is readable<br />
     * The file has to be opened
     * @return boolean
     */
    public function isReadable(){
        return ($this->_openMode & self::ReadOnly) != 0;
    }

    /**
     * Checks if the file is writable<br />
     * The file has to be opened
     * @return boolean
     */
    public function isWritable(){
        return ($this->_openMode & self::WriteOnly) != 0;
    }

    /**
     * Open the file
     * @param int $openMode Combinations of DFile consts
     * @return \DFile
     * @throws DFileOpenException
     */
    public function open($openMode){
        $this->_getOpenMode($openMode);
        if(QDir::isAbsolutePath($this->_filename)){
            if(!($this->_handle = @fopen($this->_filename, $this->_phpOpenMode))){
                throw new QFileOpenException('Unable to open the file "' . $this->_filename . '" with mode "' . $this->_phpOpenMode . '"');
            }
        } else {
            if(!($this->_handle = @fopen(QDir::currentPath() . $this->_filename, $this->_phpOpenMode))){
                throw new QFileOpenException('Unable to open the file "' . QDir::currentPath() . $this->_filename . '" with mode "' . $this->_phpOpenMode . '"');
            }
        }
        return $this;
    }

    /**
     * Open a file using its path
     * @param string $filename
     * @param int $openMode
     * @return \self
     */
    public static function openFile($filename, $openMode){
        $f = new self($filename);
        $f->open($openMode);
        return $f;
    }

    /**
     * Returns the open mode<br />
     * The file has to be opened
     * @return int
     * @throws DFileException
     */
    public function openMode(){
        if(!$this->isOpen()){
            throw new QFileException('Unable to get the open mode of an unopened file');
        }
        return $this->_openMode;
    }

    /**
     * Returns the php open mode used
     * @return string
     * @throws DFileException
     */
    public function phpOpenMode(){
        if(!$this->isOpen()){
            throw new QFileException('Unable to get the open mode of an unopened file');
        }
        return $this->_phpOpenMode;
    }

    /**
     * Returns the file permission
     * @return int (octal)
     */
    public function permissions(){
        return self::filePermissions($this->_filename);
    }

    /**
     * Returns permissions of a file
     * @param string $path
     * @return int (octal)
     */
    public function filePermissions($path){
        return fileperms($path);
    }

    /**
     * Returns the position of the current handle
     * @return int
     * @throws DFilePosException
     */
    public function pos(){
        if(($tell = ftell($this->_handle)) === false){
            throw new QFilePosException('Unable to determine the current file position');
        }
        return $tell;
    }

    /**
     * Reand $length bytes from file
     * @param int $length Length to read
     * @return string
     * @throws DFileReadException
     */
    public function read($length = null){
        if(!$this->isOpen()){
            throw new QFileReadException('Unable to read into an unopened file');
        }
        if(!($this->_openMode & self::ReadOnly)){
            throw new QFileReadException('File is not readable. Open mode is "' . $this->_phpOpenMode . '"');
        }
        if($length === null){
            clearstatcache();
            if(($fs = filesize($this->_filename))){
                if(!(($str = fread($this->_handle, $fs)) === false)){
                    $this->_codecConvert($str);
                    return $str;
                }
                if(feof($this->_handle)){
                    return false;
                }
                throw new QFileReadException('Unable to read into file "' . $this->_filename . '"');
            } else {
                return '';
            }
        } else if($length > 0){
            if(!(($str = fread($this->_handle, $length)) === false)){
                $this->_codecConvert($str);
                return $str;
            }
            if(feof($this->_handle)){
                return false;
            }
            throw new QFileReadException('Unable to read into file "' . $this->_filename . '"');
        }
        return '';
    }

    /**
     * Read one byte from file
     * @return char
     * @throws DFileReadException
     */
    public function readChar(){
        if(!$this->isOpen()){
            throw new QFileReadException('Unable to read into an unopened file');
        }
        if(!($this->_openMode & self::ReadOnly)){
            throw new QFileReadException('File is not readable. Open mode is "' . $this->_phpOpenMode . '"');
        }
        if(($c = fgetc($this->_handle)) !== false){
            return $c;
        }
        if(feof($this->_handle)){
            return false;
        }
        throw new QFileReadException('Unable to read into file "' . $this->_filename . '"');
    }

    /**
     * Read until first line feed is encountered or up $length bytes
     * @param int $length
     * @return string
     * @throws DFileReadException
     */
    public function readLine($length = null){
        if(!$this->isOpen()){
            throw new QFileReadException('Unable to read into an unopened file');
        }
        if(!($this->_openMode & self::ReadOnly)){
            throw new QFileReadException('File is not readable. Open mode is "' . $this->_phpOpenMode . '"');
        }
        if($length === null){
            if(!($str = fgets($this->_handle)) === false){
                $this->_codecConvert($str);
                return rtrim($str, $this->_eol);
            }
            if(feof($this->_handle)){
                return false;
            }
            throw new QFileReadException('Unable to read into file "' . $this->_filename . '"');
        } else if($length > 0){
            if(!($str = fgets($this->_handle, $length)) === false){
                $this->_codecConvert($str);
                return rtrim($str, $this->_eol);
            }
            if(feof($this->_handle)){
                return false;
            }
            throw new QFileReadException('Unable to read into file "' . $this->_filename . '"');
        }
        return '';
    }

    /**
     * Remove a file
     * @param string $filename
     * @return boolean true on success, false otherwise
     */
    public static function remove($filename){
        if(!@unlink(
            $filename instanceof QFileInfo
            ? $filename->canonicalFilePath()
            : (QDir::isAbsolutePath($filename) ? $filename : QDir::currentPath() . $filename)
        )){
            throw new QFileRemoveException('Unable to remove file "' .
            ($filename instanceof QFileInfo
            ? $filename->canonicalFilePath()
            : (QDir::isAbsolutePath($filename) ? $filename : QDir::currentPath() . $filename)) . '"');
        }
    }

    /**
     * Rename (or move) a file
     * @param string|DFile|DFileInfo $from The file to rename
     * @param string|DFile|DFileInfo $to The destination file
     * @return boolean
     * @throws DFileSignatureException
     * @throws DFileCopyException
     */
    public static function rename($from, $to){
        $from = $from instanceof QFileInfo ? $from : new QFileInfo($from);
        $to = $to instanceof QFileInfo ? $to : new QFileInfo($to);

        if(!$from->isFile()){
            throw new QFileCopyException('"' . $from->canonicalFilePath() . '" is not a valid file');
        }

        if($to->isDir()){
            $to = new QFileInfo($to->canonicalFilePath() . '/' . $from->filename());
        }

        if($from != $to){
            if(!rename($from->canonicalFilePath(), $to->canonicalFilePath())){
                throw new QFileCopyException('Unable to rename "' . $from->canonicalFilePath() . '" to "' . $to->canonicalFilePath() . '"');
            }
        }
    }

    public function resize($newSize){
        if(!ftruncate($this->_handle, $newSize)){
            throw new QFileException('Unable to resize file "' . $this->_filename . '"');
        }
    }

    /**
     * Reaches to beginning of the file
     * @return \DFile
     * @throws DFileRewindException
     */
    public function rewind(){
        if(!$this->isOpen()){
            throw new QFileRewindException('Unable to rewind an unopened file');
        }
        if($this->_openMode & self::Append){
            throw new QFileRewindException('Unable to seek, file mode is "' . $this->_phpOpenMode . '"');
        }
        if(!rewind($this->_handle)){
            throw new QFileRewindException('Unable to reach start of "' . $this->_filename . '"');
        }
        return $this;
    }

    public function seek($pos){
        return $this->seekTo(ftell($this->_handle) + $pos);
    }

    /**
     * Move the file pointer to $pos
     * @param int $pos
     * @return \DFile
     * @throws DFileSeekException
     */
    public function seekTo($newPos){
        if(!$this->isOpen()){
            throw new QFileSeekException('Unable to seek into an unopened file');
        }
        if($newPos < 0 || $newPos > $this->size()){
            throw new QFileSeekException('Out of range');
        }
        if($this->_openMode & self::Append){
            throw new QFileSeekException('Unable to seek, file mode is "' . $this->_phpOpenMode . '"');
        }
        if(fseek($this->_handle, $newPos, SEEK_SET)){
            throw new QFileSeekException('Unable to reach position ' . $pos . ' inside "' . $this->_filename . '"');
        }
        return $this;
    }

    public function setCodec($codec){
        if($codec < self::Ascii || $codec > self::Utf8){
            throw new QFileCodecException('Invalid codec "' . $codec . '"');
        }
        $this->_codec = $codec;
        return $this;
    }

    public function setEol($eol){
        if($eol != self::EolLinux && $eol != self::EolMac && $eol != self::EolWindows){
            throw new QFileException('"' . $eol . '" is not a valid EOL value');
        }
        $this->_eol = $eol;
    }

    /**
     * Set file permission
     * @param int $int Octal value of permissions
     * @return \DFile
     * @throws DFileException
     */
    public function setPermissions($int){
        self::setFilePermissions($this->_filename, $int);
        return $this;
    }

    /**
     * Set the file permissions
     * @param string $path The file path
     * @param int $int Octal value of permissions
     * @throws DFileException
     */
    public static function setFilePermissions($path, $int){
        if($path instanceof QFile){
            $path = $path->_filename;
        } else if($path instanceof QFileInfo){
            $path = $path->canonicalFilePath();
        }
        if(!is_int($int)){
            throw new QFileException('File permissions must be integer');
        }
        if(!chmod($path, $int)){
            throw new QFileException('Unable to change "' . $path . '" permissions');
        }
    }

    /**
     * Returns the size of the file
     * @return type
     */
    public function size(){
        clearstatcache();
        return filesize($this->_filename);
    }

    /**
     * Writes up to $length bytes
     * @param scalar $value
     * @param int $length
     * @return \DFile
     * @throws DFileWriteException
     */
    public function write($value, $length = null){
        if(!$this->isOpen()){
            throw new QFileWriteException('Unable to write into an unopened file');
        }
        if(!($this->_openMode & self::WriteOnly)){
            throw new QFileWriteException('File is not writable. Open mode is "' . $this->_phpOpenMode . '"');
        }
        if(!is_scalar($value)){
            throw new QFileWriteException('Unable to write a non scalar value into a file');
        }
        $this->_codecConvert($value);
        if(fwrite($this->_handle, $value, ($length === null ? strlen($value) : $length)) === false){
            throw new QFileWriteException('Unable to write into the file "' . $this->_filename . '"');
        }
        return $this;
    }

    /**
     * Writes up to $length bytes + 1 (adds a "\n" or "\r\n" or "\r")
     * @see DFile::setEol()
     * @param string $value
     * @param int $length
     * @return \DFile
     */
    public function writeLine($value, $length = null){
        if($length === null){
            $this->write($value . $this->_eol);
        } else if($length > 0) {
            $this->write(substr($value, 0, $length) . $this->_eol, $length+1);
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
                $this->_phpOpenMode = 'c+';
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

    protected function _codecConvert(&$str){
        $isValidUtf8 = preg_match('//u', $str);
        if($isValidUtf8 && $this->_codec == self::Latin1){
            $str = utf8_decode($str);
        } else if(!$isValidUtf8 && $this->_codec == self::Utf8){
            $str = utf8_encode($str);
        }
    }

}

class QFileException extends QAbstractObjectException {}
class QFileSignatureException extends QFileException implements QSignatureException {}
class QFileCopyException extends QFileException {}
class QFileOpenException extends QFileException {}
class QFileWriteException extends QFileException {}
class QFileReadException extends QFileException {}
class QFileMoveException extends QFileException {}
class QFileRemoveException extends QFileException {}
//class DFileCloseException extends DFileException {}
class QFileSeekException extends QFileException {}
class QFileRewindException extends QFileSeekException {}
class QFileCodecException extends QFileException {}

?>