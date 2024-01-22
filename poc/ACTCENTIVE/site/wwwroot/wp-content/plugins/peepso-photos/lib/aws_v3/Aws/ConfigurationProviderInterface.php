<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L1M3ck9hZ0tMSUtBRjZMdmxsR3RYYWJrRTJTQlJLOHBlTDJNVUV2RGRTSWtlQzRKbDM4YTJKMFNRR2x4WEN5UUpnbmtLbVgrK0xvQ3FNdGg1eTdtKzhad0pLWTN6VHVWenVoWDZXeDdkQTlOQXJyVm1IdkRyY0Vud0VWbkM4cHZRK3ovZTkwNmxYQ3c1S2FZYTJDNVZLZz09*/
namespace Aws;

interface ConfigurationProviderInterface
{
    /**
     * Create a default config provider
     *
     * @param array $config
     * @return callable
     */
    public static function defaultProvider(array $config = []);
}
