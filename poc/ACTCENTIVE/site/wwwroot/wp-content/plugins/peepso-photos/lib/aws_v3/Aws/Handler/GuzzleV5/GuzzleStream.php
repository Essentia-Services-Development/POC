<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2NUNmR4czNBUTFCYnhvMVdla0pWVjdHSzdqZkRNUGpwSzJGMFFvVDlleW1zeVhuQTNGRm5xcFdDbWFnQkNsMmFNSUlWOVZNd245K1ljbE1PaVE5ZGZMOTNsYUpvWGZkVFE3WnlOYk5hNytkRUQ0S1Y5cHdJK3RJYzRWQXh6ZHlWdEt2RHRBeUxML0J1blNsM1ZtUkI4*/
namespace Aws\Handler\GuzzleV5;

use GuzzleHttp\Stream\StreamDecoratorTrait;
use GuzzleHttp\Stream\StreamInterface as GuzzleStreamInterface;
use Psr\Http\Message\StreamInterface as Psr7StreamInterface;

/**
 * Adapts a PSR-7 Stream to a Guzzle 5 Stream.
 *
 * @codeCoverageIgnore
 */
class GuzzleStream implements GuzzleStreamInterface
{
    use StreamDecoratorTrait;

    /** @var Psr7StreamInterface */
    private $stream;

    public function __construct(Psr7StreamInterface $stream)
    {
        $this->stream = $stream;
    }
}
