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

        if ($newValue !== Stream::SKIP) {
            $this->state = 'active';

            foreach ($this->dependentStreams as $idx => $stream) {
                $callable = $this->dependentFns[$idx];

                $stream(call_user_func($callable, $newValue));
            }

            return $this->value = $newValue;
        }
    }

    public function map(callable $fn, $ignoreInitial = false)
    {
        $target = $this->state === 'active' && $ignoreInitial !== Stream::SKIP
            ? new self(call_user_func($fn, $this->value))
            : new self();
        
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

        $stream = $ready ? new Stream(call_user_func($combiner, $streams)) : new Stream();

        $changed = [];

        $mappers = array_map(function ($mappedStream)
            use ($streams, $ready, $stream, $combiner, $changed) {

            return $stream->map(function ($value)
                use ($mappedStream, $streams, $ready, $stream, $combiner, $changed) {
                
                array_push($changed, $mappedStream);

                if ($ready || array_every($streams, function ($checkedStream)
                    use ($stream, $combiner, $changed) {
                    return $checkedStream->state !== 'pending';
                })) {
                    $ready = true;
                    $stream(call_user_func($combiner, $streams));
                    $changed = [];
                }

                return $value;

            }, Stream::SKIP);
        }, $streams);

        return $stream;
    }
}
