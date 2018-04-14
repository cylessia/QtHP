<?php
/**
 * Description of QAbstractObjectTestCase
 */

class QFakeAbstractObject extends QAbstractObject {}

class QAbstractObjectTestCase extends QUnitTestCase {
    public function testSignatureOfSetObjectNameWithArray(){
        $this->assertException('QAbstractObjectSignatureException', function(){
            $abstractObject = new QFakeAbstractObject();
            $abstractObject->setObjectName(array());
        });
    }
    
    public function testSignatureOfSetObjectNameWithObject(){
        $this->assertException('QAbstractObjectSignatureException', function(){
            $abstractObject = new QFakeAbstractObject();
            $abstractObject->setObjectName(new stdClass());
        });
    }
    
    public function testSignatureOfSetObjectNameWithBoolean(){
        $this->assertException('QAbstractObjectSignatureException', function(){
            $abstractObject = new QFakeAbstractObject();
            $abstractObject->setObjectName(true);
        });
    }
    
    public function testSignatureOfSetObjectNameWithNull(){
        $this->assertException('QAbstractObjectSignatureException', function(){
            $abstractObject = new QFakeAbstractObject();
            $abstractObject->setObjectName(null);
        });
    }
}
