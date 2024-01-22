<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3Uk1rVWdsUXIwUUk1d3plazZ2c0FHRmc3UkVLWlZyMUxaZEQrYmQzZW9wTkRoVkR1Q0VSTE9XV2IycDRSS08zTXdRSFB1d3N3WEVENzJBa1ZxVlBqVHRUemt2bk92Q01lV1ZXblFnaFc1bWNPTnJBTjQwWStCdllxQ0prSjA1RVE0PQ==*/
namespace Aws\DynamoDb;

use GuzzleHttp\Psr7;

/**
 * Special object to represent a DynamoDB binary (B) value.
 */
class BinaryValue implements \JsonSerializable
{
    /** @var string Binary value. */
    private $value;

    /**
     * @param mixed $value A binary value compatible with Guzzle streams.
     *
     * @see GuzzleHttp\Stream\Stream::factory
     */
    public function __construct($value)
    {
        if (!is_string($value)) {
            $value = Psr7\Utils::streamFor($value);
        }
        $this->value = (string) $value;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
