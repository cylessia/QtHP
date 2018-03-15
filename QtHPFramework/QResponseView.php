<?php

class QResponseView extends QView implements QResponseInterface, QDependencyInjectionInterface {
    private $_di,
            $_dir,
            $_level = self::RenderAll,
            $_content,
            $_eventManager,
            $_view = '',
            $_viewExt = 'phtml';
    
    const NoRender = 0x00,
          RenderLayout = 0x01,
          RenderView = 0x02,
          RenderAll = 0x03,
            
          EventBeforeRender = 'view:beforeRender',
          EventAfterRender = 'view:afterRender';
    
    public function baseDir() {
        return $this->_dir;
    }
    
    public function content(){
        return $this->_content;
    }
    
    public function di(){
        return $this->_di;
    }
    
    public function disableRenderLevel($renderLevel){
        $this->_level ^= $renderLevel;
    }
    
    public function eventManager(){
        return $this->_eventManager;
    }
    
    public function ext(){
        return $this->_viewExt;
    }
    
    public function renderLevel() {
        return $this->_level;
    }

    public function setBaseDir($dir) {
        $this->_dir = $dir;
    }
    
    public function setContent($content) {
        if(!is_string($content)){
            throw new QResponseViewSignatureException('A content must be a string');
        }
        $this->_content = $content;
    }
    
    public function setDi(QDependencyInjectorInterface $di) {
        $this->_di = $di;
    }
    
    public function setEventManager(QEventManagerInterface $em){
        $this->_eventManager = $em;
    }
    
    public function setExt($ext){
        if(!is_string($ext)){
            throw new QResponseViewSignatureException('Call to undefined function QResponseView::setExt(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
        if(!isset($ext{0})){
            throw new QResponseViewExtException('View\'s extension file cannot be empty');
        }
        $this->_viewExt = ($ext{0} == '.' ? substr($ext, 1) : $ext);
    }

    public function setRenderLevel($level) {
        $this->_level = $level;
    }
    
    public function setView($view){
        $this->_view = $view;
        return $this;
    }
    
    public function show(){
        if($this->_eventManager)
            $this->_eventManager->fire(self::EventBeforeRender, array($this));
        if($this->_level & self::RenderView){
            try {
                parent::setView($this->_dir . $this->_view);
                ob_start();
                parent::show();
                $this->_content = ob_get_clean();
            } catch(QViewPathException $e){}
        }
        if($this->_level & self::RenderLayout){
            try {
                parent::setView($this->_dir . 'index.' . $this->_viewExt);
                ob_start();
                parent::show();
                $this->_content = ob_get_clean();
            } catch(QViewPathException $e){}
        }
        if($this->_eventManager)
            $this->_eventManager->fire(self::EventAfterRender, array($this));
        echo $this->_content;
    }
    
    public function __get($name){
        return $this->_di->getShared($name);
    }
}

class QResponseViewException extends QViewException {}
class QResponseViewSignatureException extends QResponseViewException implements QSignatureException {}
class QResponseViewExtException extends QResponseViewException {}

?>