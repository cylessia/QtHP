<?php

class QDir extends QAbstractObject {
    
    private $_path;
    
    private static $_current,
            $_overloadedFunctions = array(
                'absoluteFilePath','absolutePath','canonicalPath','entryList',
                'exists','isReadable','mkDir','mkPath','remove'
            );
    
    public function __construct($path = ''){
        if($path === ''){
            $this->_path = self::currentPath();
        } else if(is_string($path)){
            $this->_path = self::cleanPath(self::_absolutePath($path));
        } else {
            throw new QDirException('Not a valid path');
        }
    }
    
    public function absoluteFilePath($path){
        return QDir::isAbsolutePath($path)  ? $path : $this->_path . $path;
    }
    
    private static function absoluteFilePathStatic($path){
        return QDir::isAbsolutePath($path)  ? $path :self::currentPath() . $path;
    }
    
    public function absolutePath($path){
        return dirname(self::isAbsolutePath($path) ? $path : $this->_path . '/' . $path);
    }
    
    private static function absolutePathStatic($path){
        return dirname(self::isAbsolutePath($path) ? $path : self::current() . '/' . $path);
    }
    
    public function canonicalPath(){
        return $this->_path;
    }
    
    private static function canonicalPathStatic($path){
        return self::cleanPath($path);
    }
    
    public function cd(){
        if(($tmp = dirname($this->_path)) === '.'){
            throw new QDirException('No parent directory');
        }
        $this->_path = $tmp;
        return $this;
    }
    
    public function cdUp(){
        return $this->cd('..');
    }
    
    public static function cleanPath($path){
        if(!($return = realpath($path))){
            return implode('/', self::_cleanPath($path)) . '/';
        }
        return $return;
    }
    
    public function count(){
        return count(scandir($this->_path));
    }
    
    public static function current(){
        return new QDir();
    }
    
    public static function currentPath(){
        return self::$_current ?: (self::$_current = strtr(dirname($_SERVER['SCRIPT_FILENAME']), '\\', '/') . '/');
    }
    
    public static function dirName(){
        return substr($this->_path, strrpos($this->_path, '.'));
    }
    
    /**
     * Get all the entry of a dir
     * @param string $directory
     * @param array $diff
     * @todo Needs to be rewritten
     * @return QStringList
     */
    public function entryList($diff = array('.', '..')){
        if(isset($this)){
            return QStringList::fromArray(array_diff(scandir($this->_path), $diff));
        }
    }
    
    private static function entryListStatic($path = null, $diff = array('.', '..')){
        if(self::isAbsolutePath($path)) {
            return QStringList::fromArray(array_diff(scandir($path), $diff));
        } else {
            return QStringList::fromArray(array_diff((scandir(self::currentPath() . $path) ?: array()), $diff));
        }
    }
    
    public function exists($path = null){
        return file_exists($this->_path) && is_dir($this->_path);
    }
    
    private static function existsStatic($path){
        return file_exists($path) && is_dir($path);
    }


    public static function fromNativeSeparator($path){
        return strtr($path, DIRECTORY_SEPARATOR, '/');
    }
    
    public static function isAbsolutePath($path){
        if(is_string($path) && isset($path{0})){
            if(DIRECTORY_SEPARATOR === '/'){
                return $path{0} === '/';
            } else {
                $path = strtr($path, '\\', '/');
                return $path{0} == '/' && $path{1} == '/' ?: (((($chr = ord($path{0})) > 64 && $chr < 87) || ($chr > 97 && $chr < 123)) && isset($path{1}) && isset($path{2}) && $path{1} == ':' && $path{2} === '/');
            }
        }
    }
    
    public function isReadable(){
        return is_readable($this->_path);
    }
    
    private static function isReadableStatic($path){
        return is_readable($path);
    }


    public static function isRelativePath($path){
        return !self::isAbsolutePath($path);
    }
    
    public function isRoot(){
        return self::isAbsolutePath($this->_path) && (DIRECTORY_SEPARATOR === '/' ? !isset($this->_path{2}) : isset($this->_path{2}) && !isset($this->_path{3}));
    }
    
    public function mkDir($dirName){
        self::mkDirStatic($dirName);
        return $this;
    }
    
    private static function mkDirStatic($dirName){
        if(strpos($dirName, DIRECTORY_SEPARATOR) !== false){
            throw new QDirMakeDirException('QDir::mkdir create directory only in the current dir');
        }
        mkdir($dirName);
    }
    
    public function mkPath(){
        if(!mkdir($this->_path, 0777, true)){
            throw new QDirMakePathException('Unable to create "' . $this->_path . '"');
        }
    }
    
