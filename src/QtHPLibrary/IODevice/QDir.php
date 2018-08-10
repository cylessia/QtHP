<?php

/**
 * All dirs of DDir contains the last '/'
 */
class QDir extends QAbstractObject {

    const
        Dirs = 0x01,
        Files = 0x02,
        AllEntries = 0x03,
        NoDot = 0x10,
        NoDotDot = 0x20,
        NoDotAndDotDot = 0x30;

    private $_path;

    private static $_current/*,
            $_overloadedFunctions = array(
                'absoluteFilePath','absolutePath','canonicalPath','entryList',
                'exists','isReadable','mkDir','mkPath','remove'
            )*/;

    public function __construct($path = ''){
        if($path === ''){
            $this->_path = self::currentPath();
        } else if(is_string($path)){
            $this->_path = self::cleanPath(self::toAbsolutePath($path)) . '/';
        } else if($path instanceof QDir) {
            $this->_path = $path->_path;
        } else if($path instanceof QFileInfo){
            $this->_path = $path->canonicalFilePath() . '/';
        } else {
            throw new QDirException('Not a valid path');
        }
    }

    /*public function absoluteFilePath($path){
        return Dir::isAbsolutePath($path)  ? $path : $this->_path . $path;
    }*/

    /*public static function absoluteFilePath($path){
        return Dir::isAbsolutePath($path)  ? $path :self::currentPath() . $path;
    }*/

    /**
     * Return the absolute path of current dir
     * @param string $path The path
     * @return string
     */
    public function absolutePath(){
        return self::toAbsolutePath($this->_path);
    }

    /**
     * Transform $path to an absolute path
     * @param string $path The path to transform
     * @return string
     */
    public static function toAbsolutePath($path){
        return self::isAbsolutePath($path) ? $path : self::currentPath() . '/' . $path;
    }

    public static function toRelativePath($path){
        $other = self::currentPath();
        $fi = new QFileInfo($path);
        $path = $fi->canonicalFilePath();
        if(($l1 = strlen($other)) < ($l2 = strlen($path))){
            $left = $other;
            $leftLength = $l1;
            $right = $path;
            $rightLength = $l2;
        } else {
            $left = $path;
            $leftLength = $l2;
            $right = $other;
            $rightLength = $l1;
        }

        $start = -1;
        while(++$start < $leftLength){
            if($left{$start} !== $right{$start}){
                break;
            }
        }
        return './' . str_repeat('../', substr_count(ltrim(substr($other, $start), '/'), '/')) . substr($path, $start);
    }

    /*public static function absolutePath($path){
        return dirname(self::isAbsolutePath($path) ? $path : self::current() . '/' . $path);
    }*/

    /*public function canonicalPath(){
        return $this->_path;
    }*/

    /*public static function canonicalPath($path){
        return self::cleanPath($path);
    }*/

    /**
     * Move ptr to directory
     * @param string $dirname The name of the directory
     * @return \DDir
     * @throws DDirException
     */
    public function cd($dirname){
        if(!is_dir($this->_path . $dirname)){
            if($dirname == '..'){
                throw new QDirException('No parent directory');
            } else {
                throw new QDirException('Directory "' . $dirname . '" does not exists');
            }
        } else if($dirname == '..'){
            $this->_path = dirname($this->_path) . '/';
        } else if($dirname != '.'){
            $this->_path = $this->_path . $dirname . '/';
        }
        return $this;
    }

    /**
     * Go to parent directory
     * @return \DDir
     */
    public function cdUp(){
        return $this->cd('..');
    }

    /**
     * Clean a path by removing ./ and ../
     * @param string $path The path to clean
     * @return string The canonical path
     */
    public static function cleanPath($path){
        if(!($return = strtr(realpath($path), '\\', '/'))){
            return implode('/', self::_cleanPath($path));
        }
        return $return;
    }

