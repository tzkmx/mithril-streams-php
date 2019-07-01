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
        if (!is_null($value)) {
            $this->state = 'active';
        }
        $this->value = $value;
    }

    public function __invoke()
    {
        $args = func_get_args();
        if (count($args) === 0) {
            return $this->value;
        }

        $newValue = $args[0];

        if ($newValue !== Stream::SKIP) {
            $this->state = 'active';

            
            foreach ($this->dependentStreams as $idx => $stream) {
                $callable = $this->dependentFns[$idx];

                if (is_callable($callable)) {
                    $val = call_user_func($callable, $newValue);
                    $stream($val);
                }
            }

            return $this->value = $newValue;
        }
    }

    public function map(callable $fn, $ignoreInitial = false)
    {
        $target = $this->state === 'active' && $ignoreInitial !== Stream::SKIP
            ? new Stream(call_user_func($fn, $this->value))
            : new Stream();
        array_push($target->parents, $this);

        array_push($this->dependentStreams, $target);
        array_push($this->dependentFns, $fn);

        return $target;
    }


    protected function isOpen(Stream $stream)
    {
        return $stream->state === 'pending' || $stream->state === 'active';
    }

    public static function combine(callable $combiner, array $streams)
    {
        $ready = array_every($streams, function ($stream) {
            if (!$stream instanceof Stream) {
                throw new TypeError("Ensure that each item passed to Stream::combine/Stream::merge/lift is a Stream");
            }

            return $stream->state === 'active';
        });

        $newStream = $ready ? new Stream(call_user_func($combiner, $streams)) : new Stream();

        $mappers = [];
        
        $delegate = self::makeDelegate($combiner, $ready, $newStream, $streams);
        foreach ($streams as $originStream) {
            $mappers[] = $originStream->map(
                $delegate,
                Stream::SKIP
            );
        }

        return $newStream;
    }

    public static function isNotPending(Stream $stream)
    {
        return $stream->state !== 'pending';
    }

    protected static function makeDelegate(
        callable $combiner,
        bool &$isReady,
        Stream $newStream,
        array &$streams
    ) {
        return function ($value) use ($combiner, &$isReady, $newStream, &$streams) {
            if ($isReady || array_every($streams, __CLASS__ . '::isNotPending')) {
                $isReady = true;
                $streamValues = array_map(function ($s) { return $s(); }, $streams);
                $newValue = $combiner->call(new self($streamValues)); 
                $newStream($newValue);
            }
        };
    }
}
