<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UytaTEpycDNlUEx0ZUJTQlVmQjJNeVFwZFFSZmx5N1RwajA0NTNuTzBrby9WVXVaSDJwdWU5dTRsNHd2RDQzWjJSU0dqdzJWL0Q4dHJPNnAxZWRzZ05kc1RTeFlyYXZGb2E2Sm8vVGpITDFHa3hHZlAyM1FSdGl2aGZjREg2UzViR3NrWGxFSEl3R0EwZG1NRS80ZHph*/
namespace Aws\S3;

use Aws\Api\Parser\AbstractParser;
use Aws\Api\StructureShape;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal Decorates a parser for the S3 service to correctly handle the
 *           GetBucketLocation operation.
 */
class GetBucketLocationParser extends AbstractParser
{
    /**
     * @param callable $parser Parser to wrap.
     */
    public function __construct(callable $parser)
    {
        $this->parser = $parser;
    }

    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        $fn = $this->parser;
        $result = $fn($command, $response);

        if ($command->getName() === 'GetBucketLocation') {
            $location = 'us-east-1';
            if (preg_match('/>(.+?)<\/LocationConstraint>/', $response->getBody(), $matches)) {
                $location = $matches[1] === 'EU' ? 'eu-west-1' : $matches[1];
            }
            $result['LocationConstraint'] = $location;
        }

        return $result;
    }

    public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    ) {
        return $this->parser->parseMemberFromStream($stream, $member, $response);
    }
}
