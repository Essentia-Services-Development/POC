<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UWlZRlNPd0hZSDlQaWxjcCtDK08rNG9qTE9hQkQxR29ERm8vMGxucjdEVkhFdXFiR2VsRlhsWjFtU0hHU2xzUDhGQ1oyY0lBUlJjMlQ0SW96RWJTSG1oT21obmR2WDlFUkVLZHlRTmxpVkh2b3B6eWVZNncyZFA1TkxxUkNvRzZWaVY1TzNyaXVwZXNPL0VqRlhqc1J4*/
namespace Aws;

/**
 * Represents an AWS result object that is returned from executing an operation.
 */
interface ResultInterface extends \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Provides debug information about the result object
     *
     * @return string
     */
    public function __toString();

    /**
     * Convert the result to an array.
     *
     * @return array
     */
    public function toArray();

    /**
     * Check if the model contains a key by name
     *
     * @param string $name Name of the key to retrieve
     *
     * @return bool
     */
    public function hasKey($name);

    /**
     * Get a specific key value from the result model.
     *
     * @param string $key Key to retrieve.
     *
     * @return mixed|null Value of the key or NULL if not found.
     */
    public function get($key);

    /**
     * Returns the result of executing a JMESPath expression on the contents
     * of the Result model.
     *
     *     $result = $client->execute($command);
     *     $jpResult = $result->search('foo.*.bar[?baz > `10`]');
     *
     * @param string $expression JMESPath expression to execute
     *
     * @return mixed Returns the result of the JMESPath expression.
     * @link http://jmespath.readthedocs.org/en/latest/ JMESPath documentation
     */
    public function search($expression);
};
