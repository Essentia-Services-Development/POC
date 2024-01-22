<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UjJORWt6ZXdVTnJ5MThPNDdnNUQ2akd5MkVZY1VhQlJJelk4U2dlemJkLzNPcEhkTDhDQTJIQXIyOUhpc2t2QTlTaC9qMmdyTzA2WWZtZjVTaEZsU0hDWkphQncvVEw2QnB4aFVHcU9tdzM4dTQ2YXdtcklrZE9ITDVDa1QwTDROOCt3STBaUFJZNkNNY1hmMVRVZHN6*/
namespace Aws\Api\Serializer;

use Aws\Api\Shape;
use Aws\Api\ListShape;

/**
 * @internal
 */
class Ec2ParamBuilder extends QueryParamBuilder
{
    protected function queryName(Shape $shape, $default = null)
    {
        return ($shape['queryName']
            ?: ucfirst(@$shape['locationName'] ?: ""))
                ?: $default;
    }

    protected function isFlat(Shape $shape)
    {
        return false;
    }

    protected function format_list(
        ListShape $shape,
        array $value,
        $prefix,
        &$query
    ) {
        // Handle empty list serialization
        if (!$value) {
            $query[$prefix] = false;
        } else {
            $items = $shape->getMember();
            foreach ($value as $k => $v) {
                $this->format($items, $v, $prefix . '.' . ($k + 1), $query);
            }
        }
    }
}
