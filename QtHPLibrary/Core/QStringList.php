<?php

class QStringList extends QList {

    protected
            /**
             * @access protected
             * @var string The template of the list
             */
            $_template = 'string',

            /**
             * @access protected
             * @var string The callback to check template type
             */
            $_callback = '_isTemplateType';

    /**
     * Initializes a QList of integer template
     * Calling QVector::__construct avoid wasting time of QList checks
     */
    public function __construct(){
        QVector::__construct();
    }

    public static function fromArray(array $array, $recursive = false){
        $list = new self;
        $list->append($array, $recursive);
        return $list;
    }
}

?>