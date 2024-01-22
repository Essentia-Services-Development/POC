<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UmhJMTlhRHp1Z2h4biswdXlMaUIrdXdBTUIwV3RoYzFpeHBGU1czNWZYZTJVZjF6a2NwaVpSekdvRi9SeTRrVXYxQ1ZvSGwvY1B1aXJBdk90TllaVS84VUhlNk1wMHYzaUlaakVSQ2IxZGFCRW5uOWo5dmhhN3ZwL2ZLaktqdjAxWUg3MFJFNzFYZGZQWlJCaERxWENk*/
namespace Aws\S3\Crypto;

use Aws\Crypto\MaterialsProviderInterfaceV2;

trait CryptoParamsTraitV2
{
    use CryptoParamsTrait;

    protected function getMaterialsProvider(array $args)
    {
        if ($args['@MaterialsProvider'] instanceof MaterialsProviderInterfaceV2) {
            return $args['@MaterialsProvider'];
        }

        throw new \InvalidArgumentException('An instance of MaterialsProviderInterfaceV2'
            . ' must be passed in the "MaterialsProvider" field.');
    }
}
