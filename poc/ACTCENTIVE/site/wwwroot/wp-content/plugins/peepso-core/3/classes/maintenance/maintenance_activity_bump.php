<?php
if(class_exists('PeepSoMaintenanceFactory')) {
	class PeepSo3_Maintenance_Activity_Bump extends PeepSoMaintenanceFactory {

		public static function bump() {
            require_once ABSPATH . 'wp-includes/pluggable.php';
            global $wpdb;

            $last_act_id = get_option('peepso_activity_bump', FALSE);

            if ($last_act_id > 0 || $last_act_id === FALSE) {
                $query = "SELECT * FROM `{$wpdb->prefix}peepso_activities` WHERE `act_comment_object_id` > 0  ";

                if (is_numeric($last_act_id)) {
                    $query .= " AND `act_id` < " . $last_act_id;
                }

                $query .= " GROUP BY `act_comment_object_id`  ORDER BY `{$wpdb->prefix}peepso_activities`.`act_comment_object_id` DESC LIMIT 100";
                $comments = $wpdb->get_results($query);

                $PeepSoActivity = new PeepSoActivity();
                $checked_posts = [];

                if ($comments) {
                    foreach ($comments as $comment) {
                        $comment_post = get_post($comment->act_external_id);
                        $last_act_id = $comment->act_id;
        
                        // get parent post
                        $root_act = $PeepSoActivity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
                        
                        // if activity somewhat does not exist
                        if (!is_object($root_act)) {
                            continue;
                        }
                        $root_post = $PeepSoActivity->get_activity_post($root_act->act_id);
    
                        // if post somewhat does not exist
                        if (!is_object($root_post)) {
                            continue;
                        }
                        // if root post is still a comment
                        if ($root_post->post_type == PeepSoActivityStream::CPT_COMMENT) {
                            $comment = $root_post;
                            $root_act = $PeepSoActivity->get_activity_data($comment->act_comment_object_id, $comment->act_comment_module_id);
                            $root_post = $PeepSoActivity->get_activity_post($root_act->act_id);
                        }
        
                        if (in_array($root_post->ID, $checked_posts)) {
                            continue;
                        }
        
                        $checked_posts[] = $root_post->ID;
                        
                        // if the post date equal to modified date, then it's not modified yet
                        if ($root_post->post_type == PeepSoActivityStream::CPT_POST && $root_post->post_date == $root_post->post_modified) {
                            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts} SET post_modified = %s, post_modified_gmt = %s WHERE ID = %d", $comment_post->post_date, $comment_post->post_date_gmt, $root_post->ID));
                        }
                    }
        
                    update_option('peepso_activity_bump', $last_act_id);
                } else {
                    update_option('peepso_activity_bump', 0);
                }
            }
		}

	}
}

// EOF