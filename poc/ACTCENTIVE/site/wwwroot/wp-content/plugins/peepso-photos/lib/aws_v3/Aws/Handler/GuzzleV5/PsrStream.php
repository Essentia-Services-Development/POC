<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3Ukg4eXpUc2syTlZ2TVRnMmFSRzIrNk9LNkRhWVhJa2YyWkZXR0x3QXFjQ3VWeldYeCt1SzJjUEJjd0VkSTdFU09XVVdtNzRqSFJRVkc5NE4wYVlpNmhjMThoak01b1dnalI0K21yYTV0OUt6ajZZMFFZTjliWEVwOWZSVzhFdjJjPQ==*/
namespace Aws\Handler\GuzzleV5;

use GuzzleHttp\Stream\StreamDecoratorTrait;
use GuzzleHttp\Stream\StreamInterface as GuzzleStreamInterface;
use Psr\Http\Message\StreamInterface as Psr7StreamInterface;

/**
 * Adapts a Guzzle 5 Stream to a PSR-7 Stream.
 *
 * @codeCoverageIgnore
 */
class PsrStream implements Psr7StreamInterface
{
    use StreamDecoratorTrait;

    /** @var GuzzleStreamInterface */
    private $stream;

    public function __construct(GuzzleStreamInterface $stream)
    {
        $this->stream = $stream;
    }

    public function rewind()
    {
        $this->stream->seek(0);
    }

    public function getContents()
    {
        return $this->stream->getContents();
    }
}
