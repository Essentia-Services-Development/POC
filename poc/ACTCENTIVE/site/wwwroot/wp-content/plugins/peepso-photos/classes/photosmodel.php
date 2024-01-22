<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3UThyVWcwWTlmTlBzWnFsWGhDbVNCejFNK0M1OVhORFcwd2xKcXF2cWR1K0pYSXNaRWRZYkZTNTBlaktod2FRZEFKYVV1VWVReXhSUTFBMngxUGFXamxNZUJhZGsyb3Qxd0ZyY2NkZTFDSjBPdGYwRzN3dEhOcXVXUHFlMzIvK2xBPQ==*/

class PeepSoPhotosModel
{
	public static $notices = array();				// error messages to be returned to user
	public $last_orientation = NULL;
	private $_aws_error = NULL;						// last error from AWS

    public $_iterator;

    private  $thumb_settings = array(
        's_s' => array( 256, 256, TRUE ),
        'm_s' => array( 512, 512, TRUE ),
        #'l_s' => array( 1024, 1024, TRUE ),

        #'s' => array( 128, 0, FALSE ),
        'm' => array( 512, 0, FALSE ),
        'l' => array( 1024, 0, FALSE ),
    );

	const TABLE = 'peepso_photos';

	public function get_thumb_settings() {
	    return apply_filters('peepso_filter_photos_thumb_settings', $this->thumb_settings);
    }

	/**
	 * Save images/photos including uploading to Amazon S3
	 * @param int $post_id The post ID
	 * @param int $act_id The activity ID
	 */
	public function save_images(Array $files, $post_id, $act_id, $album_id = 0)
	{
		global $wpdb;

		$image_dir = $this->get_photo_dir();

		if (!is_dir($image_dir)) {
			mkdir($image_dir, 0755, TRUE);
		}

		$photo_album = new PeepSoPhotosAlbumModel();

		if(empty($album_id)) {
			$album_id = $photo_album->get_photo_album_id(get_current_user_id(), PeepSoSharePhotos::ALBUM_STREAM);
			$album_id = apply_filters('peepso_photos_stream_photos_album', $album_id);
		}

		$input = new PeepSoInput();
		$module_id = $input->int('module_id', 0);

		$photo_data = array(
            'pho_post_id'   => $post_id,
            'pho_owner_id'  => get_current_user_id(),
            'pho_album_id' 	=> $album_id,
            'pho_module_id'	=> $module_id,
            );

		// Get activity row data to sync access and IP address
		$activities = PeepSoActivity::get_instance();

		$post_activity = $activities->get_activity($act_id);

		if (NULL !== $post_activity) {
			$post_activity = (array) $post_activity;
			unset($post_activity['act_id']);
		} else {
			new PeepSoError('[PHOTOS] No activity record for post with ID ' . $post_id);
			// TODO: exit at this point? No sense continuing...
		}

		$enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');
		$tmp_folder = $this->get_tmp_dir();
		foreach ($files as $file) {
			// Filesystem protection
			if(strstr($file,'..')) { continue; }

			$tmp_file = $tmp_folder . $file;
			$filename = $file;
            $filetype = wp_check_filetype($tmp_file);

			$image = wp_get_image_editor($tmp_file);

			if (is_wp_error($image)) {
				self::$notices[] = $image->get_error_message();
				continue;
			}


			$dest_file = $image_dir . $filename;
			$image->save($dest_file);

            $files_to_save = array($dest_file);

            $temp= wp_get_image_editor($dest_file);
            $temp->resize(1024,1024);

            $thumbs_new = $this->generate_thumbs($filename, $filetype['ext'], $temp, NULL);

            $files_to_save = array_merge($files_to_save, $thumbs_new);

			unlink($tmp_file);

			$gif_file = str_replace('.jpg', '.gif', $tmp_file);
			if (is_file($gif_file)) {
				$dest_file = str_replace('.jpg', '.gif', $dest_file);
				copy($gif_file, $dest_file);
				unlink($gif_file);
				$files_to_save['gif'] = $dest_file;
			}

			$photo_data['pho_file_name'] = $file;
			$photo_data['pho_orig_name'] = $file;
			$photo_data['pho_filesystem_name'] = $filename;
			$photo_data['pho_size'] = filesize($dest_file);
            $photo_data['pho_ext'] = $filetype['ext'];
			$thumbs = [];

			$aws_fallback = true;
			if ($enable_aws_s3) {
                $i=0;
                foreach ($files_to_save as $key=> $file) {
                    $pho_token = $this->upload_to_amazon_s3($file);
                    if (NULL !== $pho_token) {
						$aws_fallback = false;

                        // First item is the photo itself
                        if($i==0) {
                            $photo_data['pho_token'] = $pho_token;
                            $photo_data['pho_stored'] = 1;
                        } else {
                            // the rest are thumbs
                            $thumbs[$key] = $pho_token;
                        }

						$i++;


						// remove local file
						if (PeepSo::get_option('photos_aws_s3_not_keep', 0)) {
							@unlink($file);
						}
                    } else {
                        // persist the AWS error
                        $aws_errors = new PeepSoPhotosAWSErrors();
                        $aws_errors->add_error($this->_aws_error);
                    }
                }
			}

			if ($aws_fallback) {
                // if no S3, just get filenames into an array
                foreach($thumbs_new as $key=>$thumb) {
                    $thumbs[$key] = basename($thumb);
                }
            }

			// json encode for storage
            $photo_data['pho_thumbs'] = json_encode($thumbs);


			$wpdb->insert($wpdb->prefix . self::TABLE, $photo_data);

			if (NULL !== $post_activity) {
				// add data to Activity Stream data table
				$act_data = $post_activity;
				$act_data['act_external_id'] = $wpdb->insert_id;
				$act_data['act_module_id'] = PeepSoSharePhotos::MODULE_ID;
				$act_data['act_description'] = (NULL !== $act_data['act_description']) ? $act_data['act_description'] : '';

				$wpdb->insert($wpdb->prefix . PeepSoActivity::TABLE_NAME, $act_data);
			}
		}
	}

