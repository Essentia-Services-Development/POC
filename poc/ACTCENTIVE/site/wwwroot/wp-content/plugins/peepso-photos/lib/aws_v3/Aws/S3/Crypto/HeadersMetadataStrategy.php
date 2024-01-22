<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3Um8zaGR0S0paR0hJU3ZGT0Q0SllqaFR0M1QzbklnL3pnSkRFbW5mWVUzazdjSy9aVlZac0M0WkdGS2JJb0VPS1dDKzljQmhKU0ZTelRYejZDbCtUcy9YZ0JYeDFlWW96bDRDQlBKc0lpbFNvZWdVbXYvU29FQUwvR083djNEY1kxWTQwa2ZZZkEya0FiaU95Q09FTnRF*/
namespace Aws\S3\Crypto;

use \Aws\Crypto\MetadataStrategyInterface;
use \Aws\Crypto\MetadataEnvelope;

class HeadersMetadataStrategy implements MetadataStrategyInterface
{
    /**
     * Places the information in the MetadataEnvelope in to the metadata for
     * the PutObject request of the encrypted object.
     *
     * @param MetadataEnvelope $envelope Encryption data to save according to
     *                                   the strategy.
     * @param array $args Arguments for PutObject that can be manipulated to
     *                    store strategy related information.
     *
     * @return array Updated arguments for PutObject.
     */
    public function save(MetadataEnvelope $envelope, array $args)
    {
        foreach ($envelope as $header=>$value) {
            $args['Metadata'][$header] = $value;
        }

        return $args;
    }

    /**
     * Generates a MetadataEnvelope according to the metadata headers from the
     * GetObject result.
     *
     * @param array $args Arguments from Command and Result that contains
     *                    S3 Object information, relevant headers, and command
     *                    configuration.
     *
     * @return MetadataEnvelope
     */
    public function load(array $args)
    {
        $envelope = new MetadataEnvelope();
        $constantValues = MetadataEnvelope::getConstantValues();

        foreach ($constantValues as $constant) {
            if (!empty($args['Metadata'][$constant])) {
                $envelope[$constant] = $args['Metadata'][$constant];
            }
        }

        return $envelope;
    }
}
