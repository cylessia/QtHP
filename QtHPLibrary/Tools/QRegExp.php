<?php
/**
 * @todo : indexIn, capturedText(s) must be rewritten !
 */
class QRegExp extends QAbstractObject {
    
    private $_pattern,
            $_options,
            $_regexp,
            /**
             * @var QStringList 
             */
            $_matches;
    
    public function __construct($pattern, $options = null){
        if($pattern instanceof QRegExp){
            $this->_pattern = $pattern->_pattern;
            if($options == null){
                $this->_options = $pattern->_options;
            } else {
                $this->_options = $options;
            }
        } else if(is_string($pattern)){
            $this->_pattern = $pattern;
            $this->_options = $options;
        } else {
            throw new QRegExpException('Call to undefined signature QRegExp::(' . implode('gettype', func_get_args()) . ')');
        }
        $this->_regexp = (strpos($this->_pattern, '/') ? '#' . str_replace('#', '\#', $this->_pattern) . '#' : '/' . $this->_pattern . '/') . $this->_options;
    }
    
    public function capturedText($pos){
        if($pos < 0 || $pos > $this->_matches->size()){
            throw new QRegExpRangeException('Out of range');
        }
        return $this->_matches->at($pos);
    }
    
    public function capturedTexts(){
        return $this->_matches;
    }
    
    public function indexIn($string, $offset){
        preg_match($this->_regexp, $string, $m, PREG_OFFSET_CAPTURE, $offset);
        if(count($m)){
            $this->_matches = QStringList::fromArray(array_map(function($v){return $v[0];}, $m));
            return $m[0][1];
        }
        return null;
    }
    
    public function pattern(){
        return $this->_pattern;
    }
    
    public function options(){
        return $this->_options;
    }
}

class QRegExpException extends QAbstractObjectException {}
class QRegExpRangeException extends QRegExpException {}

?>