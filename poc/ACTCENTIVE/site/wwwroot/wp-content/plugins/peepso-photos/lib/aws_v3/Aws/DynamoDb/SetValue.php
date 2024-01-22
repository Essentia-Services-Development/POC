<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2ZYN1BqczBxNHlQZTZyT29naUVsVkdZbXpCdjBnOU1sTmcyUUthV3ZCNmJ6NW90d2hqTVdYUlM2VjhvZlF6SWZEQi9DRGZzK0RvTHlCVzlCM24rOHpNcGxucHlUUEJFVi9IQTdVVHEvWXlSUzN3eEFDdzlHeG1QeTZjQ0p6NHBRPQ==*/
namespace Aws\DynamoDb;

/**
 * Special object to represent a DynamoDB set (SS/NS/BS) value.
 */
class SetValue implements \JsonSerializable, \Countable, \IteratorAggregate
{
    /** @var array Values in the set as provided. */
    private $values;

    /**
     * @param array  $values Values in the set.
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Get the values formatted for PHP and JSON.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->values);
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->values);
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
