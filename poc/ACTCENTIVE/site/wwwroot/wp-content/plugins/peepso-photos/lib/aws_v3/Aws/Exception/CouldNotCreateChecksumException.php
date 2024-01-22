<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VHcwaTdyZDZDK1RyT1hBdmcwMkM4YzNEUXM4cWFnTGVEL25yak5UbFNIcjViK1JIbUx6NHQwWFI0TDMydzdXaEFMTzJDWFlycGNGMEw5eElWSmcvUm90ZnhqaUx6RFd1VFowd002UEg2N1o2TVphL0VPRS9GQm5DdWhaUVo4cnBPbEtqMHFtSHRrcFFicEs5ZURVUFJsV3pIdUZ0bUUyZC9kcEFxZHlFL0FsUT09*/
namespace Aws\Exception;

use Aws\HasMonitoringEventsTrait;
use Aws\MonitoringEventsInterface;

class CouldNotCreateChecksumException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;

    public function __construct($algorithm, \Exception $previous = null)
    {
        $prefix = $algorithm === 'md5' ? "An" : "A";
        parent::__construct("{$prefix} {$algorithm} checksum could not be "
            . "calculated for the provided upload body, because it was not "
            . "seekable. To prevent this error you can either 1) include the "
            . "ContentMD5 or ContentSHA256 parameters with your request, 2) "
            . "use a seekable stream for the body, or 3) wrap the non-seekable "
            . "stream in a GuzzleHttp\\Psr7\\CachingStream object. You "
            . "should be careful though and remember that the CachingStream "
            . "utilizes PHP temp streams. This means that the stream will be "
            . "temporarily stored on the local disk.", 0, $previous);
    }
}