	/**
	 * Save images/photos comment
	 * @param string $file
	 * @param int $post_id The post ID
	 * @param int $act_id The activity ID
	 */
	public function save_images_comment($file, $post_id, $act_id)
	{
		// Filesystem protection
		if(strstr($file,'..')) { return; }

		$image_dir = $this->get_photo_dir();

		if (!is_dir($image_dir)) {
			mkdir($image_dir, 0755, TRUE);
		}

		$tmp_folder = $this->get_tmp_dir();

		$tmp_file = $tmp_folder . $file;
		$filename = $file;
        $filetype = wp_check_filetype($tmp_file);

		$image = wp_get_image_editor($tmp_file);

		if (is_wp_error($image)) {
			self::$notices[] = $image->get_error_message();
			return;
		}

		$dest_file = $image_dir . $filename;
		$image->save($dest_file);
		$this->fix_image_orientation($image, $dest_file);	// reorient once copied - so we have write access

        $temp= wp_get_image_editor($dest_file);
        $temp->resize(1024,1024);

        $thumbs_new = $this->generate_thumbs($filename, $filetype['ext'], $temp);

		unlink($tmp_file);

		$gif_file = str_replace('.jpg', '.gif', $tmp_file);
		if (is_file($gif_file)) {
			$dest_file = str_replace('.jpg', '.gif', $dest_file);
			copy($gif_file, $dest_file);
			unlink($gif_file);
			$thumbs_new['gif'] = $dest_file;
		}

		foreach($thumbs_new as $key=>$thumb) {
            $thumbs[$key] = basename($thumb);
        }

        $photo = new stdClass();
        $photo->stored = 0;
        $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');
        if ($enable_aws_s3) {
            $photo->stored = 1;
            $files_to_save = array_merge([$file], $thumbs_new);

            $i=0;
            foreach ($files_to_save as $key=> $file_to_save) {

                // First item is the photo itself
                if($i==0) {
                    $file_to_save = $image_dir . $file_to_save;
                }
                $pho_token = $this->upload_to_amazon_s3($file_to_save);
                if (NULL !== $pho_token) {
                    // First item is the photo itself
                    if($i==0) {
                        $file = $pho_token;
                    } else {
                        // the rest are thumbs
                        $thumbs[$key] = $pho_token;
                    }
                    $i++;

                    // remove local file
                    if (PeepSo::get_option('photos_aws_s3_not_keep', 0)) {
                        @unlink($file_to_save);
                    }
                } else {
                    // persist the AWS error
                    $aws_errors = new PeepSoPhotosAWSErrors();
                    $aws_errors->add_error($this->_aws_error);
                }
            }
        }
        $photo->filesystem_name = $file;
        $photo->thumbs = $thumbs;

		// json encode for storage
        $photo = json_encode($photo);
        add_post_meta($post_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_COMMENTS, $photo, true);
	}

	/**
	 * Save images/photos avatar including uploading to Amazon S3
	 * @param int $post_id The post ID
	 * @param int $act_id The activity ID
	 */
	public function save_images_profile($file, $post_id, $act_id, $album=PeepSoSharePhotos::ALBUM_STREAM)
	{
		global $wpdb;

		// Get activity row data to sync access and IP address
		$activities = PeepSoActivity::get_instance();
		$post_activity = $activities->get_activity($act_id);

		$user = PeepSoUser::get_instance($post_activity->act_owner_id);
		$image_dir = $this->get_photo_dir($post_activity->act_owner_id);

		if (!is_dir($image_dir)) {
			mkdir($image_dir, 0755, TRUE);
		}

		$input = new PeepSoInput();
		$module_id = $input->int('module_id', 0);

		$photo_album = new PeepSoPhotosAlbumModel();
		$album_id = $photo_album->get_photo_album_id($user->get_id(), $album);
		$pho_owner_id = $user->get_id();

		if($module_id !== 0) {
			$album_id = apply_filters('peepso_photos_profile_photos_album', $album_id, $album);
			$user = NULL;
		}

		$photo_data = array(
            'pho_post_id'   => $post_id,
            'pho_owner_id'  => $pho_owner_id,
            'pho_album_id' 	=> $album_id,
            'pho_module_id'	=> $module_id,
            );

		// Get activity row data to sync access and IP address
		$activities = PeepSoActivity::get_instance();

		$post_activity = $activities->get_activity($act_id);

		if (NULL !== $post_activity) {
			$post_activity = (array) $post_activity;
			unset($post_activity['act_id']);
		} else {
			new PeepSoError('[PHOTOS] No activity record for post with ID ' . $post_id);
			// TODO: exit at this point? No sense continuing...
		}

		$enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');

		$tmp_file = $file;
		$filetype = wp_check_filetype($tmp_file);
		$new_filename = md5(microtime()) . '.' . $filetype['ext'];
		$filename = $new_filename;

		$image = wp_get_image_editor($tmp_file);

		if (is_wp_error($image)) {
			self::$notices[] = $image->get_error_message();
			return;
		}
//			$image = $this->fix_image_orientation($image, $tmp_file);
//			$this->fix_image_orientation($image, $tmp_file);	// moved down to after $image->save()

		// TODO: let's do some testing on this. If the photo grows a little bit I think it's fine. As long as:
		// 1) There is *always* a test, server-side for image size and dimensions
		// 2) The messages about 'upload quota reached' is consistent, whether it's the uploaded image or here on the modified image.
		// Temporarily disable this line, is there really a need to check? it already passed the validation.
		// There's a scenario where this will fail because the uploaded file is always modified and there's a chance that it becomees greater than the original uploaded file
		// TODO: use if (!fn()) - compares take time
//			if (FALSE === $this->photo_size_can_fit(get_current_user_id(), filesize($tmp_file))) {
//				self::$notices[] = __('Maximum file upload quota reached, delete posts with photos to free some space.', 'picso');
//				continue;
//			}

		$dest_file = $image_dir . $filename ;
		$image->save($dest_file);
		$this->fix_image_orientation($image, $dest_file);	// reorient once copied - so we have write access

        $files_to_save = array($dest_file);

        $temp= wp_get_image_editor($dest_file);
        $temp->resize(1024,1024);

        $thumbs_new = $this->generate_thumbs($filename, $filetype['ext'], $temp, $user);

        $files_to_save = array_merge($files_to_save, $thumbs_new);

		$photo_data['pho_file_name'] = $filename;
		$photo_data['pho_orig_name'] = $filename;
		$photo_data['pho_filesystem_name'] = $filename;
		$photo_data['pho_size'] = filesize($dest_file);
        $photo_data['pho_ext'] = $filetype['ext'];

		$aws_fallback = true;
		if ($enable_aws_s3) {
            $i=0;
            foreach ($files_to_save as $key=> $file) {
                $pho_token = $this->upload_to_amazon_s3($file);
                if (NULL !== $pho_token) {
					$aws_fallback = false;

                    // First item is the photo itself
                    if($i==0) {
                        $photo_data['pho_token'] = $pho_token;
                        $photo_data['pho_stored'] = 1;
                    } else {
                        // the rest are thumbs
                        $thumbs[$key] = $pho_token;
                    }

                    $i++;
                } else {
                    // persist the AWS error
                    $aws_errors = new PeepSoPhotosAWSErrors();
                    $aws_errors->add_error($this->_aws_error);
                }
            }
        }

		if ($aws_fallback) {
			// if no S3, just get filenames into an array
			foreach($thumbs_new as $key=>$thumb) {
				$thumbs[$key] = basename($thumb);
			}
		}

        // json encode for storage
        $photo_data['pho_thumbs'] = json_encode($thumbs);


		$wpdb->insert($wpdb->prefix . self::TABLE, $photo_data);

		if (NULL !== $post_activity) {
			// add data to Activity Stream data table
			$act_data = $post_activity;
			$act_data['act_external_id'] = $wpdb->insert_id;
			$act_data['act_module_id'] = PeepSoSharePhotos::MODULE_ID;
			$act_data['act_description'] = (NULL !== $act_data['act_description']) ? $act_data['act_description'] : '';

			$wpdb->insert($wpdb->prefix . PeepSoActivity::TABLE_NAME, $act_data);
		}
	}

