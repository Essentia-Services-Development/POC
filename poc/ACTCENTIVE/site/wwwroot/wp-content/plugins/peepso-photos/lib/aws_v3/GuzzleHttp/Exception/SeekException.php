<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UWg0VGw4Q1piV2l2T2dVd011dmZGamVHVDVmR1lXMTdzczkzOGNmKzRxZ2VtOTNrTVAwU3YzMWRVTFVnT3lOaTlnZ3hUV3dleFVlMmhSQ3g1cW9LL2JhTVBaOTVPS2Q5dTZEeFJSN0hGYUVkT2daZE9MZjFGK3lYQ281cU5seGFKRjhkZmZFUkZEMS9peGlBazVtVlRn*/
namespace GuzzleHttp\Exception;

use Psr\Http\Message\StreamInterface;

/**
 * Exception thrown when a seek fails on a stream.
 */
class SeekException extends \RuntimeException implements GuzzleException
{
    private $stream;

    public function __construct(StreamInterface $stream, $pos = 0, $msg = '')
    {
        $this->stream = $stream;
        $msg = $msg ?: 'Could not seek the stream to position ' . $pos;
        parent::__construct($msg);
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }
}
