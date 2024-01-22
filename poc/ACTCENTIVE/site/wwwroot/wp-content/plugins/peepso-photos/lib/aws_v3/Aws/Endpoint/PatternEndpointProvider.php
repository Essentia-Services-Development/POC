<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VEt6NFFmcElyMnYrcmMxUllGZjgyb215MUUyWWh4aGV2eEpFbkl1azYxeVhEbGtkOTJkZTViZlJ2SHM3OElnS0J1YkZ1TGY0SHdUazllY3lFYzd6Vml3UmtzT2RNM0RCTVNhUkJHSXFuaTZzdmxrdWs4MVVlQ2QzcmVzZ3BtaysrNTNzNjhtaGQ5Z1Z5Y1cxRkM0U3hP*/
namespace Aws\Endpoint;

/**
 * Provides endpoints based on an endpoint pattern configuration array.
 */
class PatternEndpointProvider
{
    /** @var array */
    private $patterns;

    /**
     * @param array $patterns Hash of endpoint patterns mapping to endpoint
     *                        configurations.
     */
    public function __construct(array $patterns)
    {
        $this->patterns = $patterns;
    }

    public function __invoke(array $args = [])
    {
        $service = isset($args['service']) ? $args['service'] : '';
        $region = isset($args['region']) ? $args['region'] : '';
        $keys = ["{$region}/{$service}", "{$region}/*", "*/{$service}", "*/*"];

        foreach ($keys as $key) {
            if (isset($this->patterns[$key])) {
                return $this->expand(
                    $this->patterns[$key],
                    isset($args['scheme']) ? $args['scheme'] : 'https',
                    $service,
                    $region
                );
            }
        }

        return null;
    }

    private function expand(array $config, $scheme, $service, $region)
    {
        $config['endpoint'] = $scheme . '://'
            . strtr($config['endpoint'], [
                '{service}' => $service,
                '{region}'  => $region
            ]);

        return $config;
    }
}
