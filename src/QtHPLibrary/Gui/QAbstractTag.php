<?php
abstract class QAbstractTag extends QObject {
    
    public static function create(){}
    abstract public function __toString();
}

class QAbstractTagException extends QObjectException{};

?>