<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UWtTc0hmSktEOFBpRlFodFpJNkcvVm9oWlFBZDVaUzd4Rkl6RUltTHdXNnRROGRvakVXcmh2WENoWUw5TDNHL21CcXRxdXdPNmRTNWdwdUY4MDF5MlRMSTJKRy85TFZzYzY4cDZXNHhQV2pIaXlBQjRSeERGcWs2Q2NuenpyZ2V1c1RDSUU3dlREendRcXpUcmZTUnh0*/
namespace Aws\Api\ErrorParser;

use Aws\Api\Parser\JsonParser;
use Aws\Api\Service;
use Aws\Api\StructureShape;
use Aws\CommandInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Parses JSON-REST errors.
 */
class RestJsonErrorParser extends AbstractErrorParser
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

        // Merge in error data from the JSON body
        if ($json = $data['parsed']) {
            $data = array_replace($data, $json);
        }

        // Correct error type from services like Amazon Glacier
        if (!empty($data['type'])) {
            $data['type'] = strtolower($data['type']);
        }

        // Retrieve the error code from services like Amazon Elastic Transcoder
        if ($code = $response->getHeaderLine('x-amzn-errortype')) {
            $colon = strpos($code, ':');
            $data['code'] = $colon ? substr($code, 0, $colon) : $code;
        }

        // Retrieve error message directly
        $data['message'] = isset($data['parsed']['message'])
            ? $data['parsed']['message']
            : (isset($data['parsed']['Message'])
                ? $data['parsed']['Message']
                : null);

        $this->populateShape($data, $response, $command);

        return $data;
    }
}