    public static function copy($from, $to, $mode = 0777, $recursive = true){
        if($from instanceof QFileInfo){
            $from = $from->isDir() ? $from->canonicalFilePath() : $from->canonicalPath();
        } else if($from instanceof QDir){
            $from = $from->_path;
        } else if(!is_string($from)){
            throw new QDirSignatureException('Call to undefined function DDir::copy(' . implode(',', array_map('dpsGetType', func_get_args())) . ')');
        }
        if($to instanceof QFileInfo){
            $to = $to->isDir() ? $to->canonicalFilePath() : $to->canonicalPath();
        } else if($to instanceof QDir){
            $to = $to->_path;
        } else if(!is_string($to)){
            throw new QDirSignatureException('Call to undefined function DDir::copy(' . implode(',', array_map('dpsGetType', func_get_args())) . ')');
        }

        if(!is_dir($from)){
            throw new QDirCopyException('"' . $from . '" is not a valid directory');
        }

        self::_copy(rtrim($from, '/') . '/', rtrim($to, '/') . '/', $mode, $recursive);
    }

    /**
     * Counts entries in the directory without dot dirs
     * @return int The number of entries
     */
    public function count(){
        return count(scandir($this->_path))-2;
    }

    /*public static function current(){
        return new Dir();
    }*/

    /**
     * Return the current working directory
     * @return string
     */
    public static function currentPath(){
        return self::$_current ? self::$_current : (self::$_current = strtr(getcwd(), '\\', '/') . '/');
    }

    public function dirname(){
        return substr(($p = rtrim($this->_path, '/')), strrpos($p, '/')+1);
    }

    /**
     * Get all the entriy names of a dir
     * @param string $nameFilter Filter to apply to entries
     * @param bool $filters [optional] get files or dirs w/ or w/o dot dirs
     * @return QStringList
     */
    public function entryList($nameFilter = '.*', $filters = 0x33){
        $entries = new QStringList;
        foreach(scandir($this->_path) as $entry){
            if(
                (
                    (is_file($this->_path . $entry) && ($filters & self::Files))
                    || (is_dir($this->_path . $entry) && ($filters & self::Dirs && !($entry == '.' && ($filters & self::NoDot)) && !($entry == '..' && ($filters & self::NoDotDot)))))
                && (preg_match('/' . preg_replace('/[^\\\\]\//', '\/', $nameFilter) . '/', $entry))
            ){
                $entries[] = $entry;
            }
        }
        return $entries;
    }

    /**
     * Get all the entries of a dir
     * @param string $nameFilter Filter to apply to entries
     * @param bool $filters [optional] get files or dirs w/ or w/o dot dirs
     * @return QFileInfoList
     */
    public function entryInfoList($nameFilter = '.*', $filters = 0x33){
        if(!$this->exists()){
            throw new QDirException('Path "' . $this->_path . '" does not exists');
        }
        $fil = new QFileInfoList;
        foreach(scandir($this->_path) as $entry){
            if(
                (
                    (is_file($this->_path . $entry) && ($filters & self::Files))
                    || (is_dir($this->_path . $entry) && ($filters & self::Dirs && !($entry == '.' && ($filters & self::NoDot)) && !($entry == '..' && ($filters & self::NoDotDot)))))
                && (preg_match('/' . $nameFilter . '/', $entry))
            ){
                $fil->append(new QFileInfo($this->_path . $entry));
            }
        }
        return $fil;
    }

    /*private static function _entryList($path = null, $diff = array('.', '..')){
        if(self::isAbsolutePath($path)) {
            return DStringList::fromArray(array_diff(scandir($path), $diff));
        } else {
            return DStringList::fromArray(array_diff((($sd = scandir(self::currentPath() . $path)) ? $sd : array()), $diff));
        }
    }*/

    /**
     * Checks if the path exists
     * @return boolean
     */
    public function exists($name = ''){
        return file_exists($this->_path . $name);
    }

    public static function dirExists($path){
        return file_exists($path) && is_dir($path);
    }

    /*public static function exists($path){
        return file_exists($path) && is_dir($path);
    }*/


    /*public static function fromNativeSeparator($path){
        return strtr($path, DIRECTORY_SEPARATOR, '/');
    }*/

    /**
     * Checks if $path is absolute<br />
     * Recognize Windows, GNU/Linux and network path
     * @param string $path The path to check
     * @return boolean
     */
    public static function isAbsolutePath($path){
        if(is_string($path) && isset($path{0})){
            if(preg_match('/^[a-zA-Z]+:\\/\\//', $path)){
                return true;
            } else if(DIRECTORY_SEPARATOR === '/'){
                return $path{0} === '/';
            } else {
                $path = strtr($path, '\\', '/');
                return $path{0} == '/' && $path{1} == '/' ? true : (((($chr = ord($path{0})) > 64 && $chr < 87) || ($chr > 97 && $chr < 123)) && isset($path{1}) && isset($path{2}) && $path{1} == ':' && $path{2} === '/');
            }
        }
    }

