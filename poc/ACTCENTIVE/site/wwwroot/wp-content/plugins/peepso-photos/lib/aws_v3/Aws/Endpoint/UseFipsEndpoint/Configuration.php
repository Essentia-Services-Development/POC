<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L2dzZ3ZhdWx0bTNYYnp0SXEzNWY4MW0yR25yRjdqazlrWnJvVkxVY05pczBaMktWcDZWeUZmU2tLNjdZUUwvVXFHck43V2wzVklDVGhmN3JoeGVpYkxpYjdRZHc5blBobURQK0xlUFdzM0tuUFV6ZXEyeVVDeHY4Qkl2aU5yWWQx*/
namespace Aws\Endpoint\UseFipsEndpoint;

use Aws;
use Aws\ClientResolver;
use Aws\Endpoint\UseFipsEndpoint\Exception\ConfigurationException;

class Configuration implements ConfigurationInterface
{
    private $useFipsEndpoint;

    public function __construct($useFipsEndpoint)
    {
        $this->useFipsEndpoint = Aws\boolean_value($useFipsEndpoint);
        if (is_null($this->useFipsEndpoint)) {
            throw new ConfigurationException("'use_fips_endpoint' config option"
                . " must be a boolean value.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isUseFipsEndpoint()
    {
        return $this->useFipsEndpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'use_fips_endpoint' => $this->isUseFipsEndpoint(),
        ];
    }
}
