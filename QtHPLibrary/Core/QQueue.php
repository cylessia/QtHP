<?php

class QQueue extends QList {
    
    protected
            /**
             * @access protected
             * @var string The type of the object
             */
            $_type = 'QStack';
    /**
     * Returns the first item and removes it from the stack
     * @return mixed The last item
     */
    public function dequeue(){
        return $this->takeFirst();
    }
    
    /**
     * Inserts an item to the queue
     * @throws QListException If $value is nor a template type nor a QList with the same template type
     * @param mixed $value The value to append
     */
    public function enqueue($value){
        return $this->append($value);
    }
    
    /**
     * Returns a reference to the first item
     * @throws QVectorException If the vector is empty
     * @return mixed A reference to the last item
     */
    public function &head(){
        return $this->first();
    }
}

class QQueueException extends QException{}

?>