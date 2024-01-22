<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UTJpNXBmRTl5czU0d0oycWJEVFNFSDZEN3phNW1zMHRNYndIQ1BnZFgyQXl4VUsybmFWSTBQZFpHRVBDVW5jODlidEdTQnd3R1k3N2dSaGpSREc5cjhEWi9DOUkwcUNmdGxZL1hoT09WT09mU2lVS05zbk81ODlGVHY0Y0d3N2YwcmJBc2NtQ0poOGdlSEJRUXFSYUw5*/
namespace Aws\Exception;

/**
 * Represents an exception that was supplied via an EventStream.
 */
class EventStreamDataException extends \RuntimeException
{
    private $errorCode;
    private $errorMessage;

    public function __construct($code, $message)
    {
        $this->errorCode = $code;
        $this->errorMessage = $message;
        parent::__construct($message);
    }

    /**
     * Get the AWS error code.
     *
     * @return string|null Returns null if no response was received
     */
    public function getAwsErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get the concise error message if any.
     *
     * @return string|null
     */
    public function getAwsErrorMessage()
    {
        return $this->errorMessage;
    }
}
