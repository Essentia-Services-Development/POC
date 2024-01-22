<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3Uk5ESXJRN0xaTlNvQnd1bXVVS2d4RDEvaUlNVUtBNEJJUEF1WkFFUWlESVZpMmpPYytqd2NlSVphN3dQU1kzRWhFNjJoVU1LMGZveHVlaVJ6eUdWK3BVaklnbUJkaElOYWlLTUYrYlp1S3M0OWRBMnNmZXNza2ZFZDJweUtQaHdFPQ==*/
namespace Aws;

/**
 * AWS command object.
 */
class Command implements CommandInterface
{
    use HasDataTrait;

    /** @var string */
    private $name;

    /** @var HandlerList */
    private $handlerList;

    /**
     * Accepts an associative array of command options, including:
     *
     * - @http: (array) Associative array of transfer options.
     *
     * @param string      $name           Name of the command
     * @param array       $args           Arguments to pass to the command
     * @param HandlerList $list           Handler list
     */
    public function __construct($name, array $args = [], HandlerList $list = null)
    {
        $this->name = $name;
        $this->data = $args;
        $this->handlerList = $list ?: new HandlerList();

        if (!isset($this->data['@http'])) {
            $this->data['@http'] = [];
        }
        if (!isset($this->data['@context'])) {
            $this->data['@context'] = [];
        }
    }

    public function __clone()
    {
        $this->handlerList = clone $this->handlerList;
    }

    public function getName()
    {
        return $this->name;
    }

    public function hasParam($name)
    {
        return array_key_exists($name, $this->data);
    }

    public function getHandlerList()
    {
        return $this->handlerList;
    }

    /** @deprecated */
    public function get($name)
    {
        return $this[$name];
    }
}
