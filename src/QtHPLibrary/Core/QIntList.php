<?php

class QIntList extends QList {
    
    protected
    
            /**
             * @access protected
             * @var string The type of the object
             */
            $_type = 'QIntList',

            /**
             * @access protected
             * @var string The template of the list
             */
            $_template = 'integer',

            /**
             * @access protected
             * @var string The callback to check template type
             */
            $_callback = '_isTemplateType';
              
    /**
     * Initializes a QList of integer template
     * Calling QVector::__construct avoid wasting time of QList check
     */
    public function __construct(){
        QVector::__construct();
    }
}

?>