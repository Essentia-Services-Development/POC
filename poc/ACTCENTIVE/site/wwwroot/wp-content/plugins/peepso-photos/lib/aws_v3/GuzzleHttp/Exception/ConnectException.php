<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UlpxV01yZTFFc1lQazQzaHpyOEhoeTVRaGl3bWtUVUo2c05zT3FRbUVKRUg0MWphSG5zZG1JRFFFVzZaUU5KRDJJalVyT0FKTlhEQnlkdTZVNXY2OEQwMVZScFdNbzBnVXlMYmpjODAvdHFPZWlWUFZ1S2x0OE94THpiV3VaaUZFbks5MTVCd05XM2hwNDZjMHhpMzRI*/
namespace GuzzleHttp\Exception;

use Psr\Http\Message\RequestInterface;

/**
 * Exception thrown when a connection cannot be established.
 *
 * Note that no response is present for a ConnectException
 */
class ConnectException extends RequestException
{
    public function __construct(
        $message,
        RequestInterface $request,
        \Exception $previous = null,
        array $handlerContext = []
    ) {
        parent::__construct($message, $request, null, $previous, $handlerContext);
    }

    /**
     * @return null
     */
    public function getResponse()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return false;
    }
}
