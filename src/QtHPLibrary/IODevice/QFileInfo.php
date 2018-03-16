<?php

class QFileInfo extends QAbstractObject {

    private $_path = null,
            $_absPath;

    /**
     *
     * @param string|DFile|DDir|DFileInfo $path
     * @throws DFileInfoException
     */
    public function __construct($path){
        if(is_string($path)){
            $this->_path = $path;
        } else if($path instanceof QFile){
            $this->_path = $path->filename();
        } else if($path instanceof QDir){
            $this->_path = $path->path();
        } else if($path instanceof QFileInfo){
            $this->_path = $path->_path;
        } else {
            throw new QFileInfoException('Not a valid path (' . dpsGetType($path) . ')');
        }
        $this->_absPath = QDir::isAbsolutePath($this->_path) ? $this->_path : QDir::currentPath() . $this->_path;
    }

    /**
     * Returns the absolute file path
     * @return string
     */
    public function absoluteFilePath(){
        return QDir::isAbsolutePath($this->_path) ? $this->_path : $this->_absPath;
    }

    /**
     * Returns the absolute path of path's directory
     * @return string
     */
    public function absolutePath(){
        return QDir::isAbsolutePath($this->_path) ? dirname($this->_path) . '/' : dirname($this->_absPath) . '/';
    }

    /**
     * Returns the base name of the file path<br />
     * The base name starts at the beginning of filename and ends at the first '.' encountered
     * @return string
     */
    public function basename(){
        return ($pos = strpos($bn = basename($this->_path), '.')) ? substr($bn, 0, $pos) : substr($bn, 0);
    }

    /**
     * Returns the canonical file path
     * @return string
     */
    public function canonicalFilePath(){
        return QDir::cleanPath($this->_absPath);
    }

    /**
     * Returns the canonical path of path's directory
     * @return string
     */
    public function canonicalPath(){
        return QDir::cleanPath(is_dir($this->_absPath) ? $this->_absPath : dirname($this->_absPath)) . '/';
    }

    /**
     * Returns the complete base name<br />
     * The complete base name start a beginning of filename and ends at the rightmost '.'
     * @return string
     */
    public function completeBasename(){
        return substr(($bn = basename($this->_path)), 0, ($p = strrpos($bn, '.')) ? $p : strlen($bn));
    }

    /**
     * Returns the complete suffix<br />
     * The complete suffix start at the first '.' until the end of filename
     * @return string
     */
    public function completeSuffix(){
        return substr(($bn = basename($this->_path)), strpos($bn, '.'));
    }

    /**
     * Returns the inode change date and time of the file
     * On most Unix systems, this function returns the time of the last status
     * change. A status change occurs when the file is created, but it also
     * occurs whenever the user writes or sets inode information (for example,
     * changing the file permissions).
     * @return int The timestamp
     */
    public function created(){
        return filectime($this->_absPath);
    }

    public function dir(){
        return new QDir($this->canonicalPath());
    }

    /**
     * Checks if the file exists
     * @return boolean
     */
    public function exists(){
        return file_exists($this->_absPath);
    }

    /**
     * Returns the filename
     * @return string
     */
    public function filename(){
        return basename($this->_absPath);
    }

    /**
     * Returns the file path
     * @return string
     */
    public function filePath(){
        return $this->_path;
    }

    /**
     * Check is this path is absolute
     * @return boolean
     */
    public function isAbsolute(){
        return QDir::isAbsolutePath($this->_path);
    }

    /**
     * Checks if the path is a directory
     * @return boolean
     */
    public function isDir(){
        return is_dir($this->_absPath);
    }

    /**
     * Checks if the path is a file
     * @return boolean
     */
    public function isFile(){
        return is_file($this->_absPath);
    }

    /**
     * Check if the file is readable
     * @return type
     */
    public function isReadable(){
        return is_readable($this->_absPath);
    }

    /**
     * Checks if the file is a directory
     * @return boolean
     */
    public function isRelative(){
        return QDir::isRelativePath($this->_path);
    }

    /**
     * Check if the file is writable (or its parents dir if file does not exists)
     * @return bool
     */
    public function isWritable(){
        return file_exists($this->_absPath) ? is_writable($this->_absPath) : is_writable($this->canonicalPath());
    }

    /**
     * Gets the size of the file
     * @return int
     */
    public function fileSize(){
        if($this->isDir()){
            $d = new QDir($this->_absPath);
            return $d->size();
        } else if($this->exists()) {
            return filesize($this->_absPath);
        } else {
            return 0;
        }
    }

    /**
     * Get the last accessed time of the file
     * @return int The timestamp
     */
    public function lastAccessedTime(){
        return fileatime($this->_absPath);
    }

    /**
     * Get the last modified date of the file
     * @return int The timestamp
     */
    public function lastModified(){
        return $this->exists() ? filemtime($this->_absPath) : 0;
    }

    /**
     * Alias of DFileInfo::lastAccessedTime
     * @return int The timestamp
     */
    public function lastRead(){
        return $this->lastAccessedTime();
    }

    public function path(){
        return substr($this->_path, 0, strrpos($this->_path, '/')+1);
    }

    /**
     * Returns the suffix of the file<br />
     * The suffix starts at the leftmost '.' until the end of filename
     * @return type
     */
    public function suffix(){
        return ($pos = strrpos($this->_path, '.')) ? substr($this->_path, $pos+1) : '';
    }

    public function __toString(){
        return $this->canonicalFilePath();
    }
}

class QFileInfoException extends QAbstractObjectException{}
class QFileInfoSuffixException extends QFileInfoException{}
class QFileInfoFilenameException extends QFileInfoException{}
class QFileInfoBaseNameException extends QFileInfoException{}
class QFileInfoRelativePathException extends QFileInfoException{}
class QFileInfoAbsolutePathException extends QFileInfoException{}
class QFileInfoCanonicalFilePathException extends QFileInfoException{}

?>