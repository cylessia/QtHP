<?php

class QCommandLine extends QAbstractObject {

    const endl = PHP_EOL;

    private $_exec,
            $_args;

    /**
     *
     * @param string $execPath The path of the executable or the program name
     */
    public function __construct($execPath){
        parent::__construct();
        if($execPath instanceof QCommandLine){
            $this->_exec = $execPath->_exec;
            $this->_args = new QMap($execPath->_args);
        } else if(is_string($execPath)) {
            $this->_exec = $execPath;
            $this->_args = new QMap;
        } else {
            $args = func_get_args();
            throw new QCommandLineSignatureException('Call to undefined function QCommandLine::QCommandLine(' . array_map('qGetType', $args) . ')');
        }
    }

    /**
     * Add arguments to this command
     * @param array|DStringList $args Arguments validating this format :<br />
     * array(<br />
     *     'arg' => 'val'
     * )
     */
    public function addArgs($args){
        $this->_args->insert($args);
    }

    /**
     * Return the arguments
     * @return DMap
     */
    public function args(){
        return $this->_args;
    }

    /**
     * Run the current command
     * @param array|DStringList $args The args to add to this run
     * @param array $output Pass a reference to get the output
     * @return int The return code of the command
     */
    public function run($args, &$output = null){
        exec($this->build($args), $output, $rc);
        return $rc;
    }

    /**
     * Build the command and return the string which can
     * be executed using exec for example
     * @param array $args
     * @return string
     */
    public function build($args = array()){
        $cmd = $this->_exec;
        foreach($this->_args as $key => $arg){
            $cmd .= (is_int($key) ? ' ' . $arg : ' ' . $key . ' ' . $arg);
        }
        foreach($args as $key => $arg){
            $cmd .= (is_int($key) ? ' ' . $arg : ' ' . $key . ' ' . $arg);
        }
        return $cmd;
    }

    /******************
     * Static methods *
     ******************/

    /**
     * Return the arguments passed to
     * the current cli script
     * @return \DStringList
     */
    public static function arguments(){
        if(!isset($_SERVER['argv'])){
            return new QStringList;
        } else {
            return QStringList::fromArray($_SERVER['argv']);
        }
    }

    /**
     * Output a message and wait for an answer<br />
     * @param string $question The string to output
     * @param array $answers [optional] The valid inputs the user can set<br />
     * If this array is set, the program will loop until a valid value (space trimmed) is set
     * @param string $defaultValue [optional] The default value if no value is set by the user
     * @param array $answersToShow [optional] If set, only these valid inputs will be shown
     * @return string
     */
    public static function ask($question, $answers = null, $defaultValue = null, $answersToShow = null){
        if($answers !== null){
            $question = $answersToShow === null
            ? trim($question) . ' [' . implode('/', $answers) . ']' . ($defaultValue !== null ? ' - (' . $defaultValue . ')' : '')
            : trim($question) . ' [' . implode('/', $answersToShow) . ']' . ($defaultValue !== null ? ' - (' . $defaultValue . ')' : '');
            do {
                self::cout($question);
                $answer = self::cin();
                if(trim($answer) == ''){
                    $answer = $defaultValue;
                }
            } while(!in_array($answer, $answers));
            return $answer;
        } else {
            self::cout($question);
            return self::cin();
        }
    }

//    public static function passwordPrompt($prompt = 'Please enter password :') {
//        if (preg_match('/^win/i', PHP_OS)) {
//            $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
//            file_put_contents(
//                    $vbscript, 'wscript.echo(InputBox("'
//                    . addslashes($prompt)
//                    . '", "", "password here"))');
//            $command = "cscript //nologo " . escapeshellarg($vbscript);
//            $password = rtrim(shell_exec($command));
//            unlink($vbscript);
//            return $password;
//        } else {
//            $command = "/usr/bin/env bash -c 'echo OK'";
//            if (rtrim(shell_exec($command)) !== 'OK') {
//                trigger_error("Can't invoke bash");
//                return;
//            }
//            $command = "/usr/bin/env bash -c 'read -s -p \""
//                    . addslashes($prompt)
//                    . "\" mypassword && echo \$mypassword'";
//            $password = rtrim(shell_exec($command));
//            echo "\n";
//            return $password;
//        }
//    }

    /**
     * Tells if one or several extensions are loaded
     * @param string|array $extension
     * @return boolean
     */
    public static function isLoaded($extension){
        try {
            self::checkLoaded($extension);
            return true;
        } catch (QCommandLineExtensionException $e) {
            return false;
        }
    }

    /**
     * Checks if one or several extensions are loaded
     * @param array|string $extension
     * @throws DCommandLineExtensionException
     * @throws DCommandLineSignatureException
     */
    public static function checkLoaded($extension){
        if(is_array($extension)){
            foreach($extension as $ext){
                if(!extension_loaded($ext)){
                    throw new QCommandLineExtensionException($extension);
                }
            }
        } else if(is_string($extension)){
            if(!extension_loaded($extension)){
                throw new QCommandLineExtensionException($extension);
            }
        } else {
            throw new QCommandLineSignatureException('Call to undefined function QCommandLine::isLoaded(' . implode(',', array_map('qGetType', func_get_args())) . ')');
        }
    }

