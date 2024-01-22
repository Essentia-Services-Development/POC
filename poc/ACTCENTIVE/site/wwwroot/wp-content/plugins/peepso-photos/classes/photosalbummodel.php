<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UkswckRaZXBMUVc0OWwwb1RVTTBUM01DZkxWSGlpclEzWXNNdVNNekppdnRIUVVpMzQ5TTZNR1FJZGx3SFhLWmJGZjdhVVA5QzVjOUVMRVRxck5LSWxTYmlNcTgrdC8yVzBLWW5LbVUzaEdXZHEwLzdxTUh4OW4yTjYwWVBYcXhqYk1YT01XNXprbm10Y1ZLUWhFbjNW*/

class PeepSoPhotosAlbumModel
{
	const TABLE = 'peepso_photos_album';
    public $_iterator;
    /**
     * Create album 
     */
    public function create_album($user_id, $name, $privacy, $description, $post_id, $module_id = 0) {
        if (is_user_logged_in()) {

            if(get_current_user_id() == $user_id || intval($module_id) !== 0) {
                global $wpdb;

                $album_data['pho_owner_id'] = $user_id;
                $album_data['pho_album_acc'] = $privacy;
                $album_data['pho_album_name'] = $name;
                $album_data['pho_album_desc'] = $description;
                $album_data['pho_post_id'] = $post_id;
                $album_data['pho_module_id'] = $module_id;

                $wpdb->insert($wpdb->prefix . self::TABLE, $album_data);

                $album_id = $wpdb->insert_id;

                return $album_id;
            }
        }

        // if not have priviledges
        return FALSE;
    }

    /**
     * delete album 
     */
    public function delete_album($user_id, $album_id, $module_id = 0) {
        if (is_user_logged_in()) {

            if(get_current_user_id() == $user_id || (PeepSo::is_admin()) || intval($module_id) !== 0) {
                global $wpdb;

                if(PeepSo::is_admin()) {
                    return $wpdb->delete( $wpdb->prefix . self::TABLE, array( 'pho_album_id' => $album_id) );
                } else {
                    return $wpdb->delete( $wpdb->prefix . self::TABLE, array( 'pho_album_id' => $album_id, 'pho_owner_id' => $user_id ) );
                }
            }
        }

        // if not have priviledges
        return FALSE;
    }    

