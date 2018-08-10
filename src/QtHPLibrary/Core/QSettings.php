<?php

class QSettings extends QAbstractObject implements ArrayAccess {

    private
            /**
             * @access private
             * @vars bool
             */
            $_autoSave,

            /**
             * @access private
             * @vars bool
             */
            $_saved = true,

            /**
             * @access private
             * @vars array
             */
            $_currentGroup,

            /**
             * @access private
             * @vars array
             */
            $_settings,

            /**
             * @access private
             * @vars string
             */
            $_fileName,

            /**
             * @access private
             * @vars array
             */
            $_prefix = array(),

            /**
             * @access private
             * @vars int
             */
            $_prefixPtr = 0;

    /**
     * @param string $filename [optional] The name of the file to load and save
     * @param bool $autoSave [optional] true to save the settings at the destruction of the QSettings object
     */
    public function __construct($filename = null, $autoSave = true){
        parent::__construct();
        $this->_fileName = $filename;
        $this->_autoSave = $autoSave;
        $this->_settings = new QRecursiveObject;
        $this->_loadConfigFile();
    }

    /**
     * If $autoSave has been set to true, it saves the settings
     */
    public function __destruct(){
        if($this->_autoSave && !$this->_saved && $this->_fileName){
            try {$this->save();}catch(Exception $e){}
        }
    }

    /**
     * Returns all the keys set in the settings
     */
    public function allKeys(){
        return $this->_allKeys($this->_settings);
    }

    /**
     * Appends $prefix to the current group
     * @throws QSettingsException If $prefix already exists and is not a group
     * @param string $prefix The prefix to append, if prefix doesn't exists
     */
    public function beginGroup($prefix){
        $this->_prefix[$this->_prefixPtr++] = &$this->_currentGroup;
        if(!isset($this->_currentGroup[$prefix])){
            $this->_currentGroup[$prefix] = array();
        } else if(!is_array($this->_currentGroup[$prefix])) {
            throw new QSettingsException('"' . $prefix . '" is not a group');
        }
        $this->_currentGroup = &$this->_currentGroup[$prefix];
        return $this;
    }

    /**
     * Returns all the child group of the current group
     */
    public function childGroups(){
        $group = array();
        foreach($this->_currentGroup as $value){
            if(is_array($value)){
                $group[] = $value;
            }
        }
        return $group;
    }

    /**
     * Returns all the keys of the current group.<br/>
     * A key is a non group index
     */
    public function childKeys(){
        return QStringList::fromArray(array_keys($this->_currentGroup));
        }

    /**
     * Resets the group to what it was before the corresponding begin group
     * @throws QSettingsException If the current group is the base depth
     */
    public function endGroup(){
        if($this->_prefixPtr == 0){
            throw new QSettingsException('Unable to end the main settings group');
        }
        $this->_currentGroup = $this->_prefix[--$this->_prefixPtr];
        array_pop($this->_prefix);
        return $this;
    }

    public function has($key){
        return isset($this->_currentGroup[$key]);
    }

    /**
     * Removes the setting $key and any sub-settings of $key
     * @param string $key
     * @throws DSettingsException
     */
    public function remove($key){
        if(strpos($key, '/')){
            if(($key = trim(preg_replace('#[/]+#', '/', $key), ' /')) === ''){
                throw new QSettingsException('"' . $key . '" is not valid');
            }
            $this->_remove($key, $this->_currentGroup, $key);
        } else if(isset($this->_currentGroup[$key])) {
            unset($this->_currentGroup[$key]);
        }
    }

    /**
     * Saves the settings
     */
    public function save($filename = ''){
        $filename = $filename ? $filename : $this->_fileName;
        if(!$filename){
            throw new QSettingsException('You must define a filename to save the settings');
    }
        $file = new QFile($filename);
        $file->open(QFile::WriteOnly | QFile::Truncate)
             ->write($this->_prettifyJson(json_encode($this->_settings)));
        $file->close();
        $this->_saved = true;
    }

    /**
     * Sets a value to the corresponding key
     * @throws QSettingsException If $key is not well written
     * @param string $key The depth slahes key
     * @param mixed The value to set
     */
    public function setValue($key, $value){
        if(strpos($key, '/') !== false){
            if(($key = trim(preg_replace('#[/]+#', '/', $key), ' /')) === ''){
                throw new QSettingsException('"' . $key . '" is not valid');
            }
            $this->_setValue($key, $this->_currentGroup, $value);
        } else {
            $this->_currentGroup[$key] = $value;
        }
        $this->_saved = false;
        return $this;
    }

