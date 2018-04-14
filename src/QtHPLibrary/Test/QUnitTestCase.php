<?php

/**
 * Description of QUnitTestCase
 */
class QUnitTestCase extends QAbstractObject {

    const
    ModifierPublic = 0x01,
    ModifierProtected = 0x02,
    ModifierPrivate = 0x04,
    ModifierAsbtract = 0x08,
    ModifierStatic = 0x10,
    ModifierFinal = 0x20,
    ModifierConstructor = 0x40,
    ModifierDestructor = 0x80,
    
    MaskPropertyModifiers = self::ModifierPublic | self::ModifierProtected | self::ModifierPrivate | self::ModifierStatic,
    MaskMethodModifiers = self::ModifierPublic | self::ModifierProtected | self::ModifierPrivate | self::ModifierStatic
                          | self::ModifierAsbtract | self::ModifierFinal | self::ModifierConstructor | self::ModifierDestructor,
    MaskAllModifiers = self::ModifierPublic | self::ModifierProtected | self::ModifierPrivate | self::ModifierStatic
                          | self::ModifierAsbtract | self::ModifierFinal | self::ModifierConstructor | self::ModifierDestructor;
    
    private
    $_timeout,
    $_startTime,
    $_expectedException;
    
    private static
    
    $_asserting;
    
    public static function assertions(){
        return self::$_asserting;
    }
    
