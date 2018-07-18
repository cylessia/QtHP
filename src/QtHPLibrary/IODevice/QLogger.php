<?php
/**
 * Description of QLogger
 *
 */
class QLogger {

    const

    Debug = 'debug',
    Critical = 'critical',
    Info = 'info',
    Warning = 'warning';

    private
    $_logFiles,
    $_stdout;

    public function __construct($logFile, $stdout = false, $separateLevels = true){
        if($logFile instanceof QFileInfo){
            $log = $logFile;
        } else {
            $log = new QFileInfo($logFile);
        }
        $this->_stdout = $stdout;
        $this->_initiateLogs($log, $separateLevels);
        register_shutdown_function(array($this, '_shutdownHandler'));
    }

    public function __destruct() {
        $this->close();
    }

    private function _initiateLogs(QFileInfo $file, $separateLevels){
        if($separateLevels){
            foreach(array(self::Debug, self::Info, self::Warning, self::Critical) as $logLevel){
                $this->_logFiles[$logLevel] = new QFile($file->canonicalPath() . $file->completeBaseName() . '.' . $logLevel . '.' . ($file->suffix() ?: 'log'));
                $this->_logFiles[$logLevel]->open(QFile::Append | QFile::WriteOnly);
            }
        } else {
            $f = new QFile($file->canonicalPath());
            $f->open(QFile::Append | QFile::WriteOnly);
            foreach(array(self::Debug, self::Info, self::Warning, self::Critical) as $logLevel){
                $this->_logFiles[$logLevel] = $f;
            }
        }
    }

    public function close(){
        foreach ($this->_logFiles as $log){
            if($log->isOpen()){
                $log->close();
            }
        }
    }

    public function debug($msg){$this->_log(self::Debug, $msg);}
    public function info($msg){$this->_log(self::Info, $msg);}
    public function warning($msg){$this->_log(self::Warning, $msg);}
    public function critical($msg){$this->_log(self::Critical, $msg);}

    private function _log($which, $msg){
        if($this->_stdout){
            echo $which
            . ' [' . QDateTime::now(QTimeZone::TzUtc)->toString('dd/mm/yyyy hh:ii:ss') . ']'
            . ' ' . $msg . "\n";
        }
        $this->_logFiles[$which]->writeLine(
            $which
            . ' [' . QDateTime::now(QTimeZone::TzUtc)->toString('dd/mm/yyyy hh:ii:ss') . ']'
            . ' ' . $msg
        );
    }

    public function _shutdownHandler(){
        // exit was called
        if (($error = error_get_last()) == null) {
            return;
        } else {
            $this->_log(self::Critical, $error['message'] . ' in ' . $error['file'] . ' at ' . $error['line']);
        }
    }
}
