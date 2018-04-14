<?php

/**
 * Description of QAbstractMockExpectation
 */
abstract class QAbstractMockExpectation extends QAbstractObject {
    abstract public function matches($times);
}

class QAbstractMockExpectationException extends QAbstractObjectException {}
class QAbstractMockExpectationSignatureException extends QAbstractMockExpectationException {}