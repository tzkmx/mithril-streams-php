<?php

namespace Jefrancomix\MithrilStreams\Tests;
use Jefrancomix\MithrilStreams\Stream;
use PHPUnit\Framework\TestCase;

class StreamCombineTest extends TestCase
{
    public function testTransformsValue()
    {
        $stream = new Stream();

        $doubled = Stream::combine(function ($val) {
            return $val() * 2;
        }, [$stream]);
        
        $stream(2);

        $this->assertEquals(4, $doubled());
    }
}
