<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VHpFdmptMm1ydlRsTmx3ejRhNW5iSitxcnA5WjBEVGVJZ1p6Y3owbmpabG15a29NT2RUSVBweFN4ejZhUkxHY0dwYjd4WHhxNVF3Y2puVUcya3o1Mkp2TmVEdlF4NTB3LzVBdFFLZ0RKRmFNd3JnUkxSUGw3MnZpZkVpUkxobVhWTm04NXVjY3pIZWU0eGJHMWh0dTVt*/
namespace Aws\Exception;

use Aws\HasMonitoringEventsTrait;
use Aws\MonitoringEventsInterface;
use Aws\Multipart\UploadState;

class MultipartUploadException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;

    /** @var UploadState State of the erroneous transfer */
    private $state;

    /**
     * @param UploadState      $state Upload state at time of the exception.
     * @param \Exception|array $prev  Exception being thrown.
     */
    public function __construct(UploadState $state, $prev = null) {
        $msg = 'An exception occurred while performing a multipart upload';

        if (is_array($prev)) {
            $msg = strtr($msg, ['performing' => 'uploading parts to']);
            $msg .= ". The following parts had errors:\n";
            /** @var $error AwsException */
            foreach ($prev as $part => $error) {
                $msg .= "- Part {$part}: " . $error->getMessage(). "\n";
            }
        } elseif ($prev instanceof AwsException) {
            switch ($prev->getCommand()->getName()) {
                case 'CreateMultipartUpload':
                case 'InitiateMultipartUpload':
                    $action = 'initiating';
                    break;
                case 'CompleteMultipartUpload':
                    $action = 'completing';
                    break;
            }
            if (isset($action)) {
                $msg = strtr($msg, ['performing' => $action]);
            }
            $msg .= ": {$prev->getMessage()}";
        }

        if (!$prev instanceof \Exception) {
            $prev = null;
        }

        parent::__construct($msg, 0, $prev);
        $this->state = $state;
    }

    /**
     * Get the state of the transfer
     *
     * @return UploadState
     */
    public function getState()
    {
        return $this->state;
    }
}
