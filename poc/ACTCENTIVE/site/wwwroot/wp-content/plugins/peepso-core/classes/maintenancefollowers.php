<?php

if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenanceFollowers extends PeepSoMaintenanceFactory
    {
        /**
         * Rebuild missing user_followers records based on friends table
         */
        public static function rebuildFollowers($limit = 5)
        {
            if (empty(PeepSo3_Mayfly::get('peepso_user_followers_synced'))) {

                $i = 0;
                global $wpdb;

                if (class_exists('PeepSoFriends')) {
                    $r = $wpdb->get_results("SELECT `fnd_user_id` as ua, `fnd_friend_id` as ub FROM " . $wpdb->prefix . 'peepso_friends' . " fnd WHERE NOT EXISTS (SELECT * FROM " . $wpdb->prefix . 'peepso_user_followers' . " WHERE uf_active_user_id=fnd.fnd_user_id AND uf_passive_user_id=fnd.fnd_friend_id) LIMIT 0,$limit");


                    if (count($r)) {
                        foreach ($r as $f) {
                            new PeepSoUserFollower($f->ua, $f->ub);
                            new PeepSoUserFollower($f->ub, $f->ua);
                            $i++;
                        }
                    }

                    // just in case switch user_a and user_b and active in WHERE clause to rebuild the relation in the opposite direction as well
                    $r = $wpdb->get_results("SELECT `fnd_user_id` as ub, `fnd_friend_id` as ua FROM " . $wpdb->prefix . 'peepso_friends' . " fnd WHERE NOT EXISTS (SELECT * FROM " . $wpdb->prefix . 'peepso_user_followers' . " WHERE uf_active_user_id=fnd.fnd_friend_id AND uf_passive_user_id=fnd.fnd_user_id) LIMIT 0,$limit");

                    if (count($r)) {
                        foreach ($r as $f) {
                            new PeepSoUserFollower($f->ua, $f->ua);
                            new PeepSoUserFollower($f->ub, $f->ub);
                            $i++;
                        }
                    }
                }

                // Pause recount for a while if nothing found
                if (0 == $i) {
                    PeepSo3_Mayfly::set('peepso_user_followers_synced', 1, PeepSoUserFollower::CACHE_TIME);
                }

                $wpdb->query("DELETE FROM `$wpdb->prefix" . PeepSoUserFollower::TABLE . "` WHERE `uf_passive_user_id` NOT IN (SELECT ID FROM `$wpdb->users`)");
                $wpdb->query("DELETE FROM `$wpdb->prefix" . PeepSoUserFollower::TABLE . "` WHERE `uf_active_user_id` NOT IN (SELECT ID FROM `$wpdb->users`)");

                return $i;
            }
        }

    }
}