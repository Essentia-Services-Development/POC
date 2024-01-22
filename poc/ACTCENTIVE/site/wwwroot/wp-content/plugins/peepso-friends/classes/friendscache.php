<?php

/**
 * @class PeepSoFriendsCache
 *
 * ALL METHODS accept array[int] as argument
 * ALL METHODS return nothing
 */
class PeepSoFriendsCache {

    const CACHE_TIME = 120;

    /******* WRITE UTILITIES ****/

    // FIRE EVERYTHING
    public static function _(array $users) {
        #5849 only fires when param $users is array
        if (is_array($users) && count($users) > 0) {
            self::reset_friends_ids($users);
            self::reset_friends_count($users);
            self::reset_friends_cache_table($users);
            self::reset_widgets($users);
        }
    }

    // Reset friends
    public static function reset_friends_count(array $users) {
        foreach($users as $user) {
            if ($user) {
                PeepSoFriends::get_instance()->get_num_friends($user, TRUE);
            }
        }
    }

    // Reset friend ids
    public static function reset_friends_ids(array $users) {
        foreach($users as $user) {
            if ($user) {
                PeepSoFriendsModel::get_friends_ids($user,TRUE);
            }
        }
    }

    // Reset friends_cache table
    public static function reset_friends_cache_table(array $users, $forced = TRUE)
    {
        global $wpdb;

        foreach ($users as $user) {

            if (!$user) { 
                continue; 
            }

            $key = 'friends_cache_'.$user;

            if(!$forced && NULL !== PeepSo3_Mayfly::get($key)) {
                continue;
            }

            if(!$forced) {
                new PeepSoError("\t$user\t Resetting friends cache");
            }

            $wpdb->delete($wpdb->prefix . 'peepso_friends_cache', ['user_id' => $user]);

            $ids = PeepSoFriendsModel::get_friends_ids($user);

            foreach ($ids as $id) {
                $wpdb->query("INSERT IGNORE INTO {$wpdb->prefix}peepso_friends_cache (user_id, friend_id) VALUES ($user,$id)");
                $wpdb->query("INSERT IGNORE INTO {$wpdb->prefix}peepso_friends_cache (user_id, friend_id) VALUES ($id,$user)");
            }

            PeepSo3_Mayfly::set($key, 1, self::CACHE_TIME);
        }
    }

    // Widget transients
    public static function reset_widgets(array $users) {
        foreach($users as $user) {
            PeepSo3_Mayfly::del_like('peepso_cache_widget_friends%_'.$user);
//            PeepSo3_Mayfly::del('peepso_cache_widget_friendsupcomingbirthday_' . $user);
//            PeepSo3_Mayfly::del('peepso_cache_widget_friendsupcomingbirthday_savedate_' . $user);
//            PeepSo3_Mayfly::del('peepso_cache_widget_friendsbirthday_' . $user);
//            PeepSo3_Mayfly::del('peepso_cache_widget_friendsbirthday_savedate_' . $user);
//            PeepSo3_Mayfly::del('peepso_cache_widget_friendslist_' . $user);
//            PeepSo3_Mayfly::del('peepso_cache_widget_friendslist_savedate_' . $user);
        }
    }
}