<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXQ1RmYzVUt1ZzlIVGFreXVsdDQ4L0dDTlo1aVZSWE96Z1QwZWRGaEN3OFlhKy8xMDBmUG81dG5wZ2pGYW5palFhOVVxNE1UQkdKZTc2S0I1dkJncDk0ZjhhQTlrU2pHOWZ6VWhkcUVqRzIzN2NPa1RCb0tDZHFsWHFjMXBaYzFUTloxeGI4SDNoVHR6OHNzNnlpdC8y*/
namespace Aws\Sts\RegionalEndpoints\Exception;

use Aws\HasMonitoringEventsTrait;
use Aws\MonitoringEventsInterface;

/**
 * Represents an error interacting with configuration for sts regional endpoints
 */
class ConfigurationException extends \RuntimeException implements
    MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
