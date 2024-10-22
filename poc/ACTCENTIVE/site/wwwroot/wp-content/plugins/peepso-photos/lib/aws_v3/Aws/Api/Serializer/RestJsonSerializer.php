<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VHRTYjlCL0xiV0pVd0JjRDNmRWViSDd4OGtUVlZYMGFDbGtyTXA0TVBzZXU2S05odGZsYmROZDdpR1NTUXFWR2o4c2xDKzFEbjI4UnN5dFNUNlVveThUZWJTWGFIRkg5Q1lyNGc4cnFTQTRtbVM3d05NMkJRZ1hZVkJ4cEdTUWEwUE1GRTdkVDlDcklYUGlDQTJXWHlP*/
namespace Aws\Api\Serializer;

use Aws\Api\Service;
use Aws\Api\StructureShape;

/**
 * Serializes requests for the REST-JSON protocol.
 * @internal
 */
class RestJsonSerializer extends RestSerializer
{
    /** @var JsonBody */
    private $jsonFormatter;

    /** @var string */
    private $contentType;

    /**
     * @param Service  $api           Service API description
     * @param string   $endpoint      Endpoint to connect to
     * @param JsonBody $jsonFormatter Optional JSON formatter to use
     */
    public function __construct(
        Service $api,
        $endpoint,
        JsonBody $jsonFormatter = null
    ) {
        parent::__construct($api, $endpoint);
        $this->contentType = 'application/json';
        $this->jsonFormatter = $jsonFormatter ?: new JsonBody($api);
    }

    protected function payload(StructureShape $member, array $value, array &$opts)
    {
        $body = isset($value) ?
            ((string) $this->jsonFormatter->build($member, $value))
            : "{}";
        $opts['headers']['Content-Type'] = $this->contentType;
        $opts['body'] = $body;
    }
}
