<?php

class QFileInfoList extends QList {

    protected
            /**
             * @access protected
             * @var string The template of the list
             */
            $_template = 'QFileInfo',

            /**
             * @access protected
             * @var string The callback to check template type
             */
            $_callback = '_isTemplateInstance';

    /**
     * Initializes a DList of string template
     * Calling DVector::__construct avoid wasting time of DList checks'
     */
    public function __construct(){
        QVector::__construct();
    }

    /**
     * Construct a list of DFileInfo from php array
     * @param string $pathList
     * @return \self
     */
    public static function fromArray(array $pathList, $recursive = false){
        $list = new self;
        foreach($pathList as $path){
            $list->append(new QFileInfo($path));
        }
        return $list;
    }
}

?>