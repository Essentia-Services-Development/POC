<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UjI5ZXFwajRFV2hKQmJSZ0NsOUVMSHZYQkR4eE8zdVAyK3lzeXRMQUlMMkNqMmIyd0xJOUl6RFAyTjNHRzNLMGdBUTh6Ry9xWnNpL2dYNkY2QkVwNEVvQ2xyMVVqTzJMRFVvM2thOXlCUTRGeEVVTzNLRjNLWmlvTDhGcWdkZlc0dlJpeGJJNjZ5ZlVxTmRsVGdrSStz*/
namespace Aws\Api\Serializer;

use Aws\Api\Service;
use Aws\CommandInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

/**
 * Prepares a JSON-RPC request for transfer.
 * @internal
 */
class JsonRpcSerializer
{
    /** @var JsonBody */
    private $jsonFormatter;

    /** @var string */
    private $endpoint;

    /** @var Service */
    private $api;

    /** @var string */
    private $contentType;

    /**
     * @param Service  $api           Service description
     * @param string   $endpoint      Endpoint to connect to
     * @param JsonBody $jsonFormatter Optional JSON formatter to use
     */
    public function __construct(
        Service $api,
        $endpoint,
        JsonBody $jsonFormatter = null
    ) {
        $this->endpoint = $endpoint;
        $this->api = $api;
        $this->jsonFormatter = $jsonFormatter ?: new JsonBody($this->api);
        $this->contentType = JsonBody::getContentType($api);
    }

    /**
     * When invoked with an AWS command, returns a serialization array
     * containing "method", "uri", "headers", and "body" key value pairs.
     *
     * @param CommandInterface $command
     *
     * @return RequestInterface
     */
    public function __invoke(CommandInterface $command)
    {
        $name = $command->getName();
        $operation = $this->api->getOperation($name);

        return new Request(
            $operation['http']['method'],
            $this->endpoint,
            [
                'X-Amz-Target' => $this->api->getMetadata('targetPrefix') . '.' . $name,
                'Content-Type' => $this->contentType
            ],
            $this->jsonFormatter->build(
                $operation->getInput(),
                $command->toArray()
            )
        );
    }
}