    /**
     * Checks if the path exists and is a directory
     * @return boolean
     */
    public function isDir(){
        return file_exists($this->_path) && is_dir($this->_path);
    }

    /**
     * Checks if the path is readable
     * @return boolean
     */
    public function isReadable(){
        return is_readable($this->_path);
    }

    /*public static function isReadable($path){
        return is_readable($path);
    }*/

    /**
     * Checks if the path is relative<br />
     * A path is considered relative if it's not absolute
     * @return boolean
     */
    public static function isRelativePath($path){
        return !self::isAbsolutePath($path);
    }

    /*public function isRoot(){
        return self::isAbsolutePath($this->_path) && (DIRECTORY_SEPARATOR === '/' ? !isset($this->_path{2}) : isset($this->_path{2}) && !isset($this->_path{3}));
    }*/

    /**
     * Get the last modified date of the file
     * @return int The timestamp
     */
    public function lastModified(){
        return filemtime($this->_path . '.');
    }

    /**
     * Create a folder in the current path directory
     * @param string $dirname The name of the directory to make
     * @param int $permissions [optional] The permissions to set
     * @param boolean $move [optional] true to move into $dirname ( default = false)
     * @throws DDirMakeDirException
     */
    public function mkDir($dirname, $permissions = 0777, $move = false){
        if(!is_dir($this->_path . $dirname)){
            if(!@mkdir($this->_path . $dirname, $permissions)){
                throw new QDirMakeDirException('Unable to create directory "' . $dirname . '" into "' . $this->_path . '"');
            }
            $this->setPermissions($permissions, $dirname);
        }
        if($move){
            $this->_path .= trim($dirname, '/') . '/';
        }
    }

    /*public function mkDir($dirName){
        self::mkDirStatic($dirName);
        return $this;
    }*/

    /*public static function mkDir($dirName){
        if(strpos($dirName, DIRECTORY_SEPARATOR) !== false){
            throw new DDirMakeDirException('Dir::mkdir create directory only in the current dir');
        }
        mkdir($dirName);
    }*/

    /*public function mkPath(){
        if(!mkdir($this->_path, 0777, true)){
            throw new DDirMakePathException('Unable to create "' . $this->_path . '"');
        }
    }*/

    /**
     * Creates a path
     * @param string $path The path to create
     * @param int $permissions The permissions to set
     * @throws DDirMakePathException
     */
    public static function mkPath($path, $permissions = 0777){
        $path = $path instanceof QDir ? $path->path() : $path;
        $path = self::isAbsolutePath($path) ? $path : self::cleanPath(self::currentPath() . $path);
        if(!is_dir($path)){
            if(!is_dir(dirname($path))){
                self::mkPath(dirname($path), $permissions);
            }
            if(!@mkdir($path)){
                throw new QDirMakePathException('Unable to create "' . $path . '"');
            }
            self::setDirPermissions($path, $permissions);
        }
    }

    /**
     * Returns the current path
     * @return string
     */
    public function path(){
        return $this->_path;
    }

    /**
     * Removes a directory recursively
     * @param string $path The path to remove
     * @param boolean $recursive [optional] false to remove directory only if it's empty (default = true)
     * @throws DDirRmdirException
     */
    public static function rmDir($path, $recursive = true){
        $fi = new QFileInfo($path);
        $d = new QDir($fi->absoluteFilePath());
        if($d->isDir()){
            foreach($d->entryList() as $entry){
                $fie = new QFileInfo($d->path() . $entry);
                if($fie->isDir() && $recursive){
                    self::rmDir($fie->canonicalFilePath(), $recursive);
                } else {
                    QFile::remove($fie->canonicalFilePath());
                }
            }
            if(!@rmdir($fi->canonicalFilePath())){
                throw new QDirRmdirException('Unable to remove "' . $path . '"');
            }
        }
    }

    /*public static function root(){
        return new Dir(self::rootPath());
    }*/

