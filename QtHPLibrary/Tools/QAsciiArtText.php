<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DAsciiArtText
 *
 * @author rbala
 */
class QAsciiArtText {

    private $_font = null,
            $_text = '';

    public function __construct($text = ''){
        $this->_text = $text;
    }

    /**
     * Set the font for this text
     * @param string|DAsciiArtFont $ftName
     * @return DAsciiArtFont
     * @throws DAsciiArtTextException
     */
    public function setFont($ftName){
        if($ftName instanceof QAsciiArtFont){
            $this->_font = $ftName;
        } else if(is_string($ftName)){
            $this->_font = new QAsciiArtFont($ftName);
        } else {
            throw new QAsciiArtTextException('Unable to init font');
        }
        return $this;
    }

    public function setText($text){
        $this->_text = utf8_decode($text);
        return $this;
    }

    /**
     *
     * @return DAsciiArtFont
     */
    public function font(){
        return $this->_font ? $this->_font : ($this->_font = new QAsciiArtFont('standard'));
    }

    public function text(){
        return $this->_text;
    }

    public function output($asString = false){
        $output = array_fill(0, ($height = $this->_font->height()), '');
        foreach(str_split($this->_text) as $chr){
            $tmp = $this->_font->chr($chr);
            if($this->_font->horizontalPolicy() != QAsciiArtFont::Full){
                $this->_horizontalSmush($output, $tmp);
            }
            for($i = 0; $i < $height; ++$i){
                $output[$i] .= $tmp[$i];
            }
        }
        // Now remove hardblank
        for($i = 0; $i < $height; ++$i){
            $output[$i] = str_replace('$', ' ', $output[$i]);
        }
        return $asString ? implode("\n", $output) : $output;
    }

    public function _horizontalSmush(&$leftTxt, &$rightTxt){
        switch($this->_font->horizontalPolicy()){
            case QAsciiArtFont::Fitted:
                // Ici, on supprime les espaces indésirables !
                $overlap = $this->_font->maxLength()+1;
                // On calcul pour chaque ligne, on calcul le nombre d'espace !
                for($i = 0; $i < $this->_font->height(); ++$i){
                    // Get the number of spaces !
                    $leftSpaces = strlen($leftTxt[$i]) - strlen(rtrim($leftTxt[$i]));
                    $rightSpaces = strlen($rightTxt[$i]) - strlen(ltrim($rightTxt[$i]));
                    $overlap = min($leftSpaces + $rightSpaces, $overlap);
//                    echo $i . " :\n";
//                    echo '"' . $leftTxt[$i] . '"';
//                    echo "\n";
//                    echo '"' . $rightTxt[$i] . '"';
//                    echo "\n";
//                    echo 'Left length : ' . strlen($leftTxt[$i]) . "\n" . 'Left trim : ' . strlen(rtrim($leftTxt[$i])) . "\n";
//                    echo 'Left : ' . $leftSpaces . "\n" . 'Right : ' . $rightSpaces . "\n" . 'Min : ' . $overlap . "\n\n";
//                    exit;
                }
//                echo 'OVERLAP : ' . $overlap . "\n";
                if($overlap){
                    // Now delete derisednu spaces
                    for($i = 0; $i < $this->_font->height(); ++$i){
//                        echo '$$$$$$$$$$$$$$$$' . "\n" . '"' . $leftTxt[$i] . '"-"' . $rightTxt[$i] . '"' . "\n";
                        $leftOldLength = strlen($leftTxt[$i]);
                        $left = substr($leftTxt[$i], -$overlap);
                        $leftLength = strlen(rtrim($left));
//                        echo 'Left trimmed length = ' . $leftLength . "\n";
                        if($leftLength == 0){
                            // Delete left
//                            echo 'Delete left' . "\n";
                            $leftTxt[$i] = substr($leftTxt[$i], 0, -$overlap);
                        } else if($leftLength == $overlap){
                            // Delete right
//                            echo 'Delete right' . "\n";
                            $rightTxt[$i] = substr($rightTxt[$i], $overlap);
                        } else {
                            // Delete both
//                            echo 'Delete both' . "\n";
                            $leftTxt[$i] = substr($leftTxt[$i], 0, $leftOldLength-$overlap+$leftLength);
                            $rightTxt[$i] = substr($rightTxt[$i], $leftLength);
                        }
//                        echo "\n" . '"' . $leftTxt[$i] . '"-"' . $rightTxt[$i] . '" => ' . (strlen($leftTxt[$i]) + strlen($rightTxt[$i])) . "\n\n";
                    }
                }
                break;
        }
    }
}

class QAsciiArtTextException extends Exception {}