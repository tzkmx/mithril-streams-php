<?php
namespace Jefrancomix\MithrilStreams;

class Stream
{
    const SKIP = 'Stream5d196afee9eda8.68885278';

    protected $value;

    protected $state = 'pending';

    /**
     * @var Stream[]
     */
    protected $parents = [];

    /**
     * @var Stream[]
     */
    protected $dependentStreams = [];

    /**
     * @var callable[]
     */
    protected $dependentFns = [];

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

        if ($newValue !== self::SKIP) {
            $this->state = 'active';

            foreach ($this->dependentStreams as $idx => $stream) {
                $callable = $this->dependentFns[$idx];

                $stream(call_user_func($callable, $newValue));
            }

            return $this->value = $newValue;
        }
    }

    public function map(callable $fn)
    {
        $target = new self(call_user_func($fn, $this->value));
        
        array_push($target->parents, $this);

        array_push($this->dependentStreams, $target);
        array_push($this->dependentFns, $fn);

        return $target;
    }


    protected function isOpen(Stream $stream)
    {
        return $stream->state === 'pending' || $stream->state === 'active';
    }
}
