<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U3VVeTR0TnRUeUtTVzVoYnd1U1FMU1lIK3dHa28xRmltUXQ2WlUrenJSL25KWmJpUVgrK1F6b3JnQm0rcWlSU29nME5QOHVvT09oSU5NYkZZU1NyZ2dtSlRwRjYyVGl2SUsyMWRERXlReFlBdWJ4YWNJUVRld0JhQWpscWlnTkpZREFIVVcrUm5Ma3MvbENjTjBmMnUz*/
namespace Aws\Api\Parser;

use Aws\Api\StructureShape;
use Aws\Api\Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal Implements REST-XML parsing (e.g., S3, CloudFront, etc...)
 */
class RestXmlParser extends AbstractRestParser
{
    use PayloadParserTrait;

    /**
     * @param Service   $api    Service description
     * @param XmlParser $parser XML body parser
     */
    public function __construct(Service $api, XmlParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new XmlParser();
    }

    protected function payload(
        ResponseInterface $response,
        StructureShape $member,
        array &$result
    ) {
        $result += $this->parseMemberFromStream($response->getBody(), $member, $response);
    }

    public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    ) {
        $xml = $this->parseXml($stream, $response);
        return $this->parser->parse($member, $xml);
    }
}
