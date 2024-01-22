<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3Um1zRFdnbmkzVGhBbVZtcUF4M21LVm40NGdtby9ML1hqdkFGem9hcEVLQUtaQTZabVBMZmZzOWZtV0s2RkJZMVJ6Umt0MHp5Qm9CanIzVld4RlkrdjJ6OEFiam5CYU05L2JCTXVmR255emk4RDZrM1B0Um53WmJLTVlYYmZmKytTSTlPbDZFVi9lSGJ4RWRDTmwydDF2*/
namespace Aws\Signature;

use Aws\Credentials\CredentialsInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Interface used to provide interchangeable strategies for signing requests
 * using the various AWS signature protocols.
 */
interface SignatureInterface
{
    /**
     * Signs the specified request with an AWS signing protocol by using the
     * provided AWS account credentials and adding the required headers to the
     * request.
     *
     * @param RequestInterface     $request     Request to sign
     * @param CredentialsInterface $credentials Signing credentials
     *
     * @return RequestInterface Returns the modified request.
     */
    public function signRequest(
        RequestInterface $request,
        CredentialsInterface $credentials
    );

    /**
     * Create a pre-signed request.
     *
     * @param RequestInterface              $request     Request to sign
     * @param CredentialsInterface          $credentials Credentials used to sign
     * @param int|string|\DateTimeInterface $expires The time at which the URL should
     *     expire. This can be a Unix timestamp, a PHP DateTime object, or a
     *     string that can be evaluated by strtotime.
     *
     * @return RequestInterface
     */
    public function presign(
        RequestInterface $request,
        CredentialsInterface $credentials,
        $expires,
        array $options = []
    );
}
