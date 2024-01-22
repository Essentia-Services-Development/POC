<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VGRlTnFnK0xMckcybjVJVHNEU2ZyZWJOTWNnakdYNFg0M21pNVJnWm9kQysrTmphMVF1aUZ2eU5hRCtGMTlGWjcxdXBiemhwTUFjSlN0TVRhakZ2ZS9GaVhERXJDcTFjWkJpMDdjb3BHOFkyUWZJQkhjdnJ0ckQ0UnFUSGovbVlRPQ==*/
namespace Aws\Api;

/**
 * Represents a list shape.
 */
class ListShape extends Shape
{
    private $member;

    public function __construct(array $definition, ShapeMap $shapeMap)
    {
        $definition['type'] = 'list';
        parent::__construct($definition, $shapeMap);
    }

    /**
     * @return Shape
     * @throws \RuntimeException if no member is specified
     */
    public function getMember()
    {
        if (!$this->member) {
            if (!isset($this->definition['member'])) {
                throw new \RuntimeException('No member attribute specified');
            }
            $this->member = Shape::create(
                $this->definition['member'],
                $this->shapeMap
            );
        }

        return $this->member;
    }
}
