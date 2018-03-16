<?php

class DTemplate extends QAbstractObject {

    private

    $_input,
    $_vars = array(),
    $_codec = QFile::Latin1;

    public function __construct($template){
        if($template instanceof QFileInfo){
            if(!$template->exists()){
                throw new DTemplateException('Template file "' . $template . '" does not exists');
            }
            $this->_input = new QFile($template);
        } else if($template instanceof QFile){
            if(!$template->exists()){
                throw new DTemplateException('Template file "' . $template . '" does not exists');
            }
            $this->_input = $template;
        } else if(is_string($template)){
            if(!file_exists($template)){
                throw new DTemplateException('Template file "' . $template . '" does not exists');
            }
            $this->_input = new QFile($template);
        }
    }

    public function setCoded($codec){
        $this->_codec = $codec;
    }

    public function set($k, $v = null){
        if($k instanceof QMap){
            $this->_vars = array_merge($this->_vars, $k->toArray());
        } else if(is_array($k) && $v === null){
            $this->_vars = array_merge($this->_vars, $k);
        } else if(is_scalar($k)) {
            $this->_vars[$k] = $v;
        } else {
            $fga = func_get_args();
            throw new DTemplateSignatureException('Call to undefined function DTemplate::set(' . array_map('dpsGetType', $fga) . ')');
        }
    }

    public function output(){
        return $this->_compile();
    }

    public function save($filename = null){
        if($filename == null){
            $output = new QTempFile;
            $output->open(QFile::WriteOnly | QFile::Truncate);
            $output->write($this->_compile());
            $output->close();
        } else {
            $output = new QFile($filename);
            $output->open(QFile::WriteOnly | QFile::Truncate);
            $output->write($this->_compile());
            $output->close();
        }
        return $output;
    }

    private function _compile(){
        $template = $this->_input->open(QFile::ReadOnly)->setCodec($this->_codec)->read();

        foreach($this->_vars as $k => $v){
            $template = str_replace('{' . $k . '}', $v, $template);
        }
        return $template;
    }
}

class DTemplateException extends QAbstractObjectException {}
class DTemplateSignatureException extends DTemplateException implements QSignatureException {}