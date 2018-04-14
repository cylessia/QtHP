<?php
/**
 * Description of QBitArrayTestCase
 */
class QBitArrayTestCase extends QUnitTestCase {
    
    // QBitArray::__construct
    
    public function testConstructorWithoutParams(){
        $bitArray = new QBitArray;
        $this->assertEqual(0, $bitArray->size());
    }
    
    public function testConstructorWithSize(){
        $bitArray = new QBitArray(2);
        $this->assertEqual(2, $bitArray->size());
    }
    
    public function testConstructorCopy(){
        $expectedbitArray = new QBitArray(2, true);
        $actualBitArray = new QBitArray($expectedbitArray);
        $this->assertObjectProperties($expectedbitArray, $actualBitArray);
    }
    
    public function testWrongSignatureExceptionOfContructor(){
        $this->assertException('QBitArraySignatureException', function(){
            $bitArray = new QBitArray(array());
        });
    }
    
    // QBitArray::at()
    
    public function testBitIsAvailableAndCorrect(){
        $bitArray = new QBitArray(2, false);
        $this->assertEqual(0, $bitArray->at(1));
    }
    
    public function testBitAtThrowRangeException(){
        $this->assertException('QBitArrayRangeException', function(){
            $bitArray = new QBitArray(2, false);
            $bitArray->at(2);
        });
    }
    
    // QBitArray::clear()
    public function testClearBitArrayReinitializeAll(){
        $bitArray = new QBitArray(2, true);
        $bitArray->clear();
        $this->assertEqual(0, $bitArray->size());
        $this->assertException('QBitArrayRangeException', function() use($bitArray){
            $bitArray->at(0);
        });
    }
    
    // QBitArray::clearBit()
    public function testBitCanBeRemoved(){
        $bitArray = new QBitArray(3, true);
        $bitArray->toggleBit(1);
        $bitArray->clearBit(1);
        $this->assertEqual(3, $bitArray->toInt());
    }
    
    // QBitArray::count()
    public function testCountBitReturnsExpected(){
        $bitArray = new QBitArray(3, true);
        $this->assertEqual(3,$bitArray->count());
        $this->assertEqual(0,$bitArray->count(false));
    }
    
    // QBitArray::fill()
    public function testFillReturnThis(){
        $bitArray = new QBitArray(3, true);
        $other = $bitArray->fill(false);
        $this->assertReferences($bitArray, $other);
    }
    
    public function testFillsEveyBitToCorrectValue(){
        $bitArray = new QBitArray(3, false);
        $bitArray->fill(true);
        $this->assertEqual(7, $bitArray->toInt());
    }
    
    public function testFillsEveyBitAndTruncateToSize(){
        $bitArray = new QBitArray(3, false);
        $bitArray->fill(true, 2);
        $this->assertEqual(3, $bitArray->toInt());
    }
    
    public function testFillsSampleOfBit(){
        $bitArray = new QBitArray(3, false);
        $bitArray->fill(true, 1, 2);
        $this->assertEqual(2, $bitArray->toInt());
    }
    
    public function testFillsCannotSetNegativeSize(){
        $this->assertException('QBitArraySizeException', function(){
            $bitArray = new QBitArray(3, false);
            $bitArray->fill(true, 2, 1);
        });
    }
    
    public function testFillsThrowOutOfRange(){
        $this->assertException('QBitArrayRangeException', function(){
            $bitArray = new QBitArray(3, false);
            $bitArray->fill(true, 4, 5);
            $bitArray->fill(true, 3, 4);
        });
    }
    
    // QBitArray::fromInt()
    public function testBitArrayFromInt(){
        $bitArray = QBitArray::fromInt(10);
        $this->assertEqual(10, $bitArray->toInt());
        $this->assertEqual('1010', $bitArray->toString());
    }

    // QBitArray::toInt()
    public function testbitArrayCanBeConvertedToInt(){
        $bitArray = new QBitArray(16, true);
        $this->assertEqual(65535, $bitArray->toInt());
    }
    
    // QBitArray::isEmpty()
    public function testBitArrayEmptiness(){
        $bitArray = new QBitArray(2);
        $this->assertFalse($bitArray->isEmpty());
        $bitArray->clear();
        $this->assertTrue($bitArray->isEmpty());
    }
    
    //QBitArray::neg()
    public function testNegativeValue(){
        $bitArray = new QBitArray(4, true);
        $bitArray->toggleBit(1);
        $other = $bitArray->neg();
        $this->assertEqual(2, $other->toInt());
    }
    
    //QBitArray::performAnd()
    public function testPerformingAndOnSmallerBitArray(){
        $bitArray1 = new QBitArray(5, true);
        $bitArray2 = new QBitArray(4, true);
        $bitArray1->toggleBit(0);
        $bitArray2->toggleBit(1);
        $bitArray1->performAnd($bitArray2);
        $this->assertEqual(0x1C, $bitArray1->toInt());
        $this->assertEqual(5, $bitArray1->size());
    }
    
    public function testPerforminAndOnSameSize(){
        $bitArray1 = new QBitArray(5, true);
        $bitArray2 = new QBitArray(5, true);
        $bitArray1->toggleBit(1);
        $bitArray2->toggleBit(2);
        $bitArray1->performAnd($bitArray2);
        $this->assertEqual(0x19, $bitArray1->toInt());
        $this->assertEqual(5, $bitArray1->size());
    }
    
    public function testPerforminAndOnBiggerBitArray(){
        $bitArray1 = new QBitArray(4, true);
        $bitArray2 = new QBitArray(5, true);
        $bitArray1->toggleBit(1);
        $bitArray2->toggleBit(2);
        $bitArray1->performAnd($bitArray2);
        $this->assertEqual(0x9, $bitArray1->toInt());
        $this->assertEqual(5, $bitArray1->size());
    }
    
