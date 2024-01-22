<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2lOUTA0OHhGaVN6YnppV2ZYMm1NK3paNTNvbktIRXZyQ21NM0V6dTFnNGsxQ1RpSHFIbGdRUXRRSjViWE1qeFpMRWl2eXBpZFV5bWorODNUYUNVWUFaNWhFbWNrUStabGY3R3IySTQ2TXRic1B4VWlqWHcxSHFDNUJDSEpUeXFKRkc4c2FubTQ0NmxMbm1CcUw3OXBv*/

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator that prevents a stream from being seeked.
 *
 * @final
 */
class NoSeekStream implements StreamInterface
{
    use StreamDecoratorTrait;

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new \RuntimeException('Cannot seek a NoSeekStream');
    }

    public function isSeekable()
    {
        return false;
    }
}
