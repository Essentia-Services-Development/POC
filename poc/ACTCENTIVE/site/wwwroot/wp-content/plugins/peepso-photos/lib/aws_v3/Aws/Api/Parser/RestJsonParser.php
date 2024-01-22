<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VEdLNFdSK0hHUkFuK1c4azFIeFdXbTRHb1NJSHNpQ3ZvaGFEa3lrODJVcFdSOG9jdElKV21YTnoxTUFkSDIzQ1lFdmFwTHJzbm1WRDJzaXYxV0NyeGZ1NmhIMVJncGkzNEZ4eDVxOHRlcW00L3FLYUhma1B3M0U2bWRlOXhubGdDajFKNldPQ2o5M1luVjlBS1VPdjlQ*/
namespace Aws\Api\Parser;

use Aws\Api\Service;
use Aws\Api\StructureShape;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal Implements REST-JSON parsing (e.g., Glacier, Elastic Transcoder)
 */
class RestJsonParser extends AbstractRestParser
{
    use PayloadParserTrait;

    /**
     * @param Service    $api    Service description
     * @param JsonParser $parser JSON body builder
     */
    public function __construct(Service $api, JsonParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new JsonParser();
    }

    protected function payload(
        ResponseInterface $response,
        StructureShape $member,
        array &$result
    ) {
        $jsonBody = $this->parseJson($response->getBody(), $response);

        if ($jsonBody) {
            $result += $this->parser->parse($member, $jsonBody);
        }
    }

    public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    ) {
        $jsonBody = $this->parseJson($stream, $response);
        if ($jsonBody) {
            return $this->parser->parse($member, $jsonBody);
        }
        return [];
    }
}
