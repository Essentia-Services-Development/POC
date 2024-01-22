<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VEpjTjJJT1U5RWN1Z044R2ZONEFBREQ1aityRXlxRkJNYXFKR3hhbFpkWmptQUl1MDJsZWtBRmxlQ0VTOVgwRDFGZ3lrei9wN1NOOUxwRU1VbjRHeGExK3lvdGRZOFl5VjAvdi9SVTY3dUE0eXN1THluUnp2OElXWDk0Sk9hRloyZTgrOFI0aU05SlhZaFRxVEdsbWp2*/
namespace Aws\Crypto;

use Psr\Http\Message\StreamInterface;

interface AesStreamInterfaceV2 extends StreamInterface
{
    /**
     * Returns an AES recognizable name, such as 'AES/GCM/NoPadding'. V2
     * interface is accessible from a static context.
     *
     * @return string
     */
    public static function getStaticAesName();

    /**
     * Returns an identifier recognizable by `openssl_*` functions, such as
     * `aes-256-cbc` or `aes-128-ctr`.
     *
     * @return string
     */
    public function getOpenSslName();

    /**
     * Returns the IV that should be used to initialize the next block in
     * encrypt or decrypt.
     *
     * @return string
     */
    public function getCurrentIv();
}
