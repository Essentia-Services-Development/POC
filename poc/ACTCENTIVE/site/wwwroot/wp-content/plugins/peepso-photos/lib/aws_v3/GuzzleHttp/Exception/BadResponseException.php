<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UlAwOHBzQUtITGJPazlYVEhNMk5Kb0l4czlMZy9sKzJLbTN3UUdKWVhiNjhFWTluclZHdndxODhhamdpREtiS2RhSzVrTmZZWjliUG9kM1pYMjRoZmVKVnAzbndxMk5BNGJTZFFLN2ROR2k0L0l3OUtyYWN0Z2txQVY5Mk1SSHA4OVNqengwZytXZU10aUFLWWtlUno0*/
namespace GuzzleHttp\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Exception when an HTTP error occurs (4xx or 5xx error)
 */
class BadResponseException extends RequestException
{
    public function __construct(
        $message,
        RequestInterface $request,
        ResponseInterface $response = null,
        \Exception $previous = null,
        array $handlerContext = []
    ) {
        if (null === $response) {
            @trigger_error(
                'Instantiating the ' . __CLASS__ . ' class without a Response is deprecated since version 6.3 and will be removed in 7.0.',
                E_USER_DEPRECATED
            );
        }
        parent::__construct($message, $request, $response, $previous, $handlerContext);
    }
}
