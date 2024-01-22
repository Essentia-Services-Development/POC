<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UlJKWnpYdFFBam1KbkNwa3FqUWRuUlB1TCtyWVd0eE0zUXIzNmE2VTNxejRRS1J4ck9XbmVMdHo0RDg3RXZybkZES0JkTFoydlFrdFRobXdWbHBFZW5XckhhTW9FMkRHcTh6dkN1eTMzczJReEpaTm96b2xHbWd3YTFYeTh1K0NOTGlXN1praEV4RE1BYnFoVm1WenVO*/
namespace Aws\Signature;

use Aws\Credentials\CredentialsInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Provides anonymous client access (does not sign requests).
 */
class AnonymousSignature implements SignatureInterface
{
    /**
     * /** {@inheritdoc}
     */
    public function signRequest(
        RequestInterface $request,
        CredentialsInterface $credentials
    ) {
        return $request;
    }

    /**
     * /** {@inheritdoc}
     */
    public function presign(
        RequestInterface $request,
        CredentialsInterface $credentials,
        $expires,
        array $options = []
    ) {
        return $request;
    }
}
