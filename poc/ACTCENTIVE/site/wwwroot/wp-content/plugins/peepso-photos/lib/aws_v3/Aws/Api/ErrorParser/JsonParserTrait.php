<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UWNOTEZ2SDUwRDVERTBQNWs4bEpTa0FDTXloaVUva1AyN01UbW5aMDMwcHNXaTI2SExsd291UDFBclM4bmd5TXVSZFFSc3BvS2lBVlJqU0E1OGgvVUgxWFFwSWFEUjIyL1BRYktpdGQvcGI0Y3lyUFMrNmhmdzFpd0hxTzljZFRWQXhPeG1vOXY0QmVITWlWd0daVG52*/
namespace Aws\Api\ErrorParser;

use Aws\Api\Parser\PayloadParserTrait;
use Aws\Api\StructureShape;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides basic JSON error parsing functionality.
 */
trait JsonParserTrait
{
    use PayloadParserTrait;

    private function genericHandler(ResponseInterface $response)
    {
        $code = (string) $response->getStatusCode();

        return [
            'request_id'  => (string) $response->getHeaderLine('x-amzn-requestid'),
            'code'        => null,
            'message'     => null,
            'type'        => $code[0] == '4' ? 'client' : 'server',
            'parsed'      => $this->parseJson($response->getBody(), $response)
        ];
    }

    protected function payload(
        ResponseInterface $response,
        StructureShape $member
    ) {
        $jsonBody = $this->parseJson($response->getBody(), $response);

        if ($jsonBody) {
            return $this->parser->parse($member, $jsonBody);
        }
    }
}
