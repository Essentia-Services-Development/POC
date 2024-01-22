<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UzNEWDBaUnJ3ODFCdlozNUlaWFJjbUFaMFpUaW9UVXJQZi9hVVpOVytxWjZjOEx5cWVHOFJ4QVVlNU9lS0xqU1ZZSGhRaGNwK2p3SFlnMHd4aVExUHc4VFh5M1VabkRXS0VwMDdXL1YyVjhKanZGcjRObUVXZXRFK3JXSjQ4WlllV2xzTmdIb2M5M2lLSXlYUmxJanZw*/
namespace Aws\Arn\S3;

use Aws\Arn\Arn;
use Aws\Arn\ResourceTypeAndIdTrait;

/**
 * This class represents an S3 multi-region bucket ARN, which is in the
 * following format:
 *
 * @internal
 */
class MultiRegionAccessPointArn extends AccessPointArn
{
    use ResourceTypeAndIdTrait;

    /**
     * Parses a string into an associative array of components that represent
     * a MultiRegionArn
     *
     * @param $string
     * @return array
     */
    public static function parse($string)
    {
        return parent::parse($string);
    }

    /**
     *
     * @param array $data
     */
    public static function validate(array $data)
    {
        Arn::validate($data);
    }

}
