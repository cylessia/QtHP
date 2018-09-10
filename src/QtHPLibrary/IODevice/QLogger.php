<?php

class QLogger {
	
	const
		Debug = 'debug',
		Warning = 'warning',
		Info = 'info',
		Error = 'error',
		Trace = 'trace',
		Exception = 'exception';

	private 
        $logFile,
        $userLevels = [
            self::Debug,
            self::Warning,
            self::Info,
            self::Error,
            self::Trace,
            self::Exception
        ];
	
	public function __construct($logFile){
		if(!$logFile instanceof QFile){
			$logFile = new QFile($logFile);
		}
		
		if($logFile->isOpen() && ($logFile->openMode() !== QFile::Append)){
				$logFile->close();
				$logFile->open(QFile::Append);
		}else{
			$logFile->open(QFile::Append);
		}
		$this->logFile = $logFile;
        set_exception_handler([$this, 'exception']);
        set_error_handler([$this, 'errorHandler']);
	}
    
    public function debug($data){       $this->log(self::Debug, $data); }
    public function warning($data){     $this->log(self::Warning, $data); }
    public function info($data){        $this->log(self::Info, $data); }
    public function error($data){       $this->log(self::Error, $data); }
    public function trace($data){       $this->log(self::Trace, $data); }
    public function exception($data){   $this->log(self::Exception, $data);}
    
    public function log($level, $data){
        if(in_array($level, $this->userLevels)){
            $header = [
                'timestamp' => QDateTime::now()->toString('yyyy-mm-ddThh:ii:ss.uuu'),
                'version'	=> '0.1',
                'script'	=> $_SERVER['SCRIPT_FILENAME'],
                'level'		=> $level
            ];

            if(is_scalar($data)){
                $data = [
                    'message' => $data
                ];
            }

            $this->logFile->writeLine(json_encode(['headers' => $header, 'data' => $data]));
        }
    }
    
    public function errorHandler($errNum, $errMsg, $errFile, $errLine, $ctx){
        $this->error([
            'errno'      => $errNum,
            'errstr'     => $errMsg,
            'errfile'    => $errFile,
            'errline'    => $errLine,
            'errcontext' => $ctx
        ]);
    }
    
    public function filterByLevel(array $levels){
        $this->userLevels = $levels;
    }
	
}