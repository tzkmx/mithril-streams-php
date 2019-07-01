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

    public function testHasNullValueAsDefault()
    {
        $stream = new Stream();

        $this->assertNull($stream());
    }

    public function testCanUpdateToUndefined()
    {
        $stream = new Stream('test');

        $this->assertEquals('test', $stream());

        $stream(null);

        $this->assertNull($stream());
    }

    public function testCanBeStreamOfStreams()
    {
        $stream = new Stream(new Stream(1));

        $this->assertEquals(1, $stream()());
    }

    public function testCanSKIP()
    {
        $stream = new Stream(2);

        $depStream = $stream->map(function ($value) {
            return $value === 5
                ? Stream::SKIP
                : $value;
        });
        $this->assertEquals(2, $depStream());

        $stream(5);
        $this->assertEquals(2, $depStream());

        $stream(1);
        $this->assertEquals(1, $depStream());
    }
} 