    public function generate_thumbs($filename, $filetype, $image, $user=NULL, $required=NULL){

        $filename = str_replace('.'.$filetype, '', $filename);
     	if($user !== NULL) {
        	$image_dir = $user->get_image_dir().'photos'. DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR;
        }else{
        	$image_dir = $this->get_photo_dir().'thumbs' . DIRECTORY_SEPARATOR;
        }

        if (!is_dir($image_dir)) {
            mkdir($image_dir, 0755, TRUE);
		}
		
		add_filter('image_resize_dimensions', function($default, $orig_w, $orig_h, $new_w, $new_h, $crop){
			if (!$crop) {
				return null;
			}
		 
			$aspect_ratio = $orig_w / $orig_h;
			$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);
		 
			$crop_w = round($new_w / $size_ratio);
			$crop_h = round($new_h / $size_ratio);
		 
			$s_x = floor( ($orig_w - $crop_w) / 2 );
			$s_y = floor( ($orig_h - $crop_h) / 2 );
		 
			return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );
		}, 99, 6);

        $thumbs = array();

		$thumb_quality = PeepSo::get_option('photos_quality_thumb', 75);
        $thumb_settings = $this->get_thumb_settings();
        foreach($thumb_settings as $key => $settings) {

            $thumb_file = $filename ."_". $key . '.' . $filetype;
            $thumb_dest = $image_dir . $thumb_file;

            if(NULL==$required || (is_array($required) && in_array($key, $required))) {
                if (!file_exists($thumb_dest)) {
                    $image->save($thumb_dest);

                    $image_thumb = wp_get_image_editor($thumb_dest);
                    $image_thumb->set_quality($thumb_quality);
                    $image_thumb->resize($settings[0], $settings[1], $settings[2]);
                    $image_thumb->save($thumb_dest);
                }
            }

            $thumbs[$key] = $thumb_dest;
        }

        return $thumbs;
    }

	/**
	 * Checks if a photo of "$size" can still be uploaded
	 * @param  int $user_id The user ID
	 * @param  int $size The file size in bytes
	 * @return boolean Returns TRUE if there's sufficient space for the photos to be uploaded
	 */
	public function photo_size_can_fit($user_id, $size)
	{
		if (PeepSo::is_admin()) {
            return TRUE;
        }
		$total_filesize = $this->get_user_total_filesize($user_id);

		$allowed_user_space = PeepSo::get_option('photos_allowed_user_space');
		if (intval($allowed_user_space) === 0) {
			return true;
		}

		// convert to bytes
		$allowed_user_space = $allowed_user_space * 1048576;

		return ($total_filesize + $size < $allowed_user_space);
	}

	/**
	 * Return the total file size (in bytes) consumed by the user
	 * @param  int $user_id The user ID
	 * @return int The file size in bytes
	 */
	public function get_user_total_filesize($user_id)
	{
		global $wpdb;

		$sql = 'SELECT SUM(`pho_size`)
					FROM `' . $wpdb->prefix . self::TABLE . '` `photos`
						LEFT JOIN `'. $wpdb->posts . '` `posts`
				  			ON `posts`.`ID` = `photos`.`pho_post_id`
					WHERE
				  		`posts`.`post_author` = %d';

		$result = $wpdb->get_col($wpdb->prepare($sql, $user_id));

		return (is_null($result[0]) ? 0 : $result[0]);
	}

	/**
	 * Get Amazon S3 object
	 * @return mixed object Aws\S3\S3Client if successful otherwise NULL if failed
	 */
	private function get_amazon_s3()
	{
		$aws_access_key_id = PeepSo::get_option('photos_aws_access_key_id');
		$aws_secret_access_key = PeepSo::get_option('photos_aws_secret_access_key');
		$aws_s3_bucket = PeepSo::get_option('photos_aws_s3_bucket');
		if (empty($aws_access_key_id) || empty($aws_secret_access_key) || empty($aws_s3_bucket)) {
			$this->_aws_error = __('Missing Amazon configuration.', 'picso');
			self::$notices[] = $this->_aws_error;
			new PeepSoError('[PHOTOS] '.$this->_aws_error);
			return (NULL);
		}

		// disable auto discovery for default config
		if (!defined('AWS_DISABLE_CONFIG_AUTO_DISCOVERY')) {
			define('AWS_DISABLE_CONFIG_AUTO_DISCOVERY', TRUE);
		}

		require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'lib/aws_v3/aws-autoloader.php');

		try {
			$s3 = new Aws\S3\S3Client(array(
				'version'     => 'latest',
				'region'      => PeepSo::get_option('photos_aws_bucket_location', 'us-east-1'),
				'credentials' => array(
					'key'    => $aws_access_key_id,
					'secret' => $aws_secret_access_key
				)
			));

			$bucket_exists = FALSE;
			$buckets = $s3->listBuckets();

			foreach ($buckets['Buckets'] as $bucket){
				if ($bucket['Name'] == $aws_s3_bucket) {
					$bucket_exists = TRUE;
				}
			}

            if (!$bucket_exists) {
                $error = 'Bucket ' . $aws_s3_bucket . ' is not exists.';
                throw new Exception($error);
            }

			$resp = $s3->getBucketAcl([
				'Bucket' => $aws_s3_bucket
			]);
		} catch (Aws\Exception\AwsException $e) {
			$this->_aws_error = $e->getMessage();
			new PeepSoError('[PHOTOS] '.$this->_aws_error);
			return NULL;
		} catch (Exception $e) {
            $_aws_error = $e->getMessage();
            new PeepSoError('[PHOTOS] '.$_aws_error);
            return NULL;
        }

		return ($s3);
	}

	/**
	 * Upload object/file to Amazon S3 bucket
	 * @param  string $filepath absolute path of the file to be uploaded
	 * @return mixed public URL if successful otherwise NULL if upload failed
	 */
	public function upload_to_amazon_s3($filepath)
	{
		$s3 = $this->get_amazon_s3();
		if (NULL === $s3) {
			return (NULL);
		}

		// replace peepso absolute dir with just peepso
		// replace \ with / for windows environment
		$filename = str_replace('\\', '/', 'peepso/' . substr($filepath, strlen(PeepSo::get_peepso_dir())));

		try {
			$check = wp_check_filetype($filename);

			$result = $s3->putObject([
				'Bucket' => PeepSo::get_option('photos_aws_s3_bucket'),
				'Key'    => $filename,
				'Body'   => file_get_contents($filepath),
				'ACL'    => 'public-read',
				'ContentType' => $check['type']
			]);

			$metadata = $result->get('@metadata');
			if ($metadata['statusCode'] != 200) {
				$this->_aws_error = __('There was a problem when uploading the file', 'picso');
				new PeepSoError('[PHOTOS] '.$this->_aws_error);
			} else {
				return $metadata['effectiveUri'];
			}
		} catch (Aws\Exception\AwsException $e) {
			$this->_aws_error = $e->getMessage();
			new PeepSoError('[PHOTOS] '.$this->_aws_error);
		}

		return (NULL);
	}

	/**
	 * Remove objects/files from Amazon S3 bucket
	 * @param  array $files array of Key
	 * @return mixed deleted objects if successful otherwise NULL if remove failed
	 */
	public function remove_from_amazon_s3($files)
	{
		$s3 = $this->get_amazon_s3();

		if (NULL === $s3) {
			$error = __('There was an error setting up Amazon S3 library.', 'picso');
			new PeepSoError('[PHOTOS] '.$error);
			return (NULL);
		}

		try {
			foreach ($files as $file) {
				$url = parse_url($file);
				$objects[] = array('Key' => substr($url['path'], 1));
			}

			$result = $s3->deleteObjects(array(
				'Bucket' => PeepSo::get_option('photos_aws_s3_bucket'),
				'Delete' => array(
					'Objects' => $objects
				),
			));

			$metadata = $result->get('@metadata');
			if ($metadata['statusCode'] != 200) {
				$this->_aws_error = __('There was a problem when deleting the file', 'picso');
				new PeepSoError('[PHOTOS] '.$this->_aws_error);
			} else {
				return TRUE;
			}
		} catch (Exception $e) {
			$error = sprintf(__('There was an error removing the file %1$s. Error: %2$s', 'picso'), $file, $e->getMessage());
			new PeepSoError('[PHOTOS] '.$error);
		}
		return (NULL);
	}

    /**
     * Return all photo entries associated to a post
     * @param  int $post_id The post ID
     * @return array $photos Unmodified photos
     */
    public function get_post_photos($post_id, $order='asc')
    {
        global $wpdb;
        $post = get_post($post_id);

        $sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
				LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`" . $wpdb->prefix . self::TABLE . "`.`pho_id` AND `act`.`act_module_id` = " . PeepSoSharePhotos::MODULE_ID .
            " WHERE (`pho_post_id` = %d) AND (`pho_owner_id` = `act_owner_id`) AND (`act_description` IS NOT NULL)";

		// add checks for post's access
		$access = '';

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

		if (!empty($access)) {
			$sql .= ' AND ' . $access;
		}

        $sql .= " GROUP BY `" . $wpdb->prefix . self::TABLE . "`.`pho_id`";

        $sql .= " ORDER BY `" . $wpdb->prefix . self::TABLE . "`.`pho_id` " . $order;

        $photos = $wpdb->get_results($wpdb->prepare($sql, $post_id));

        if (!empty($photos)) {
            $user = PeepSoUser::get_instance($post->post_author);
            $image_dir = $user->get_image_url() . 'photos/'; //DIRECTORY_SEPARATOR generated wrong slash


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

                $photo->ajax_file = explode('.', $photo->pho_file_name);
                $photo->ajax_file = $photo->ajax_file[0];
                $photo->ajax_id = apply_filters('peepso_post_photos_ajax_id', $post->post_author, $photo->pho_post_id);
                $photo->ajax_dir = apply_filters('peepso_post_photos_ajax_dir', 'users', $photo->pho_post_id);

            }

            $photos = $this->get_thumbs($user->get_id(), $photos);
        }



        return ($this->set_photos($photos));
    }

	/**
     * Return all photo entries associated to a album
     * @param  int $post_id The post ID
     * @return array $photos Unmodified photos
     */
    public function get_user_photos_by_album($user_id, $album_id, $offset = 0, $limit = 10, $sort = 'desc', $module_id = 0)
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->posts}` ON `pho_post_id`=`{$wpdb->posts}`.`ID` ";

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`" . $wpdb->prefix . self::TABLE . "`.`pho_id`";

        $clauses['where'] .=
            " WHERE `" . $wpdb->prefix . self::TABLE . "`.`pho_album_id` = %d ";

        if(intval($user_id) !== 0 && intval($module_id) === 0) {
	        $clauses['where'] .=
	            " AND `" . $wpdb->prefix . self::TABLE . "`.`pho_owner_id` = %d ";
        }

        $clauses['where'] .=
	           	" AND `" . $wpdb->prefix . self::TABLE . "`.`pho_module_id` = %d ";

        $clauses['where'] .=
                " AND `act`.`act_module_id`=".PeepSoSharePhotos::MODULE_ID;

        $clauses['where'] .=
                " AND (`{$wpdb->posts}`.`post_status`='publish' OR `{$wpdb->posts}`.`post_status`='pending') and `{$wpdb->posts}`.`post_title` <> ''";

		// exclude other plugins photos from listing
        $widgets = FALSE;
        $clauses = apply_filters('peepso_photos_post_clauses', $clauses, $module_id, $widgets);

        $sql = "SELECT `" . $wpdb->prefix . self::TABLE . "`.*, `{$wpdb->posts}`.*, act.* FROM `{$wpdb->prefix}" . PeepSoPhotosModel::TABLE . "` ";
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
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';
        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

		if (!empty($access)) {
			$sql .= ' AND ' . $access;
		}

		$sql .= " GROUP BY `" . $wpdb->prefix . self::TABLE . "`.`pho_id`";

		// #4856 - Fix photos sort order on the custom photo album page.
		$sql .= " ORDER BY `" . $wpdb->prefix . self::TABLE . "`.`pho_id` {$sort}";

        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
		}

        #echo $wpdb->prepare($sql, $album_id, $user_id, $module_id);

        if($module_id ==0) {
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

        return ($this->set_photos($photos));
    }

    /**
     * Return all photo entries associated to a post
     * @param  int $post_id The post ID
     * @return array $photos Unmodified photos
     */
    public function get_community_photos($offset = 0, $limit = 10, $sort = 'desc')
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['join'] .= " LEFT JOIN `{$wpdb->posts}` ON `act_external_id`=`{$wpdb->posts}`.`ID` ";
		$clauses['where'] .= " WHERE `act_module_id`=".PeepSoSharePhotos::MODULE_ID;
		$clauses['where'] .= " AND `{$wpdb->posts}`.`post_status`='publish'";
		$clauses['where'] .= " AND `{$wpdb->posts}`.`post_type`='peepso-post'";
		$clauses['where'] .= " AND `pho`.`pho_post_id` = `{$wpdb->posts}`.`ID`";
		$clauses['where'] .= " AND `pho`.`pho_id` IS NOT NULL";

		$sql = "SELECT 1 FROM `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` ";

		$widgets = TRUE;
		$module_id = 0;

		$clauses = apply_filters('peepso_photos_post_clauses', $clauses, $module_id, $widgets);

        // add checks for post's access
        if (is_user_logged_in()) {

            //$clauses = apply_filters('peepso_activity_post_clauses', $clauses);

            // PRIVATE and owner by current user id  - OR -
            // MEMBERS and user is logged in - OR -
            // PUBLIC
            $access = ' ((`act_access`=' . PeepSo::ACCESS_MEMBERS . ') OR (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ') ';

            // Hooked methods must wrap the string within a paranthesis
            #just view only for members and public privacy

            $access .= ') ';
        } else {
            // PUBLIC
            $access = ' (`act_access`<=' . PeepSo::ACCESS_PUBLIC . ' ) ';
        }

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        $sql .= ' AND ' . $access;

		$main_sql = "SELECT pho.* FROM {$wpdb->prefix}peepso_photos pho WHERE EXISTS ($sql)";

        $main_sql .= " ORDER BY `pho`.`pho_created` {$sort} LIMIT {$offset}, {$limit}";

		$photos = $wpdb->get_results($main_sql);

        $photos = $this->get_community_thumbs($photos);

        if (!empty($photos)) {
			$peepso_activity = new PeepSoActivity();
            $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');
            foreach ($photos as &$photo) {
            	$user = PeepSoUser::get_instance($photo->pho_owner_id);
        		$image_dir = $user->get_image_url() . 'photos/';

                $location = NULL;
                if ('1' === $photo->pho_stored && $enable_aws_s3) {
                    $location = $photo->pho_token;
                }
                if (NULL === $location || 0 === strlen($location)) {
                    $location = $image_dir . $photo->pho_filesystem_name;
                }
                $photo->location = $location;

				$activity = $peepso_activity->get_activity_data($photo->pho_id, PeepSoSharePhotos::MODULE_ID);
				if ($activity) {
					$photo->act_id = $activity->act_id;
				}
            }
        }

        return ($this->set_photos($photos));
    }

    /**
     * Return all photo entries associated to a post
     * @param  int $post_id The post ID
     * @return array $photos Unmodified photos
     */
    public function get_user_photos($user_id, $offset = 0, $limit = 10, $sort = 'desc', $module_id=0)
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->posts}` ON `" . $wpdb->prefix . self::TABLE . "`.`pho_post_id`=`{$wpdb->posts}`.`ID` ";

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`" . $wpdb->prefix . self::TABLE . "`.`pho_id`";

		$clauses['where'] .=
				" WHERE `act`.`act_module_id`=".PeepSoSharePhotos::MODULE_ID;

		if(intval($user_id) !== 0 && intval($module_id) === 0) {
        	$clauses['where'] .=
            	" AND `" . $wpdb->prefix . self::TABLE . "`.`pho_owner_id` = %d ";
		}

        $clauses['where'] .=
            " AND `" . $wpdb->prefix . self::TABLE . "`.`pho_module_id` = %d ";

		$clauses['where'] .=
				" AND (`{$wpdb->posts}`.`post_status`='publish' OR `{$wpdb->posts}`.`post_status`='pending') ";

		$clauses['where'] .=
			" AND `{$wpdb->posts}`.`post_type`='peepso-post' ";

		$sql = "SELECT `" . $wpdb->prefix . self::TABLE . "`.*, `{$wpdb->posts}`.*, act.* FROM `{$wpdb->prefix}" . self::TABLE . "` ";

		// exclude other plugins photos from listing
		$widgets = FALSE;
		$clauses = apply_filters('peepso_photos_post_clauses', $clauses, $module_id, $widgets);
		$access = '';

        // add checks for post's access
        if (is_user_logged_in() && $module_id==0) {

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

        $sql .= " GROUP BY `" . $wpdb->prefix . self::TABLE . "`.`pho_id` ORDER BY `{$wpdb->posts}`.`post_date` {$sort}";

        if ($limit) {
            $sql .= " LIMIT {$offset}, {$limit}";
        }

        if($module_id ==0) {
        	$photos = $wpdb->get_results($wpdb->prepare($sql, $user_id, $module_id));
        } else {
        	$photos = $wpdb->get_results($wpdb->prepare($sql, $module_id));
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

        return ($this->set_photos($photos));
    }

    public function get_community_thumbs($photos)
    {
        $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');

        foreach($photos as &$photo) {

        	$user = PeepSoUser::get_instance($photo->pho_owner_id);
        	$image_dir = $user->get_image_url() . 'photos/thumbs/';

            if(strlen($photo->pho_thumbs)) {

                $thumbs = json_decode($photo->pho_thumbs, true);

                if($photo->pho_stored && $enable_aws_s3) {

                    // S3

                } else {
					if (is_null($thumbs)) {
						continue;
					}

                    foreach($thumbs as $key=>$thumb) {
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

    public function get_thumbs($user_id, $photos)
    {
        $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');

        $user = PeepSoUser::get_instance($user_id);
        $image_dir = $user->get_image_url() . 'photos/thumbs/';

        foreach($photos as &$photo) {

            if(strlen($photo->pho_thumbs)) {

                $thumbs = json_decode($photo->pho_thumbs, true);

                if($photo->pho_stored && $enable_aws_s3) {

                    // S3

                } else {
                	$image_dir = apply_filters('peepso_post_photos_location',  $image_dir, $photo->pho_post_id, 'thumbs');

                	if(count($thumbs)) {
                        foreach ($thumbs as $key => $thumb) {
							// aws  fallback
							if (strpos($thumb, 'https://') !== FALSE) {
								$local_files = explode('/', $thumb);
								$thumbs[$key] = $image_dir . end($local_files);
							} else {
								$thumbs[$key] = $image_dir . $thumb;
							}
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

    public function get_num_community_photos()
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['join'] .=
            "  LEFT JOIN `{$wpdb->posts}` ON `{$wpdb->posts}`.`ID`=`" . $wpdb->prefix . self::TABLE . "`.`pho_post_id` ";

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`{$wpdb->posts}`.`ID`";

        $clauses['where'] .=
            " AND `act`.`act_module_id`=".PeepSoSharePhotos::MODULE_ID;


        $sql = "SELECT COUNT(DISTINCT(`" . $wpdb->prefix . self::TABLE . "`.`pho_id`)) as num_photos  FROM `{$wpdb->prefix}" . self::TABLE . "` ";

        $module_id = 0;
        $widgets = TRUE;
        $clauses = apply_filters('peepso_photos_post_clauses', $clauses, $module_id, $widgets);

        // add checks for post's access
        if (is_user_logged_in()) {

            //$clauses = apply_filters('peepso_activity_post_clauses', $clauses);

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

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

        $sql .= ' AND ' . $access;


        $photos = $wpdb->get_results($sql);

        return $photos[0]->num_photos;
    }

    public function get_num_photos($user_id)
    {
        global $wpdb;

        $clauses=array('join'=>'', 'where'=>'');

        $clauses['join'] .=
            "  LEFT JOIN `{$wpdb->posts}` ON `{$wpdb->posts}`.`ID`=`" . $wpdb->prefix . self::TABLE . "`.`pho_post_id` ";

        $clauses['join'] .=
            " LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act` ON `act`.`act_external_id`=`{$wpdb->posts}`.`ID`";

        $clauses['where'] .=
            " WHERE `" . $wpdb->prefix . self::TABLE . "`.`pho_owner_id` = %d ";

        $clauses['where'] .=
            " AND `act`.`act_module_id`=".PeepSoSharePhotos::MODULE_ID;

        $module_id = 0;
        $widgets = TRUE;
        $clauses = apply_filters('peepso_photos_post_clauses', $clauses, $module_id, $widgets);


        $sql = "SELECT COUNT(DISTINCT(`" . $wpdb->prefix . self::TABLE . "`.`pho_id`)) as num_photos  FROM `{$wpdb->prefix}" . self::TABLE . "` ";
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

        $sql .= $clauses['join'];

        $sql .= $clauses['where'];

		if (!empty($access)) {
			$sql .= ' AND ' . $access;
		}

        $photos = $wpdb->get_results($wpdb->prepare($sql, $user_id));

        return $photos[0]->num_photos;
    }

	/**
	 * Set photo iterator
	 * @param array $photos List of photos
	 * @return array $photos Unmodified photos
	 */
	public function set_photos($photos)
	{
		$photos_object = new ArrayObject($photos);
		$this->_iterator = $photos_object->getIterator();

		return ($photos);
	}

	/**
	 * Return a row from the photos table.
	 * @param  int $photo_id The ID of the photo to retrieve.
	 * @return array
	 */
	public function get_photo($photo_id)
	{
		global $wpdb;

		$sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
					LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `pho_post_id`
					LEFT JOIN `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` `act`
						ON `act`.`act_external_id`=`pho_id` AND `act`.`act_module_id`=%d
					WHERE `pho_id`=%s";

		return ($wpdb->get_row($wpdb->prepare($sql, PeepSoSharePhotos::MODULE_ID, $photo_id)));
	}

	/**
	 * Return a row from the photos table.
	 * @param  int $photo_id The ID of the photo to retrieve.
	 * @return array
	 */
	public function get_photo_owner($post_id, $post_author)
	{
		global $wpdb;

		$sql = "SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "`
					LEFT JOIN `{$wpdb->posts}` `posts` ON `posts`.`ID` = `pho_post_id`
					WHERE `posts`.`ID`=%d and `posts`.`post_author`=%d";

		return ($wpdb->get_row($wpdb->prepare($sql, $post_id, $post_author)));
	}

	/**
	 * Get photo comments
	 */
	public function get_photo_comments($post)
	{
		$photos = array();
		$thumbs = array();
		$photo = get_post_meta($post->ID, PeepSoSharePhotos::POST_META_KEY_PHOTO_COMMENTS, true);
		if(!empty($photo))
		{
	        $user = PeepSoUser::get_instance($post->post_author);
	        $image_dir = $user->get_image_url() . 'photos/';
	        $thumb_dir = $image_dir . 'thumbs/';

            if(strlen($photo)) {

                $photos = json_decode($photo, true);
                $enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');
                if($enable_aws_s3 && isset($photos['stored']) && $photos['stored'] === 1){
                    $image_dir = '';
                    $thumb_dir = '';
                }

                foreach($photos['thumbs'] as $key=>$thumb) {
					if(!$enable_aws_s3 && isset($photos['stored']) && $photos['stored'] === 1){
						$local_file = explode('/', $thumb);
						$thumb = end($local_file);
					}

                	$thumbs[$key] = $thumb_dir . $thumb;
				}

				$filesystem_name = $photos['filesystem_name'];

				if(!$enable_aws_s3 && isset($photos['stored']) && $photos['stored'] === 1){
					$local_file = explode('/', $filesystem_name);
					$filesystem_name = end($local_file);
				}

            	$photos['thumbs'] = $thumbs;
            	$photos['original'] = $image_dir . $filesystem_name;
            }
		}

		return $photos;
	}

	/**
	 * Return the total number of photo entries associated to a post
	 * @param  int $post_id The post ID
	 * @return int Number of photos for post
	 */
	public function count_post_photos($post_id, $deep_search=TRUE)
	{
		global $wpdb;

		$sql = "SELECT COUNT(`" . $wpdb->prefix . self::TABLE . "`.`pho_id`)
				FROM `{$wpdb->prefix}" . self::TABLE . "`
				WHERE `pho_post_id` = %d";

		$total_photos = $wpdb->get_var($wpdb->prepare($sql, $post_id));

		// prevent count `0`, then select by pho_id
        if($total_photos == 0 && $deep_search) {
        	$sql = "SELECT `pho_post_id`
				FROM `{$wpdb->prefix}" . self::TABLE . "`
				WHERE `pho_id` = %d";

			$pho_post_id = $wpdb->get_var($wpdb->prepare($sql, $post_id));

			if($pho_post_id != 0) {
				$sql = "SELECT count(`" . $wpdb->prefix . self::TABLE . "`.`pho_id`)
					FROM `{$wpdb->prefix}" . self::TABLE . "`
					WHERE `pho_post_id` = %d";

				$total_photos = $wpdb->get_var($wpdb->prepare($sql, $pho_post_id));
			}
        }
		return ($total_photos);
	}

	/**
	 * Return the activity_ID for single upload photo
	 * @param  int $post_id The post ID
	 * @return int $activity_id
	 */
	public function get_photo_activity($post_id)
	{
		global $wpdb;

		$sql = "SELECT `act_id`
				FROM `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "`
				WHERE `act_external_id` = %d AND `act_module_id`= %d";
		$result = $wpdb->get_var($wpdb->prepare($sql, $post_id, PeepSoSharePhotos::MODULE_ID));

		return ($result);
	}

	/**
	 * Deletes photos associated to a post when it is deleted
	 * @param  int $post_id The post ID
	 */
	public function delete_content($post_id)
	{
		global $wpdb;

		$photos = $this->get_post_photos($post_id);
		$user_id = get_post_field('post_author', $post_id, 'raw');
		$user = PeepSoUser::get_instance($user_id);
		$enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');

		if (!empty($photos)) {
			foreach ($photos as $photo) {
				$this->delete_photo($photo);
			}
		} else {
			$activity = new PeepSoActivity();
			$act = $activity->get_activity_data($post_id);
			$this->delete_photo_comment($act);
		}

		// TODO: removing from S3 does not remove the database record. Update the record, setting `pho_stored` to 0 to indicate the photo is stored on the filesystem, not on S3
		$wpdb->delete($wpdb->prefix . self::TABLE, array('pho_post_id' => $post_id));
	}

	/**
	 * Deletes a single photo
	 * @return boolean
	 */
	public function delete_photo($photo)
	{
		global $wpdb;
		$activities = PeepSoActivity::get_instance();
		if(!PeepSo::is_admin()) {
			$post = $activities->get_post($photo->pho_post_id)->post;
		} else {
			// if peepso admin just using get_post to prevent error notice
			$post = get_post($photo->pho_post_id);
		}
		// $user = PeepSoUser::get_instance($post->post_author);
		// $image_dir = $user->get_image_dir() . 'photos' . DIRECTORY_SEPARATOR;
		$image_dir = $this->get_photo_dir($post->post_author);
		$image_thumb_dir = $image_dir . 'thumbs' . DIRECTORY_SEPARATOR;

		$wpdb->delete($wpdb->prefix . self::TABLE, array('pho_id' => $photo->pho_id));
		// Delete activities that may have reposted this photo
		$wpdb->delete($wpdb->prefix . PeepSoActivity::TABLE_NAME, array('act_repost_id' => $photo->act_id));

		if (is_string($photo->pho_thumbs)) {
			$photo->pho_thumbs = json_decode($photo->pho_thumbs);
		}

		$files = [];
        foreach($photo->pho_thumbs as $file) {
			$files[] = $file;

        	$thumb_url = strpos($file, $this->get_image_url($post->post_author)  . 'photos/');
        	if($thumb_url !== false) {
        		$file = str_replace($this->get_image_url($post->post_author) . 'photos/', $this->get_photo_dir($post->post_author), $file);
        	} else {
        		$file = $image_thumb_dir . $file;
        	}

            if (file_exists($file)) {
            	unlink($file);
            }
        }

		$enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');
		if ($enable_aws_s3 && $photo->pho_token) {
			$files[] = $photo->pho_token;
			$this->remove_from_amazon_s3($files);
		}

		return (@unlink($image_dir . $photo->pho_filesystem_name));
	}

	/**
	 * Deletes a single photo comment
	 * @return boolean
	 */
	public function delete_photo_comment($activity)
	{
		global $wpdb;

		$user = PeepSoUser::get_instance($activity->act_owner_id);
		$image_dir = $user->get_image_dir() . 'photos' . DIRECTORY_SEPARATOR;
		$image_thumb_dir = $image_dir . 'thumbs' . DIRECTORY_SEPARATOR;

		$photo = get_post_meta($activity->act_external_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_COMMENTS, true);
		$enable_aws_s3 = PeepSo::get_option('photos_enable_aws_s3');

		if(!empty($photo)) {
			if (is_string($photo)) {
				$photo = json_decode($photo, TRUE);
			}

			foreach($photo['thumbs'] as $file) {
				if (strpos($file, '/') !== FALSE) {
					$file = explode('/', $file);
					$file = end($file);
				}
	            // @TODO should not use "@"
				@unlink($image_thumb_dir.$file);
				#echo "unlink($file)";
			}

			// delete main photo
			if (strpos($photo['filesystem_name'], '/') !== FALSE) {
				$file = explode('/', $photo['filesystem_name']);
				$file = end($file);
			} else {
				$file = $photo['filesystem_name'];
			}

			@unlink($image_dir.$file);

			if ($enable_aws_s3) {
				$files = array_merge([$photo['filesystem_name']], array_values($photo['thumbs']));

				$this->remove_from_amazon_s3($files);
			}
	    }

	    @unlink($image_dir . $photo->filesystem_name);

	    $delete = delete_post_meta($activity->act_external_id, PeepSoSharePhotos::POST_META_KEY_PHOTO_COMMENTS);

		return ($delete);
	}

	/**
	 * Count posts by author id
	 * @param int $user_id Author's user id
	 * @param bool $today filter by post date to Today if TRUE, otherwise no post date filter
	 * @return int number of posts
	 */
	public function count_author_post($user_id, $today = FALSE)
	{
		global $wpdb;
		$sql = " SELECT COUNT(`" . $wpdb->prefix . self::TABLE . "`.`pho_id`)
				 FROM `{$wpdb->posts}`
				 RIGHT JOIN `{$wpdb->prefix}" . self::TABLE . "`
				 ON  `{$wpdb->posts}`.`ID` = `pho_post_id`
				 WHERE `post_author` = %s ";
		if ($today) {
			$sql .= ' AND DATE(`post_date`) = CURDATE() ';
		}

		return ($wpdb->get_var($wpdb->prepare($sql, $user_id)));
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
	 * Get photo directory for current user
	 * @return string $photo_dir Photo directory
	 */
	public function get_photo_dir($user_id = 0, $reset = false)
	{
		static $photo_dir = NULL; // used for caching to avoid multiple query when instantiating PeepSoUser
		if (NULL === $photo_dir || $reset) {
			$input = new PeepSoInput();
			$module_id = $input->int('module_id', 0);

			if($module_id === 0) {
				$user_id = ($user_id == 0) ? get_current_user_id() : $user_id;
				$user = PeepSoUser::get_instance($user_id);
				$photo_dir = ($user) ? $user->get_image_dir() : '';
				$photo_dir .= 'photos' . DIRECTORY_SEPARATOR;
			} else {
				$photo_dir = apply_filters('peepso_photos_dir_' . $module_id, $photo_dir);
			}
		}
		return ($photo_dir);
	}

	public function get_image_url($user_id = 0)
	{
		$input = new PeepSoInput();
		$module_id = $input->int('module_id', 0);

		$dir = '';
		if($module_id === 0) {
			$user_id = ($user_id == 0) ? get_current_user_id() : $user_id;
			$user = PeepSoUser::get_instance($user_id);
			$dir = PeepSo::get_peepso_uri() . 'users/' . $user->get_id() . '/';
		} else {
			$dir = apply_filters('peepso_photos_url_' . $module_id, $dir);
		}
		return ($dir);
	}

	/**
	 * Get photo url
	 * @return string $photo_dir Photo directory
	 */
	public function get_photo_thumbs_url($thumbs)
	{
		$photo_url = NULL;
		if (!empty($thumbs)) {
			$input = new PeepSoInput();
			$module_id = $input->int('module_id', 0);

			if($module_id === 0) {
				$user = PeepSoUser::get_instance(get_current_user_id());
				$photo_url = PeepSo::get_peepso_uri() . 'users/'.$user->get_id().'/photos/thumbs/' . $thumbs;
			} else {
				$photo_url = apply_filters('peepso_photos_thumbs_url_' . $module_id, $photo_url, $thumbs);
			}
		}
		return ($photo_url);
	}

	/**
	 * Get photo temporary directory
	 * @return string Temporary photo directory
	 */
	public function get_tmp_dir()
	{
		return ($this->get_photo_dir() . 'tmp' . DIRECTORY_SEPARATOR);
	}

	/**
	 * Get photo thumbs directory
	 * @return string Thumbs photo directory
	 */
	public function get_thumbs_dir()
	{
		return ($this->get_photo_dir() . 'thumbs' . DIRECTORY_SEPARATOR);
	}

	/**
	 * Get photo iterator
	 * @return ArrayObject list of photos in object form
	 */
	public function get_iterator()
	{
		return ($this->_iterator);
	}

	/**
	 * Change a Photo post's privacy setting and all photos posted under it.
	 * @param  int $post_id The post ID
	 * @param  int $act_access The activity level to set
	 */
	public function update_post_photos_privacy($post_id, $act_access)
	{
		// TODO: do we need to check ownership/permissions on this? Not sure of the context of this method's use and want to make sure this cannot be abused
		global $wpdb;

		$sql = "UPDATE `{$wpdb->prefix}" . PeepSoActivity::TABLE_NAME . "` SET `act_access`=%d
				WHERE `act_module_id`=" . PeepSoSharePhotos::MODULE_ID . " AND `act_external_id` IN (
					SELECT `pho_id` FROM `{$wpdb->prefix}" . self::TABLE . "` WHERE `pho_post_id`=%d
				)";

		$wpdb->query($wpdb->prepare($sql, $act_access, $post_id));
	}

	/**
	 * Fix image orientation
	 * @param object $image WP_Image_Editor
	 * @param string $image_file Image filename/path
	 * @return object $image WP_Image_Editor
	 */
	public function fix_image_orientation(&$image, $image_file)
	{
		// @Since 1.7.4 the EXIF PHP extension is required
		// http://php.net/manual/en/function.exif-imagetype.php
		if (!function_exists('exif_read_data')) {
			return;
		}
		$exif = @exif_read_data($image_file);
		$orientation = isset($exif['Orientation']) ? $exif['Orientation'] : 0;

		$this->last_orientation = $orientation;

		$resave = FALSE;
		switch ($orientation)
		{
		case 3:
			$image->rotate(180);
			$resave = TRUE;
			break;
		case 6:
			$image->rotate(-90);
			$resave = TRUE;
			break;
		case 8:
			$image->rotate(90);
			$resave = TRUE;
			break;
		}
		if ($resave) {				// resave here if image was rotated
			$image->save();
		}
//		return ($image); // no need to return, passed in by reference
	}

	public function imagick_strip($file) {
	    if(class_exists('Imagick') && file_exists($file)) {
                $imagick = new Imagick($file);
                $imagick->stripImage();
                $imagick->writeImage();
        }

	}

	/**
	 * Get database timestamp
	 * @return string timestamp
	 */
	public function get_timestamp()
	{
		global $wpdb;
		return $wpdb->get_var("SELECT CURRENT_TIMESTAMP");
	}

	/**
	 * Get photo by filename
	 * @return object photo
	 */
	public function get_photo_by_filename($filename)
	{
		global $wpdb;
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}" . self::TABLE . "` WHERE `pho_file_name`=%s", $filename));
	}
}

// EOF
