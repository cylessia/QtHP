<?php

class QFloatList extends QList {
    
    protected
              /**
               * @access protected
               * @var string The type of the object
               */
              $_type = 'QFloatList',
              
              /**
               * @access protected
               * @var string The template of the list
               */
              $_template = 'double',
              
              /**
               * @access protected
               * @var string The callback to check template type
               */
              $_callback = '_isTemplateType';

    /**
     * Initializes a QList of floating point numbers template
     * Calling QVector::__construct avoid wasting time of QList checks
     */
    public function __construct(){
        QVector::__construct();
    }
}

?>