<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3VHRaYzR5L0hCa2N6UFdWYjUrMkJyTVlwYWF3YjhubU9JVW1UdWJQakNwckVjdFU5bkhuS0tySDBkNzdWeTFheThKT3NYUlV6RGljOFBvVDJid3lEYXZnZlZLZnpLdjVkcGVnTDZ3MXhkMHV0Z2NRc2tncFc2a3J3RkNteWhkaE00M1ZqSU1Wb3FYaHFWS0NHMzFXamFN*/
namespace Aws;

/**
 * Interface for adding and retrieving client-side monitoring events
 */
interface MonitoringEventsInterface
{

    /**
     * Get client-side monitoring events attached to this object. Each event is
     * represented as an associative array within the returned array.
     *
     * @return array
     */
    public function getMonitoringEvents();

    /**
     * Prepend a client-side monitoring event to this object's event list
     *
     * @param array $event
     */
    public function prependMonitoringEvent(array $event);

    /**
     * Append a client-side monitoring event to this object's event list
     *
     * @param array $event
     */
    public function appendMonitoringEvent(array $event);

}
