<?php

abstract class QAbstractEngine extends QAbstractObject{
    
    protected $_class = array(),
              $_function = array(),
              $_vars = array();
    
    abstract public function exec($stmts);
}

class QAbstractEngineException extends QAbstractObjectException{}

?>