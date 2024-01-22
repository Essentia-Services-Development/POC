<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L2dzZ3ZhdWx0bTNYYnp0SXEzNWY4MW0yR25yRjdqazlrWnJvVkxVY05pczBaMktWcDZWeUZmU2tLNjdZUUwvVXFHck43V2wzVklDVGhmN3JoeGVpYkxpYjdRZHc5blBobURQK0xlUFdzM0tuUFV6ZXEyeVVDeHY4Qkl2aU5yWWQx*/
namespace Aws\S3\RegionalEndpoint;

class Configuration implements ConfigurationInterface
{
    private $endpointsType;

    public function __construct($endpointsType)
    {
        $this->endpointsType = strtolower($endpointsType);
        if (!in_array($this->endpointsType, ['legacy', 'regional'])) {
            throw new \InvalidArgumentException(
                "Configuration parameter must either be 'legacy' or 'regional'."
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpointsType()
    {
        return $this->endpointsType;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'endpoints_type' => $this->getEndpointsType()
        ];
    }
}
