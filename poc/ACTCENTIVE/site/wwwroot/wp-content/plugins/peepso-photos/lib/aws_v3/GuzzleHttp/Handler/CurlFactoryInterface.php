<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UnFMUVM0VXREL2hUN05rMWo4dzRtZmpaNzAzdmFNSlRGRGl6WjRVdzA0QStyMFU4SVFzc3o3MmI4VlF4enF0cTl5cURhNVgrZERNaGhTRmxNTENxdGRmeFd3WitiNm1KMzluODZWUUZYWFduNEd2c0ZoTFJqN2xISENrS3Y5ckJkM2IrVlFzWEIzZ0swbVRMN1cvN1My*/
namespace GuzzleHttp\Handler;

use Psr\Http\Message\RequestInterface;

interface CurlFactoryInterface
{
    /**
     * Creates a cURL handle resource.
     *
     * @param RequestInterface $request Request
     * @param array            $options Transfer options
     *
     * @return EasyHandle
     * @throws \RuntimeException when an option cannot be applied
     */
    public function create(RequestInterface $request, array $options);

    /**
     * Release an easy handle, allowing it to be reused or closed.
     *
     * This function must call unset on the easy handle's "handle" property.
     *
     * @param EasyHandle $easy
     */
    public function release(EasyHandle $easy);
}