    // QBitArray::performOr
    public function testPerformingOrOnSmallerBitArray(){
        $bitArray1 = new QBitArray(5, false);
        $bitArray2 = new QBitArray(4, true);
        $bitArray1->toggleBit(1);
        $bitArray1->toggleBit(4);
        $bitArray2->toggleBit(1);
        $bitArray1->performOr($bitArray2);
        $this->assertEqual(0x1F, $bitArray1->toInt());
        $this->assertEqual(5, $bitArray1->size());
    }
    
    public function testPerformingOrOnSameSize(){
        $bitArray1 = new QBitArray(4, false);
        $bitArray2 = new QBitArray(4, true);
        $bitArray1->toggleBit(1);
        $bitArray2->toggleBit(1);
        $bitArray1->performOr($bitArray2);
        $this->assertEqual(0xF, $bitArray1->toInt());
        $this->assertEqual(4, $bitArray1->size());
    }
    
    public function testPerformingOrOnBiggerBitArray(){
        $bitArray1 = new QBitArray(2, false);
        $bitArray2 = new QBitArray(5, true);
        $bitArray1->toggleBit(1);
        $bitArray2->toggleBit(1);
        $bitArray2->toggleBit(3);
        $bitArray1->performOr($bitArray2);
        $this->assertEqual(0x17, $bitArray1->toInt());
        $this->assertEqual(5, $bitArray1->size());
    }
    
    // QBitArray::performXor
    public function testPerformingXorOnSmallerBitArray(){
        $bitArray1 = new QBitArray(5, false);
        $bitArray2 = new QBitArray(4, true);
        $bitArray1->toggleBit(1);
        $bitArray1->toggleBit(4);
        $bitArray2->toggleBit(0);
        $bitArray1->performXor($bitArray2);
        $this->assertEqual(0x1C, $bitArray1->toInt());
        $this->assertEqual(5, $bitArray1->size());
    }
    
    public function testPerformingXorOnSameSize(){
        $bitArray1 = new QBitArray(4, false);
        $bitArray2 = new QBitArray(4, true);
        $bitArray1->toggleBit(1);
        $bitArray2->toggleBit(2);
        $bitArray1->performXor($bitArray2);
        $this->assertEqual(0x9, $bitArray1->toInt());
        $this->assertEqual(4, $bitArray1->size());
    }
    
    public function testPerformingXorOnBiggerBitArray(){
        $bitArray1 = new QBitArray(2, false);
        $bitArray2 = new QBitArray(5, true);
        $bitArray1->toggleBit(1);
        $bitArray2->toggleBit(3);
        $bitArray1->performXor($bitArray2);
        $this->assertEqual(0x15, $bitArray1->toInt());
        $this->assertEqual(5, $bitArray1->size());
    }
    
    // QBitArray::performNeg()
    public function testPerformNegativeValue(){
        $bitArray = new QBitArray(4, true);
        $bitArray->toggleBit(3);
        $this->assertEqual(8, $bitArray->performNeg()->toInt());
    }
    
    // QBitArray::resize()
    public function testResizeThrowRangeExceptionWithNegativeSize(){
        $this->assertException('QBitArrayRangeException', function(){
            $bitArray = new QBitArray;
            $bitArray->resize(-1);
        });
    }
    
    public function testResizeTruncateBitArrayWithSmallerSize(){
        $bitArray = new QBitArray(3, true);
        $bitArray->resize(2);
        $this->assertEqual(2, $bitArray->size());
    }
    
    public function testResizeBitArrayWithGreaterSize(){
        $bitArray = new QBitArray(3, true);
        $bitArray->resize(5);
        $this->assertEqual(5, $bitArray->size());
    }
    
    // QBitArray::setBit()
    public function testSettingBitActuallySetIt(){
        $bitArray = new QBitArray(3, false);
        $bitArray->setBit(2);
        $this->assertEqual(4, $bitArray->toInt());
    }
    
    public function testSettingOutOfRangeBitThrowsRangeException(){
        $this->assertException('QBitArrayRangeException', function(){
            $bitArray = new QBitArray(3, false);
            $bitArray->setBit(3);
        });
    }
    
    // QBitArray::toggleBit()
    public function testTogglingOutOfRangeBitThrowsRangeException(){
        $this->assertException('QBitArrayRangeException', function(){
            $bitArray = new QBitArray(3, false);
            $bitArray->toggleBit(3);
        });
    }
    
    public function testTogglingBitChangesValueBothWays(){
        $bitArray = new QBitArray(1, false);
        $bitArray->toggleBit(0);
        $this->assertEqual(1, $bitArray->at(0));
        $bitArray->toggleBit(0);
        $this->assertEqual(0, $bitArray->at(0));
    }

    // QBitArray::toInt
    public function testBitArrayIsCastableToInt(){
        $bitArray = new QBitArray(6, true);
        $this->assertEqual(0x3F, $bitArray->toInt());
    }

    // QBitArray::toString
    public function testBitArrayIsCastableToString(){
        $bitArray = new QBitArray(6, true);
        $bitArray->toggleBit(1);
        $this->assertEqual('111101', $bitArray->toString());
    }
    
    // QBitArray::truncate
    public function testTruncatesBitArray(){
        $bitArray = new QBitArray(6, true);
        $bitArray->toggleBit(1);
        $bitArray->toggleBit(2);
        $bitArray->truncate(2);
        $this->assertEqual('01', $bitArray->toString());
    }
    
}
