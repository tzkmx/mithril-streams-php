<?php

namespace Jefrancomix\MithrilStreams\Tests;
use Jefrancomix\MithrilStreams\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    public function testWorksAsGetterAndSetter()
    {
        $expectedValue1 = 1;
        $stream = new Stream($expectedValue1);

        $this->assertEquals($expectedValue1, $stream());

        $stream(2);

        $expectedValue2 = 2;

        $this->assertEquals($expectedValue2, $stream());
    }
} 