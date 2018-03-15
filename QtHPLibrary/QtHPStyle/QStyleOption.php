<?php

class QStyleOption extends QAbstractObject implements ArrayAccess {
    
    const Background = 'background',
          BackgroundAttachment = 'background-attachment',
          BackgroundColor = 'background-color',
          BackgroundImage = 'background-image',
          BackgroundPosition = 'background-position',
          BackgroundRepeat = 'background-repeat',
          Border = 'border',
          BorderBottom = 'border-bottom',
          BorderBottomColor = 'border-bottom-color',
          BorderBottomStyle = 'border-bottom-style',
          BorderBottomWidth = 'border-bottom-width',
          BorderColor = 'border-color',
          BorderLeft = 'border-left',
          BorderLeftColor = 'border-left-color',
          BorderLeftStyle = 'border-left-style',
          BorderLeftWidth = 'border-left-width',
          BorderRight = 'border-right',
          BorderRightColor = 'border-right-color',
          BorderRightStyle = 'border-right-style',
          BorderRightWidth = 'border-right-width',
          BorderSpacing = 'border-spacing',
          BorderStyle = 'border-style',
          BorderTop = 'border-top',
          BorderTopColor = 'border-top-color',
          BorderTopStyle = 'border-top-style',
          BorderTopWidth = 'border-top-width',
          BorderWidth = 'border-width',
          Bottom = 'bottom',
          Clear = 'clear',
          Clip = 'clip',
          Color = 'color',
          Content = 'content',
          Cursor = 'cursor',
          Font = 'font',
          FontFamily = 'font-family',
          FontSize = 'font-size',
          FontStyle = 'font-style',
          FontVariant = 'font-variant',
          FontWeight = 'font-weight',
          ImeMode = 'ime-mode',
          Left = 'left',
          LetterSpacing = 'letter-spacing',
          LineBreak = 'line-break',
          LineHeigth = 'line-height',
          LineStyle = 'list-style',
          LineStyleImage = 'list-style-image',
          LineStylePosition = 'list-style-position',
          LineStyleType = 'list-style-type',
          Marks = 'marks',
          BorderRaidus = 'border-radius',
          Outline = 'outline',
          OutlineColor = 'outline-color',
          OutlineStyle = 'outline-style',
          OutlineWidth = 'outline-width',
          Overflow = 'overflow',
          OverflowX = 'overflow-x',
          OverflowY = 'overflow-y',
          Position = 'position',
          Right = 'right',
          TextAlign = 'text-align',
          TextDecoration = 'text-decoration',
          TextIndent = 'text-indent',
          TextShadow = 'text-shadow',
          TextTransform = 'text-transform',
          Top = 'Top',
          VerticalAlign = 'vertical-align',
          WhiteSpacing = 'white-spacing',
          ZIndex = 'zIndex',
    
          Red = '#FF0000',
          Green = '#00FF00',
          Blue = '#0000FF',
          White = '#FFFFFF',
          Black = '#000000',
            
          Absolute = 'absolute',
          BaseLine = 'baseline',
          Blink = 'blink',
          Block = 'block',
          Bold = 'bold',
          //Bottom = 'bottom',
          Capitalize = 'capitalize',
          Center = 'center',
          Cicle = 'circle',
          CrossHair = 'crosshair',
          Dashed = 'dashed',
          Decimal = 'decimal',
          Defaults = 'default', 
          Disc = 'disc',
          Dotted = 'dotted',
          Double = 'double',
          Fixed = 'fixed',
          Groove = 'groove',
          Help = 'help',
          Hidden = 'hidden',
          Hide = 'hide',
          Inline = 'inline',
          InlineBlock = 'inline-block',
          Inset = 'inset',
          Inside = 'inside',
          Italic = 'italic',
          Justify = 'justify',
          //Left = 'left',
          LineThrough = 'line-through',
          LowerAlpha = 'lower-alpha',
          LowerCase = 'lowercase',
          LowerRoman = 'lower-roman',
          Middle = 'middle',
          Move = 'move',
          None = 'none',
          NoRepeat = 'no-repeat',
          Normal = 'normal',
          NoWrap = 'nowarap',
          Oblique = 'oblique',
          Outset = 'outset',
          Outside = 'outside',
          Overline = 'overline',
          Pointer = 'pointer',
          Progress = 'progress',
          Pre = 'pre',
          Rect = 'rect',
          Relative = 'relative',
          Repeat = 'repeat',
          RepeatX = 'repeat-x',
          RepeatY = 'repeat-y',
          Ridge = 'ridge',
          //Right = 'right',
          Scroll = 'scroll',
          Show = 'show',
          SmallCaps = 'small-caps',
          Solid = 'solid',
          Square = 'square',
          Statically = 'static',
          Sub = 'sub',
          Super = 'super',
          Table = 'table',
          TableCell = 'table-cell',
          Text = 'text',
          //Top = 'top',
          Underline = 'underline',
          UpperAlpha = 'upper-alpha',
          UpperCase = 'uppercase',
          UpperRoman = 'upper-roman',
          Url = 'url',
          Visisble = 'visible',
          Wait = 'wait',
          WordWrap = 'word-wrap',
            
