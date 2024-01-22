<?php
if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenanceCore extends PeepSoMaintenanceFactory
    {
        public static function deleteOldNotifications()
        {
            global $wpdb;

            $days = 30;

            if(defined('PEEPSO_DELETE_NOTIFICATIONS_AFTER_DAYS')) {
                $days = intval(PEEPSO_DELETE_NOTIFICATIONS_AFTER_DAYS);
            }

            if($days == 0) { return 0; }

            // look for items more than 20 days old
            $dt = date('Y-m-d', strtotime($days.' days ago'));

            // TODO: use PeepSoNotifications class constant for table name
            $sql = "DELETE FROM `{$wpdb->prefix}peepso_notifications` " .
                " WHERE CAST(`not_timestamp` AS DATE) < %s ";
            return $wpdb->query($wpdb->prepare($sql, $dt));
        }

        // TODO: delete any mail queue items that are `status`=PeepSoMailQueue::STATUS_SENT and more than 24 hours old
    }
}

// EOF