    public function assertEqual($expected, $actual, $strict = true){
        ++self::$_asserting;
        if(!($strict ? ($expected === $actual) : ($expected == $actual))){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that two vars are ' . ($strict ? 'strictely' : '') . ' equal' . "\n"
                . 'Expected : ' . $this->_printableValue($expected) . "\n"
                . 'Actual : ' . $this->_printableValue($actual)
            );
        }
    }
    
    public function assertScalar($value){
        ++self::$_asserting;
        if(!is_scalar($value)){
            throw new QUnitTestAssertExcetpion(
                'Failed asserting that a value is scalar (ie : boolean, float, integer or string)' . "\n"
                . 'Actual : ' . "\n"
                . $this->_printableValue($value, true)
            );
        }
    }
    
    public function assertNotEqual($expected, $actual, $strict = true){
        ++self::$_asserting;
        if(($strict ? ($expected !== $actual) : ($expected != $actual))){
            if(is_scalar($expected) && is_scalar($actual)){
                throw new QUnitTestCaseCaseException(
                    'Fail asserting that two vars are not ' . ($strict ? 'strictely' : '') . ' equal' . "\n"
                    . 'Expected : ' . "\n" . $this->_printableValue($expected) . "\n"
                    . 'Actual :' . "\n" . $this->_printableValue($actual)
                );
            }
        }
    }
    
    public function assertEmpty($stack){
        ++self::$_asserting;
        if(!empty($stack)){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a var is empty'
                . 'Actual :' . "\n" . $this->_printableValue($stack)
            );
        }
    }
    
    public function assertNotEmpty($stack){
        ++self::$_asserting;
        if(empty($stack)){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a var is not empty'
                . 'Actual :' . "\n" . $this->_printableValue($stack)
            );
        }
    }
    
    public function assertSize($size, $ht){
        ++self::$_asserting;
        throw new QUnitTestCaseException('The developper is dumb and must rewrite this assertion');
        try {
            $this->assertType($ht, 'array');
            $this->assertEqual(count($ht), $size);
        } catch(QUnitTestCaseCaseException $e){
            $this->assertInstanceOf($ht, 'Countable');
            $this->assertMethod($ht, 'count');
            $this->assertEqual(count($ht), $size);
        }
    }
    
    public function assertHashTableKey($hashTable, $key){
        ++self::$_asserting;
        $this->assertType($hashTable, 'array');
        if(!array_key_exists($key, $hashTable)){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a hashtable containes key "' . $this->_printableValue($key) . '"' . "\n"
                . 'Hashtable :' . $this->_printableValue($hashTable)
            );
        }
    }
    
    public function assertHashTable($expected, $actual, $value = true){
        ++self::$_asserting;
        try {
            $this->assertType($expected, 'array');
            $this->assertType($actual, 'array');
            foreach($expected as $k => $v){
                if(!array_key_exists($k, $actual)){
                    throw new QUnitTestCaseCaseException(
                        'Fail asserting that hashtable are equal ' . ($value ? '(keys and values)' : '(only keys)')
                        . 'Expected : ' . $this->_printableValue($expected) . "\n"
                        . 'Actual : ' . $this->_printableValue($actual)
                    );
                }
                try {
                    $this->assertEqual($expected[$k], $actual[$k]);
                } catch(QUnitTestCaseCaseException $e){
                    throw new QUnitTestCaseCaseException(
                        'Fail asserting that hashtable are equal ' . ($value ? '(keys and values)' : '(only keys)')
                        . 'Expected : ' . $this->_printableValue($expected) . "\n"
                        . 'Actual : ' . $this->_printableValue($actual)
                    );
                }
            }
        } catch(QUnitTestCaseCaseException $e){
            $this->assertInstanceOf($expected, 'ArrayAccess');
            $this->assertInstanceOf($actual, 'ArrayAccess');
            $this->assertMethod($expected, 'offsetExists');
            $this->assertMethod($actual, 'offsetExists');
            foreach($expected as $k => $v){
                if(!$actual->offsetExists($k)){
                    throw new QUnitTestException(
                        'Fail asserting that hashtable are equal ' . ($value ? '(keys and values)' : '(only keys)')
                        . 'Expected : ' . $this->_printableValue($expected) . "\n"
                        . 'Actual : ' . $this->_printableValue($actual)
                    );
                }
                try {
                    $this->assertEqual($expected[$k], $actual[$k]);
                } catch(QUnitTestCaseCaseException $e){
                    throw new QUnitTestCaseCaseException(
                        'Fail asserting that hashtable are equal ' . ($value ? '(keys and values)' : '(only keys)')
                        . 'Actual : ' . $this->_printableValue($expected) . "\n"
                        . 'Expected : ' . $this->_printableValue($actual)
                    );
                }
            }
        }
        $this->assertHashTable($actual, $expected, $value);
    }
    
    public function assertTrue($value){
        $this->assertEqual(true, $value, true);
    }
    
    public function assertFalse($value){
        $this->assertEqual(false, $value, true);
    }
    
    public function assertReferences(&$expected, &$other){
        ++self::$_asserting;
        switch(gettype($expected)){
            case 'object':
                do {
                    $ref = uniqid('__QtHP_QUnitTestCase_assertReferences_');
                }while(property_exists($other, $ref));
                $other->{$ref} = uniqid();
                $result = $other->{$ref} === $expected->{$ref};
                unset($other->{$ref});
                break;
            case 'array':
                do {
                    $ref = uniqid('__QtHP_QUnitTestCase_assertReferences_');
                }while(array_key_exists($ref, $other));
                $other[$ref] = uniqid();
                $result = $other[$ref] === $expected[$ref];
                unset($other[$ref]);
                break;
            case 'resource':
                $result = print_r($expected, true) === print_r($other, true);
                break;
            default:
                $key = uniqid('__QtHP_QUnitTestCase_assertReferences_');
                $tmp = $other;
                $other = $key;
                $result = $other === $expected;
                $other = $tmp;
                break;
        }
        if(!$result){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that two variables reference each other' . "\n"
                . 'Expected : ' . $this->_printableValue($expected) . "\n"
                . 'Acutal : ' . $this->_printableValue($other) . "\n"
            );
        }
    }
    
    public function assertType($expected, $actual, $objectType = true){
        ++self::$_asserting;
        if(!($objectType ?qGetType($actual) === $expected : gettype($actual) === $expected)){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that value is of type ' . $expected . "\n"
                . 'Acutal : ' . ($objectType ? qGetType($actual) : gettype($actual)) . "\n"
            );
        }
    }
    
    public function assertSameType($expected, $actual, $objectType = true){
        ++self::$_asserting;
        if(!($objectType ? (qGetType($expected) === qGetType($actual)) : (gettype($expected) === gettype($actual)))){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that values have the same type' . "\n"
                . 'Expected : ' . ($objectType ? qGetType($expected) : gettype($expected)) . "\n"
                . 'Actual : ' . ($objectType ? qGetType($actual) : gettype($actual))
            );
        }
    }
    
    public function assertGreaterThan($expected, $actual){
        ++self::$_asserting;
        if($expected >= $actual){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that ' . $this->_printableValue($actual) . ' is greater than ' . $this->_printableValue($expected)
            );
        }
    }
    
    public function assertGreaterOrEqual($expected, $actual){
        ++self::$_asserting;
        if($expected > $actual){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that ' . $this->_printableValue($actual) . ' is greater than or equals ' . $this->_printableValue($expected) 
            );
        }
    }
    
    public function assertLessThan($expected, $actual){
        ++self::$_asserting;
        if($expected <= $actual){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that ' . $this->_printableValue($actual) . ' is less than ' . $this->_²printableValue($expected) 
            );
        }
    }
    
    public function assertLessThanOrEqual($expected, $actual){
        ++self::$_asserting;
        if($expected < $actual){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that ' . $this->_printableValue($actual) . ' is less than or equals ' . $this->_printableValue($expected)
            );
        }
    }
    
    public function assertRegExp($pattern, $value){
        ++self::$_asserting;
        $this->assertIsRegExp($pattern);
        if(@preg_match($pattern, $value) === 0){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a pattern matches value' . "\n"
                . 'Expected match : ' . $this->_printableValue($value) . "\n"
                . 'Actual : ' . $pattern
            );
        }
    }
    
    public function assertIsRegExp($pattern){
        ++self::$_asserting;
        if(@preg_match($pattern, '') === false){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a pattern is valid' . "\n"
                . 'Actual : ' . $pattern
            );
        }
    }
    
    public function assertInstanceOf($expected, $class){
        ++self::$_asserting;
        if(!($class instanceof $expected)){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that class is instance of ' . $this->_printableValue($expected) . "\n"
                . 'Actual : ' . "\n" . $this->_printableValue($class)
            );
        }
    }
    
    public function assertMethod($class, $method, $modifiers = null){
        ++self::$_asserting;
        $this->assertType($class, 'object', false);
        $r = new ReflectionClass($class);
        try {
            $m = $r->getMethod($method);
            if($modifiers){
                $this->_assertModifier($m, $modifiers);
            }
        } catch(ReflectionException $e){
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a method exists within the class "' . qGetType($class) . '"' . "\n"
                . 'Actual :' . $this->_printableValue($method)
            );
        }
    }
    
    public function assertNotMethod($class, $method){
        ++self::$_asserting;
        try {
            $this->assertMethod($class, $method);
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a method does not exists within the class "' . qGetType($class) . '"' . "\n"
                . 'Actual :' . $this->_printableValue($method)
            );
        } catch (QUnitTestCaseCaseException $e) {}
    }
    
    public function assertProperty($class, $property, $modifiers = null){
        ++self::$_asserting;
        $this->assertType($class, 'object', false);
        $r = new ReflectionClass($class);
        try {
            $p = $r->getProperty($property);
            if($modifiers){
                $this->_assertModifier($property, $modifiers);
            }
        } catch (ReflectionException $e) {
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a property exists within the class "' . qGetType($class) . '"' . "\n"
                . 'Actual :' . $this->_printableValue($property)
            );
        }
    }
    
    public function assertNotProperty($class, $property){
        ++self::$_asserting;
        try {
            $this->assertProperty($class, $property);
            throw new QUnitTestCaseCaseException(
                'Fail asserting that a property does not exists within the class "' . qGetType($class) . '"' . "\n"
                . 'Actual :' . $this->_printableValue($property)
            );
        } catch (QUnitTestCaseCaseException $e) {}
    }
    
    public function assertObjectProperties($expected, $class, $allowStdClass = false){
        ++self::$_asserting;
        if($allowStdClass){
            $this->assertInstanceOf($class, $expected);
        }
        $classR = new ReflectionClass($class);
        $expectedR = new ReflectionClass($expected);
        
        foreach($expectedR->getProperties() as $property){
            $propertyR = $classR->getProperty($property->getName());
            
            $property->setAccessible(true);
            $propertyR->setAccessible(true);
            
            $this->assertSameType($property->getValue($expected), $propertyR->getValue($class));
            switch(gettype($property->getValue($expected))){
                case 'object':
                    $this->assertObjectProperties($property->getValue($expected), $propertyR->getValue($class));
                    break;
                default:
                    $this->assertEqual($property->getValue($expected), $propertyR->getValue($class), true);
                    break;
            }
        }
    }
    
    public function assertTimeout($milliseconds, $closure){
        ++self::$_asserting;
        $this->assertClosure($closure);
        $t = QDateTime::now();
        $closure();
        if(($msecs = $t->msecsTo(QDateTime::now())) > $milliseconds){
            throw new QUnitTestException(
                'Fail asserting that execution time is less than ' . $milliseconds . "\n"
                . 'Expected : ' . $milliseconds . "\n"
                . 'Actual : ' . ($msecs)
            );
        }
    }
    
    public function assertException($exception, $closure){
        ++self::$_asserting;
        /**
         * Waiting for VCRUNTIME140.dll ^^ 
        if(PHP_VERSION_ID >= 70000 && !($exception instanceof Throwable)){
            throw new QUnitTestCaseCaseExceptionException('"' . qGetType($expectedException) . '" must be an instance of Throwable');
        } else /**/if(!is_a($exception, 'Exception', true)){
            throw new QUnitTestCaseExceptionException(
                '"' . (is_string($exception) ? $exception : qGetType($exception)) . '" must be an instance of Exception'
            );
        }
        $type = qGetType($e);
        if(PHP_VERSION_ID >= 70000){
            try {
                $closure();
                throw new QUnitTestCaseNoExceptionThrownException(
                    'Fail asserting that closure throws exception ' . $exception . "\n"
                    . 'No exception thrown'
                );
            } catch(QUnitTestCaseNoExceptionThrownException $e){
                throw new QUnitTestCaseCaseException($e->getMessage());
            } catch (Throwable $e) {
                if(!($e instanceof $exception)){
                    throw new QUnitTestCaseCaseException(
                        'Fail asserting that closure throws exception ' . $exception . "\n"
                        . 'Actual : ' . qGetType($e)
                    );
                }
            }
        } else {
            try {
                $closure();
                throw new QUnitTestCaseNoExceptionThrownException(
                    'Fail asserting that closure throws exception ' . $exception . "\n"
                    . 'No exception thrown'
                );
            } catch(QUnitTestCaseNoExceptionThrownException $e){
                throw new QUnitTestCaseCaseException($e->getMessage());
            } catch (Exception $e) {
                if(!($e instanceof $exception)){
                    throw new QUnitTestCaseCaseException(
                        'Fail asserting that closure throws exception ' . $exception . "\n"
                        . 'Actual : ' . qGetType($e)
                    );
                }
            }
        }
        
    }
    
    private function _assertModifier($reflection, $modifiers){
        ++self::$_asserting;
        throw new QUnitTestCaseException('The developper is dumb and must rewrite this assertion');
        if($reflection instanceof ReflectionProperty){
            if(
                !($modifiers & self::ModifierPublic && $reflection->isPublic())
                || !($modifiers & self::ModifierProtected && $reflection->isProtected())
                || !($modifiers & self::ModifierPrivate && $reflection->isPrivate())
                || !($modifiers & self::ModifierStatic && $reflection->isStatic())
                || !($modifiers & self::ModifierDefault && $reflection->isDefault())
            ){
                throw new QUnitTestCaseCaseException(
                    'Assertion failed that a propery has modifiers' . "\n"
                    . 'Expected : ' . "\n" . $this->_pintableModifiers(self::MaskPropertyModifiers & $modifiers) . "\n"
                    . 'Actual : ' . "\n" . $this->_printableModifiers($modifiers)
                );
            }
        } else if($reflection instanceof ReflectionMethod) {
            if(
                !($modifiers & self::ModifierPublic && $reflection->isPublic())
                || !($modifiers & self::ModifierProtected && $reflection->isProtected()) 
                || !($modifiers & self::ModifierPrivate && $reflection->isPrivate()) 
                || !($modifiers & self::ModifierAsbtract && $reflection->isAbstract()) 
                || !($modifiers & self::ModifierStatic && $reflection->isStatic()) 
                || !($modifiers & self::ModifierFinal && $reflection->isFinal()) 
                || !($modifiers & self::ModifierConstruct && $reflection->isConstructor()) 
                || !($modifiers & self::ModifierDestructor && $reflection->isDestructor()) 
            ){
                throw new QUnitTestCaseCaseException(
                    'Assertion failed that a method has modifiers' . "\n"
                    . 'Actual : ' . "\n" . $this->_printableModifiers($modifiers) . "\n"
                    . 'Expected : ' . "`\n" . $this->_pintableModifiers(self::MaskMethodModifiers & $modifiers)
                );
            }
        } else {
            $fga = func_get_args();
            throw new QUnitTestCaseSignatureException('Call to undefined QUnitTestCase::_assertModifier(' . array_map('qGetType', $fga) . ')');
        }
    }
    
    private function _printableValue($value){
        if(is_scalar($value)){
            return $value;
        } else if(gettype($value) == 'resource'){
            return print_r($value, true);
        } else {
            return qGettype($value) . ' -> ' . print_r($value, true);
        }
    }
    
    private function _printableModifiers($mask){
        $modifiers = [];
        foreach(array(
            self::ModifierPublic => 'public',
            self::ModifierProtected => 'protected',
            self::ModifierPrivate => 'private',
            self::ModifierAsbtract => 'abstract',
            self::ModifierStatic => 'static',
            self::ModifierFinal => 'final',
            self::ModifierConstructor => 'constructor',
            self::ModifierDestructor => 'destructor',
        ) as $modifier => $string){
            if($mask & $modifier){
                $modifiers[] = $string;
            }
        }
        return implode(' - ', $modifiers);
    }
    
}

class QUnitTestCaseException extends QAbstractObjectException {}
class QUnitTestCaseSignatureException extends QUnitTestCaseException implements QSignatureException {}
class QUnitTestCaseCaseException extends QUnitTestCaseException {}
class QUnitTestCaseCaseExceptionException extends QUnitTestCaseException {}
class QUnitTestCaseNoExceptionThrownException extends QUnitTestCaseException {}