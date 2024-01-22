<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U2E4bGRSa2lrMERkTC9TekVxemYzQ1czSldYZTQ3bEdzSHVoRy8vZEdWRFdwVVoyb0J1cyswK1pySnFxcGFZcTYyMmgxRXA1NnZ1alkzMXgyaURZMkhvK2RRalVaZExDTEpvVU1GeDVwRUwwVzl6SjlVN3VWai9RSXpEWVFtdlNCUkw4Y3AzbDFnc1pmVWIxcEY1MDV1*/
namespace Aws\Signature;

use Aws\Credentials\CredentialsInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Amazon S3 signature version 4 support.
 */
class S3SignatureV4 extends SignatureV4
{
    /**
     * S3-specific signing logic
     *
     * {@inheritdoc}
     */
    use SignatureTrait;

    public function signRequest(
        RequestInterface $request,
        CredentialsInterface $credentials,
        $signingService = null
    ) {
        // Always add a x-amz-content-sha-256 for data integrity
        if (!$request->hasHeader('x-amz-content-sha256')) {
            $request = $request->withHeader(
                'x-amz-content-sha256',
                $this->getPayload($request)
            );
        }
        $useCrt =
            strpos($request->getUri()->getHost(), "accesspoint.s3-global")
            !== false;
        if (!$useCrt) {
            if (strpos($request->getUri()->getHost(), "s3-object-lambda")) {
                return parent::signRequest($request, $credentials, "s3-object-lambda");
            }
            return parent::signRequest($request, $credentials);
        }
        $signingService = $signingService ?: 's3';
        return $this->signWithV4a($credentials, $request, $signingService);
    }

    /**
     * Always add a x-amz-content-sha-256 for data integrity.
     *
     * {@inheritdoc}
     */
    public function presign(
        RequestInterface $request,
        CredentialsInterface $credentials,
        $expires,
        array $options = []
    ) {
        if (!$request->hasHeader('x-amz-content-sha256')) {
            $request = $request->withHeader(
                'X-Amz-Content-Sha256',
                $this->getPresignedPayload($request)
            );
        }
        if (strpos($request->getUri()->getHost(), "accesspoint.s3-global")) {
            $request = $request->withHeader("x-amz-region-set", "*");
        }

        return parent::presign($request, $credentials, $expires, $options);
    }

    /**
     * Override used to allow pre-signed URLs to be created for an
     * in-determinate request payload.
     */
    protected function getPresignedPayload(RequestInterface $request)
    {
        return SignatureV4::UNSIGNED_PAYLOAD;
    }

    /**
     * Amazon S3 does not double-encode the path component in the canonical request
     */
    protected function createCanonicalizedPath($path)
    {
        // Only remove one slash in case of keys that have a preceding slash
        if (substr($path, 0, 1) === '/') {
            $path = substr($path, 1);
        }
        return '/' . $path;
    }
}
