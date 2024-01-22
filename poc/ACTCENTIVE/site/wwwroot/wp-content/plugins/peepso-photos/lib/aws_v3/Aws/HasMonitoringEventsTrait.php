<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UTA0Y3ZSckZFNmZ0ZXhTTjRGY3hwU21DdHJ4Rk5YeE9vblJFanpRVFIxK0xmZi9pRHBJRm9xUkFpM0RXaEdKWUxDZFVyVkVWTFVJdTZxdGFnSkNySXY4dWtXNVk4RjBGaE1SN0dnT0tWTEJuRlNvRmpTTEtFZGJqQUkxbng1NktVbktwaXpyNmkxYkt4eVRVSWtVTU9m*/
namespace Aws;


trait HasMonitoringEventsTrait
{
    private $monitoringEvents = [];

    /**
     * Get client-side monitoring events attached to this object. Each event is
     * represented as an associative array within the returned array.
     *
     * @return array
     */
    public function getMonitoringEvents()
    {
        return $this->monitoringEvents;
    }

    /**
     * Prepend a client-side monitoring event to this object's event list
     *
     * @param array $event
     */
    public function prependMonitoringEvent(array $event)
    {
        array_unshift($this->monitoringEvents, $event);
    }

    /**
     * Append a client-side monitoring event to this object's event list
     *
     * @param array $event
     */
    public function appendMonitoringEvent(array $event)
    {
        $this->monitoringEvents []= $event;
    }
}
