<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UitMTjZ1aGw5WC9ocmlVY1JjaWY5K0FJWWdhZ25wUUZrT3FuMUVwMWNtQUl6SjkyUVU5U0UvbDJETVo3c3JtQU53Ukd6ZVpwWW9PaEIwSDcxaGxkK0xWL2tpVHM2Wnh2ZVZma0MyQTF6aXJtNC8wd1RIL3lUcW5KMVVjR3kyQnlPU1RONVBiVVoyTmZPMU1OWFdEM25W*/
namespace Aws;

/**
 * Trait implementing ToArrayInterface, \ArrayAccess, \Countable, and
 * \IteratorAggregate
 */
trait HasDataTrait
{
    /** @var array */
    private $data = [];

    /**
     * @return \Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * This method returns a reference to the variable to allow for indirect
     * array modification (e.g., $foo['bar']['baz'] = 'qux').
     *
     * @param $offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function & offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        $value = null;
        return $value;
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->data);
    }
}
