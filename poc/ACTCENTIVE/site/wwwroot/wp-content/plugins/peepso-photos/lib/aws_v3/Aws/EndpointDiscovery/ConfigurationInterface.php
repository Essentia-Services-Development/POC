<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L3R6WExGWFRGS1k3Tjg5blh5K2d4eGVSY0RnRml6Mmt2MHYvREFscXltVGY2aUpyaWhkaUNPSlB4NTAzWW8xVEFxaWR0QWxuUzlIVXpBVE1jcWplQnFidEZJRzk1czB4dXMwMFN2YkR6L3lYSlFqaFZUYjJpMnBHWmtMSFphQ2tO*/
namespace Aws\EndpointDiscovery;

/**
 * Provides access to endpoint discovery configuration options:
 * 'enabled', 'cache_limit'
 */
interface ConfigurationInterface
{
    /**
     * Checks whether or not endpoint discovery is enabled.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Returns the cache limit, if available.
     *
     * @return string|null
     */
    public function getCacheLimit();

    /**
     * Returns the configuration as an associative array
     *
     * @return array
     */
    public function toArray();
}
