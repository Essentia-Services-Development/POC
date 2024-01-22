<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2lWbnJTMU5CSy9jWGpmb1FBazZkVEw0VGxiWWJDMWIvNEtnWmp6bFpraVFqdGZybWMrQ0NKRkM3QkNMbGoyckRqZnJXQWNvSHA5WkFxNyt1bTBnT3dVRzVxWXBLSHAwSThLaHdBcmxJV3YvWGkxVU5sT3dmcWpOQVc5eHk2Yjc3cDRwZHJRa3NqdnVYUzNSbWpZY1g0*/

namespace GuzzleHttp\Promise;

/**
 * Exception thrown when too many errors occur in the some() or any() methods.
 */
class AggregateException extends RejectionException
{
    public function __construct($msg, array $reasons)
    {
        parent::__construct(
            $reasons,
            sprintf('%s; %d rejected promises', $msg, count($reasons))
        );
    }
}
