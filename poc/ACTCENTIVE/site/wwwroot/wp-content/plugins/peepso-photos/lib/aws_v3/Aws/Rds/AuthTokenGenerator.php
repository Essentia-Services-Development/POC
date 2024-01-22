<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U3Q5Qkc5dnl6dlUxR0NOU3J0NVFFUDR3clFEaCtEU2o5cmNNMVM2QlRVb2Q3bnZxdDVEVGNEUXhQVHZyaG9Bb0RZNS91RTh2Y3NXa3hMeWZzMVhLS1FTZStWamsvVm1Tb2lvQ2FyWVRZSTVBUEt0b3I5Z2xvcWJTR1Fid00wRG80S2xQOHArMmE0SmlTZC82RXZEOGNS*/
namespace Aws\Rds;

use Aws\Credentials\CredentialsInterface;
use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Promise;
use Aws;

/**
 * Generates RDS auth tokens for use with IAM authentication.
 */
class AuthTokenGenerator
{

    private $credentialProvider;

    /**
     * The constructor takes an instance of Credentials or a CredentialProvider
     *
     * @param callable|Credentials $creds
     */
    public function __construct($creds)
    {
        if ($creds instanceof CredentialsInterface) {
            $promise = new Promise\FulfilledPromise($creds);
            $this->credentialProvider = Aws\constantly($promise);
        } else {
            $this->credentialProvider = $creds;
        }
    }

    /**
     * Create the token for database login
     *
     * @param string $endpoint The database hostname with port number specified
     *                         (e.g., host:port)
     * @param string $region The region where the database is located
     * @param string $username The username to login as
     * @param int $lifetime The lifetime of the token in minutes
     *
     * @return string Token generated
     */
    public function createToken($endpoint, $region, $username, $lifetime = 15)
    {
        if (!is_numeric($lifetime) || $lifetime > 15 || $lifetime <= 0) {
            throw new \InvalidArgumentException(
                "Lifetime must be a positive number less than or equal to 15, was {$lifetime}",
                null
            );
        }

        $uri = new Uri($endpoint);
        $uri = $uri->withPath('/');
        $uri = $uri->withQuery('Action=connect&DBUser=' . $username);

        $request = new Request('GET', $uri);
        $signer = new SignatureV4('rds-db', $region);
        $provider = $this->credentialProvider;

        $url = (string) $signer->presign(
            $request,
            $provider()->wait(),
            '+' . $lifetime . ' minutes'
        )->getUri();

        // Remove 2 extra slash from the presigned url result
        return substr($url, 2);
    }
}
