<?php

class QPixmap extends QAbstractObject {
    
    const IgnoreAspectRatio = 0,
          KeepAspectRatio = 1,
          KeepAspectRatioByExpanding = 2;
    
    private $_width,
            $_height,
            $_img;
    
    public function __construct($width, $height = null){
        if($width instanceof QSize){
            $this->_width = $width->width();
            $this->_height = $width->height();
        } else if(is_int($width) && is_int($height)){
            $this->_width = $width;
            $this->_height = $height;
        }
        $this->_img = imagecreatetruecolor($this->_width, $this->_height);
    }
    
    public function copy($x, $y = null, $width = null, $height = null){
        if($x instanceof QRect){
            if($this->_height > $x->top() && $this->_width > $x->left()){
                $img = new QPixmap($x->width(), $x->height());
                if(!imagecopy($img->_img, $this->_img, 0, 0, $x->left(), $x->top(), $x->width()<$this->_width?$x->width():$this->_width, $x->height()<$this->_height?$x->height():$this->_height)){
                    throw new QPixmapCopyException('Unable to copy the image');
                }
                return $img;
            }
        } else if(is_int($x) && is_int($y) && is_int($width) && is_int($height)){
            if($this->_height > $y && $this->_width > $x){
                $img = new QPixmap($width, $height);
                if(!imagecopy($img->_img, $this->_img, 0, 0, $x, $y, $width<$this->_width?$width:$this->_width, $height<$this->_height?$height:$this->_height)){
                    throw new QPixmapCopyException('Unable to copy the image');
                }
                return $img;
            }
        }
    }
    
    public function fill(QColor $color){
        if(!imagefilledrectangle($this->_img, 0, 0, $this->_width, $this->_height, $color->index())){
            throw new QPixmapFillException('Unable to fill the image');
        }
    }
    
    public function fillRect($x, $y, $width = null, $height = null, QColor $color = null){
        if($x instanceof QRect && $y instanceof QColor){
            if(!imagefilledrectangle($this->_img, $x->left(), $x->top(), $x->right(), $x->bottom(), $y->index())){
                throw new QPixmapFillException('Unable to fill the image');
            }
        } else if($x instanceof QPoint && $y instanceof QPoint && $width instanceof QColor){
            if($x->x() == $y->x() ||$x->y() == $y->y()){
                return;
            }
            if(!imagefilledrectangle($this->_img, $x->x(), $x->y(), $y->x(), $y->y(), $width->index())){
                throw new QPixmapFillException('Unable to fill the image');
            }
        } else if(is_int($x) && is_int($y) && is_int($width) && is_int($height) && $color != null){
            if(!imagefilledrectangle($this->_img, $x, $y, $x + $width, $y + $height, $color->index())){
                throw new QPixmapFillException('Unable to fill the image');
            }
        } else {
            throw new QPixmapException('Call to undefined signature QPixmap::fillRect(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
    }
    
    public function height(){
        return $this->_height;
    }
    
    public function pixel($x, $y = null){
        if($x instanceof QPoint){
            if($x->x() > $this->_width ||$x->y() > $this->_height){
                throw new QPixmapException('Out of image');
            }
            return QColor::fromIndex(imagecolorat($this->_img, $x->x(), $x->y()));
        } else if(is_int($x) && is_int($y)){
            if($x > $this->_width ||$y > $this->_height){
                throw new QPixmapException('Out of image');
            }
            return QColor::fromIndex(imagecolorat($this->_img, $x, $y));
        } else {
            throw new QPixmapException('Call to undefined QPixamp::pixel(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
    }
    
    public function rect(){
        return new QRect(0, 0, $this->_width, $this->_height);
    }
    
    public function scaled($width, $height, $aspectRatioMode = self::IgnoreAspectRatio){
        if($width instanceof QSize && is_int($height)){
            // Il faut trouver le bon ratio
            $_w = $width->width();
            $_h = $width->height();
            $wScale = $_w / $this->_width;
            $hScale = $_h / $this->_height;
            $ratio = $height;
        } else if(is_int($width) && is_int($height) && is_int($aspectRatioMode)){
            $_w = $width;
            $_h = $height;
            $wScale = $_w / $this->_width;
            $hScale = $_h / $this->_height;
            $ratio = $aspectRatioMode;
        } else {
            throw new QPixmapException('Call to undefined function QPixamp::pixel(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
        
        switch($ratio){
            case self::KeepAspectRatio:
                if($wScale < $hScale){
                    $_w = intval($this->_width * $wScale);
                    $_h = intval($this->_height * $wScale);
                } else {
                    $_w = intval($this->_width * $hScale);
                    $_h = intval($this->_height * $hScale);
                }
                break;
            case self::KeepAspectRatioByExpanding:
                if($wScale < $hScale){
                    $_w = intval($this->_width * $hScale);
                    $_h = intval($this->_height * $hScale);
                } else {
                    $_w = intval($this->_width * $wScale);
                    $_h = intval($this->_height * $wScale);
                }
                break;
            case self::IgnoreAspectRatio:
            default:
                //break;
        }
        
        $img = new QPixmap((int)$_w, (int)$_h);
        if(!imagecopyresized($img->_img, $this->_img, 0, 0, 0, 0, $_w, $_h, $this->_width, $this->_height)){
            throw new QPixmapScaleException('Unable to scale the image');
        }
        return $img;
    }
    
    public function scaleToHeight($height){
        $height /= $this->_height;
        $img = new QPixmap(intval($this->_width*$height), intval($this->_height*$height));
        if(!imagecopyresized($img->_img, $this->_img, 0, 0, 0, 0, $_w, $_h, $this->_width, $this->_height)){
            throw new QPixmapScaleException('Unable to scale the image');
        }
        return $img;
    }
    
    public function scaleToWidth($width){
        $width /= $this->_width;
        $img = new QPixmap(intval($this->_width*$width), intval($this->_height*$width));
        if(!imagecopyresized($img->_img, $this->_img, 0, 0, 0, 0, $_w, $_h, $this->_width, $this->_height)){
            throw new QPixmapScaleException('Unable to scale the image');
        }
        return $img;
    }
    
    public function setPixel($x, $y, QColor $color = null){
        if($x instanceof QPoint && $y instanceof QColor){
            if(!imagesetpixel($this->_img, $x->x(), $x->y(), $y->index())){
                throw new QPixmapSetPixelException('Unable to set pixel (' . $x->x() . ',' . $x->y() . ')');
            }
        } else if(is_int($x) && is_int($y)){
            if(!imagesetpixel($this->_img, $x, $y, $color->index())){
                throw new QPixmapSetPixelException('Unable to set pixel (' . $x . ',' . $y . ')');
            }
        }
    }
    
    public function valid($x, $y = null){
        if($x instanceof QPoint){
            return ($x->x() > 0 && $x->x() < $this->_width && $x->y() > 0 && $x->y() < $this->_height);
        } else if(is_int($x) && is_int($y)) {
            return ($x > 0 && $x < $this->_width && $y > 0 && $y < $this->_height);
        } else {
            throw new QPixmapException('Call to undefined function QPixamp::pixel(' . implode(',', array_map('gettype', func_get_args())) . ')');
        }
    }
    
    public function width(){
        return $this->_width;
    }
    
    public function __toString(){
        header('Content-Type:image/png');
        imagepng($this->_img);
    }
}

class QPixmapException extends QException{};
class QPixmapFillException extends QPixmapException{};
class QPixmapCopyException extends QPixmapException{};
class QPixmapScaleException extends QPixmapException{};
class QPixmapSetPixelException extends QPixmapException{};

?>