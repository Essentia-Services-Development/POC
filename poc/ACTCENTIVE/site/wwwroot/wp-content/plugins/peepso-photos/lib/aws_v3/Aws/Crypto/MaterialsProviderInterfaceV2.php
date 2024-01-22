<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VFJJRDB1Vkh5ZWNtRC9xbFdnRG9WWFdHYWZWTFJHc0ZwZGpQMG1jNlg5V0l6TVp6UlcwcUUrdGRMZHZOMWV4cTNLZzVaUjN0ZmhKZURzV29TUHVNKzQ1T0pDVG11dFdUdUdBY2hod2lpMkxlRllia055Vk9YcTQrSFB1V1ZtVmRsWHpOdnc1djRFK09NeXdja2QwbjdhQkFpZW90ODRxQjdMM2wvY2FZNHYwUT09*/
namespace Aws\Crypto;

interface MaterialsProviderInterfaceV2
{
    /**
     * Returns if the requested size is supported by AES.
     *
     * @param int $keySize Size of the requested key in bits.
     *
     * @return bool
     */
    public static function isSupportedKeySize($keySize);

    /**
     * Returns the wrap algorithm name for this Provider.
     *
     * @return string
     */
    public function getWrapAlgorithmName();

    /**
     * Takes an encrypted content encryption key (CEK) and material description
     * for use decrypting the key according to the Provider's specifications.
     *
     * @param string $encryptedCek Encrypted key to be decrypted by the Provider
     *                             for use decrypting other data.
     * @param string $materialDescription Material Description for use in
     *                                    decrypting the CEK.
     * @param array $options Options for use in decrypting the CEK.
     *
     * @return string
     */
    public function decryptCek($encryptedCek, $materialDescription, $options);

    /**
     * @param string $keySize Length of a cipher key in bits for generating a
     *                        random content encryption key (CEK).
     * @param array $context Context map needed for key encryption
     * @param array $options Additional options to be used in CEK generation
     *
     * @return array
     */
    public function generateCek($keySize, $context, $options);

    /**
     * @param string $openSslName Cipher OpenSSL name to use for generating
     *                            an initialization vector.
     *
     * @return string
     */
    public function generateIv($openSslName);
}
