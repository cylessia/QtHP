<?php
/**
 * Description of DAsciiArtFont
 *
 * @author rbala
 */
class QAsciiArtFont {

    private $_name = '',
            $_height = 0,
            $_baseLine = 0,
            $_info = '',
            $_hardBlank = '',
            $_smushPolicy = null,
            $_chars = array(),
            $_maxLength = 0;

    const Fitted = 1,
          SmushedU = 2,
          SmushedR = 3,
          Full = 4;

    public static function availableFonts(){
        $d = new QDir(dirname(__FILE__) . '/fonts');
        $fonts = new QStringList;
        foreach($d->entryList() as $entry){
            $fi = new QFileInfo($d->path() . $entry);
            $fonts->append($fi->baseName());
        }
        return $fonts;
    }

    public function __construct($ftName){
        if(!file_exists(($path = dirname(__FILE__) . '/fonts/' . $ftName . '.flf'))){
            throw new QAsciiArtFontException('Font "' . $ftName . '" does not exists');
        }
        if(!($h = fopen($path, 'rb'))){
            throw new QAsciiArtFontException('Unable to read font "' . $ftName . '"');
        }

        $lines = explode("\n", str_replace("\r\n", "\n", fread($h, filesize($path))));

        fclose($h);
        $headers = explode(' ', trim($lines[0]));
        $this->_hardBlank = substr($headers[0], 5, 1);
        $this->_height = $headers[1];
        $this->_baseLine = $headers[2];
        $this->_maxLength = $headers[3];
        $this->_smushPolicy = $headers[4];
        $this->_info = implode("\n", array_slice($lines, 1, ($commentLines = $headers[5])));

        // All FIGlet fonts must contain chars 32-126, 196, 214, 220, 228, 246, 252, 223
        if(($lineCount = count($lines)) < (101  * $this->_height + $commentLines)){
            throw new QAsciiArtFontException('Font "' . $ftName . '" is corrupted');
        }

        $end = $commentLines+1 + 94 * $this->_height;
        for($chrIdx = 32, $line = $commentLines+1; $line < $lineCount && $line < $end; $line+=$this->_height){
            $currentChr = array_slice($lines, $line, $this->_height);
            foreach($currentChr as &$currentChrLine){
                $currentChrLine = rtrim($currentChrLine, substr($currentChrLine, -1));
            }
            $this->_chars[$chrIdx++] = $currentChr;
        }
        foreach(array(196, 214, 220, 228, 246, 252, 223) as $chrIdx){
            $line+=$this->_height;
            $currentChr = array_slice($lines, $line, $this->_height);
            foreach($currentChr as &$currentChrLine){
                $currentChrLine = rtrim($currentChrLine, substr($currentChrLine, -1));
            }
            $this->_chars[$chrIdx++] = $currentChr;
        }

        // Is there chars left ?
        $line += $this->_height;
        while($line < $lineCount){
            $elements = explode(' ', $lines[$line], 2);
            $chrIdx = $elements[0];
            if(strtolower(substr($chrIdx, 0, 2)) == '0x'){
                $chrIdx = hexdec($chrIdx);
            } else if (preg_match('/^0[0-7]+$/', $chrIdx)){
                $chrIdx = (int)$chrIdx;
            } else if(strtolower(substr($chrIdx, 0, 3)) == '-0x'){
                $chrIdx = -hexdec($chrIdx);
            } else if(ctype_digit($chrIdx)) {
                $chrIdx = (int)$chrIdx;
            } else if(implode('', $elements) != null) {
                throw new QAsciiArtFontException('Font "' . $ftName . '" is corrupted at line "' . $line . '"');
            } else {
                break;
            }
            $currentChr = array_slice($lines, ++$line, $this->_height);
            foreach($currentChr as &$currentChrLine){
                $currentChrLine = rtrim($currentChrLine, substr($currentChrLine, -1));
            }
            $this->_chars[$chrIdx++] = $currentChr;
            $line += $this->_height;
        }
    }

    public function chr($char){
        return $this->_chars[is_int($char)?$char:ord($char)];
    }

    public function height(){
        return $this->_height;
    }

    public function setHorizontalPolicy($policy){
        $this->_smushPolicy = $policy;
    }

    public function horizontalPolicy(){
        return $this->_smushPolicy;
    }

    public function maxLength(){
        return $this->_maxLength;
    }
}

class QAsciiArtFontException extends Exception {}