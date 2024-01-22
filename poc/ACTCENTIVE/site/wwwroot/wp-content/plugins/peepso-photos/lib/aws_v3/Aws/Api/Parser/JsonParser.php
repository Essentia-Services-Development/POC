<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UnNtUDNLU2tVdjJmOUFRTFU0WDZta29sYk9pdHExNXRZanQ0RElGc21oNEFjR3pVRSs2VktmQ2JjTWdmZ3JJNitIZndTTzIwS2c5eWZudmNJQ1dJaUQ5RFNuU0JIclBJeUdpNzIzT1dWanp6RzJGNUM1dTFjb2pqT1VZUml1aUw4PQ==*/
namespace Aws\Api\Parser;

use Aws\Api\DateTimeResult;
use Aws\Api\Shape;

/**
 * @internal Implements standard JSON parsing.
 */
class JsonParser
{
    public function parse(Shape $shape, $value)
    {
        if ($value === null) {
            return $value;
        }

        switch ($shape['type']) {
            case 'structure':
                if (isset($shape['document']) && $shape['document']) {
                    return $value;
                }
                $target = [];
                foreach ($shape->getMembers() as $name => $member) {
                    $locationName = $member['locationName'] ?: $name;
                    if (isset($value[$locationName])) {
                        $target[$name] = $this->parse($member, $value[$locationName]);
                    }
                }
                if (isset($shape['union'])
                    && $shape['union']
                    && is_array($value)
                    && empty($target)
                ) {
                    foreach ($value as $key => $val) {
                        $target['Unknown'][$key] = $val;
                    }
                }
                return $target;

            case 'list':
                $member = $shape->getMember();
                $target = [];
                foreach ($value as $v) {
                    $target[] = $this->parse($member, $v);
                }
                return $target;

            case 'map':
                $values = $shape->getValue();
                $target = [];
                foreach ($value as $k => $v) {
                    $target[$k] = $this->parse($values, $v);
                }
                return $target;

            case 'timestamp':
                return DateTimeResult::fromTimestamp(
                    $value,
                    !empty($shape['timestampFormat']) ? $shape['timestampFormat'] : null
                );

            case 'blob':
                return base64_decode($value);

            default:
                return $value;
        }
    }
}

