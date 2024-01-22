<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UTZXTE9ycEFpRys5Zmh6OEdaY1E5QW9IYzB6THlQSWVvUG9TYVh1MnVFTkQ1OS9WS3lBUEJrRGJPbmN1UUtkZHd4c0V5cXYwelZGL3FxUnRuWDgxNG1Ra1dZZGRYODRXMk5PelVTVWd4VW5yc3NSYUUvNnpiU0UvVU4zcFNna3MrVTFLM0hJbTk0MmpvNG5Pc3hWUkkr*/
namespace Aws\Arn;

/**
 * Amazon Resource Names (ARNs) uniquely identify AWS resources. Classes
 * implementing ArnInterface parse and store an ARN object representation.
 *
 * Valid ARN formats include:
 *
 *   arn:partition:service:region:account-id:resource-id
 *   arn:partition:service:region:account-id:resource-type/resource-id
 *   arn:partition:service:region:account-id:resource-type:resource-id
 *
 * Some components may be omitted, depending on the service and resource type.
 *
 * @internal
 */
interface ArnInterface
{
    public static function parse($string);

    public function __toString();

    public function getPrefix();

    public function getPartition();

    public function getService();

    public function getRegion();

    public function getAccountId();

    public function getResource();

    public function toArray();
}