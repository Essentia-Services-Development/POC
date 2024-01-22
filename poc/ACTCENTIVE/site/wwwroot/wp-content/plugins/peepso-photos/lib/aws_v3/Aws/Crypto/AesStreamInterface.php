<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VEpjTjJJT1U5RWN1Z044R2ZONEFBRFhuOFdXbDlISS9zNjc0Um52VzVUKzVKdlRQL2RUbDc2elk1U0ZKekVpeDNsKy9CcW9HY09FZXEwNDZaVllRelJZZ3hPRVMzbGhTQWNmQmxZSncwS29BR2F0dDlWOXZTM2NURTZmUko2U0xWd0doa3lJNmY5MkRuOURDT0RuWEV1*/
namespace Aws\Crypto;

use Psr\Http\Message\StreamInterface;

interface AesStreamInterface extends StreamInterface
{
    /**
     * Returns an identifier recognizable by `openssl_*` functions, such as
     * `aes-256-cbc` or `aes-128-ctr`.
     *
     * @return string
     */
    public function getOpenSslName();

    /**
     * Returns an AES recognizable name, such as 'AES/GCM/NoPadding'.
     *
     * @return string
     */
    public function getAesName();

    /**
     * Returns the IV that should be used to initialize the next block in
     * encrypt or decrypt.
     *
     * @return string
     */
    public function getCurrentIv();
}
