<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U3lVM1E3ZmFlNjEzYWFVaEpQak1nR0lDVUhOS3diVXB3cmpOTHlmWGJIUVlkSkpPbU8xdE40UTM0QnprcjJJQytCNHovUUthT1J0RUpvUlpPTkt1em4yOUkwUmV0MzBFa3FRdHloai9WNWphdzRQeWNpUkhjcWF4NVBCUnRGOXcrbHFERVNKNGYxbUlhOEhuODdDWm9n*/
namespace Aws\Api\Parser;

use Aws\Api\StructureShape;
use Aws\Api\Service;
use Aws\Result;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal Implements JSON-RPC parsing (e.g., DynamoDB)
 */
class JsonRpcParser extends AbstractParser
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

    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        $operation = $this->api->getOperation($command->getName());
        $result = null === $operation['output']
            ? null
            : $this->parseMemberFromStream(
                $response->getBody(),
                $operation->getOutput(),
                $response
            );

        return new Result($result ?: []);
    }

    public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    ) {
        return $this->parser->parse($member, $this->parseJson($stream, $response));
    }
}
