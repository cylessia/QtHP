<?php

/**
 * Description of DTempFile
 *
 * @author rbala
 */
class QTempFile extends QFile {
    public function __construct($filename = ''){
        parent::__construct(uniqid($filename, true));
        $this->open(QFile::ReadWrite);
    }

    public function __destruct() {
        parent::__destruct();
        try { QFile::remove($this->filename()); } catch(QFileRemoveException $e){}
    }
}
