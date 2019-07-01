<?php
namespace Jefrancomix\MithrilStreams;

class Stream
{
    protected $value;
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke($newValue = null)
    {
        if (is_null($newValue)) {
            return $this->value;
        }
        $this->value = $newValue;
    }
}