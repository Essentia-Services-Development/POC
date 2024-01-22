<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VEFnRGJzTm52RXp4VEI2YWhUKzZCY0QvakZOT2FDK1NLZDFLekhpUkhObEwvcXJKV2F1ZnRWZHF1TUY3MnZ5dzh1ajd5MkN3RnFRY1M3N0tISzdyMUxhZGZqZmI3QjlReEJPN09ybjdiRlBWQnZHbXl2SUFYc2JtOEFJYWIvZ3F5TUlSWlBDOWhDUzlqbnJDU1cwcTNw*/

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that begins dropping data once the size of the underlying
 * stream becomes too full.
 *
 * @final
 */
class DroppingStream implements StreamInterface
{
    use StreamDecoratorTrait;

    private $maxLength;

    /**
     * @param StreamInterface $stream    Underlying stream to decorate.
     * @param int             $maxLength Maximum size before dropping data.
     */
    public function __construct(StreamInterface $stream, $maxLength)
    {
        $this->stream = $stream;
        $this->maxLength = $maxLength;
    }

    public function write($string)
    {
        $diff = $this->maxLength - $this->stream->getSize();

        // Begin returning 0 when the underlying stream is too large.
        if ($diff <= 0) {
            return 0;
        }

        // Write the stream or a subset of the stream if needed.
        if (strlen($string) < $diff) {
            return $this->stream->write($string);
        }

        return $this->stream->write(substr($string, 0, $diff));
    }
}