          StyleTag = 0,
          StyleId = 1,
          StyleClass = 2;
    
    private $_properties = array(),
            $_styleName = '',
            $_styleType = null;
          
            
            
          /*
           * border-collapse : QTableView/Widget::collapseBorders(true|&false);
           * caption-side : QTableView/Widget::setCaptionOrientation(&QtHP::Top|Left|Bottom|Right);
           * cursor : QCursor class
           * direction : QWidget::setDirection(QtHP::Left|&Right);
           * display : QWidget sub classes should make it useless
           * empty-cells : QTableView/Widget::hideEmptyCells(true|&false);
           * float : QHBoxLayout::setOrientation(QtHP::Left|&Right); (Right means float left...)
           * height : QWidget::setHeight();
           * margin : QMargin class of a QWidget's parent
           * max-height : QWidget::setMaximumHeight()
           * max-width : QWidget::setMaximumWidth()
           * min-height : QWidget::setMinimumHeight()
           * min-width : QWidget::setMinimumWidth()
           * overflow : QWidget::setSizePolicy()
           * padding : QWidget::setMargin()
           * width : QWidget::setWidth()
           */
            
            /**
             * filter 
             * include-source
             * layer-*
             * layout-*
             */
    
    public function __construct($name = ''){
        if($name){
            $this->setStyleName($name);
        }
    }
    
    public function build(){
        $return = '';
        foreach($this->_properties as $k => $v){
            $return .= $k . ':' . $v . ';';
        }
        return $return;
    }
    
    public function hasStyleName(){
        return $this->_styleName != '';
    }
    
    public function setProperty($propertyName, $value){
        $this->_properties[$propertyName] = $value;
    }
    
    public function setStyleName($name){
        $name = trim($name);
        
        if(!isset($name{0}) || (($name{0} == '#' || $name{0} == '.') && !isset($name{1}))){
            throw new QStyleOptionException('A style option\'s name cannot be empty and must be valid');
        }
        if($this->_styleName){
            throw new QStyleOptionException('Unable to overwrite the name of a style option ("' . $this->_styleName . '" to "' . $name . '")');
        }
        if($name{0} == '#'){
            $this->_styleType = self::StyleId;
            $this->_styleName = substr($name, 1);
        } else if($name{0} == '.'){
            $this->_styleType = self::StyleClass;
            $this->_styleName = substr($name, 1);
        } else {
           $this->_styleType = self::StyleTag;
            $this->_styleName = $name;
        }
    }
    
    public function styleName($styleChar = false){
        return ($styleChar ? ($this->_styleType == self::StyleId ? '#' : ($this->_styleType == self::StyleClass ? '.' : '')) . $this->_styleName : $this->_styleName);
    }
    
    public function styleType(){
        return $this->_styleType;
    }
    
    /****************************
     * Interface implementation *
     ****************************/
    public function offsetExists($offset){
        return isset($this->_properties[$offset]);
    }
    
    public function offsetGet($offset){
        return $this->_properties[$offset];
    }
    
    public function offsetSet($offset, $value){
        $this->_properties[$offset] = $value;
    }
    
    public function offsetUnset($offset){
        unset($this->_properties[$offset]);
    }
}

class QStyleOptionException extends QException {}

?>