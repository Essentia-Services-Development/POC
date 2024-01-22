<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXpkMTRWZElKN1dPLzAzNzBockU3Rk1Wald1QVdBb0RsK3BmdHRHYjd1UE1hQ2ZabFRudDRUYW44UFRBMEVyaVRzT1VyY056TGQvOEl0RDJORDB2R3J3NGd4SllaZ3BEeEtjQ0ZPU2pBKzhQZ1JXM0NydEJFUmNiV1JCOTU1NzZBSjMzem0zUDZGSU5tdE03ZXh0Smhs*/

namespace GuzzleHttp\Promise;

interface TaskQueueInterface
{
    /**
     * Returns true if the queue is empty.
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Adds a task to the queue that will be executed the next time run is
     * called.
     */
    public function add(callable $task);

    /**
     * Execute all of the pending task in the queue.
     */
    public function run();
}
