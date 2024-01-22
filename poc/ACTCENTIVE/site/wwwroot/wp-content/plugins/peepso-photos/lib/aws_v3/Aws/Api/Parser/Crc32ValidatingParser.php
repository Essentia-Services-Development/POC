<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UkpzMllXazNPM0lScE1qYUxXNmp3eCt6QmJQRlRlUFVJTzNkcXhaeFFROE9FYjhiTFhkdCtkTk4xczJLS3BaS1FHSjc2QWtCTUY5V0luVmtETVl3S2V1eUNsVk5sTGduU2RWV1lBM202bjJDazFaTklIdG5hK1dmcWJ4RkxoV1ZqK2N1VDFzTE5md3NQYVg3VEJlb05H*/
namespace Aws\Api\Parser;

use Aws\Api\StructureShape;
use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7;

/**
 * @internal Decorates a parser and validates the x-amz-crc32 header.
 */
class Crc32ValidatingParser extends AbstractParser
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
        if ($expected = $response->getHeaderLine('x-amz-crc32')) {
            $hash = hexdec(Psr7\Utils::hash($response->getBody(), 'crc32b'));
            if ($expected != $hash) {
                throw new AwsException(
                    "crc32 mismatch. Expected {$expected}, found {$hash}.",
                    $command,
                    [
                        'code'             => 'ClientChecksumMismatch',
                        'connection_error' => true,
                        'response'         => $response
                    ]
                );
            }
        }

        $fn = $this->parser;
        return $fn($command, $response);
    }

    public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    ) {
        return $this->parser->parseMemberFromStream($stream, $member, $response);
    }
}