	/**
     * Return all photo album entries associated to user
     * @param  int $user_id The user ID
     * @return array $photos_album Unmodified photos
     */
    public function get_user_photos_album($user_id, $offset = 0, $limit = 10, $sort = 'desc', $module_id = 0)
    {
        global $wpdb;

        $clauses=array();

        $clauses['join'] =
            "  LEFT JOIN `{$wpdb->posts}` ON `{$wpdb->posts}`.`ID`=`pho_post_id` ";

        $clauses['where'] =
            " WHERE `pho_owner_id` = %d ";

        $clauses['where'] .=
            " AND `pho_module_id` = %d ";

		$sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` ";

        // add checks for photo album's access
        if (is_user_logged_in() && $module_id === 0) {

            // PRIVATE and owner by current user id  - OR -
            // MEMBERS and user is logged in - OR -
            // PUBLIC
			$access = '';

			if ($user_id !== get_current_user_id()) {

                if (!PeepSo::is_admin()) {
                    $access = ' ((`pho_album_acc`=' . PeepSo::ACCESS_PRIVATE . ' AND `pho_owner_id`=' . get_current_user_id() . ') OR ' .
                        ' (`pho_album_acc`=' . PeepSo::ACCESS_MEMBERS . ') OR (`pho_album_acc`<=' . PeepSo::ACCESS_PUBLIC . ') ';
                
                    if (class_exists('PeepSoFriendsPlugin')) {
                        $join = ' LEFT JOIN `' . $wpdb->prefix . PeepSoFriendsPlugin::TABLE  . '` `friends` ON ' .
                                        ' (`fnd_user_id` = `' . $wpdb->posts . '`.`post_author` AND `fnd_friend_id`=%1$d) ' .
                                        ' OR (`fnd_user_id` = %1$d AND `fnd_friend_id`=`' . $wpdb->posts . '`.`post_author`) ' .
                                        ' OR (`fnd_user_id` = %1$d AND `fnd_friend_id`=`pho_owner_id`) ' .
                                        ' OR (`fnd_user_id` = `pho_owner_id` AND `fnd_friend_id` = %1$d) ';

                        $clauses['join'] .= sprintf($join, get_current_user_id());

                        $access .= " OR (`pho_album_acc`=" . PeepSoFriendsPlugin::ACCESS_FRIENDS . " AND IF(`friends`.`fnd_user_id` IS NOT NULL, TRUE, FALSE) )";
                    }
                    
                    $access .= ') ';
                }
			}
        } else if (is_user_logged_in() && $module_id != 0) {
            // MEMBERS
            $access = ' (`pho_album_acc`<=' . PeepSo::ACCESS_MEMBERS . ' ) ';
        } else {
            // PUBLIC
            $access = ' (`pho_album_acc`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';
        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

		if (!empty($access)) {
			$sql .= ' AND ' . $access;
		}
        $sql .= " GROUP BY `pho_album_id` ORDER BY `".$wpdb->prefix.self::TABLE."`.`pho_system_album` {$sort}, `".$wpdb->posts."`.`post_date` {$sort} LIMIT {$offset}, {$limit}";

        $prep = $wpdb->prepare($sql, $user_id, $module_id);
        
        $photo_album = $wpdb->get_results($prep);

        // todo:@set cover album
        //$photos = $this->get_thumbs($user_id, $photos);

        return ($this->set_photos_album($photo_album));
    }

    /**
     * todo:@docblock
     */
    public function get_num_photos_by_album($user_id, $album_id='', $module_id = 0)
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['join'] .=
            "  LEFT JOIN `{$wpdb->posts}` ON `{$wpdb->posts}`.`ID`=`pho_post_id` ";

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`{$wpdb->posts}`.`ID`";

        $clauses['where'] .=
            " WHERE `" . $wpdb->prefix . PeepSoPhotosModel::TABLE . "`.`pho_album_id` = %d ";

        if(intval($user_id) !== 0 && intval($module_id) === 0) {
            $clauses['where'] .=
                " AND `" . $wpdb->prefix . PeepSoPhotosModel::TABLE . "`.`pho_owner_id` = %d ";
        }

        $clauses['where'] .=
            " AND `" . $wpdb->prefix . PeepSoPhotosModel::TABLE . "`.`pho_module_id` = %d ";

        $clauses['where'] .=
            " AND `act`.`act_module_id`=".PeepSoSharePhotos::MODULE_ID;

        // exclude other plugins photos from listing
        $widgets = FALSE;
        $clauses = apply_filters('peepso_photos_post_clauses', $clauses, $module_id, $widgets);

        $sql = "SELECT COUNT(DISTINCT(`" . $wpdb->prefix . PeepSoPhotosModel::TABLE . "`.`pho_id`)) as num_photos  FROM `{$wpdb->prefix}" . PeepSoPhotosModel::TABLE . "` ";
        $access = '';

        // add checks for post's access
        if (is_user_logged_in() && $module_id == 0) {

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
        } else if (is_user_logged_in() && $module_id != 0) {
            // MEMBERS
            $access = ' (`act_access`<=' . PeepSo::ACCESS_MEMBERS . ' ) ';

            $clauses = apply_filters('peepso_photos_filter_owner_' . $module_id, $clauses);
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';

            $clauses = apply_filters('peepso_photos_filter_owner_' . $module_id, $clauses);
        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        if (!empty($access)) {
            $sql .= ' AND ' . $access;
        }

        if($module_id ==0) {
            $photos = $wpdb->get_results($wpdb->prepare($sql, $album_id, $user_id, $module_id));
        } else {
            $photos = $wpdb->get_results($wpdb->prepare($sql, $album_id, $module_id));
        }

        return $photos[0]->num_photos;
    }    

    /**
     * todo:docblock
     */
    public function check_album($user_id, $album_id)
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['where'] .=
            " WHERE `pho_owner_id` = %d ";

        $clauses['where'] .=
            " AND `pho_album_id` = %d ";        

        $sql = "SELECT COUNT(DISTINCT(`pho_album_id`)) as num_album  FROM `{$wpdb->prefix}" . self::TABLE . "` ";
        $access = '';

        // add checks for post's access
        if (is_user_logged_in()) {

            // PRIVATE and owner by current user id  - OR -
            // MEMBERS and user is logged in - OR -
            // PUBLIC
            if (!PeepSo::is_admin()) {
                $access = ' ((`pho_album_acc`=' . PeepSo::ACCESS_PRIVATE . ' AND `pho_album_acc`=' . get_current_user_id() . ') OR ' .
                    ' (`pho_album_acc`=' . PeepSo::ACCESS_MEMBERS . ') OR (`pho_album_acc`<=' . PeepSo::ACCESS_PUBLIC . ') ';

                $access .= ') ';
            }

        } else {
            // PUBLIC
            $access = ' (`pho_album_acc`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';
        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        if (!empty($access)) {
            $sql .= ' AND ' . $access;
        }

        $album = $wpdb->get_results($wpdb->prepare($sql, $user_id, $album_id));

        return ( intval($album[0]->num_album) > 0 ? TRUE : FALSE );
    }

    /**
     * todo:@docblock
     */
    public function get_album_photo($user_id, $album_id='', $offset = 0, $limit = 10, $sort = 'desc', $module_id = 0)
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['join'] .=
            "  LEFT JOIN `{$wpdb->posts}` ON `{$wpdb->posts}`.`ID`=`pho_post_id` ";

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`{$wpdb->posts}`.`ID`";

        $clauses['where'] .=
            " WHERE `" . $wpdb->prefix . PeepSoPhotosModel::TABLE . "`.`pho_album_id` = %d ";

        if(intval($user_id) !== 0 && intval($module_id) === 0) {
            $clauses['where'] .=
                " AND `" . $wpdb->prefix . PeepSoPhotosModel::TABLE . "`.`pho_owner_id` = %d ";
        }

        $clauses['where'] .=
            " AND `" . $wpdb->prefix . PeepSoPhotosModel::TABLE . "`.`pho_module_id` = %d ";

        $clauses['where'] .=
            " AND `act`.`act_module_id`=".PeepSoSharePhotos::MODULE_ID;

        // exclude other plugins photos from listing
        $widgets = FALSE;
        $clauses = apply_filters('peepso_photos_post_clauses', $clauses, $module_id, $widgets);
        

        $sql = "SELECT `{$wpdb->prefix}" . PeepSoPhotosModel::TABLE . "`.* FROM `{$wpdb->prefix}" . PeepSoPhotosModel::TABLE . "` ";
        $access = '';

        // add checks for post's access
        if (is_user_logged_in() && $module_id === 0) {

            if(get_current_user_id() != $user_id ) {
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

            $clauses = apply_filters('peepso_photos_filter_owner_' . $module_id, $clauses);
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';

            $clauses = apply_filters('peepso_photos_filter_owner_' . $module_id, $clauses);
        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        if (!empty($access)) {
            $sql .= ' AND ' . $access;
        }
        $sql .= " ORDER BY `{$wpdb->posts}`.`post_date` {$sort} LIMIT {$offset}, {$limit} ";

        if(intval($user_id) !== 0) {
            $photos = $wpdb->get_results($wpdb->prepare($sql, $album_id, $user_id, $module_id));
        } else {
            $photos = $wpdb->get_results($wpdb->prepare($sql, $album_id, $module_id));
        }

        if (!empty($photos)) {
            $user = PeepSoUser::get_instance($user_id);
            $image_dir = $user->get_image_url() . 'photos/';

            $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');
            foreach ($photos as &$photo) {
                $location = NULL;
                if ('1' === $photo->pho_stored && $enable_aws_s3) {
                    $location = $photo->pho_token;
                }
                if (NULL === $location || 0 === strlen($location)) {
                    $image_dir = apply_filters('peepso_post_photos_location',  $image_dir, $photo->pho_post_id, '');
                    $location = $image_dir . $photo->pho_filesystem_name;
                }
                $photo->location = $location;
            }
        }

        $photos = $this->get_thumbs($user_id, $photos);        

        return $photos;
    }    

    public function get_thumbs($user_id, $photos)
    {
        $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');

        $user = PeepSoUser::get_instance($user_id);
        $image_dir = $user->get_image_url() . 'photos/thumbs/';

        //print_r($photos);

        foreach($photos as &$photo) {

            if(strlen($photo->pho_thumbs)) {

                $thumbs = json_decode($photo->pho_thumbs, true);
                
                if($photo->pho_stored && $enable_aws_s3) {

                    // S3

                } else {

                    foreach($thumbs as $key=>$thumb) {
                        $image_dir = apply_filters('peepso_post_photos_location',  $image_dir, $photo->pho_post_id, 'thumbs');
                        
                        if (strpos($thumb, 'https://') !== FALSE) {
							$local_files = explode('/', $thumb);
							$thumbs[$key] = $image_dir . end($local_files);
						} else {
							$thumbs[$key] = $image_dir . $thumb;
						}
                    }
                }

            } else {
                $thumbs = array(
                    's_s' => $photo->location,
                );
            }

            $photo->pho_thumbs = $thumbs;
        }



        return $photos;
    }    

    /**
     * todo:@docblok
     */
    public function get_photo_album_id($user_id='', $is_system=0, $post_id = 0, $module_id = 0)
    {
        global $wpdb;

        $clauses=array();
        $clauses['join'] = '';
        $clauses['where'] = " WHERE `pho_owner_id` = %d ";

        $clauses['where'] = $clauses['where'] . " AND `pho_system_album` = %d ";

        $clauses['where'] = $clauses['where'] . " AND `pho_module_id` = %d ";

        if(!empty($post_id)) {
            $clauses['where'] = $clauses['where'] . " AND `pho_post_id` = %d ";
        }

        $sql = "SELECT DISTINCT(`pho_album_id`) as `album_id`  FROM `{$wpdb->prefix}" . self::TABLE . "` ";

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        if(!empty($post_id)) {
            $album = $wpdb->get_results($wpdb->prepare($sql, $user_id, $is_system, $module_id, $post_id));
        } else {
            $album = $wpdb->get_results($wpdb->prepare($sql, $user_id, $is_system, $module_id));
        }

        return isset($album[0]->album_id)?$album[0]->album_id:FALSE;
    }

    /**
     * Return a row from the photos album table.
     * @param  int $album_id The ID of the photo to retrieve.
     * @return array
     */
    public function get_album($album_id, $owner_id)
    {
        global $wpdb;

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    WHERE `pho_album_id`=%d AND `pho_owner_id`=%d";

        return ($wpdb->get_row($wpdb->prepare($sql, $album_id, $owner_id)));
    }    

    /**
     * Return a row from the photos album table.
     * @param  int $album_id The ID of the photo to retrieve.
     * @return object
     */
    public function get_album_by_id($album_id)
    {
        global $wpdb;

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    WHERE `pho_album_id`=%d";

        return ($wpdb->get_row($wpdb->prepare($sql, $album_id)));
    }    

    /**
     * Return a row from the photos album table.
     * @param  int $post_id The post ID.
     * @param  int $owner_id The user ID.
     * @return array
     */
    public function get_album_by_post($post_id, $owner_id = 0)
    {
        global $wpdb;

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
                    WHERE `pho_post_id`=%d";
        if ($owner_id > 0) {
            $sql .= " AND `pho_owner_id`=%d";
        } 

        return ($wpdb->get_row($wpdb->prepare($sql, $post_id, $owner_id)));
    }    


    /**
     * todo:@docblok
     */
    public function get_photo_album($user_id='', $album_id=0, $post_id=0, $module_id=0)
    {
        global $wpdb;

        $clauses=array();
        $clauses['join'] = '';
        $clauses['where'] = " WHERE `pho_owner_id` = %d ";

        $clauses['where'] = $clauses['where'] . " AND `pho_module_id` = %d ";

        if(!empty($album_id)) {
            $clauses['where'] = $clauses['where'] . " AND `pho_album_id` = $album_id ";
        }

        if(!empty($post_id)) {
            $clauses['where'] = $clauses['where'] . " AND `pho_post_id` = $post_id ";
        }

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` ";

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        $album = $wpdb->get_results($wpdb->prepare($sql, $user_id, $module_id));

        return $album;
    }    
	
