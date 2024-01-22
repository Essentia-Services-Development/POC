<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VE9VNFgyVXdZeTB6VFNYbENJTElJUVE2b21BaGhiNzNRbTFwQmZyMGh2eFREMi9GbDZxWE1HTzlaODZHMHdJbkdKNHZoeFhvZVBGTTFucmNValMrMGFKdlR0SkZyRHZnVnhCTUY4bFE1ejM2TGE0bTA2MEFsUzVReXl6anExMExMZlFzZXAvbWpKS1ZzdFEzMkswY3po*/
namespace GuzzleHttp\Exception;

use Throwable;

if (interface_exists(Throwable::class)) {
    interface GuzzleException extends Throwable
    {
    }
} else {
    /**
     * @method string getMessage()
     * @method \Throwable|null getPrevious()
     * @method mixed getCode()
     * @method string getFile()
     * @method int getLine()
     * @method array getTrace()
     * @method string getTraceAsString()
     */
    interface GuzzleException
    {
    }
}
