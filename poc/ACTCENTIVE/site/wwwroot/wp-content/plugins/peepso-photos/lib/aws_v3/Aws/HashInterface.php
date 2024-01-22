<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UysvZ1h0WWRvYVVwc1VESmc0cFhhOEFENXRBZzVSMW5Kd2pITElHOUovNEIyVkNXTXg2M1cyekNFc29ha21QSFdoUmt4dktZZGdFaTlzcVdBVkJzZm15Nnl6d2g4RHQ3V1RxWHFWcmJVWVdGdUFhYkF0RnRmSmV6WTVuVllIOWpHbEhsdGczcEdFcGlCRFNLVnk3cGJL*/
namespace Aws;

/**
 * Interface that allows implementing various incremental hashes.
 */
interface HashInterface
{
    /**
     * Adds data to the hash.
     *
     * @param string $data Data to add to the hash
     */
    public function update($data);

    /**
     * Finalizes the incremental hash and returns the resulting digest.
     *
     * @return string
     */
    public function complete();

    /**
     * Removes all data from the hash, effectively starting a new hash.
     */
    public function reset();
}
