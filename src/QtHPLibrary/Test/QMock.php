<?php
/**
 * Description of QMock
 */
class QMock extends QAbstractObject {
    
    const
    
    MockByDefinition = 0,
    MockByReflection = 1
    /**
    ,
    MockByAnnotation,
    /**/
    ;
    
    
    private
    
    $_classname,
    $_properties,
    $_methods,
    $_expectations;
    
    public static function fromClass($object){
        if(!class_exists($object)){
            throw new QMockClassNotFoundException('Cannot create a mock from an non existing class');
        }
        $r = new ReflectionClass($object);
        $m = new self($r->getName());
        foreach($r->getMethods() as $method){
            $method->setAccessible(true);
            $m->_methods->insert($method->getName(), new QMockExpectExactly(null));
        }
        foreach($r->getProperties() as $property){
            $property->setAccessible(true);
            $m->_properties->insert($property->getName(), null);
        }
        return $m;
    }
    
    public function __construct($object = null) {
        if(is_string($object)){
            $this->_classname = $object;
        } else if(is_object($object)){
            $this->_classname = qGetType($object);
        }
        $this->_methods = new QMap;
        $this->_properties = new QMap;
    }
    
    public function expect($methodName, $times){
        if(is_string($methodName) && is_int($times)){
            $times = QMockExpectationExactly($times);
        }else if(!is_string($methodName) || !($times instanceof QAbstractMockExpectation)){
            $fga = func_get_args();
            throw new QMockSignatureException('Call to undefined function QMock::expect(' . implode(', ', array_map('qGetType', $fga)) . ')');
        }
        if(!$this->_methods->has($methodName)){
            throw new QMockUndefinedMethodException('Trying to mock undefined method "' . $this->_classname . '::' . $methodName . '()"');
        }
        $this->_expectations->insert($methodName, $times);
    }
    
    public function __call($name, $arguments) {
        if(!$this->_methods->has($name)){
            throw new QMockUndefinedMethodException('Call to undefined function "' . $this->_classname . '::' . $methodName . '(' . implode(', ', array_map('qGetType', $arguments)) . ')"');
        }
        return $this->_methods->value($name)->call($arguments);
    }
    
    public function __set($name, $value){
        if(!$this->_properties->has($name)){
            throw new QMockUndefinedPropertyException('Call to undefined property "' . $this->_classname . '::' . $name);
        }
        $this->_properties->insert($name, $value);
    }
    
    public function __get($name){
        if(!$this->_properties->has($name)){
            throw new QMockUndefinedPropertyException('Call to undefined property "' . $this->_classname . '::' . $name);
        }
        return $this->_properties->insert($name);
    }
}

class QMockException extends QAbstractObjectException {}
class QMockSignatureException extends QMockException implements QSignatureException {}
class QMockClassNotFoundException extends QMockException {}
class QMockUndefinedException extends QMockException {}
class QMockUndefinedMethodException extends QMockUndefinedException {}
class QMockUndefinedPropertyException extends QMockUndefinedException {}
