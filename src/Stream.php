<?php
namespace Jefrancomix\MithrilStreams;

class Stream
{
    protected $value;
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        $args = func_get_args();
        if (count($args) === 0) {
            return $this->value;
        }

        $newValue = $args[0];

        return $this->value = $newValue;
    }


}