<?php

class QRunnable extends QAbstractObject {
    
    private
            /**
             * @access private
             * @var int The microtime at start
             */
            $_microtime;
    protected
            
            /**
             * @access protected
             * @var string The type of the object
             */
            $_type = 'QRunnable';
    
    /**
     * Initializes the timer 
     */
    public final function __construct(){
        $this->_microtime = microtime(true);
    }

    /**
     * Calculates the time of $stmt
     * @param void $stmt Must be a return of a called function (void would be better)
     * @return float The time spent
     */
    public final function exec($stmt){
        return microtime(true) - $this->_microtime;
    }

    /**
     * Creates an instance of QRunnable
     * @return \self 
     */
    public static function create(){
        return new self;
    }
}

?>