<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UVRHajJKbmdoVlJpSWJ6OGpJcGZYdWFzWkovVFpuTmp6KzBCajY0UnZFcm13Sm5PUHBjeldZK25TYmF0cTdvTUNQd21KTXZTQmlQVnBHNjdYYVgvNWljZzJNNEhDMXJTNmNOUjlEM0hHSWh4WnRDM3JaYkg1N3piQnp0RG5JWDNFPQ==*/

namespace GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(PromiseInterface $promise)
    {
        return $promise->getState() === PromiseInterface::PENDING;
    }

    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(PromiseInterface $promise)
    {
        return $promise->getState() !== PromiseInterface::PENDING;
    }

    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(PromiseInterface $promise)
    {
        return $promise->getState() === PromiseInterface::FULFILLED;
    }

    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(PromiseInterface $promise)
    {
        return $promise->getState() === PromiseInterface::REJECTED;
    }
}
