<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UndJMEpXakNSakgzNXg4cmwrUDNaS1JUTVJGaUtySGhkUTFDSHhkajYyY0VyV05meldML1VlTmhtaEtDdWpPZ1lCcFUvRW1iZS8vRXpaM3BmL25XZE1YOGV6MFdRazFZSlduSXRTNC83UkRXSXhlSUpVd3JRMDVkeHJxZDdxS2lRPQ==*/
namespace Aws\DynamoDb;

/**
 * Special object to represent a DynamoDB Number (N) value.
 */
class NumberValue implements \JsonSerializable
{
    /** @var string Number value. */
    private $value;

    /**
     * @param string|int|float $value A number value.
     */
    public function __construct($value)
    {
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
