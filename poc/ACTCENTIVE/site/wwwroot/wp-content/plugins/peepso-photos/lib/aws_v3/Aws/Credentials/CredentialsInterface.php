<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3Um5JaG5Id253RDJjbEZRQi92bU9BNUpUTmU2eXViMGhpU0R3S3I4R3E5UU9qcnZldG1TaEZ1QS9PZ0VKdk84dHo0TDZ4c0hDbktPNzk1RVlSY3VDVVFWSFR5UWZWR3ZtL3kzdTZLZzBjM0FBZGdyWUpjUG1wYUZGMzZPdnM5TzZpY2F5YmFkaWFlQmhpOXl0V0RhOFBM*/
namespace Aws\Credentials;

/**
 * Provides access to the AWS credentials used for accessing AWS services: AWS
 * access key ID, secret access key, and security token. These credentials are
 * used to securely sign requests to AWS services.
 */
interface CredentialsInterface
{
    /**
     * Returns the AWS access key ID for this credentials object.
     *
     * @return string
     */
    public function getAccessKeyId();

    /**
     * Returns the AWS secret access key for this credentials object.
     *
     * @return string
     */
    public function getSecretKey();

    /**
     * Get the associated security token if available
     *
     * @return string|null
     */
    public function getSecurityToken();

    /**
     * Get the UNIX timestamp in which the credentials will expire
     *
     * @return int|null
     */
    public function getExpiration();

    /**
     * Check if the credentials are expired
     *
     * @return bool
     */
    public function isExpired();

    /**
     * Converts the credentials to an associative array.
     *
     * @return array
     */
    public function toArray();
}
