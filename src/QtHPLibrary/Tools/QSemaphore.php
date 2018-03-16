<?php

/**
 * Description of DSemaphore
 *
 * @author rbala
 */
class QSemaphore {
    
    private $_sem,
            $_tok,
            $_shm;
    
    public function __construct($path, $id){
        if(isset($id{1})){
            throw new QSemaphoreException('$id must be one char long');
        }
        if(!file_exists($path)){
            throw new QSemaphoreException('File "' . $path . '" does not exists');
        }
        if(($this->_tok = ftok($path, $id)) == -1){
            throw new QSemaphoreException('Invalid token "' . $this->_tok . '"');
        }
    }
    
    public function __destruct() {
        $this->release();
        $this->detach();
    }
    
    public function acquire(){
        if(!($this->_sem = sem_get($this->_tok, 1, 0666, 0))){
            throw new QSemaphoreException('Unable to create semaphore');
        }
        if(!sem_acquire($this->_sem)){
            throw new QSemaphoreException('Unable to acquire semaphore');
        }
    }
    
    public function attach(){
        if(!$this->_shm){
            $this->_shm = shm_attach($this->_tok);
        }
    }
    
    public function detach(){
        if($this->_shm){
            shm_detach($this->_shm);
        }
    }
    
    public function value($key){
        if(!$this->_shm){
            $this->attach();
        }
        return shm_get_var($this->_shm, $key);
    }
    
    public function release(){
        if($this->_sem){
            if(!sem_release($this->_sem)){
                throw new QSemaphoreException('Unable to release semaphore');
            }
            $this->_sem = null;
        }
    }
    
    public function remove(){
        sem_remove($this->_sem);
    }
    
    public function setValue($key, $value){
        if(!$this->_shm){
            $this->attach();
        }
        if(!shm_put_var($this->_shm, $key, $value)){
            throw new QSemaphoreException('Unable to set shared var "' . $key . '"');
        }
    }
}

class QSemaphoreException extends Exception {}