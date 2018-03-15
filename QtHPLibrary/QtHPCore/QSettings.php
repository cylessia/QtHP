<?php

class QSettings extends QAbstractObject {
    
    private
            /**
             * @access private
             * @vars bool
             */
            $_autoSave,
            
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
            $_prefixPtr = 0,
            
            
            /**
             * @access private
             * @vars bool
             */
            $_saved = false;
    /**
     * @param string $filename [optional] The name of the file to load and save
     * @param bool $autoSave [optional] true to save the settings at the destruction of the QSettings object
     */
    public function __construct($fileName = null, $autoSave = true){
        parent::__construct();
        $this->_fileName = $fileName;
        $this->_autoSave = $autoSave;
        $this->_loadConfigFile();
    }
    
    /**
     * If $autoSave has been set to true, it saves the settings
     */
    public function __destruct(){
        if($this->_autoSave && !$this->_saved){
            $this->save();
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
        if(!isset($this->_currentGroup[$prefix]))
            $this->_currentGroup[$prefix] = array();
        else if(!is_array($this->_currentGroup[$prefix]))
            throw new QSettingsException('"' . $prefix . '" is not a group');
        $this->_currentGroup = &$this->_currentGroup[$prefix];
        return $this;
    }
    
    /**
     * Returns all the child group of the current group
     */
    public function childGroups(){
        $group = array();
        foreach($this->_currentGroup as $value){
            if(is_array($value))
                $group[] = $value;
        }
        return $group;
    }
    
    /**
     * Returns all the keys of the current group.<br/>
     * A key is a non group index
     */
    public function childKeys(){
        $keys = array();
        foreach($this->_currentGroup as $key => $value){
            if(!is_array($key))
                $keys[] = $key;
        }
        return $keys;
    }

    /**
     * Resets the group to what it was before the corresponding begin group
     * @throws QSettingsException If the current group is the base depth
     */
    public function endGroup(){
        if($this->_prefixPtr == 0)
            throw new QSettingsException('Unable to end the main settings group');
        $this->_currentGroup = $this->_prefix[--$this->_prefixPtr];
        array_pop($this->_prefix);
        return $this;
    }
    
    /**
     * Saves the settings
     */
    public function save(){
        //echo 'Still in progress';
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
            if(is_array($setting))
                $this->_allKeys($setting, $newSettings, ($prefix===''?$key:$prefix.'/'.$key));
            else
                $newSettings[] = $prefix.'/'.$key;
        }
        return $newSettings;
    }
    
    private function _loadConfigFile(){
        // Because he can create it on the fly
        if(file_exists($this->_fileName)){
            $array = parse_ini_file($this->_fileName, true);
            foreach($array as $section => $values){
                if(!is_array($values)){
                    $this->_setValue($section, $this->_settings, $values);
                } else {
                    $this->_settings[$section] = array();
                    foreach($values as $key => $value){
                        $this->_setValue($key, $this->_settings[$section], $value);
                    }
                }
            }
            $this->_currentGroup = &$this->_settings;
        }
    }
    
    private function _setValue($key, &$configDepth, $value){
        if(($pos = strpos($key, '/'))){
            if(!isset($configDepth[($k = substr($key, 0, $pos))]))
                $configDepth[$k] = array();
            $this->_setValue(substr($key, $pos+1), $configDepth[$k], $value);
        } else {
            $configDepth[$key] = $value;
        }
    }
    
    private function _value($key, &$configDepth, $prefix){
        if(($pos = strpos($key, '/'))){
            if(!isset($configDepth[($k = substr($key, 0, $pos))]))
                throw new QSettingsException('The prefix "' . $prefix . '" doesn\'t exists');
            return $this->_value(substr($key, $pos+1), $configDepth[$k], $prefix);
        } else if(!isset($configDepth[$key])) {
            throw new QSettingsException('The prefix "' . $prefix . '" doesn\'t exists');
        } else {
            return $configDepth[$key];
        }
    }
}

class QSettingsException extends QException{}
?>