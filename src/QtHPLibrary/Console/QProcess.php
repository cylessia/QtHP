<?php

class QProcess extends QAbstractObject {

    private $_pid = null,
            $_cmd = null,
            $_outputFile = null,
            $_rc = null,
            $_processedCmd = '',
            $_isRunning = false;

    /**
     *
     * @param DCommandLine $cmd The command line to execute
     */
    public function __construct($cmd){
        parent::__construct();
        if($cmd instanceof QCommandLine){
            $this->_cmd = $cmd;
        } else if(is_string($cmd)){
            $this->_cmd = new QCommandLine($cmd);
        } else {
            throw new QProcessSignatureException('Call to undefined function  ' . __METHOD__ . '(' . implode(', ', array_map('qGetType', func_get_args())) . ')');
        }
    }

    public function __destruct() {
        parent::__destruct();
        $this->_checkProcess();
        if($this->_isRunning){
            $this->kill();
        }
    }

    /**
     * Run a command line
     * @return string The processed command
     * @throws DProcessExecException
     */
    public function run(){
        if($this->_isRunning){
            throw new QProcessExecException('Process is already running');
        }
        $this->_outputFile = QCryptographicHash::generatePseudoRandomString(7);
        $this->_processedCmd = $this->_cmd->build(array());
        switch(QSystem::os()){
            case QSystem::Linux:
                $this->_runLinux();
            break;
            case QSystem::Windows:
                $this->_runWindobe();
            break;
            default :
                throw new QProcessException('This operating system is not handled yet !');

        }
        $this->_isRunning = true;
        return $this->_processedCmd;
    }

    /**
     * Return the exit status of the processed command
     * @return int
     */
    public function exitStatus(){
        return $this->_rc;
    }

    /**
     * Return the processed command (all options and value included)
     * @return string
     */
    public function processedCmd(){
        return $this->_processedCmd;
    }

    /**
     * Wait at least for $seconds second the process ends (otherwise, it kills it)
     * @param int $seconds
     * @return int The exit status
     */
    public function wait($seconds = null){
        $start = microtime(true);
        do{
            $time = microtime(true);
            $this->_checkProcess();
            if($this->_isRunning){
                if($seconds != null && $seconds < (microtime(true) - $start)){
                    $this->kill();
                }
                if(($processTime = (microtime(true) - $time) * 1000) < 1000){
                    usleep(1000 - $processTime);
                }
            }
        }while($this->_isRunning);
        return $this->_rc;
    }

    public function isFinished(){
        $this->_checkProcess();
        return !$this->_isRunning;
    }

    /**
     * Kill the process
     * @return int The exit status
     */
    public function kill(){
        if($this->_pid){
            switch (QSystem::os()){
                case QSystem::Linux:
                    $this->_killLinux();
                    break;
                case QSystem::Windows:
                    $this->_killWindobe();
                    break;
            }
            $this->_rc = 137;
            $this->_clean();
        }
        return $this->_rc;
    }

    /**
     * Get the processed id
     * @return int
     */
    public function pid(){
        return $this->_pid;
    }

    private function _runLinux(){
        $f = new QFile(QSystem::tempPath() . $this->_outputFile . '.sh');
        $f->open(QFile::ReadWrite | QFile::Truncate);
        $f->setPermissions(0776);
        $f->write('#!/bin/sh' . "\n" . $this->_processedCmd . ' ; echo $? > ' . QSystem::tempPath() . $this->_outputFile);
        $f->close();
        exec($f->filename() . '  > /dev/null & echo $!', $output);
        if(!is_numeric($this->_pid = $output[0])){
            throw new QProcessExecException('PID seems to be incorrect "' . $this->_pid . '"');
        }
    }

    private function _runWindobe(){
        $id = QCryptographicHash::generatePseudoRandomString(12, 'dprocess_');
        $f = new QFile(QSystem::tempPath() . $this->_outputFile . '.bat');
        $f->open(QFile::ReadWrite | QFile::Truncate);
        $f->setPermissions(0776);
        $f->write('@echo off' . "\n" . $this->_processedCmd . "\n" . '(echo %errorlevel%)>' . QSystem::tempPath() . $this->_outputFile . "\n" . 'exit');
        $f->close();
        pclose(popen('START /min "' . $id . '" ' . $f->filename(), 'r'));
        exec('tasklist /v /FO csv | findstr "' . $id . '"', $output);
        if(!isset($output[0])){
            throw new QProcessUnknownException('Command ' . $this->_processedCmd . ' is already finished or is not started yet');
        }
        $data = str_getcsv($output[0]);
        if(!is_numeric($this->_pid = $data[1])){
            throw new QProcessExecException('PID seems to be incorrect "' . $this->_pid . '"');
        }
    }

    private function _killLinux(){
        @exec('kill -9 ' . $this->_pid, $output, $rc);
    }

    private function _killWindobe(){
        exec('taskkill /PID ' . $this->_pid, $output, $rc);
    }

    private function _getTaskLinux(){
        return shell_exec('ps -ef | grep ' . $this->_pid . ' | grep -v "grep" | wc -l');
    }

    private function _getTaskWindobe(){
        return shell_exec('tasklist /v /FO csv | findstr "' . $this->_pid . '" | find /c /v ""');
    }

    /**
     * Check the status of the process
     * @throws DProcessException
     */
    private function _checkProcess(){
        if($this->_pid){
            if(!($this->_isRunning = trim(QSystem::os() == QSystem::Windows ? $this->_getTaskWindobe() : $this->_getTaskLinux()) > 0)){
                // On doit attendre (o_O) que le fichier avec le code de retour soit créé...
                // usleep ne fonctionne pas
                // sleep 1 est le minimum...
                // On peut pas améliorer ça ?!
                $try = 0;
                while(!QFile::fileExists(QSystem::tempPath() . $this->_outputFile)){
                    sleep(1);
                    ++$try;
                    if($try > 10){
                        throw new QProcessException('Error while waiting for process ' . $this->_pid . ' exit status file "' . QSystem::tempPath() . $this->_outputFile . '"');
                    }
                }
                $f = QFile::openFile(QSystem::tempPath() . $this->_outputFile, QFile::ReadOnly);
                $this->_rc = trim($f->read());
                $f->close();
            }
        }
    }

    /**
     * Clean the process temporary files
     */
    private function _clean(){
        try {
            QFile::remove(QSystem::tempPath() . $this->_outputFile . '.' . (QSystem::Windows ? 'bat' : 'sh'));
            QFile::remove(QSystem::tempPath() . $this->_outputFile);
        } catch(QFileRemoveException $e){}
        $this->_isRunning = false;
        $this->_pid = null;
    }
}

class QProcessException extends QAbstractObjectException {}
class QProcessExecException extends QProcessException {}
class QProcessUnknownException extends QProcessException {}

?>