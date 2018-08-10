<?php

class QFtp extends QAbstractObject {

    const Unconnected = 0,
          Connected = 1,
          LoggedIn = 2,
          Closed = 3,

          Binary = FTP_BINARY,
          Ascii = FTP_ASCII,

          Active = false,
          Passive = true;

    private $_socket,
            $_state = self::Unconnected;

    public function __destruct(){
        $this->close();
    }

    public function cd($dirName){
        if(!$this->isOpen()){
            throw new QFtpCdException('Unable to change directory because connection is closed (or unconnected)');
        }
        if(!@ftp_chdir($this->_socket, $dirName)){
            throw new QFtpCdException('Changing directory failed');
        }
        return $this;
    }

    public function close(){
        if($this->_state != self::Unconnected && $this->_state != self::Closed){
            ftp_close($this->_socket);
            $this->_socket = null;
        }
        return $this;
    }

    public function connectToHost($host, $port = 21, $ssl = false){
        if($this->_state != self::Unconnected || $this->_state != self::Closed){
            throw new QFtpConnectedException('Connection already set');
        }
        if(!is_string($host)){
            throw new QFtpConnectionException('HostName must be a string');
        }
        if(!($this->_socket = ftp_connect($host, $port))){
            throw new QFtpConnectionException('Unable to connect to host "' . $host . ':' . $port . '"');
        }
        return $this;
    }

    public function get($remote, $local, $mode = self::Binary){
        if(!is_string($remote) || ($mode !== self::Binary && $mode !== self::Ascii)){
            throw new QFtpGetException('Call to undefined function QFtp::get(' . implode(', ', array_map('gettype', func_get_args())) . ')');
        }
        if($local instanceof QFile){
            if(!$local->isOpen()){
                if(!ftp_get($this->_socket, $local->fileName(), $remote, $mode)){
                    throw new QFtpGetException('Unable to get remote file');
                }
            } else {
                if(!ftp_fget($this->_socket, $local->handle(), $remote, $mode)){
                    throw new QFtpGetException('Unable to get remote file');
                }
            }
        } else if(is_string($local)){
            if(!ftp_get($this->_socket, $local, $remote, $mode)){
                throw new QFtpGetException('Unable to get remote file');
            }
        } else if(is_resource($local)){
            if(!ftp_fget($this->_socket, $local, $remote, $mode)){
                throw new QFtpGetException('Unable to get remote file');
            }
        } else {
            throw new QFtpGetException('Call to undefined function QFtp::get(' . implode(', ', array_map('gettype', func_get_args())) . ')');
        }
        return $this;
    }

    public function entryList($dir = null){
        if(!($array = ftp_nlist($this->_socket, $dir))){
            $array = array();
        }
        return QStringList::fromArray($array);
    }

    public function isOpen(){
        return $this->_socket != null;
    }

    public function login($login = null, $password = null){
        if(!ftp_login($this->_socket, $login, $password)){
            throw new QFtpLoginException('Unable to connect using "' . $login . ':' . $password . '"');
        }
        return $this;
    }

    public function mkdir($dirName){
        if(!ftp_mkdir($this->_socket, $dirName)){
            throw QFtpMakeDirException('Unable to create dir "' . $dirName . '"');
        }
        return $this;
    }

    public function put(){
        return $this;

    }

    public function remove(){
        return $this;

    }

    public function move(){
        return $this;
    }
    public function rmdir(){
        return $this;
    }

//    public function setProxy(){
//
//    }

    public function setTransferMode($mode){
        if(is_bool($mode)){
            if(!ftp_pasv($this->_socket, $mode)){
                throw new QFtpTransfertModeException('Unable to set transfert mode to "' . ($mode ? 'Passive' : 'Active') . '"');
            }
        }
        return $this;
    }

    public function state(){
        return $this->_state;
    }

}

?>