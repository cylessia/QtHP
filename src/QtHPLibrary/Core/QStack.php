<?php

class QStack extends QList {

    protected
            /**
             * @access protected
             * @var string The type of the object
             */
            $_type = 'QStack';
    
    /**
     * Returns the last item and removes it from the stack
     * @return mixed The last item
     */
    public function pop(){
        return $this->takeLast();
    }
    
    /**
     * Inserts an item to the stack
     * @throws QListException If $value is nor a template type nor a QList with the same template type
     * @param mixed $value The value to append
     */
    public function push($value){
        $this->append($value);
    }
   
    /**
     * Returns a reference to the last item
     * @throws QVectorException If the vector is empty
     * @return mixed A reference to the last item
     */
    public function &tail(){
        return $this->last();
    }

}

class QStackException extends QException{}

?>