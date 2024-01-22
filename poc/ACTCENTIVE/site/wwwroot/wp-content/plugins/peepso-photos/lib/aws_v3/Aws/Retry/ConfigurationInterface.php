<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L3R6WExGWFRGS1k3Tjg5blh5K2d4eGVSY0RnRml6Mmt2MHYvREFscXltVGY2aUpyaWhkaUNPSlB4NTAzWW8xVEFxaWR0QWxuUzlIVXpBVE1jcWplQnFidEZJRzk1czB4dXMwMFN2YkR6L3lYSlFqaFZUYjJpMnBHWmtMSFphQ2tO*/
namespace Aws\Retry;

/**
 * Provides access to retry configuration
 */
interface ConfigurationInterface
{
    /**
     * Returns the retry mode. Available modes include 'legacy', 'standard', and
     * 'adapative'.
     *
     * @return string
     */
    public function getMode();

    /**
     * Returns the maximum number of attempts that will be used for a request
     *
     * @return string
     */
    public function getMaxAttempts();

    /**
     * Returns the configuration as an associative array
     *
     * @return array
     */
    public function toArray();
}