    /**
     * todo:@docblok
     */
	public function set_photo_album_name($name, $album_id)
	{
		global $wpdb;

		$album_data['pho_album_name'] = $name;

		$wpdb->update($wpdb->prefix . self::TABLE, $album_data, array('pho_album_id' => $album_id));
	}
	
	/**
     * todo:@docblok
     */
	public function set_photo_album_description($description, $album_id)
	{
		global $wpdb;

		$album_data['pho_album_desc'] = $description;

		$wpdb->update($wpdb->prefix . self::TABLE, $album_data, array('pho_album_id' => $album_id));
	}
	
	/**
     * todo:@docblok
     */
	public function set_photo_album_acc($acc, $album_id, $owner)
	{
		global $wpdb;
		
		$activity = new PeepSoActivity();

		$album_data = $this->get_album($album_id, $owner);
		$act_data = $activity->get_activity_data($album_data->pho_post_id, PeepSoSharePhotos::MODULE_ID);
		
		// update album privacy
		$wpdb->update($wpdb->prefix . self::TABLE, array('pho_album_acc' => $acc), array('pho_album_id' => $album_id));
		
		// update photo privacy
		$wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix . PeepSoActivity::TABLE_NAME . " SET `act_access` = %d WHERE `act_external_id` IN (SELECT `pho_id` FROM " . $wpdb->prefix . PeepSoPhotosModel::TABLE . " WHERE `pho_album_id` = %d)", array($acc, $album_id)));
		
		// update activity privacy
		$wpdb->update($wpdb->prefix . PeepSoActivity::TABLE_NAME, array('act_access' => $acc), array('act_id' => $act_data->act_id));
	}

	/**
	 * Set photo album iterator
	 * @param array $photo_album List of photos
	 * @return array $photos Unmodified photos
	 */
	public function set_photos_album($photo_album)
	{
		$photos_object = new ArrayObject($photo_album);
		$this->_iterator = $photos_object->getIterator();

		return ($photo_album);
	}    

	/**
	 * Get photo album iterator
	 * @return ArrayObject list of photos album in object form
	 */
	public function get_iterator()
	{
		return ($this->_iterator);
	}	
}

// EOF
