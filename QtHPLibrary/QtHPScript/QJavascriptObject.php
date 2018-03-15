<?php

class QJavascriptObject extends QAbstractObject {
    
    const Main = 0,
          Core = 1,
          Widget = 2,
          Draggable = 3,
          Resizable = 4,
          Droppable = 5,
          Selectable = 6,
          Sortable = 7,
          Mouse = 8,
          Button = 9;
    
    private static
            $_scriptNames = array(),
            // Faire un arbre de dépendance récursive
            $_dependencies = array(
                self::Main => array(),
                self::Core => array(self::Main),
                self::Widget => array(self::Core),
                self::Draggable => array(self::Mouse),
                self::Resizable => array(self::Mouse),
                self::Droppable => array(self::Draggable),
                self::Selectable => array(self::Mouse),
                self::Sortable => array(self::Mouse),
                self::Mouse => array(self::Widget),
                self::Button => array(self::Widget)
            ),
            $_urls = array(
                self::Main => 'http://jquery-ui.googlecode.com/svn/tags/latest/jquery-1.7.2.js',
                self::Core => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.core.min.js',
                self::Widget => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.widget.min.js',
                self::Draggable => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.draggable.min.js',
                self::Resizable => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.resizable.min.js',
                self::Droppable => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.droppable.min.js',
                self::Selectable => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.selectable.min.js',
                self::Sortable => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.sortable.min.js',
                self::Mouse => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.mouse.min.js',
                self::Button => 'http://jquery-ui.googlecode.com/svn/tags/latest/ui/minified/jquery.ui.button.min.js'
            );
    
    public function __construct(){
        parent::__construct();
    }


    public function addScript($scriptName){
        if(!isset(self::$_scriptNames[$scriptName])){
            self::$_scriptNames[] = $scriptName;
        }
    }
    
    public function getScripts(){
        $scripts = array();
        foreach(self::$_scriptNames as $script){
            if(!isset($scripts[$script])){
                $this->_include($script, $scripts);
            }
        }
        return '<link rel="stylesheet" href="http://jquery-ui.googlecode.com/svn/tags/latest/themes/base/jquery.ui.all.css" /><script type="text/javascript" src="' . implode('"></script><script type="text/javascript" src="', $scripts) . '"></script>';
    }
    
    private function _include($scriptName, &$scripts){
        foreach(self::$_dependencies[$scriptName] as $script){
            $this->_include($script, $scripts);
        }
        $scripts[$scriptName] = self::$_urls[$scriptName];
    }
}

?>