    /**
     * Return a setting value
     * @throws QSettingsException If the key doesn't exists or is null
     * @param string $key The depth slashes key
     * @return mixed The value corresponding to the key
     */
    public function value($key){
        if(strpos($key, '/')){
            if(($key = trim(preg_replace('#[/]+#', '/', $key), ' /')) === ''){
                throw new QSettingsException('"' . $key . '" is not valid');
            }
            return $this->_value($key, $this->_currentGroup, $key);
        } else if(isset($this->_currentGroup[$key])) {
            return $this->_currentGroup[$key];
        } else {
            throw new QSettingsException('"' . $key . '" is not a valid key');
        }
    }

    /*******************
     * Private methods *
     *******************/
    private function _allKeys($settings, &$newSettings = null, $prefix = ''){
        foreach($settings as $key => $setting){
            if(is_array($setting)) {
                $this->_allKeys($setting, $newSettings, ($prefix===''?$key:$prefix.'/'.$key));
            } else {
                $newSettings[] = $prefix.'/'.$key;
            }
        }
        return $newSettings;
    }

    private function _loadConfigFile(){
        // Because he can create it on the fly
        if(file_exists($this->_fileName)){
            if(!($tmp = json_decode(file_get_contents($this->_fileName), true))){
                throw new QSettingsException('Unable to load file "' . $this->_fileName . '"');
            }
            foreach($tmp as $k => $v){
                $this->_settings->{$k} = $v;
            }
        }
        $this->_currentGroup = &$this->_settings;
        }

    private function _prettifyJson($json){
        $L = strlen($json);
        $_json = '';
        $i = 0;
        $inStr = false;
        $inEsc = false;
        $depth = null;
        $newLine = 0;
        while($i < $L){
            $c = $json{$i};
            if($inEsc){
                $inEsc = false;
            } else if($c == '"'){
                $inStr = !$inStr;
            } else if(!$inStr){
                switch($c){
                    case '}':
                    case ']':
                        --$depth;
                        $newLine = (isset($json{$i+1}) && $json{$i+1} == ',') ? 1 : 3;
                        break;
                    case '{':
                    case '[':
                        ++$depth;
                        $newLine = 2;
                        break;
                    case ',':
                        if(!$inStr){
                            $newLine = 2;
                        }
                        break;
                }
            } else if($c == '\\'){
                $inEsc = true;
            }
            if($newLine & 1){
                $_json .= "\n" . str_repeat(' ', $depth*4);
            }
            $_json .= $c;
            if($newLine & 2){
                $_json .= "\n" . str_repeat(' ', $depth*4);
            }
            $newLine = 0;
            ++$i;
        }
        return $_json;
    }

    private function _remove($key, &$configDepth, $prefix){
        if(($pos = strpos($key, '/'))){
            if(!isset($configDepth[($k = substr($key, 0, $pos))])) {
                throw new QSettingsException('The prefix "' . $prefix . '" doesn\'t exists');
            }
            $this->_value(substr($key, $pos+1), $configDepth[$k], $prefix);
        } else if(isset($configDepth[$key])) {
            unset($configDepth[$key]);
        }
    }

    private function _setValue($key, &$configDepth, $value){
        if(($pos = strpos($key, '/'))){
            if(!isset($configDepth[($k = substr($key, 0, $pos))])) {
                $configDepth[$k] = array();
            }
            $this->_setValue(substr($key, $pos+1), $configDepth[$k], $value);
        } else {
            $configDepth[$key] = $value;
        }
    }

    private function _value($key, &$configDepth, $prefix){
        if(($pos = strpos($key, '/'))){
            if(!isset($configDepth[($k = substr($key, 0, $pos))])) {
                throw new QSettingsException('The prefix "' . $prefix . '" doesn\'t exists');
            }
            return $this->_value(substr($key, $pos+1), $configDepth->{$k}, $prefix);
        } else if(!isset($configDepth[$key])) {
            throw new QSettingsException('The prefix "' . $prefix . '" doesn\'t exists');
        } else {
            return $configDepth[$key];
        }
    }

    /*****************
     * Magic methods *
     *****************/
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->_settings);
}

    public function offsetGet($offset) {
        return $this->value($offset);
    }

    public function offsetSet($offset, $value) {
        $this->setValue($offset, $value);
    }

    public function offsetUnset($offset) {
        unset($this->_settings[$offset]);
    }

    public function __get($name){
        return $this->_settings->{$name};
    }

    public function __set($name, $value){
        return $this->_settings->{$name} = $value;
    }
}

class QSettingsException extends QException{}
?>