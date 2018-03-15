<?php
/**
 * @todo : Add the possibility to set generic messages error (for all elements)
 */
abstract class QAbstractFormLayout extends QAbstractLayout {
    
    const Get = 0,
          Post = 1;
    
    protected $_action = '',
              $_method = self::Post,
              $_inValidElements,
              $_name = '',
              $_elementId = -1,
             /**
              * @var QMap<QAbstractFormElement>
              */
              $_elements,
              $_token,
              $_messages,
              $_defaultMessages,
              $_enctype = null;
    
    protected static $_groupId = -1,
                     $_cache;
    
    const UrlEncoded = 0, //'application/x-www-form-urlencoded'
          Multipart = 1, //'multipart/form-data'
          PlainText = 2; //'text/plain'


    public function __construct(){
        parent::__construct();
        if(!self::$_cache){
            self::$_cache = new QCache('qthp_token');
        }
        $this->_elements = new QMap;
        $this->_inValidElements = new QMap;
        $this->_name = 'qthp_form_' . (++self::$_groupId);
        $this->_getToken();
        $this->_defaultMessages = new QMap;
    }
    
    public function action(){
        return $this->_action;
    }

    public function addRow($name, QAbstractFormElement $element){
        $this->_elements->insert($name, $element->setForm($this)->setName($name));
        return $this;
    }
    
    public function defaultErrorMessages(){
        return $this->_defaultMessages;
    }
    
    public function element($name){
        return $this->_elements->value($name);
    }
    
    public function elements(){
        return $this->_elements;
    }
    
    public function errorMessages(){
        return $this->_messages;
    }
    
    public function errorMessagesFor($name){
        return isset($this->_messages[$name]) ? $this->_messages[$name] : array();
    }
    
    public function errorMessageFor($name, $type){
        return isset($this->_messages[$name][$type]) ? $this->_messages[$name][$type] : '';
    }
    
    /**
     * 
     * @return QMap
     */
    public function inValidElements(){
        return $this->_inValidElements;
    }
    
    public function isValid($debug = false){
        if(!$this->hasBeenSent())
            return false;
        $values = ($this->_method == self::Post && isset($_POST[$this->_name]) ? $_POST[$this->_name] : ($this->_method == self::Get && isset($_GET[$this->_name]) ? $_GET[$this->_name] : array()));
        if($this->_enctype == self::Multipart && isset($_FILES[$this->_name]))
            $values = $this->_fileMerge($values);
        if((!isset($values['qthp_token']) || $values['qthp_token'] != $this->_token) && !$debug){
            self::$_cache->clear($this->_name);
            $this->_getToken();
            return false;
        }
        foreach($this->_elements as $name => $element){
            if(isset($values[$element->name()])){
                $element->setData($values[$element->name()]);
            }
            if(!$element->isValid()){
                $this->_inValidElements->insert($name, $element);
            }
        }
        if($this->_inValidElements->isEmpty()){
            self::$_cache->clear($this->_name);
            $this->_getToken();
            return true;
        } else {
            $this->_getToken();
            return false;
        }
    }
    
    public function hasBeenSent(){
        return $this->_method ? isset($_POST[$this->_name]) : isset($_GET[$this->_name]);
    }
    
    public function method(){
        return $this->_method;
    }
    
    public function name(){
        return $this->_name;
    }
    
    public function setAction($action){
        $this->_action = $action;
        return $this;
    }
    
    /**
     * Set the default messages for the form elements<br />
     * Must be an array or a QMap using this form :
     * [
     *      type1 = message,
     *      type2 = message
     * ],
     * [
     *     name1 = [
     *         type1 = message,
     *         type2 = message
     *     ],
     *     name2 = [
     *         type1 = message,
     *         type2 = message
     *     ]
     * ]
     * @param QMap $messages
     * @throws QAbstractFormLayoutSignatureException
     */
    public function setDefaultErrorMessages($messages){
        if($messages instanceof QMap || is_array($messages)){
            $this->_defaultMessages->insert($messages);
        } else {
            throw new QAbstractFormLayoutSignatureException('Call to undefined function QAbstractFormLayout::setDefaultMessages(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }
    
    public function setDefaultErrorMessagesFor($name, $messages){
        if(!is_string($name) || (!is_array($messages) && !$messages instanceof QMap)){
            throw new QAbstractFormLayoutSignatureException('Call to undefined function QAbstractFormLayout::setDefaultMessagesFor(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        $this->_defaultMessages->insert($name, $messages, true);
    }
    
//    public function setDefaultErrorMessageTypeFor($name, $type, $messages){
//        if(!is_string($name) || !is_string($type) || !is_string($messages)){
//            throw new QAbstractFormLayoutSignatureException('Call to undefined function QAbstractFormLayout::setDefaultMessageTypeFor(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
//        }
//        if(!$this->_elements->has($name)){
//            throw new QAbstractFormLayoutMessageException('Form doesn\'t contain element "' . $name . '"');
//        }
//        $this->_messages->insert($name, array($type => $messages), true);
//    }
    
    public function setEnctype($enctype){
        if($enctype < self::UrlEncoded || $enctype > self::PlainText){
            throw new QAbstractFormLayoutEnctypeException('"' . $enctype . '" is not a valid enctype value');
        }
        if($this->_method != self::Post){
            throw new QAbstractFormLayoutEnctypeMethodException('Form method must be post to ensure correct use of enctype');
        }
        $this->_enctype = $enctype;
        return $this;
    }
    
    public function setMethod($method){
        if($method != self::Post && $method != self::Get){
            throw new QFormLayoutException('Unknown form method');
        }
        $this->_method = $method;
        return $this;
    }
    
//    public function setName($name){
//        $this->_name = $name;
//        return $this;
//    }
    
    private function _getToken(){
        try {
            $this->_token = self::$_cache->recover($this->_name);
        } catch(QCacheValueException $e){
            self::$_cache->save($this->_name, $this->_token = QCryptographicHash::generateToken());
        }
    }
    
    // Why Php, WHY ?! Please explain to us !!!!!!!
    private function _fileMerge($post){
        if(!isset($_FILES[$this->_name])){
            return $post;
        }
        $_files = array();
        foreach($_FILES[$this->_name]['error'] as $name => $v){
            $_files[$name] = array(
                'error' => $_FILES[$this->_name]['error'][$name],
                'name' => $_FILES[$this->_name]['name'][$name],
                'size' => $_FILES[$this->_name]['size'][$name],
                'type' => $_FILES[$this->_name]['type'][$name],
                'tmp_name' => $_FILES[$this->_name]['tmp_name'][$name]
            );
        }
        return array_merge($post, $_files);
    }
}

class QAbstractFormLayoutException extends QAbstractElementException {}
class QAbstractFormLayoutSignatureException extends QAbstractFormLayoutException implements QSignatureException {}
class QAbstractFormLayoutEnctypeException extends QAbstractFormLayoutException {}
class QAbstractFormLayoutEnctypeMethodException extends QAbstractFormLayoutException {}
?>