<?php

if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenanceFriends extends PeepSoMaintenanceFactory
    {
        
        public static function cleanupFriendships()
        {
            global $wpdb;
            $wpdb->query("DELETE FROM `$wpdb->prefix" . PeepSoFriendsPlugin::TABLE . "` WHERE `fnd_user_id` NOT IN (SELECT ID FROM `$wpdb->users`)");            
            $wpdb->query("DELETE FROM `$wpdb->prefix" . PeepSoFriendsPlugin::TABLE . "` WHERE `fnd_friend_id` NOT IN (SELECT ID FROM `$wpdb->users`)");            
            $wpdb->query("DELETE FROM `$wpdb->prefix" . PeepSoFriendsRequests::TABLE . "` WHERE `freq_user_id` NOT IN (SELECT ID FROM `$wpdb->users`)");            

            if(get_current_user_id()) {
                PeepSoFriendsCache::_([get_current_user_id()]);
            }
        }
    }
}

//global $wpdb;
//
//// Delete friendships
//
//$sql = 'DELETE FROM `' . $wpdb->prefix . self::TABLE . '` WHERE `fnd_user_id`=%d';
//$wpdb->query($wpdb->prepare($sql, $id));
//
//
//$sql = 'DELETE FROM `' . $wpdb->prefix . self::TABLE . '` WHERE `fnd_friend_id`=%d';
//$wpdb->query($wpdb->prepare($sql, $id));
//
//
//// Clean up friend requests, both sent and received
//
//$sql = 'DELETE FROM `' . $wpdb->prefix . PeepSoFriendsRequests::TABLE . '` WHERE `freq_user_id`=%d';
//$wpdb->query($wpdb->prepare($sql, $id));
//
//$sql = 'DELETE FROM `' . $wpdb->prefix . PeepSoFriendsRequests::TABLE . '` WHERE `freq_friend_id`=%d';
//$wpdb->query($wpdb->prepare($sql, $id));