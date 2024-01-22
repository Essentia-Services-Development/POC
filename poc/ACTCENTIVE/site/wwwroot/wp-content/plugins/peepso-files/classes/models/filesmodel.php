<?php

class PeepSoFilesModel {

    public function get_user_files($args = array())
    {
        global $wpdb;

		$clauses = [
            'join' => '',
            'where'=> ''
        ];

        if (isset($args['return_count'])) {
            $sql = "SELECT COUNT(*) FROM `{$wpdb->posts}` ";
        } else {
            $sql = "SELECT * FROM `{$wpdb->posts}` ";
        }
		
		$clauses['join'] .= 
				" LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`{$wpdb->posts}`.`ID` AND `act`.`act_module_id` = " . PeepSoFileUploads::MODULE_ID;
		
                $clauses['join'] .= 
				" LEFT JOIN `{$wpdb->posts}` `post2` ON `post2`.`post_parent`=`{$wpdb->posts}`.`ID` AND `post2`.`post_type` = 'attachment'";
		
        $clauses['where'] .= " WHERE `{$wpdb->posts}`.`post_status`='publish' AND `act`.`act_id` IS NOT NULL";

        if (isset($args['user_id']) && !isset($args['group_id'])) {
            $clauses['where'] .= " AND `act`.`act_owner_id` = " . $args['user_id'] . " ";
        }

        $access = '';
        // add checks for post's access
        if (is_user_logged_in()) {
			
            // PRIVATE and owner by current user id  - OR -
            // MEMBERS and user is logged in - OR -
            // PUBLIC

            if (!PeepSo::is_admin()) {
                $access = ' ((`act_access`=' . PeepSo::ACCESS_PRIVATE . ' AND `act_owner_id`=' . get_current_user_id() . ') OR ' .
                ' (`act_access`=' . PeepSo::ACCESS_MEMBERS . ') OR (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ') ';

                // Hooked methods must wrap the string within a paranthesis
                $access = apply_filters('peepso_activity_post_filter_access', $access);

                $access .= ') ';
            }
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';
        }

        if (isset($args['exclude_group_files'])) {
            $clauses['where'] .= " AND `post2`.`post_content` NOT LIKE '%\"group_id\":%'";
        } else
        // if group
        if (class_exists('PeepSoGroupsPlugin') && isset($args['group_id'])) {
            $clauses['where'] .= " AND `post2`.`post_content` LIKE '%\"group_id\":\"" . $args['group_id'] . "\"%'";
        }

        if (class_exists('PeepSoFriendsPlugin')) {
            add_filter('peepso_filter_clauses', array(PeepSoFriendsPlugin::get_instance(), 'filter_post_clauses'), 10, 2);
            $clauses = apply_filters('peepso_filter_clauses', $clauses, get_current_user_id());
        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        if (!empty($access)) {
            $sql .= ' AND ' . $access;
        }

        if (!isset($args['sort'])) {
            $sort = 'desc';
        } else {
            $sort = $args['sort'];
        }
        
        $sql .= ' ORDER BY act_id '. $sort;

        if (isset($args['offset']) && isset($args['limit'])) {
            $sql .= ' LIMIT ' . $args['offset'] .', ' . $args['limit'];
        }

        if (isset($args['return_count'])) {
            $files = $wpdb->get_var($sql);
        } else {
            $files = $wpdb->get_results($sql);
        }

        return $files;
    }

    public function calculate_user_files($user_id) {
        $args = [
            'user_id' => $user_id
        ];

        $files = $this->get_user_files($args);

        $size = $count = $uploaded_today = 0;

        if ($files) {
            foreach ($files as $file) {
                $post_content = json_decode($file->post_content);

                if (!$post_content) {
                    continue;
                }

                if (date('Y-m-d', strtotime($file->post_date)) == date('Y-m-d', current_time('timestamp'))) {
                    $uploaded_today++;
                }
                $size += $post_content->size;
                $count++;
            }
        }

        return [
            'size' => $size,
            'count' => $count,
            'uploaded_today' => $uploaded_today
        ];
    }
}