<?php

namespace Jefrancomix\MithrilStreams\Tests;
use PHPUnit\Framework\TestCase;

class ArrayEveryTest extends TestCase
{
    public function testEveryItemTrue()
    {
        $numbers = [ 1, 2, 3 ];

        $greaterThanZero = function ($item) {
            return $item > 0;
        };

        $this->assertTrue(array_every($numbers, $greaterThanZero));
    }

    public function testEveryItemFalse()
    {
        $numbers = [ 1, 2, 3 ];

        $isNull = function ($it) {
            return is_null($it);
        };

        $this->assertFalse(array_every($numbers, $isNull));
    }

    public function testOneItemFalseGetsFalse()
    {
        $numbers = [ 0, 1, 2 ];

        $moduleTwoIsZero = function ($num) {
            return $num % 2 === 0;
        };

        $this->assertFalse(array_every($numbers, $moduleTwoIsZero));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage testing
     */
    public function testThrowsByCallback()
    {
        $numbers = [ 0, 1, 2 ];

        $throwerCallback = function ($num) {
            if ($num === 1) {
                throw new \Exception('testing');
            }
            return true;
        };

        array_every($numbers, $throwerCallback);
    }
}