    /**
     * Checks that this script is run from command line
     * @param boolean $strict
     * @return boolean
     * @throws DCommandLineRunException If strict is true and script not run from cli
     */
    public static function cliCheck($strict = true){
        if($strict && !self::_cliCheck()){
            throw new QCommandLineRunException;
        }
        return self::_cliCheck();
    }

    /**
     * Wait for an input from terminal (space trimmed)
     * @return string
     */
    public static function cin(){
        $f = new QFile('php://stdin');
        return QSystem::os() == QSystem::Windows ? self::_windobeDecode(trim($f->open(QFile::ReadOnly)->readLine())) : trim($f->open(QFile::ReadOnly)->readLine());
    }

    /**
     * Output a message to stdout
     * @param string $msg the message to display
     * @param boolean $encode [optional] true to encode the message to Windows terminal encoding
     */
    public static function cout($msg, $encode = true){
        if($encode && QSystem::os() == QSystem::Windows){
            $msg = self::_windobeEncode($msg);
        }
        echo $msg;
    }

    /**
     * Ouput a message to stdout with a new line
     * @param string $msg The message to display
     * @param bool $encode [optional] true to encode the message to Windows terminal encoding
     */
    public static function coutl($msg, $encode = true){
        if($encode && QSystem::os() == QSystem::Windows){
            $msg = self::_windobeEncode($msg);
        }
        echo $msg . QCommandLine::endl;
    }

    /**
     * Parse command line arguments
     * @param array $struct The structure to match with this format (-- = fullname, - = shortname :<br />
     * array(<br />
     *     "--long_option" => bool [true to check next token as a value, false otherwise],<br />
     *     "-l" => "--long_option" [shortcut the to long named option]<br />
     * )
     * @return DMap The arguments parsed
     * @throws DCommandLineException
     */
    public static function parseArgs($struct){
        $argv = self::arguments();
        if($_SERVER['argc'] != $argv->size()){
            throw new QCommandLineException('Command line args error');
        }
        // Disable the script name
        $argv->takeFirst();
        $args = new QMap;
        $it = new ArrayIterator($argv->toArray());
        while($it->valid()){
            if(substr($it->current(), 0, 2) == '--'){
                if(isset($struct[($arg = substr($it->current(), 2))])){
                    if($struct[$arg]){
                        if($it->valid()){
                            $it->next();
                            $args[$arg] = $it->current();
                        } else {
                            throw new QCommandLineException('Option ' . $it->current() . ' need a value');
                        }
                    } else {
                        $args[$arg] = false;
                    }
                } else {
                    throw new QCommandLineException('Option ' . $it->current() . ' not recognized');
                    return false;
                }
            } else if(substr($it->current(), 0, 1) == '-'){
                $arg = substr($it->current(), 1);
                $l = strlen($arg);
                for($i = 0; $i < $l; ++$i){
                    if(isset($struct[$arg{$i}]) && isset($struct[$struct[$arg{$i}]])){
                        if(isset($arg{$i+1})){
                            if($struct[$struct[$arg{$i}]]){
                                throw new QCommandLineException('Option -' . $arg{$i} . ' need a value');
                                return false;
                            } else {
                                $args[$struct[$arg{$i}]] = false;
                            }
                        } else {
                            if($struct[$struct[$arg{$i}]]){
                                $it->next();
                                $args[$struct[$arg{$i}]] = $it->current();
                            } else {
                                $args[$struct[$struct[$arg{$i}]]] = false;
                            }
                        }
                    } else {
                        if(!isset($struct[$arg{$i}])){
                            throw new QCommandLineException('Option -' . $arg{$i} . ' not recognized');
                        } else if(!isset($struct[$struct[$arg{$i}]])){
                            throw new QCommandLineException('Option -' . $arg{$i} . ' is malformed and shouldn\'t be used. Please contact the author');
                        }
                    }
                }
            }
            $it->next();
        }
        return $args;
    }

    /**
     * Check if php is in CLI mode
     * @return boolean
     */
    private static function _cliCheck(){
        return PHP_SAPI == 'cli';
    }

    /**
     * Translate from ASCII to Windows terminal encoding
     * @param string $str
     * @return string
     */
    private static function _windobeEncode($str){
        return str_replace(
            array(chr(233), chr(224), chr(231), chr(234), chr(235), chr(232), chr(239), chr(238), chr(244), chr(249)),
            array(chr(130), chr(133), chr(135), chr(136), chr(137), chr(138), chr(139), chr(140), chr(147), chr(151)),
            $str
        );
    }

    /**
     * Translate from Windows terminal encoding to ASCII
     * @param string $str
     * @return string
     */
    private static function _windobeDecode($str){
        return str_replace(
            array(chr(130), chr(133), chr(135), chr(136), chr(137), chr(138), chr(139), chr(140), chr(147), chr(151)),
            array(chr(233), chr(224), chr(231), chr(234), chr(235), chr(232), chr(239), chr(238), chr(244), chr(249)),
            $str
        );
    }
}

class QCommandLineException extends QAbstractObjectException {}
class QCommandLineSignatureException extends QAbstractObjectException implements QSignatureException {}
class QCommandLineRunException extends QCommandLineException {}

?>