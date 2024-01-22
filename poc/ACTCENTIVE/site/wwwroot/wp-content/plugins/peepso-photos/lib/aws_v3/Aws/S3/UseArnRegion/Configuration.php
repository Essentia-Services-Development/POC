<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L2dzZ3ZhdWx0bTNYYnp0SXEzNWY4MW0yR25yRjdqazlrWnJvVkxVY05pczBaMktWcDZWeUZmU2tLNjdZUUwvVXFHck43V2wzVklDVGhmN3JoeGVpYkxpYjdRZHc5blBobURQK0xlUFdzM0tuUFV6ZXEyeVVDeHY4Qkl2aU5yWWQx*/
namespace Aws\S3\UseArnRegion;

use Aws;
use Aws\S3\UseArnRegion\Exception\ConfigurationException;

class Configuration implements ConfigurationInterface
{
    private $useArnRegion;

    public function __construct($useArnRegion)
    {
        $this->useArnRegion = Aws\boolean_value($useArnRegion);
        if (is_null($this->useArnRegion)) {
            throw new ConfigurationException("'use_arn_region' config option"
                . " must be a boolean value.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isUseArnRegion()
    {
        return $this->useArnRegion;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'use_arn_region' => $this->isUseArnRegion(),
        ];
    }
}
