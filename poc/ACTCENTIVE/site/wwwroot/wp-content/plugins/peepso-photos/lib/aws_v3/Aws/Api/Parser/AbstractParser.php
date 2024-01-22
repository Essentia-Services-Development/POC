<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UXpJZEtOaVpnVlg3TVhpR2lVbDRqdFhFM29mMm0veXF5QVdaeWtMcExtd2ZHUThlV0dNNmVNSkFHVk9lUk9wbkd0SkxBYk1vbnJtT1RoRDg5aVlOMlArUkpnbVBsRDNIWi9tSFNEL3hhLzZibW55dnF1RXNMa1JlU0JoUGtXOGZnVm1uWjRVbXdZQ0J3Sm9OaisvUUQy*/
namespace Aws\Api\Parser;

use Aws\Api\Service;
use Aws\Api\StructureShape;
use Aws\CommandInterface;
use Aws\ResultInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
abstract class AbstractParser
{
    /** @var \Aws\Api\Service Representation of the service API*/
    protected $api;

    /** @var callable */
    protected $parser;

    /**
     * @param Service $api Service description.
     */
    public function __construct(Service $api)
    {
        $this->api = $api;
    }

    /**
     * @param CommandInterface  $command  Command that was executed.
     * @param ResponseInterface $response Response that was received.
     *
     * @return ResultInterface
     */
    abstract public function __invoke(
        CommandInterface $command,
        ResponseInterface $response
    );

    abstract public function parseMemberFromStream(
        StreamInterface $stream,
        StructureShape $member,
        $response
    );
}
