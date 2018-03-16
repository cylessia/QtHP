<?php

abstract class QAbstractLexer extends QAbstractObject{
    abstract public function nextToken(&$value, &$startAttributes, &$endAttributes);
}

class QAbstractLexerException extends QAbstractObjectException{}

?>