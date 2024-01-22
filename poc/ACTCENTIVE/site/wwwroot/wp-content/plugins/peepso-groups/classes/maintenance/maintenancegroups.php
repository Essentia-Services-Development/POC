<?php
if(class_exists('PeepSoMaintenanceFactory')) {
    class PeepSoMaintenanceGroups extends PeepSoMaintenanceFactory
    {
        public static function deleteGroupCategoriesForDeletedGroups()
        {
            global $wpdb;

            $t1 = $wpdb->prefix . PeepSoGroupCategoriesGroups::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.gm_group_id)";
            $wpdb->query($query);
        }

        public static function deleteGroupCategoriesForDeletedCategories()
        {
            global $wpdb;

            $t1 = $wpdb->prefix . PeepSoGroupCategoriesGroups::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.gm_cat_id)";
            $wpdb->query($query);
        }

        public static function recountGroupCategories()
        {
            $PeepSoGroupCategories = new PeepSoGroupCategories();

            foreach ($PeepSoGroupCategories->categories as $id => $category) {
                PeepSoGroupCategoriesGroups::update_stats_for_category($id);
            }
        }

        public static function deleteMembersForDeletedGroups()
        {
            global $wpdb;
            // Orphaned group_members entries for deleted groups
            $t1 = $wpdb->prefix . PeepSoGroupUsers::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.gm_group_id)";
            $wpdb->query($query);
        }

        public static function deleteMembersForDeletedUsers()
        {
            global $wpdb;

            // Orphaned group_members entries for deleted users
            $t1 = $wpdb->prefix . PeepSoGroupUsers::TABLE;
            $t2 = $wpdb->users;
            $query = "DELETE FROM $t1 WHERE NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.gm_user_id)";
            $wpdb->query($query);
        }

        public static function rebuildGroupFollowers()
        {
            return PeepSoGroupFollowers::rebuild(50);
        }

        public static function deleteNotificationsForDeletedGroups()
        {
            global $wpdb;
            // Orphaned notifications for deleted groups
            $t1 = $wpdb->prefix.PeepSoNotifications::TABLE;
            $t2 = $wpdb->posts;
            $query = "DELETE FROM $t1 WHERE $t1.not_module_id=".PeepSoGroupsPlugin::MODULE_ID." AND NOT EXISTS(SELECT `ID` FROM $t2 WHERE $t2.ID=$t1.not_external_id)";
            $wpdb->query($query);
        }

        public static function deletePostsForDeletedGroups() 
        {
            global $wpdb;

            $query = "SELECT ID FROM $wpdb->posts 
                LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID 
                WHERE $wpdb->postmeta.meta_key = 'peepso_group_id' 
                    AND $wpdb->postmeta.meta_value IS NOT NULL 
                    AND not exists(SELECT ID FROM $wpdb->posts WHERE ID = $wpdb->postmeta.meta_value AND post_type='".PeepSoGroup::POST_TYPE."')";
            $result = $wpdb->get_results($query);
            if ($result) {
                $activity = new PeepSoActivity();
                foreach ($result as $act) {
                    $activity->delete_post($act->ID);
                }
            }

            return $result;
        }
    }
}