<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2JObk1UU2UxYUNBdWFQR0VxMWV6T3ZYdVlPU1lUR1lORVVkNTdRQ3VSSk0zbUZEWGdlRzgycEgyWXZDbzVHZzFSdjBMNFJYQTlweStIVkVlRlB1djdUSmMzMzgyQkpTUmc2ZzVzZlprcUhIWGNFTGovbmU5eEJPRXpwQWJ4TnY1TVZCajF6WlJ3QkFucWMwcXQ3M2ZKS1J0QktJZWIzRDdDYVNNaUZQY1ZUZz09*/
namespace Aws\S3;

use Aws\Api\Parser\AbstractParser;
use Aws\Api\StructureShape;
use Aws\Api\Parser\Exception\ParserException;
use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Converts malformed responses to a retryable error type.
 *
 * @internal
 */
class RetryableMalformedResponseParser extends AbstractParser
{
    /** @var string */
    private $exceptionClass;

    public function __construct(
        callable $parser,
        $exceptionClass = AwsException::class
    ) {
        $this->parser = $parser;
        $this->exceptionClass = $exceptionClass;
    }

    public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    ) {
        $fn = $this->parser;

        try {
            return $fn($command, $response);
        } catch (ParserException $e) {
            throw new $this->exceptionClass(
                "Error parsing response for {$command->getName()}:"
                    . " AWS parsing error: {$e->getMessage()}",
                $command,
                ['connection_error' => true, 'exception' => $e],
                $e
            );
        }
    }

    public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    ) {
        return $this->parser->parseMemberFromStream($stream, $member, $response);
    }
}
