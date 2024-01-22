<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2tNRDcrWW5odytqVlllejhScHRUQVlJWUg0T2VvVGh4N2Q5MlZ1QTdnWGdpZGVvTWhUYmRPNWZmSW1XMytzSS9qY1NRWGJQdUhsdjRqN3dabmw4MTlxRTdtZkpVU2pyb2UwYWludWUwUFlFVnQzR1ZpOGQyV0ZsVkhKYUFBNjVjPQ==*/
namespace Aws\Crypto\Polyfill;

use Aws\Exception\CryptoPolyfillException;

/**
 * Trait NeedsTrait
 * @package Aws\Crypto\Polyfill
 */
trait NeedsTrait
{
    /**
     * Preconditions, postconditions, and loop invariants are very
     * useful for safe programing.  They also document the specifications.
     * This function is to help simplify the semantic burden of parsing
     * these constructions.
     *
     * Instead of constructions like
     *     if (!(GOOD CONDITION)) {
     *         throw new \Exception('condition not true');
     *     }
     *
     * you can write:
     *     needs(GOOD CONDITION, 'condition not true');
     * @param $condition
     * @param $errorMessage
     * @param null $exceptionClass
     */
    public static function needs($condition, $errorMessage, $exceptionClass = null)
    {
        if (!$condition) {
            if (!$exceptionClass) {
                $exceptionClass = CryptoPolyfillException::class;
            }
            throw new $exceptionClass($errorMessage);
        }
    }
}