    /*public static function rootPath(){
        return substr(self::currentPath(), 0, strpos('/'));
    }*/

    /**
     * Gets the native directory separator
     * @return string / or \
     */
    public static function separator(){
        return DIRECTORY_SEPARATOR;
    }

    /**
     * Set the current working directory
     * @param string $path The path
     * @return boolean true on success, false on failure
     */
    public static function setCurrentPath($path){
        return @chdir((self::$_current = self::cleanPath(self::isAbsolutePath($path) ? $path : self::$_current . $path) . '/'));
    }

    /**
     * Set the current path
     * @param string $path
     */
    public function setPath($path){
        $this->_path = self::cleanPath(self::isAbsolutePath($path) ? $path : $this->_path . $path) . '/';
    }

    public static function setDirPermissions($dirname, $permissions = 0777){
        if(self::dirPermissions($dirname) != $permissions){
            if(!chmod($dirname, $permissions)){
                throw new QDirPermissionException('Unable to set permissions of "' . $dirname . '" to "' . $permissions . '"');
            }
        }
    }

    public function setPermissions($permissions = 0777, $dirname = ''){
        return self::setDirPermissions($this->path() . $dirname, $permissions);
    }

    public function size(){
        $cwd = QDir::currentPath();
        QDir::setCurrentPath($this->_path);
        $s = array_sum(array_map('filesize', $this->entryList('.*', self::Files)->toArray()));
        QDir::setCurrentPath($cwd);

        $p = $this->path();
        foreach($this->entryList('.*', self::Dirs | self::NoDotAndDotDot) as $dir){
            $d = new QDir($p . $dir);
            $s += $d->size();
        }
        return $s;
    }

    public static function dirPermissions($path){
        return fileperms($path)&0777;
    }

    public function permissions($dirname = ''){
        return self::dirPermissions($this->_path . $dirname);
    }

    /**
     * Transform $path using native directory separator
     * @param string $path The path
     * @return string
     */
    public static function toNativeSeparator($path){
        return strtr($path, '/', DIRECTORY_SEPARATOR);
    }

    private static function  _cleanPath($path){
        $abs = self::isAbsolutePath($path = strtr($path, '\\', '/'));
        $parts = explode('/', rtrim(preg_replace('/\/{2,}/', '/', $path), '/'));
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

    private static function _copy($from, $to, $mode, $recursive){
        if(!is_dir($to) && !@mkdir($to, $mode)){
            throw new QDirMakePathException('Unable to make path "' . $to . '"');
        }
        if(!($dir = opendir($from))){
            throw new QDirCopyException('Dir copy faild at opening entry "' . $from . '"');
        }
        while(($entry = readdir($dir))){
            if($entry != '.' && $entry != '..'){
                if(is_dir($from . $entry) && $recursive){
                    self::_copy($from . $entry . '/', $to . $entry . '/', $mode, $recursive);
                } else {
                    if(!@copy($from . $entry, $to . $entry) || !@chmod($to . $entry, $mode)){
                        throw new QDirCopyException('Dir Copy failed at copying entry "' . $from . $entry . '"');
                    }
                }
            }
        }
        closedir($dir);
    }

    /*
    For PHP 5.3 and we are in 5.2
    public function __call($name, $arguments) {
        if(in_array($name, self::$_overloadedFunctions)){
            return call_user_func_array(array($this, '_' . $name), $arguments);
        } else {
            throw new DDirSignatureException('Call to undefined function DDir::' . $name . '(' . implode(', ', array_map('dpsGetType', func_get_args())) . ')');
        }
    }


    public static function __callStatic($name, $arguments) {
        if(in_array($name, self::$_overloadedFunctions)){
            return call_user_func_array(array($this, $name), $arguments);
        } else {
            throw new DDirSignatureException('Call to undefined function DDir::' . $name . '(' . implode(', ', array_map('dpsGetType', func_get_args())) . ')');
        }
    }*/
}

class QDirException extends QAbstractObjectException {}
class QDirSignatureException extends QDirException implements QSignatureException {}
class QDirCopyException extends QDirException {}
class QDirMakeDirException extends QDirException {}
class QDirMakePathException extends QDirException {}
class QDirRmDirException extends QDirException {}
class QDirPathException extends QDirException{}
class QDirPermissionException extends QDirException {}
?>