    private static function mkPathStatic($path){
        if(self::isAbsolutePath($path)){
            if(!mkdir($path, 0777, true)){
                throw new QDirMakePathException('Unable to create "' . $this->_path . '"');
            }
        } else {
            if(!mkdir(self::currentPath() . $path, 0777, true)){
                throw new QDirMakePathException('Unable to create "' . $this->_path . '"');
            }
        }
    }
    
    public function path(){
        return $this->_path;
    }
    
    public function remove($name){
        return @unlink($name);
    }
    
    private static function removeStatic($name){
        return @unlink(self::currentPath() . $name);
    }
    
    public function move($oldName, $newName){
        if($oldName instanceof QFile){
            $old = QDir::isAbsolutePath($oldName->fileName()) ? $oldName->fileName() : QDir::currentPath() . '/' . $oldName->fileName();
        } else if($oldName instanceof QDir){
            $old = $oldName->_path;
        } else if(is_string($oldName)){
            $old = $oldName;
        }
        if($newName instanceof QFile){
            $new = QDir::isAbsolutePath($newName->fileName()) ? $newName->fileName() : QDir::currentPath() . '/' . $newName->fileName();
        } else if($newName instanceof QDir){
            $new = $newName->_path;
        } else if(is_string($newName)){
            $new = $newName;
        }
        return @rename($old, $new);
    }
    
    public function rmdir($dirName){
        if(strpos($dirName, DIRECTORY_SEPARATOR)){
            throw new QDirRemoveDirException('QDir::rmdir can only remove in current dir');
        }
        if(count(scandir($this->_path . $dirName)) == 0 && is_dir($this->_path . $dirName)){
            return @unlink($this->_path . $dirName);
        }
    }
    
    public function rmPath($path){
        if(self::isAbsolutePath($path)){
            throw new QDirRemovePathException('Unable to remove an absolute path');
        }
        $c = count(explode('/', strtr($path, '\\', '/')));
        $path = $this->_path . $path;
        while($c){
            if(count(scandir($path)) == 0 && is_dir($path)){
                if(!@unlink($path)){
                    throw new QDirRemovePathException('Unable to remove "' . $path . '", unlink() failed');
                }
            } else {
                throw new QDirRemovePathException('Unable to remove "' . $path . '" because it is not empty');
            }
            --$c;
        }
        return $this;
    }
    
    public static function root(){
        return new QDir(self::rootPath());
    }
    
    public static function rootPath(){
        return substr(self::currentPath(), 0, strpos('/'));
    }
    
    public static function separator(){
        return DIRECTORY_SEPARATOR;
    }
    
    public static function setCurrent($path){
        self::$_current = self::cleanPath(self::isAbsolutePath($path) ? $path : self::$_current . $path);
    }
    
    public function setPath($path){
        $this->_path = self::cleanPath(self::isAbsolutePath($path) ? $path : self::cleanPath($this->_path . $path));
    }
    
    public static function toNativeSeparator($path){
        return strtr($path, '/', DIRECTORY_SEPARATOR);
    }
    
    private static function _absolutePath($path){
        return QDir::isAbsolutePath($path) ? $path : self::$_current . '/' . $path;
    }
    
    private static function  _cleanPath($path){
        $abs = self::isAbsolutePath($path = strtr($path, '\\', '/'));
        $parts = explode('/', preg_replace('/\/{2,}/', '/', $path));
        if(($c = count($parts)) === 1){
            return $parts;
        }
        
        $i = 0;
        $newParts = array();
        while($i < $c){
            if($parts[$i] !== '.'){
                if($parts[$i] === '..'){
                    if(count($newParts) < 2 && $abs) {
                        // The path try to get up the root
                        throw new QDirPathException('Not a valid path');
                    } else if(($cTmp = count($newParts)) && $newParts[$cTmp-1] !== '..'){
                        array_pop($newParts);
                    } else {
                        $newParts[] = '..';
                    }
                } else {
                    $newParts[] = $parts[$i];
                }
            }
            ++$i;
        }
        return $newParts;
    }
    
    public function __call($name, $arguments) {
        if(in_array($name, self::$_overloadedFunctions)){
            return call_user_func_array(array($this, $name), $arguments);
        } else {
            throw new QDirSignatureException('Call to undefined function QDate::' . $name . '(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
    
    public static function __callStatic($name, $arguments) {
        if(in_array($name, self::$_overloadedFunctions)){
            return call_user_func_array(array($this, $name), $arguments);
        } else {
            throw new QDirSignatureException('Call to undefined function QDate::' . $name . '(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
}

class QDirException extends QAbstractObjectException {}
class QDirSignatureException extends QDirException implements QSignatureException {}
class QDirMakeDirException extends QDirException {}
class QDirPathException extends QDirException{}

?>