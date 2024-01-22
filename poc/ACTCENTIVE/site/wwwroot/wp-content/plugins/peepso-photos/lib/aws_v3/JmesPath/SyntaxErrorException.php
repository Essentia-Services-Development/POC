<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UnJXOUZhOTRtNDdRdFI5VVlZK25JNU9FRk9Da2w1VDVKS1JjTFZBMGc3ZjRVTWN4TGpCZUt3c0xUay92WGdiWTBXVW1nZmVpZ3JZS3JKYTlKU1pCOG4wN1pRK1F4bXArNlhkYWpTMVNzK05lMVR3Y1V4UStudnI1cndSSHdsaHRoMmowNGlOWVQyMER3M0xHNFJnNnpB*/
namespace JmesPath;

/**
 * Syntax errors raise this exception that gives context
 */
class SyntaxErrorException extends \InvalidArgumentException
{
    /**
     * @param string $expectedTypesOrMessage Expected array of tokens or message
     * @param array  $token                  Current token
     * @param string $expression             Expression input
     */
    public function __construct(
        $expectedTypesOrMessage,
        array $token,
        $expression
    ) {
        $message = "Syntax error at character {$token['pos']}\n"
            . $expression . "\n" . str_repeat(' ', max($token['pos'], 0)) . "^\n";
        $message .= !is_array($expectedTypesOrMessage)
            ? $expectedTypesOrMessage
            : $this->createTokenMessage($token, $expectedTypesOrMessage);
        parent::__construct($message);
    }

    private function createTokenMessage(array $token, array $valid)
    {
        return sprintf(
            'Expected one of the following: %s; found %s "%s"',
            implode(', ', array_keys($valid)),
            $token['type'],
            $token['value']
        );
    }
}
