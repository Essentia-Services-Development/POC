<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U3dYcFdKNExLa1MxZDZ5dkhNeWhXTjFYdldXN1ZZTHZETjcwa2lldTdKWmU3aFZoZnJJMzNjTXp4TitoeEduUUdzSTVlRnc3ZDYwUXdhc3hKR1FNOTd2d09TVjdqTHdQVUp4cXlXeThzOUkwWkorTnhUTmwxckQ4djdCOHYvSmVaK3ZZQ0pjYVVOMmsyUThYTXAwVW9a*/
namespace Aws\Api;

/**
 * Base class that is used by most API shapes
 */
abstract class AbstractModel implements \ArrayAccess
{
    /** @var array */
    protected $definition;

    /** @var ShapeMap */
    protected $shapeMap;

    /**
     * @param array    $definition Service description
     * @param ShapeMap $shapeMap   Shapemap used for creating shapes
     */
    public function __construct(array $definition, ShapeMap $shapeMap)
    {
        $this->definition = $definition;
        $this->shapeMap = $shapeMap;
    }

    public function toArray()
    {
        return $this->definition;
    }

    /**
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->definition[$offset])
            ? $this->definition[$offset] : null;
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->definition[$offset] = $value;
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->definition[$offset]);
    }

    /**
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->definition[$offset]);
    }

    protected function shapeAt($key)
    {
        if (!isset($this->definition[$key])) {
            throw new \InvalidArgumentException('Expected shape definition at '
                . $key);
        }

        return $this->shapeFor($this->definition[$key]);
    }

    protected function shapeFor(array $definition)
    {
        return isset($definition['shape'])
            ? $this->shapeMap->resolve($definition)
            : Shape::create($definition, $this->shapeMap);
    }
}
