<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VFE5V3FRdVJhMENRNGJvRmwyQjNyaGg5SzlpUnI1d2gzQWVCUGNRWmptRHJhNUI2bHBXRUNOWm90V054aUhzVW9VSnRNTkFteFVXcnRFMSt5Y1dtNzNDRlh2V2dJWUVLMVliTk12b3pEUXRUVWUwS2RrWGNmMEdWWWF2MElUOTJodGp5d0hjM0lCdVBJUHhWUnF1K0Zw*/
namespace Aws\Arn;

/**
 * @internal
 */
trait ResourceTypeAndIdTrait
{
    public function getResourceType()
    {
        return $this->data['resource_type'];
    }

    public function getResourceId()
    {
        return $this->data['resource_id'];
    }

    protected static function parseResourceTypeAndId(array $data)
    {
        $resourceData = preg_split("/[\/:]/", $data['resource'], 2);
        $data['resource_type'] = isset($resourceData[0])
            ? $resourceData[0]
            : null;
        $data['resource_id'] = isset($resourceData[1])
            ? $resourceData[1]
            : null;
        return $data;
    }
}