<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UmJWQXBzek5OMndXUUxkM3QxY0txMkJkaW40dnN6WDdzdjBrOWF4MGE3Q2VubWxocWJQVGZldkNyWGxTRVByb2R3MWdneTFtblIxMWNaeDdhc3lKd1E2Tlk4ZHF4YmlrNmhvN2pUZ2xQSUYrKzc5UytwTzg4Q1p6N1RCY3dhRmV2QjJvSDkxNFpTYWZvdS9PMDcvbVBv*/
namespace Aws\Arn;

/**
 * This class represents an S3 Object bucket ARN, which is in the
 * following format:
 *
 * @internal
 */
class ObjectLambdaAccessPointArn extends AccessPointArn
{
    /**
     * Parses a string into an associative array of components that represent
     * a ObjectLambdaAccessPointArn
     *
     * @param $string
     * @return array
     */
    public static function parse($string)
    {
        $data = parent::parse($string);
        return parent::parseResourceTypeAndId($data);
    }

    /**
     *
     * @param array $data
     */
    protected static function validate(array $data)
    {
        parent::validate($data);
        self::validateRegion($data, 'S3 Object Lambda ARN');
        self::validateAccountId($data, 'S3 Object Lambda ARN');
    }
}
