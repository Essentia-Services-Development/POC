<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VGJOdHFDVzhKMERCajQxZU1WWGE1Zk9vbDR4eDN1aVZEMks2YXplZWY0S0JiKzFoVjJpOHppcUJZeTdSREVEaUd0SGNwanBEbUR2dGZ2UnVDRFFxWjhEYnNLUkp6amo1N3h3ZHlWei9qb0U4V3ZoMFZaRWl3ZEhVNUlCTWhwY0Y3bFBMa0J3WC80YkxDejgvSG96ekxk*/
namespace Aws\DynamoDbStreams;

use Aws\AwsClient;
use Aws\DynamoDb\DynamoDbClient;

/**
 * This client is used to interact with the **Amazon DynamoDb Streams** service.
 *
 * @method \Aws\Result describeStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeStreamAsync(array $args = [])
 * @method \Aws\Result getRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRecordsAsync(array $args = [])
 * @method \Aws\Result getShardIterator(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \Aws\Result listStreams(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 */
class DynamoDbStreamsClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['default'] = 11;
        $args['retries']['fn'] = [DynamoDbClient::class, '_applyRetryConfig'];

        return $args;
    }
}