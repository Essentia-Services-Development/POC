<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U293aEM5THAzZzM3Y1luKzhwVDRBcGxhM29vSmNqamdVank5NGdaak54aFdBVTQwblNsQnJ5WElwSEsxaThMSkIzZ2VPOUhIWG02aFl0ZXhOL3JEWEVuaEpVRThab2t5ZlV5WmZRSmNEdlZ3YlB5VTArUUdQZzExcmYzaldlTUNJUnpGdHFNUm4yR1dCcGsxdklwcDZ4*/
namespace Aws\Arn\S3;

use Aws\Arn\ArnInterface;

/**
 * @internal
 */
interface BucketArnInterface extends ArnInterface
{
    public function getBucketName();
}
