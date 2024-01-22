<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VDk5MmN4WDM0Q3UwQTFuMk1Ic2p0MStpZDQ0azd4Qzh0ZSs2OW54SFFjUlVmZnBPaGVJZHhXK0doMlhYdnQ0T29VK2ZwZlBJbVpldlhFZy95WjZZYS93akdEYUJvWG02RG9VSk01R3VleUdkN2xqVkgxcUdDdlZlU2FvVUptWVlXWUhwYnpEbjJyUjQycWlIdERVVHN3*/
namespace Aws\S3\Crypto;

use Aws\AwsClientInterface;
use Aws\Middleware;
use Psr\Http\Message\RequestInterface;

trait UserAgentTrait
{
    private function appendUserAgent(AwsClientInterface $client, $agentString)
    {
        $list = $client->getHandlerList();
        $list->appendBuild(Middleware::mapRequest(
            function(RequestInterface $req) use ($agentString) {
                if (!empty($req->getHeader('User-Agent'))
                    && !empty($req->getHeader('User-Agent')[0])
                ) {
                    $userAgent = $req->getHeader('User-Agent')[0];
                    if (strpos($userAgent, $agentString) === false) {
                        $userAgent .= " {$agentString}";
                    };
                } else {
                    $userAgent = $agentString;
                }

                $req =  $req->withHeader('User-Agent', $userAgent);
                return $req;
            }
        ));
    }
}
