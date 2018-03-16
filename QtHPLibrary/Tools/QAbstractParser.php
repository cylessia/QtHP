<?php

abstract class QAbstractParser extends QAbstractObject{
    
    protected $_lexer;
    
    public function __construct($lexer){
        if(!$lexer instanceof QAbstractLexer){
            throw new QAbstractParserLexerException('Not a valid lexer');
        }
        $this->_lexer = $lexer;
    }
    
    abstract public function parse($source);
    
}

class QAbstractParserException extends QAbstractObjectException{}
class QAbstractParserLexerException extends QAbstractParserException{}

?>