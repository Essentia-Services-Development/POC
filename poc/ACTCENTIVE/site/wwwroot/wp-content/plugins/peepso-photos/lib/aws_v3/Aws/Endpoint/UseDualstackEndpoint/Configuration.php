<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L2dzZ3ZhdWx0bTNYYnp0SXEzNWY4MW0yR25yRjdqazlrWnJvVkxVY05pczBaMktWcDZWeUZmU2tLNjdZUUwvVXFHck43V2wzVklDVGhmN3JoeGVpYkxpYjdRZHc5blBobURQK0xlUFdzM0tuUFV6ZXEyeVVDeHY4Qkl2aU5yWWQx*/
namespace Aws\Endpoint\UseDualstackEndpoint;

use Aws;
use Aws\Endpoint\UseDualstackEndpoint\Exception\ConfigurationException;

class Configuration implements ConfigurationInterface
{
    private $useDualstackEndpoint;

    public function __construct($useDualstackEndpoint, $region)
    {
        $this->useDualstackEndpoint = Aws\boolean_value($useDualstackEndpoint);
        if (is_null($this->useDualstackEndpoint)) {
            throw new ConfigurationException("'use_dual_stack_endpoint' config option"
                . " must be a boolean value.");
        }
        if ($this->useDualstackEndpoint == true
            && (strpos($region, "iso-") !== false || strpos($region, "-iso") !== false)
        ) {
            throw new ConfigurationException("Dual-stack is not supported in ISO regions");        }
    }

    /**
     * {@inheritdoc}
     */
    public function isUseDualstackEndpoint()
    {
        return $this->useDualstackEndpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'use_dual_stack_endpoint' => $this->isUseDualstackEndpoint(),
        ];
    }
}
