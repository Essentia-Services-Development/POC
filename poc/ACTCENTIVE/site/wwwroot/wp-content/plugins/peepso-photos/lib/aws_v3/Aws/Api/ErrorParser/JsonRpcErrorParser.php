<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UVFjRUNPSWhsclYwVkJpODdLalhMck5uUUFlY3dyYkUzamlSdTlyQVpkMHEra2wvVytvbGNCMFJDaTkrOTNZTm40SlpJa1Z0K0VrNjdEK1dTYS9ubFlVTDRwNzVud2VFUzVDNzJKVEh1azM5TEVvaWI5TEtvSVQxVnNseXJxaFpGalA1Ni9HUlREd1k4YUpYanJBa2FC*/
namespace Aws\Api\ErrorParser;

use Aws\Api\Parser\JsonParser;
use Aws\Api\Service;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Parsers JSON-RPC errors.
 */
class JsonRpcErrorParser extends AbstractErrorParser
{
    use JsonParserTrait;

    private $parser;

    public function __construct(Service $api = null, JsonParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new JsonParser();
    }

    public function __invoke(
        ResponseInterface $response,
        CommandInterface $command = null
    ) {
        $data = $this->genericHandler($response);

        // Make the casing consistent across services.
        if ($data['parsed']) {
            $data['parsed'] = array_change_key_case($data['parsed']);
        }

        if (isset($data['parsed']['__type'])) {
            $parts = explode('#', $data['parsed']['__type']);
            $data['code'] = isset($parts[1]) ? $parts[1] : $parts[0];
            $data['message'] = isset($data['parsed']['message'])
                ? $data['parsed']['message']
                : null;
        }

        $this->populateShape($data, $response, $command);

        return $data;
    }
}
