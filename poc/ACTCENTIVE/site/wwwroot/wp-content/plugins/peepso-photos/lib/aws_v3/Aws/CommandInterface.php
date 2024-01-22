<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UjBkQVphUVZ6ZjhtTzBUdXhFQllpajdvVzFML0dWVDBKS1JWM1NpVzNPYlZBaWJJdkpOdjVwMitXSjBjYjRKWG8yNFdSR21NQnBTUk5ORWZPTk16Vm5tdzFNV1ZJREpoTENvbm9JWVJBTHMxYjRSVDhtNTVkNU5vSUlqcW9WQmpGaWxLRUxJWDEzdnlZWjVmZ29MaFIr*/
namespace Aws;

/**
 * A command object encapsulates the input parameters used to control the
 * creation of a HTTP request and processing of a HTTP response.
 *
 * Using the toArray() method will return the input parameters of the command
 * as an associative array.
 */
interface CommandInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Converts the command parameters to an array
     *
     * @return array
     */
    public function toArray();

    /**
     * Get the name of the command
     *
     * @return string
     */
    public function getName();

    /**
     * Check if the command has a parameter by name.
     *
     * @param string $name Name of the parameter to check
     *
     * @return bool
     */
    public function hasParam($name);

    /**
     * Get the handler list used to transfer the command.
     *
     * @return HandlerList
     */
    public function getHandlerList();
}
