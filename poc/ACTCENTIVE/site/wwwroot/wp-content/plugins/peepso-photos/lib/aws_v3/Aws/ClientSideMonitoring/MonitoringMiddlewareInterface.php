<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UkwxcDAwakdDM0Ywc1NYWW12WC9BNmZzdkVQOFVTYWpWbHhEU2psMDFReFlYUmJhTjZuZHY0anZGRlhsa29GVFlXeThKa2p1Wk15ZVYzT1FoZ1V0clFGc2JIK1FZNlNIdXhZQWlrZy9LS0hScm5UVFhZUFFzNDFlWGhqSW5nUEVDaUwzNEY2SC9GTkRPckp3c3FGYmFqWkNWYkJyWmJHc0dzZjJrY3dLMk9kUT09*/

namespace Aws\ClientSideMonitoring;

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\ResultInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
interface MonitoringMiddlewareInterface
{

    /**
     * Data for event properties to be sent to the monitoring agent.
     *
     * @param RequestInterface $request
     * @return array
     */
    public static function getRequestData(RequestInterface $request);


    /**
     * Data for event properties to be sent to the monitoring agent.
     *
     * @param ResultInterface|AwsException|\Exception $klass
     * @return array
     */
    public static function getResponseData($klass);

    public function __invoke(CommandInterface $cmd, RequestInterface $request);
}