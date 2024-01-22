<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U0huMklza3Q1T3FpVnlEQ2xlc3FZRVhvZnVBQTl3QXdjYVB5K2JGMm9QQ0dzdEp5Y3BCKzJoMVNZTWhudEptZFlxZ1ZSNlEyeG9YUmZOR20xTWh3SmtWOFByTGxTT0NPNGVzNUNDVzNlS293UmFkWUx6aUd6RDB6M0thN2ZTZ1MvUnNCRE1aMmIxT3BRRWRCbEU1K0o1*/
namespace Aws\Endpoint;

/**
 * Represents a section of the AWS cloud.
 */
interface PartitionInterface
{
    /**
     * Returns the partition's short name, e.g., 'aws,' 'aws-cn,' or
     * 'aws-us-gov.'
     *
     * @return string
     */
    public function getName();

    /**
     * Determine if this partition contains the provided region. Include the
     * name of the service to inspect non-regional endpoints
     *
     * @param string $region
     * @param string $service
     *
     * @return bool
     */
    public function isRegionMatch($region, $service);

    /**
     * Return the endpoints supported by a given service.
     *
     * @param string    $service                    Identifier of the service
     *                                              whose endpoints should be
     *                                              listed (e.g., 's3' or 'ses')
     * @param bool      $allowNonRegionalEndpoints  Set to `true` to include
     *                                              endpoints that are not AWS
     *                                              regions (e.g., 'local' for
     *                                              DynamoDB or
     *                                              'fips-us-gov-west-1' for S3)
     * 
     * @return string[]
     */
    public function getAvailableEndpoints(
        $service,
        $allowNonRegionalEndpoints = false
    );

    /**
     * A partition must be invokable as an endpoint provider.
     *
     * @see EndpointProvider
     * 
     * @param array $args
     * @return array
     */
    public function __invoke(array $args = []);
}
