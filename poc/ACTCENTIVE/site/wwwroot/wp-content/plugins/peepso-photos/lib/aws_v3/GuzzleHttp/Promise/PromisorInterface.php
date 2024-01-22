<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UTlQcThSYmF2QWE4bk9wTGNvQU5EUy9Kb085MEdQSnRFdUZoZ3AxREZpYVNUZkUwWW9XaVRyYndNMUJ3emhPWEFYT0JqdHVGR091ci9SR1V2eVVra1VQSGQxR01IL1Vmc3Fld1dYWFVJNXNVVFlHeFluZDhBOGZoRGExUUt3ckhiR0RkYkp5Ri9LOTlxYVNxSVNVZWoz*/

namespace GuzzleHttp\Promise;

/**
 * Interface used with classes that return a promise.
 */
interface PromisorInterface
{
    /**
     * Returns a promise.
     *
     * @return PromiseInterface
     */
    public function promise();
}
