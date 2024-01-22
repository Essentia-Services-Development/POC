<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VFFRSHBJQUlxcUNDY05CMWNJcWFqRk9uUkxQSHg2aGtyZGttRHROcTVLUldEdHJEeVJBWGZ2VTU2S3B6bHg0Rnc4UmZub3NTTTl4N05pUWhiN1dNSGtYK1pHWUJvWi9xd0prK0ZnWC9kVWN4UEJhWkxsQ0t2L2dWRy94MkZDZWZ4ZTViR1FjMEI1RjBqdUExUVZBaEtM*/

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Lazily reads or writes to a file that is opened only after an IO operation
 * take place on the stream.
 *
 * @final
 */
class LazyOpenStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /** @var string File to open */
    private $filename;

    /** @var string */
    private $mode;

    /**
     * @param string $filename File to lazily open
     * @param string $mode     fopen mode to use when opening the stream
     */
    public function __construct($filename, $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
    }

    /**
     * Creates the underlying stream lazily when required.
     *
     * @return StreamInterface
     */
    protected function createStream()
    {
        return Utils::streamFor(Utils::tryFopen($this->filename, $this->mode));
    }
}
