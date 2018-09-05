<?php

class QTimer extends QAbstractObject {
    private

    $_startTime,
    $_lastTime,
    $_lapDuration,
    $_laps;

    public function __construct($lapDuration){
        if(!is_int($lapDuration)){
            $fga = func_get_args();
            throw new QTimerSignatureException('Call to undefined function QTimer::__construct(' . implode(', ', arra_map('qGetType', $fga)) . ')');
        }
        $this->_lapDuration = $lapDuration;
        $this->_lastTime = $this->_startTime = time();
    }

    public function lap(){
        if((($newTime = time()) - $this->_lastTime) > $this->_lapDuration){
            ++$this->_laps;
            $this->_lastTime = $newTime;
            return true;
        }
        return false;
    }

    public function laps(){
        return $this->_laps;
    }

    public function totalDuration(){
        return new QTime(($this->_lastTime - $this->_startTime) * 1000);
    }
}