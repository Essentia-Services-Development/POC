<?php

class PeepSoVideosModel
{
    public static $notices = array();				// error messages to be returned to user

    public $_iterator;

    const TABLE = 'peepso_videos';

    /**
     * Return all video entries associated to a post
     * @param  int $post_id The post ID
     * @return array $videos Unmodified videos
     */
    public function get_community_videos($media_type = 'all', $offset = 0, $limit = 10, $sort = 'desc')
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');                
                
        $sql = " SELECT * FROM `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ";
        
        $clauses['join'] .= 
                " LEFT JOIN `" . $wpdb->posts  . "` ON `act_external_id` = `" . $wpdb->posts  . "`.`ID` ";
        
        $clauses['join'] .= 
                " LEFT JOIN `{$wpdb->prefix}" . self::TABLE . "` `vid` ON `{$wpdb->posts}`.`ID`=`vid`.`vid_post_id` ";

        $clauses['where'] .= 
            " WHERE `act_module_id` = " . PeepSoVideos::MODULE_ID . " ";
        
        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);
        $clauses['where'] .= 
            " AND `{$wpdb->posts}`.`post_status`='publish' AND (`vid`.`vid_conversion_status`=" . PeepSoVideosUpload::STATUS_SUCCESS. " OR `vid`.`vid_stored`=1 OR (`vid`.`vid_url` NOT LIKE '%".$siteurl."%' AND `vid`.`vid_stored`=1) OR (`vid`.`vid_url` NOT LIKE '%".$siteurl."%' AND `vid`.`vid_upload_s3_status`=0)) ";
        
        $clauses['where'] .=
                " AND `vid`.`vid_id` IS NOT NULL";

        $module_id = 0;
        $widgets = TRUE;
        $clauses = apply_filters('peepso_videos_post_clauses', $clauses, $module_id, $widgets);
        
        // add checks for post's access
        if (is_user_logged_in()) {
            
            //$clauses = apply_filters('peepso_activity_post_clauses', $clauses);
            
            // PRIVATE and owner by current user id  - OR -
            // MEMBERS and user is logged in - OR -
            // PUBLIC
            $access = ' ((`act_access`=' . PeepSo::ACCESS_MEMBERS . ') OR (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ') ';

            // Hooked methods must wrap the string within a paranthesis
            #just view members and public privacy
            #$access = apply_filters('peepso_activity_post_filter_access', $access);

            $access .= ') ';
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';
        }

        if ($media_type != 'all') {
            $clauses['join'] .= " LEFT JOIN `{$wpdb->postmeta}` `psvideo_meta` ON `psvideo_meta`.`post_id`=`vid_post_id` AND `psvideo_meta`.`meta_key`='" . PeepSoVideos::POST_META_KEY_MEDIA_TYPE . "' ";
            $media_query = PeepSoVideos::ATTACHMENT_TYPE_VIDEO;
            if ($media_type == 'audio') {
                $media_query = PeepSoVideos::ATTACHMENT_TYPE_AUDIO;
            }

            $clauses['where'] .= " AND ((`psvideo_meta`.`meta_value` IS NULL) OR `psvideo_meta`.`meta_value` = '" . $media_query . "') ";

        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        $sql .= ' AND ' . $access;

        $sql .= ' ORDER BY act_id '. $sort .' LIMIT ' . $offset .', ' . $limit;

        $videos = $wpdb->get_results($sql);

        return ($this->set_videos($videos));
    }

    /**
     * Return all video entries associated to a post
     * @param  int $post_id The post ID
     * @return array $videos Unmodified videos
     */
    public function get_user_videos($user_id,$media_type = 'all', $offset = 0, $limit = 10, $sort = 'desc', $module_id = 0, $isgdpr=false)
    {
        global $wpdb;
        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);

		$clauses=array('join'=>'', 'where'=>'');
				
        $sql = " SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` ";
		
		$clauses['join'] .= 
				" LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id` = " . PeepSoVideos::MODULE_ID;
		
		$clauses['join'] .= 
				" LEFT JOIN `" . $wpdb->posts  . "` ON `act`.`act_external_id` = `" . $wpdb->posts  . "`.`ID` ";
		
        $clauses['where'] .=
            " WHERE `{$wpdb->posts}`.`post_status`='publish' AND ((`{$wpdb->prefix}" . self::TABLE . "`.`vid_conversion_status`=" . PeepSoVideosUpload::STATUS_SUCCESS. " OR `{$wpdb->prefix}" . self::TABLE . "`.`vid_stored`=1) OR (`{$wpdb->prefix}" . self::TABLE . "`.`vid_url` NOT LIKE '%" . $siteurl . "%' AND `{$wpdb->prefix}" . self::TABLE . "`.`vid_stored`=1) OR (`{$wpdb->prefix}" . self::TABLE . "`.`vid_url` NOT LIKE '%".$siteurl."%' AND `{$wpdb->prefix}" . self::TABLE . "`.`vid_upload_s3_status`=0))";

        $clauses = apply_filters('peepso_videos_post_clauses', $clauses, $module_id, FALSE);

        if(intval($user_id) !== 0 && intval($module_id) == 0) {
    		$clauses['where'] .=				
                " AND `act`.`act_owner_id` = " . $user_id . " ";
        }

        $clauses['where'] .=
            " AND `vid_module_id` = " . $module_id . " ";
        
        $access = '';
        // add checks for post's access
        if ((is_user_logged_in() && $module_id == 0 ) || $isgdpr) {
			
            if((get_current_user_id() != $user_id) && !$isgdpr) {
                $clauses = apply_filters('peepso_activity_post_clauses', $clauses, get_current_user_id());
            }
			
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
        } else if (is_user_logged_in() && $module_id != 0) {
            // MEMBERS
            $access = ' (`act_access`<=' . PeepSo::ACCESS_MEMBERS . ' ) ';

            $clauses = apply_filters('peepso_videos_filter_owner_' . $module_id, $clauses);
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';

            $clauses = apply_filters('peepso_videos_filter_owner_' . $module_id, $clauses);
        }

        if ($media_type != 'all') {
            $clauses['join'] .= " LEFT JOIN `{$wpdb->postmeta}` `psvideo_meta` ON `psvideo_meta`.`post_id`=`vid_post_id` AND `psvideo_meta`.`meta_key`='" . PeepSoVideos::POST_META_KEY_MEDIA_TYPE . "' ";
            $media_query = PeepSoVideos::ATTACHMENT_TYPE_VIDEO;
            if ($media_type == 'audio') {
                $media_query = PeepSoVideos::ATTACHMENT_TYPE_AUDIO;
            }

            $clauses['where'] .= " AND ((`psvideo_meta`.`meta_value` IS NULL) OR `psvideo_meta`.`meta_value` = '" . $media_query . "') ";

        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        if (!empty($access)) {
            $sql .= ' AND ' . $access;
        }

        $sql .= ' ORDER BY act_id '. $sort .' LIMIT ' . $offset .', ' . $limit;

        $videos = $wpdb->get_results($sql);

        return ($this->set_videos($videos));
    }

    public function get_num_community_videos($media_type = 'all')
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $sql = "SELECT COUNT(`vid_id`) as num_videos FROM `{$wpdb->prefix}" . self::TABLE . "`";
        
        $clauses['join'] .= 
                " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id` = " . PeepSoVideos::MODULE_ID ;

        $clauses['join'] .= 
                " LEFT JOIN `" . $wpdb->posts  . "` ON `act`.`act_external_id` = `" . $wpdb->posts  . "`.`ID` ";


        $module_id = 0;
        $widgets = TRUE;
        $clauses = apply_filters('peepso_videos_post_clauses', $clauses, $module_id, $widgets);

        // add checks for post's access
        if (is_user_logged_in()) {
            
            //$clauses = apply_filters('peepso_activity_post_clauses', $clauses, get_current_user_id());
            
            // PRIVATE and owner by current user id  - OR -
            // MEMBERS and user is logged in - OR -
            // PUBLIC
            $access = ' ((`act_access`=' . PeepSo::ACCESS_MEMBERS . ') OR (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ') ';

            // Hooked methods must wrap the string within a paranthesis
            $access = apply_filters('peepso_activity_post_filter_access', $access);

            $access .= ') ';
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';
        }

        if ($media_type != 'all') {
            $clauses['join'] .= " LEFT JOIN `{$wpdb->postmeta}` `psvideo_meta` ON `psvideo_meta`.`post_id`=`vid_post_id` AND `psvideo_meta`.`meta_key`='" . PeepSoVideos::POST_META_KEY_MEDIA_TYPE . "' ";
            $media_query = PeepSoVideos::ATTACHMENT_TYPE_VIDEO;
            if ($media_type == 'audio') {
                $media_query = PeepSoVideos::ATTACHMENT_TYPE_AUDIO;
            }

            $clauses['where'] .= " AND ((`psvideo_meta`.`meta_value` IS NULL) OR `psvideo_meta`.`meta_value` = '" . $media_query . "') ";

        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        $sql .= ' AND ' . $access;

        $videos = $wpdb->get_results($sql);

        return $videos[0]->num_videos;
    }    

    public function get_num_videos($user_id, $media_type = 'all', $module_id = 0)
    {
        global $wpdb;
        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);

		$clauses=array('join'=>'', 'where'=>'');

        $sql = "SELECT COUNT(`vid_id`) as num_videos FROM `{$wpdb->prefix}" . self::TABLE . "`";
		
		$clauses['join'] .= 
				" LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id` = " . PeepSoVideos::MODULE_ID ;

		$clauses['join'] .= 
				" LEFT JOIN `" . $wpdb->posts  . "` ON `act`.`act_external_id` = `" . $wpdb->posts  . "`.`ID` ";

        $clauses['where'] .=
            " WHERE `{$wpdb->posts}`.`post_status`='publish' AND ((`{$wpdb->prefix}" . self::TABLE . "`.`vid_conversion_status`=" . PeepSoVideosUpload::STATUS_SUCCESS. " OR `{$wpdb->prefix}" . self::TABLE . "`.`vid_stored`=1) OR (`{$wpdb->prefix}" . self::TABLE . "`.`vid_url` NOT LIKE '%" . $siteurl . "%' AND `{$wpdb->prefix}" . self::TABLE . "`.`vid_stored_failed` = 0))";

        $clauses['where'] .=                
            " AND `{$wpdb->prefix}" . self::TABLE . "`.`vid_module_id` = " . $module_id . " ";

		if(intval($user_id) !== 0 && intval($module_id) == 0) {
            $clauses['where'] .=                
                " AND `act`.`act_owner_id` = " . $user_id . " ";
        }

        $clauses = apply_filters('peepso_videos_post_clauses', $clauses, $module_id, FALSE);
        $access = '';

        // add checks for post's access
        if (is_user_logged_in()) {
			
            if(get_current_user_id() != $user_id) {
                $clauses = apply_filters('peepso_activity_post_clauses', $clauses, get_current_user_id());
            }			
			
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

        if ($media_type != 'all') {
            $clauses['join'] .= " LEFT JOIN `{$wpdb->postmeta}` `psvideo_meta` ON `psvideo_meta`.`post_id`=`vid_post_id` AND `psvideo_meta`.`meta_key`='" . PeepSoVideos::POST_META_KEY_MEDIA_TYPE . "' ";
            $media_query = PeepSoVideos::ATTACHMENT_TYPE_VIDEO;
            if ($media_type == 'audio') {
                $media_query = PeepSoVideos::ATTACHMENT_TYPE_AUDIO;
            }

            $clauses['where'] .= " AND ((`psvideo_meta`.`meta_value` IS NULL) OR `psvideo_meta`.`meta_value` = '" . $media_query . "') ";

        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        if (!empty($access)) {
            $sql .= ' AND ' . $access;
        }

        $videos = $wpdb->get_results($sql);

        return $videos[0]->num_videos;
    }

    /**
     * Set video iterator
     * @param array $videos List of videos
     * @return array $videos Unmodified videos
     */
    public function set_videos($videos)
    {
        $videos_object = new ArrayObject($videos);
        $this->_iterator = $videos_object->getIterator();

        return ($videos);
    }

    /**
     * Return a row from the videos table.
     * @param  int $video_id The ID of the video to retrieve.
     * @return array
     */
    public function get_video($video_id)
    {
        global $wpdb;

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
					LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `vid_post_id`
					LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act`
						ON `act`.`act_external_id`=`vid_id` AND `act`.`act_module_id`=%d
					WHERE `vid_id`=%d";

        return ($wpdb->get_row($wpdb->prepare($sql, PeepSoVideos::MODULE_ID, $video_id)));
    }


    /**
     * Fetches all video queued up on the database.
     * @param  int $limit  How many records to fetch.
     * @param  int $offset Fetch records beginning from this index.
     * @param  string  $order  Order by column.
     * @param  string  $dir The sort direction, defaults to 'asc'
     * @return array Array of the result set.
     */
    public static function fetch_all_queue($limit = NULL, $offset = 0, $order = NULL, $dir = 'asc')
    {
        global $wpdb;

        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);

        $query = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `vid_post_id`
                    LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act`
                        ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id`=".PeepSoVideos::MODULE_ID."
                    WHERE `vid_url` LIKE '%".$siteurl."%'
                        OR `vid_upload_s3_status` > 0 ";

        if (isset($order))
            $query .= ' ORDER BY `' . $order . '` ' . $dir;

        if (isset($limit))
            $query .= ' LIMIT ' . $offset . ', ' . $limit;

        return ($wpdb->get_results($query, ARRAY_A));
    }

    public function get_list_upload_to_s3()
    {
        global $wpdb;

        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `vid_post_id`
                    LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act`
                        ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id`=".PeepSoVideos::MODULE_ID."
                    WHERE `vid_stored`=0 AND `vid_stored_failed`=0 
                        AND `vid_upload_s3_status` IN (" . PeepSoVideosUpload::STATUS_S3_WAITING . ", ".PeepSoVideosUpload::STATUS_S3_RETRY.")
                        AND `vid_upload_s3_retry_count` <= 3
                        AND `vid_url` LIKE '%".  $siteurl ."%' ORDER BY vid_created ASC LIMIT 1";

        return ($wpdb->get_row($sql));
    }

    /**
     * Return a row from the videos table.
     * @param  int $video_id The ID of the video to retrieve.
     * @return array
     */
    public function get_unconverted_video()
    {
        global $wpdb;

        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `vid_post_id`
                    LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act`
                        ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id`=".PeepSoVideos::MODULE_ID."
                    WHERE `vid_stored`=0 AND `vid_stored_failed`=0 
                        AND `vid_conversion_status` IN (" . PeepSoVideosUpload::STATUS_PENDING . ", ".PeepSoVideosUpload::STATUS_RETRY.")
                        AND `vid_upload_s3_status` = 0 
                        AND `vid_url` LIKE '%".  $siteurl ."%' ORDER BY vid_created ASC LIMIT 1";
        return ($wpdb->get_row($sql));
    }


    /**
     * Return a row from the videos table.
     * @param  int $video_id The ID of the video to retrieve.
     * @return array
     */
    public function get_unfinished_transcoder_job()
    {
        global $wpdb;

        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `vid_post_id`
                    LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act`
                        ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id`=".PeepSoVideos::MODULE_ID."
                    WHERE `vid_stored`=0 AND `vid_stored_failed`=0 
                        AND `vid_conversion_status` IN (" . PeepSoVideosUpload::STATUS_PENDING . ", ".PeepSoVideosUpload::STATUS_RETRY.")
                        AND `vid_upload_s3_status` = " . PeepSoVideosUpload::STATUS_S3_COMPLETE . "
                        AND `vid_transcoder_job_id` <> '' ORDER BY vid_created ASC LIMIT 5";
        return ($wpdb->get_results($sql));
    }

    /**
     * Return a row from the videos table.
     * @param  int $video_id The ID of the video to retrieve.
     * @return array
     */
    public function get_failed_convert_video()
    {
        global $wpdb;

        $siteurl = get_option( 'siteurl' );
        $siteurl = parse_url($siteurl, PHP_URL_HOST);

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `vid_post_id`
                    LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act`
                        ON `act`.`act_external_id`=`vid_post_id` AND `act`.`act_module_id`=".PeepSoVideos::MODULE_ID."
                    WHERE `vid_stored`=0 AND `vid_stored_failed`=1 
                        AND `vid_created` <= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                        AND `vid_conversion_status`=" . PeepSoVideosUpload::STATUS_FAILED . "
                        AND `vid_url` LIKE '%" . $siteurl . "%' ORDER BY vid_created ASC";

        return ($wpdb->get_results($sql));
    }

    /**
     * Get video iterator
     * @return ArrayObject list of videos in object form
     */
    public function get_iterator()
    {
        return ($this->_iterator);
    }

    /** Videos Upload */

    /**
     * Count posts by author id
     * @param int $user_id Author's user id
     * @param bool $today filter by post date to Today if TRUE, otherwise no post date filter
     * @return int number of posts
     */
    public function count_author_post($user_id, $today = FALSE)
    {
        global $wpdb;
        $sql = " SELECT COUNT(`" . $wpdb->prefix . self::TABLE . "`.`vid_id`)
                 FROM `{$wpdb->posts}`
                 RIGHT JOIN `{$wpdb->prefix}" . self::TABLE . "`
                 ON  `{$wpdb->posts}`.`ID` = `vid_post_id`
                 WHERE `post_author` = %s ";
        if ($today) {
            $sql .= ' AND DATE(`post_date`) = CURDATE() ';
        }

        return ($wpdb->get_var($wpdb->prepare($sql, $user_id)));
    }

    /**
     * Checks if a video of "$size" can still be uploaded
     * @param  int $user_id The user ID
     * @param  int $size The file size in bytes
     * @return boolean Returns TRUE if there's sufficient space for the videos to be uploaded
     */
    public function video_size_can_fit($user_id, $size, $is_audio = FALSE)
    {
        if (PeepSo::is_admin()) {
            return TRUE;
        }
        if ($is_audio) {
            $allowed_user_space = PeepSo::get_option('videos_audio_allowed_user_space', 0);
        } else {
            $allowed_user_space = PeepSo::get_option('videos_allowed_user_space', 0);
        }

        // #3056 ADD CASE FOR "0" HERE
        if ($allowed_user_space > 0) {
            $total_filesize = $this->get_user_total_filesize($user_id, $is_audio);
            
            // convert to bytes
            $allowed_user_space = $allowed_user_space  * 1048576;

            return ($total_filesize + $size < $allowed_user_space);
        } else {
            return TRUE;
        }
    }

    /**
     * Return the total file size (in bytes) consumed by the user
     * @param  int $user_id The user ID
     * @return int The file size in bytes
     */
    public function get_user_total_filesize($user_id, $is_audio)
    {
        global $wpdb;

        $media_type = PeepSoVideos::ATTACHMENT_TYPE_VIDEO;
        if ($is_audio) {
            $media_type = PeepSoVideos::ATTACHMENT_TYPE_AUDIO;
        }

        $sql = "SELECT SUM(`vid_size`)
                    FROM `" . $wpdb->prefix . self::TABLE . "` `videos`
                        LEFT JOIN `". $wpdb->posts . "` `posts`
                            ON `posts`.`ID` = `videos`.`vid_post_id`
                        JOIN `". $wpdb->postmeta . "` `pm`
                            ON `posts`.`ID` = `pm`.`post_id`
                    WHERE
                        `posts`.`post_author` = %d
                        AND `pm`.`meta_key` = '" . PeepSoVideos::POST_META_KEY_MEDIA_TYPE . "'
                        AND `pm`.`meta_value` = '" . $media_type . "'";

        $result = $wpdb->get_col($wpdb->prepare($sql, $user_id));

        return (is_null($result[0]) ? 0 : $result[0]);
    }

    /**
     * Get photo temporary directory
     * @return string Temporary photo directory
     */
    public function get_tmp_dir()
    {
        $tmp_dir = $this->get_video_dir() . 'tmp' . DIRECTORY_SEPARATOR;
        if (!is_dir($tmp_dir)) {
            mkdir($tmp_dir, 0755, TRUE);
        }

        return ($tmp_dir);
    }

    /**
     * Generate temporary file
     * @param string name of the file
     * @return array
     */
    public function get_tmp_file($filename)
    {
        $tmp_dir = $this->get_tmp_dir();
        $file = array();
        $file['name'] = wp_unique_filename($tmp_dir, $filename);
        $file['path'] = $tmp_dir . $file['name'];
        return ($file);
    }

    /**
     * Get video directory for current user
     * @return string $video_dir Video directory
     */
    public function get_video_dir($user_id = 0)
    {
        static $video_dir = NULL; // used for caching to avoid multiple query when instantiating PeepSoUser
        if (NULL === $video_dir) {
            $input = new PeepSoInput();
            $module_id = $input->int('module_id', 0);

            if($module_id === 0) {
                $user_id = ($user_id == 0) ? get_current_user_id() : $user_id;
                $user = PeepSoUser::get_instance($user_id);
                $video_dir = ($user) ? $user->get_image_dir() : '';
                $video_dir .= 'videos' . DIRECTORY_SEPARATOR;

                if (!is_dir($video_dir)) {
                    mkdir($video_dir, 0755, TRUE);
                }
            } else {
                $video_dir = apply_filters('peepso_videos_dir_' . $module_id, $video_dir);
            }
        }
        return ($video_dir);
    }
}

// EOF