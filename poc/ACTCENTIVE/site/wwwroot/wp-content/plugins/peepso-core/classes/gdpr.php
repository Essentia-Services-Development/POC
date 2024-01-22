<?php

class PeepSoGdpr
{
	const TABLE = 'peepso_gdpr_request_data';
	const MODULE_ID = 10;

	// values for the `request_status` column
	const STATUS_PENDING = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_DELAY = 2;
	const STATUS_FAILED = 3;
	const STATUS_SUCCESS = 4;
	const STATUS_READY = 5;
	const STATUS_RETRY = 6;
	const STATUS_REJECT = 7;

	public static $array_status;
	public static $user_id;

	private static $_max_exec_time = 60;

	public function __construct() 
	{
		self::$array_status = array(
			0 => __('Pending', 'peepso-core'),
			1 => __('Processing', 'peepso-core'),
			2 => __('Delay', 'peepso-core'),
			3 => __('Failed', 'peepso-core'),
			4 => __('Success', 'peepso-core'),
			5 => __('Ready', 'peepso-core'),
			6 => __('Retry', 'peepso-core'),
			7 => __('Reject', 'peepso-core')
		);
	}

	/**
	 * Returns the privately defined $_max_exec_time variable value.
	 * @return int
	 */
	private static function get_max_exec_time()
	{
		$timeout = self::$_max_exec_time;

		if(isset($_GET['timeout'])) {
			$timeout = $_GET['timeout'];
		}

		return ($timeout);
	}

	/**
	 * Create gdpr table if not exists
	 */
	public static function create_table()
	{
		// create table if not exists
		global $wpdb;
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}peepso_gdpr_request_data` (
					`request_id`			INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`request_user_id`		BIGINT(20) UNSIGNED NULL DEFAULT '0',
					`request_created_at`	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					`request_updated_at`	TIMESTAMP DEFAULT 0,
					`request_file_path`		VARCHAR(255) NULL DEFAULT NULL,
					`request_file_url`		VARCHAR(255) NULL DEFAULT NULL,
					`request_status`		TINYINT(1) NOT NULL DEFAULT '0',
					`request_attempts` 		TINYINT(1) NOT NULL DEFAULT '0',
					`request_error_log`		TEXT,

					PRIMARY KEY (`request_id`),
					INDEX `user` (`request_user_id`),
					INDEX `status` (`request_status`)
				) ENGINE=InnoDB");
	}

	/*
	 * Adds request to the request data queue.
	 * @param int $user_id The user id of the recipient
	 */
	public static function add($user_id)
	{
		$aCols = array(
			'request_user_id' => intval($user_id),
			'request_status' => self::STATUS_READY,
		);

		global $wpdb;
		$wpdb->insert($wpdb->prefix . self::TABLE, $aCols);
	}

	/*
	 * Adds request to the request data queue.
	 * @param int $user_id The user id of the recipient
	 */
	public static function delete_request($user_id)
	{
		global $wpdb;

		// delete the generated files if exists
		$user = PeepSoUser::get_instance($user_id);
		$dir = $user->get_personaldata_dir();
		self::removedir($dir);

		// delete the record
		$query = 'DELETE FROM `' . self::get_table_name() . '` WHERE `request_user_id` = %d';
		$wpdb->query($wpdb->prepare($query, $user_id));
	}

	private static function removedir($dir) 
	{
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir . "/" . $object) == "dir") {
						self::removedir ($dir . "/" . $object); 
					} else {
						unlink   ($dir . "/" . $object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	/*
	 * Adds request to the request data queue.
	 * @param int $user_id The user id of the recipient
	 */
	public static function update_status($req_id, $status)
	{
		$where = array('request_id' => $req_id);

		global $wpdb;
		$wpdb->update(self::get_table_name(), array('request_status' => $status, 'request_updated_at' => current_time( 'mysql' )), $where);
	}

	/*
	 * Processes the items in the request data.
	 */
	public static function process_export_data()
	{
		global $wpdb;

		$sStartTime = microtime(true);

		$status = self::STATUS_READY;

		// Get 10 items to retry
		$queue_retry = self::get_by_status( self::STATUS_RETRY, 10 );

		$limit = 50;
		if(isset($_GET['limit'])) {
			$limit = $_GET['limit'];
		}


		// get regular items minus the amount of delayed we found
		$queue = self::get_by_status($status, $limit - count($queue_retry));

		$queue = array_merge($queue, $queue_retry);

		$iProcessed = 0;
		$sCurrentRunTime = 0;
		foreach ($queue as $request) {

			// same WHERE clause is used multiple times
			$where = array('request_id' => $request->request_id);

			$request->request_attempts +=1;

			$wpdb->update(self::get_table_name(), array('request_status' => self::STATUS_PROCESSING), $where);
			// $wpdb->update(self::get_table_name(), array('request_attempts' => $mail->mail_attempts), $where);

			$table_name = self::get_table_name();

			// rand filename
			$user = PeepSoUser::get_instance($request->request_user_id);
			$rand_filename = md5(time() . $user->get_username());
			
			$success = self::do_export_data($request->request_user_id, $rand_filename);

			// update `request_status` on failure
			if (FALSE === $success) {

				// set retry by default
				$status = self::STATUS_RETRY;

				// if 5 attempts have been made, quit
				if($request->request_attempts > 5) {
					$status = self::STATUS_FAILED;
				}

				$wpdb->update(self::get_table_name(), array('request_status' => $status), $where);
			}
			else {
				do_action('peepso_export_data_after', $request);
				
				$request_file_path = $user->get_personaldata_dir() . $rand_filename . '.zip';
				$request_file_url = $user->get_personaldata_url() . $rand_filename . '.zip';

				$wpdb->update(self::get_table_name(), array('request_status' => self::STATUS_SUCCESS, 'request_file_path' => $request_file_path, 'request_file_url' => $request_file_url), $where);

				$data = array('permalink' => $user->get_profileurl() . 'about/account/');
				$data = array_merge($data, $user->get_template_fields('user'));
				PeepSoMailQueue::add_message($request->request_user_id, $data, __('Your personal data is ready for download', 'peepso-core'), 'export_data_complete', 'gdpr', self::MODULE_ID);
			}

			++$iProcessed;

			$sCurrentRunTime = microtime(true) - $sStartTime;
			if ($sCurrentRunTime  > self::get_max_exec_time())
				break;
		}
	}

	/*
	 * Cleanup the items in the request data that have keep more than one week.
	 */
	public static function process_cleanup_data()
	{
		global $wpdb;

		// Get 10 items to retry
		$queue = self::get_expired_data( self::STATUS_SUCCESS, 1 );

		$sCurrentRunTime = 0;
		foreach ($queue as $request) {
			self::delete_request($request->request_user_id);
		}
	}


	/**
	 * Do export data and create zip file from it
	 * @return boolean true/false
	 */
	public static function do_export_data($user_id, $rand_filename)
	{
		$user = PeepSoUser::get_instance($user_id);

		/**
		 * Machine Readable
		 */
		self::export_profile_picture($user);

		// personal data
		self::export_personal_data($user, 'json');

		// activity
		self::export_activity($user, 'json');

		// friends
		if (class_exists('PeepSoFriendsPlugin')) {
			self::export_friends($user, 'json');
		}

		// export messages
		if (class_exists('PeepSoMessagesPlugin')) {
			self::export_messages($user, 'json');
		}

		// photos
		if (class_exists('PeepSoSharePhotos')) {
			self::export_photos($user, 'json');
		}

		// videos
		if (class_exists('PeepSoVideos')) {
			self::export_videos($user, 'json');
		}

		// groups
		if (class_exists('PeepSoGroupsPlugin')) {
			self::export_groups($user, 'json');
		}

		// export blogpost
		self::export_blogposts($user, 'json');

		// export badgeos
		if (class_exists('BadgeOS_PeepSo')) {
			self::export_badgeos($user, 'json');
		}

		/**
		 * Human readable
		 * Move to stage 2
		 */
		// copy avatar
		// self::export_profile_picture($user);

		// export personal data
		// self::export_personal_data($user);

		// export activity
		// self::export_activity($user);

		// export messages
		// if (class_exists('PeepSoMessagesPlugin')) {
		// 	self::export_messages($user);
		// }

		// export friends
		// if (class_exists('PeepSoFriendsPlugin')) {
		// 	self::export_friends($user);
		// }

		// export photos
		// if (class_exists('PeepSoSharePhotos')) {
		// 	self::export_photos($user);
		// }

		self::generate_zip_file($user, $rand_filename);

		return TRUE;
	}

	public static function generate_zip_file($user, $rand_filename) {
		$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
		
		$rootPath = realpath($dir);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open($user->get_personaldata_dir() . DIRECTORY_SEPARATOR . $rand_filename . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		// include all user files
		$userPath = $user->get_image_dir();

		$userFiles = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($userPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		if(!self::is_dir_empty($userPath)) {
			foreach ($userFiles as $name => $userfile)
			{
				// Skip directories (they would be added automatically)
				if (!$userfile->isDir())
				{
					// Get real and relative path for current file
					$userFilePath = $userfile->getRealPath();
					$userRelativePath = substr($userFilePath, strlen($userPath));

					// Add current file to archive
					$zip->addFile($userFilePath, $userRelativePath);
				}
			}
		}

		// include all user files
		if (class_exists('PeepSoVideos')) {
			$dirVideos = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'video';

			$videoFiles = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($dirVideos),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ($videoFiles as $name => $videofile)
			{
				// Skip directories (they would be added automatically)
				if (!$videofile->isDir())
				{
					// Get real and relative path for current file
					$userFileVideoPath = $videofile->getRealPath();
					$userRelativeVideoPath = substr($userFileVideoPath, strlen($dirVideos));

					// Add current file to archive
					$zip->addFile($userFileVideoPath, 'video' . DIRECTORY_SEPARATOR . $userRelativeVideoPath);
				}
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();

		if (isset($dirVideos) && class_exists('PeepSoVideos')) {
			// remove videos files after zip
			self::removedir($dirVideos);
		}
	}

	public static function export_profile_picture($user)
	{
		$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'photos';
		if (!is_dir($dir)) {
			mkdir($dir);
		}

		$profile_path = $dir . DIRECTORY_SEPARATOR . 'profile.jpg';
		file_put_contents($profile_path, file_get_contents($user->get_avatar()));
	}

	public static function export_personal_data($user, $type='json')
	{
		if ($type === 'json') {

			// generate json directory if not exists
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			$args = array('post_status'=>'publish');

			$user->profile_fields->load_fields($args);
			$fields = $user->profile_fields->get_fields();

			$data = [
				'profile_url' => $user->get_profileurl(),
				'registration_date' => $user->get_date_registered(),
				'email' => $user->get_email(),
			];
			if( count($fields) ) {
				foreach ($fields as $key => $field) {
					ob_start();
					$field->render();
					$value = ob_get_clean();
					$data['list_fields'][] = [
						'key' => $key,
						'title' => $field->title,
						'value' => $value
					];
				}
			}

			$json_string = json_encode($data, JSON_PRETTY_PRINT);

			$file_personal_data = $dir . DIRECTORY_SEPARATOR . 'personal-data.json';

			return file_put_contents($file_personal_data, $json_string);

		} else {
			$override = PeepSo::get_option('gdpr_personal_data_template_html','');
			$contents = stripslashes($override);

			$args = array('post_status'=>'publish');

			$user->profile_fields->load_fields($args);
			$data['fields'] = $user->profile_fields->get_fields();
			$data['user'] = $user;

			$data_content = PeepSoTemplate::exec_template('account', 'personal-data', $data, TRUE);

			$data = array();
			$data['data_photo'] = 'photos/profile.jpg';
			$data['data_title'] = $user->get_fullname() . ' - ' . __('Personal Data', 'peepso-core');
			$data['data_name'] = $user->get_fullname();
			$data['data_sidebar'] = self::sidebar_navigation();
			$data['data_contents'] = $data_content;

			// setup the template replacement data
			$rd = new PeepSoExportDataTemplate();
			$rd->set_tokens($data);

			$personaldata = $rd->replace_content_tokens($contents);

			// Write the contents to the file index.htm
			$file_index = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'index.htm';
			
			return file_put_contents($file_index, $personaldata);
		}
	}

	public static function export_activity($user, $type='json')
	{

		$PeepSoActivity = new PeepSoActivity;

		$user_id = $user->get_id();
		$owner_id = $user->get_id();
		self::$user_id = $user_id;

		add_filter('posts_clauses_request', array('PeepSoGdpr', 'filter_post_clauses'), 10, 2);
		$activity = $PeepSoActivity->get_posts(NULL, $owner_id, $user_id, 1, FALSE, -1, 0);
		remove_filter('posts_clauses_request', array('PeepSoGdpr', 'filter_post_clauses'), 10);

		if ($type=='json') {

			// generate json directory if not exists
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}
			
			$list_post = [];
			while ($PeepSoActivity->next_post()) {
				$PeepSoActivityComments = new PeepSoActivity();
				$PeepSoActivityComments->get_comments($PeepSoActivity->post_data['ID'], 0, 1, -1);

				$list_comments = [];
				while ($PeepSoActivityComments->next_comment()) {

					$PeepSoActivityReplies = new PeepSoActivity();
					$PeepSoActivityReplies->get_comments($PeepSoActivityComments->comment_data['ID'], 0, 1, -1);

					$list_replies = [];
					while ($PeepSoActivityReplies->next_comment()) {
						$list_replies[] = $PeepSoActivityReplies->comment_data;
					}

					$PeepSoActivityComments->comment_data['list_replies'] = $list_replies;
					$list_comments[] = $PeepSoActivityComments->comment_data;
				}
				$PeepSoActivity->post_data['list_comments'] = $list_comments;				

				$list_post[] = $PeepSoActivity->post_data;
			}

			$json_string = json_encode($list_post, JSON_PRETTY_PRINT);

			$file_activity = $dir . DIRECTORY_SEPARATOR . 'activity.json';

			return file_put_contents($file_activity, $json_string);
		} else {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'html';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			$override = PeepSo::get_option('gdpr_personal_data_template_html','');
			$contents = stripslashes($override);

			$data_content = PeepSoTemplate::exec_template('account', 'activity', array('PeepSoActivity' => $PeepSoActivity), TRUE);

			$data = array();
			$data['data_photo'] = '../photos/profile.jpg';
			$data['data_title'] = $user->get_fullname() . ' - ' . __('Activity', 'peepso-core');
			$data['data_name'] = $user->get_fullname();
			$data['data_sidebar'] = self::sidebar_navigation('activity');
			$data['data_contents'] = $data_content;

			// setup the template replacement data
			$rd = new PeepSoExportDataTemplate();
			$rd->set_tokens($data);

			$personaldata = $rd->replace_content_tokens($contents);

			// Write the contents to the file index.htm
			$file_index = $dir . DIRECTORY_SEPARATOR . 'activity.htm';
			return file_put_contents($file_index, $personaldata);
		}
	}

	public static function filter_post_clauses($clauses, $query) {
		global $wpdb;
		
		$clauses['where'] .= " AND ( " . $wpdb->posts . ".`post_author` = " . self::$user_id . ") ";

		return $clauses;
	}

	public static function export_friends($user, $type='json')
	{
		$friends_model =  PeepSoFriendsModel::get_instance();
		$friends = $friends_model->get_friends($user->get_id(), array());

		// friend request
		$friend_request = PeepSoFriendsRequests::get_instance();
		$list_requests = array();
		if ($friend_request->has_received_requests($user->get_id())) {
			while ($request = $friend_request->get_next_request()) {
				$list_requests[] = $request;
			}
		}

		// blocked members
		$peepso_args['blocked'] = 1;
		$args['_peepso_args'] = $peepso_args;

		$list_blocked = new PeepSoUserSearch($args, $user->get_id());

		if ($type == 'json') {
			// generate json directory if not exists
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			$json = array(
				'friends' => [],
				'friend_requests' => [],
				'friend_blocked' => []
			);
			if (count($friends) > 0) {
				foreach ($friends as $friend) {
					$friend = PeepSoUser::get_instance($friend);
					$json['friends'][] = [
						'friend_id' => $friend->get_id(),
						'friend_name' => $friend->get_fullname()
					];
				}
			}

			if (count($list_requests) > 0) {
				foreach ($list_requests as $req) {
					$freq = PeepSoUser::get_instance($req['freq_user_id']);
					$json['friend_requests'][] = [
						'friend_id' => $freq->get_id(),
						'friend_name' => $freq->get_fullname()
					];
				}
			}

			if (count($list_blocked->results) > 0) {
				foreach ($list_blocked->results as $blocked) {
					$block = PeepSoUser::get_instance($blocked);
					$json['friend_blocked'][] = [
						'friend_id' => $block->get_id(),
						'friend_name' => $block->get_fullname()
					];
				}
			}

			$json_string = json_encode($json, JSON_PRETTY_PRINT);

			$file_friends = $dir . DIRECTORY_SEPARATOR . 'friends.json';

			return file_put_contents($file_friends, $json_string);
		} else {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'html';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			// list friend
			$override = PeepSo::get_option('gdpr_personal_data_template_html','');
			$contents = stripslashes($override);

			$data['friends'] = $friends;
			$data['list_requests'] = $list_requests;
			$data['list_blocked'] = $list_blocked;

			$data_content = PeepSoTemplate::exec_template('account', 'friends', $data, TRUE);

			$data = array();
			$data['data_photo'] = '../photos/profile.jpg';
			$data['data_title'] = $user->get_fullname() . ' - ' . __('Friends', 'peepso-core');
			$data['data_name'] = $user->get_fullname();
			$data['data_sidebar'] = self::sidebar_navigation('friends');
			$data['data_contents'] = $data_content;

			// setup the template replacement data
			$rd = new PeepSoExportDataTemplate();
			$rd->set_tokens($data);

			$personaldata = $rd->replace_content_tokens($contents);

			// Write the contents to the file index.htm
			$file_index = $dir . DIRECTORY_SEPARATOR . 'friends.htm';
			
			return file_put_contents($file_index, $personaldata);
		}
	}

	public static function export_photos($user, $type='json')
	{
		$photos_album_model = new PeepSoPhotosAlbumModel();
		$albums = $photos_album_model->get_user_photos_album($user->get_id(), 0, 100000, 'asc', 0);

		if ($type == 'json') {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			$list_albums = [];
			foreach ($albums as $key => $album) {
				$list_photos = $photos_album_model->get_album_photo($user->get_id(), $album->pho_album_id, 0, 999999999);

				$album->list_photos = $list_photos;
				$list_albums[] = $album;
			}

			$photo_content = json_encode($list_albums, JSON_PRETTY_PRINT);
			$file_photos = $dir . DIRECTORY_SEPARATOR . 'photos.json';

			file_put_contents($file_photos, $photo_content);
		} else {

			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'html';
			if (!is_dir($dir)) {
				mkdir($dir);
			}


			$dir_photos = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'photos';
			if (!is_dir($dir_photos)) {
				mkdir($dir_photos);
			}

			// list message
			$override = PeepSo::get_option('gdpr_personal_data_template_html','');
			$contents = stripslashes($override);

			$data = array();
			$data['data_photo'] = '../photos/profile.jpg';
			$data['data_title'] = $user->get_fullname() . ' - ' . __('Photos', 'peepso-core');
			$data['data_name'] = $user->get_fullname();
			$data['data_sidebar'] = self::sidebar_navigation('photos');

			foreach ($albums as $key => $album) {
				$list_photos = $photos_album_model->get_album_photo($user->get_id(), $album->pho_album_id, 0, 999999999);

				// save the photos
				foreach ($list_photos as $photo) {
					$dir_album = $dir_photos . DIRECTORY_SEPARATOR . $album->pho_album_id;
					if (!is_dir($dir_album)) {
						mkdir($dir_album);
					}
					file_put_contents($dir_album . DIRECTORY_SEPARATOR . $photo->pho_orig_name, file_get_contents($photo->location));
				}

				$data['data_contents'] = PeepSoTemplate::exec_template('account', 'photos-album', array('user' => $user, 'album' => $album, 'list_photos' => $list_photos), TRUE);

				$rd = new PeepSoExportDataTemplate();
				$rd->set_tokens($data);

				$photo_content = $rd->replace_content_tokens($contents);
				
				$file_album_detail = $dir_photos . DIRECTORY_SEPARATOR . $album->pho_album_id . '.htm';
				file_put_contents($file_album_detail, $photo_content);
			}

			$data['data_contents'] = PeepSoTemplate::exec_template('account', 'photos', array('user' => $user, 'albums' => $albums, 'photos_album_model' => $photos_album_model), TRUE);

			// setup the template replacement data
			$rd = new PeepSoExportDataTemplate();
			$rd->set_tokens($data);

			$personaldata = $rd->replace_content_tokens($contents);

			// Write the contents to the file index.htm
			$file_index = $dir . DIRECTORY_SEPARATOR . 'photos.htm';
			
			return file_put_contents($file_index, $personaldata);
		}
	}

	public static function export_videos($user, $type='')
	{
		$media_type = 'all';

		$videos_model = new PeepSoVideosModel();
		$list_videos = $videos_model->get_user_videos($user->get_id(), $media_type, 0, 999999999, 'desc', 0);

		if ($type=='json') {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			$video_dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'video';
			if (!is_dir($video_dir)) {
				mkdir($video_dir);
			}

			foreach ($list_videos as $video) {
				if ($video->vid_stored == 1) {

					$attachments = get_posts( array(
						'post_type' => 'attachment',
						'posts_per_page' => -1,
						'post_parent' => $video->vid_post_id
					) );

					if ( $attachments ) {
						foreach ( $attachments as $attachment ) {
							$target_url = wp_get_attachment_url( $attachment->ID );
							$target_path = str_replace(WP_CONTENT_URL, WP_CONTENT_DIR, $target_url);
							if(file_exists($target_path)) {
								copy($target_path, $video_dir . DIRECTORY_SEPARATOR . basename($target_path));
							}
						}
					}

				}
			}

			$video_content = json_encode($list_videos, JSON_PRETTY_PRINT);
			$file_videos = $dir . DIRECTORY_SEPARATOR . 'videos.json';

			file_put_contents($file_videos, $video_content);
		} else {
			// human readable
		}
	}

	public static function export_messages($user, $type='json')
	{
		$per_page = 100000;
		$msgtype = 'inbox';
		$query = '';
		$offset = 0;

		$PeepSoMessagesModel = new PeepSoMessagesModel();
		$messages = $PeepSoMessagesModel->get_messages($msgtype, $user->get_id(), $per_page, $offset, $query);

		if ($type == 'json') {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			foreach ($messages as $key => $message) {

				$peepso_participants = new PeepSoMessageParticipants();
				$participants = $peepso_participants->get_participants($message->post_parent, $user->get_id());

				$users = array();
				foreach ($participants as $participant_user_id) {

					$user = PeepSoUser::get_instance($participant_user_id);

					ob_start();
					do_action('peepso_action_render_user_name_before', $user->get_id());
					$before_fullname = ob_get_clean();

					ob_start();
					do_action('peepso_action_render_user_name_after', $user->get_id());
					$after_fullname = ob_get_clean();

					$users[] = array(
						'url' => $user->get_profileurl(),
						'name_full' => $before_fullname . $user->get_fullname() . $after_fullname,
						'name_first' => $user->get_firstname(),
						'online' 	=> PeepSo3_Mayfly::get('peepso_cache_'.$user->get_id().'_online'),
						'last_seen'	=> $user->get_last_online(),
					);
				}
				$message->participants = $users;


				// conversation
				$msg_id 				= $message->post_parent;
				$list_conversations 	= [];

				$PeepSoMessages = PeepSoMessages::get_instance();
				if ($PeepSoMessages->has_messages_in_conversation(compact('msg_id'))) {
					while ($PeepSoMessages->get_next_message_in_conversation()) {

						global $post;

						$easter_eggs = array(
							'live long and prosper' => 'llap.png',
							'may the force be with you' => 'mtfbwy.png',
						);

						foreach($easter_eggs  as $key => $egg) {
							if(stristr($post->post_content, $key)) {
								$post->post_content = '<img style="height:24px;display:inline-block;" src="'.PeepSo::get_asset('images/'.$egg).'" alt="Surprise!" /> '.$post->post_content;
							}
						}

						$list_conversations[] = $post;
					}
				}

				$message->list_conversations = $list_conversations;

			}

			$message_content = json_encode($messages, JSON_PRETTY_PRINT);
			$file_messages = $dir . DIRECTORY_SEPARATOR . 'messages.json';

			file_put_contents($file_messages, $message_content);

		} else {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'html';
			if (!is_dir($dir)) {
				mkdir($dir);
			}


			$dir_msg = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'messages';
			if (!is_dir($dir_msg)) {
				mkdir($dir_msg);
			}

			// list message
			$override = PeepSo::get_option('gdpr_personal_data_template_html','');
			$contents = stripslashes($override);

			$i = 0;
			foreach ($messages as $key => $message) {
				$args = array(
					'post_author' => $message->post_author, 'post_id' => $message->ID
				);

				ob_start();
				$PeepSoMessages = PeepSoMessages::get_instance();
				$PeepSoMessages->get_recipient_name($args, $user->get_id());
				$participants = ob_get_clean();

				$message_detail = PeepSoTemplate::exec_template('account', 'message-item', array('user' => $user, 'message' => $message, 'participants' => $participants), TRUE);
				
				$file_message_item = $dir_msg . DIRECTORY_SEPARATOR . $i++ . '.htm';
				file_put_contents($file_message_item, $message_detail);
			}


			$data_content = PeepSoTemplate::exec_template('account', 'messages', array('user' => $user, 'messages' => $messages), TRUE);

			$data = array();
			$data['data_photo'] = '../photos/profile.jpg';
			$data['data_title'] = $user->get_fullname() . ' - ' . __('Messages', 'peepso-core');
			$data['data_name'] = $user->get_fullname();
			$data['data_sidebar'] = self::sidebar_navigation('messages');
			$data['data_contents'] = $data_content;

			// setup the template replacement data
			$rd = new PeepSoExportDataTemplate();
			$rd->set_tokens($data);

			$personaldata = $rd->replace_content_tokens($contents);

			// Write the contents to the file index.htm
			$file_index = $dir . DIRECTORY_SEPARATOR . 'messages.htm';
			
			return file_put_contents($file_index, $personaldata);
		}
	}

	public static function export_groups($user, $type='')
	{
		$PeepSoGroups = new PeepSoGroups();
		$groups = $PeepSoGroups->get_groups(0, -1, 'id', 'DESC', '', $user->get_id(), 0);

		if ($type=='json') {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			$groups_response = array();

			foreach ($groups as $group) {

				$PeepSoGroupUser = new PeepSoGroupUser($group->get('id'), $user->get_id());
				if(!$PeepSoGroupUser->can('access')) { continue; }

				$keys = 'id,name,description,date_created_formatted,members_count,url,published,avatar_url_full,privacy,groupuserajax.member_actions,groupfollowerajax.follower_actions';
				$groups_response[] = PeepSoGroupAjaxAbstract::format_response($group, PeepSoGroupAjaxAbstract::parse_keys('group', $keys), $group->get('id'));
			}

			$group_content = json_encode($groups_response, JSON_PRETTY_PRINT);
			$file_groups = $dir . DIRECTORY_SEPARATOR . 'groups.json';

			file_put_contents($file_groups, $group_content);
		} else {
			// human readable
		}
	}

	public static function export_blogposts($user, $type='')
	{
		$args = array(
			'author'		=> $user->get_id(),
			'orderby'	   => 'post_date',
			'post_status'	=> 'publish',
			'order'		 => 'desc',
			'posts_per_page'=> -1,
			'offset'		=> 0,
		);

		// Get the posts
		$blogposts=get_posts($args);

		if ($type=='json') {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			$blogposts_content = json_encode($blogposts, JSON_PRETTY_PRINT);
			$file_blogposts = $dir . DIRECTORY_SEPARATOR . 'blogposts.json';

			file_put_contents($file_blogposts, $blogposts_content);
		} else {
			// human readable
		}
	}

	public static function export_badgeos($user, $type='')
	{
		global $blog_id;

		// Setup our query vars
		$posttype	   = "all";
		$limit	  = 100000;
		$offset	 = 0;
		$count	  = 0;
		$filter	 = "completed";
		$search	 = false;
		$orderby	= "menu_order";
		$order	  = "ASC";
		$wpms	   = false;
		$include	= array();
		$exclude	= array();
		$meta_key   = '';
		$meta_value = '';

		// Convert $posttype to properly support multiple achievement types
		if ( 'all' == $posttype ) {
			$posttype = badgeos_get_achievement_types_slugs();
			// Drop steps from our list of "all" achievements
			$step_key = array_search( 'step', $posttype );
			if ( $step_key )
				unset( $posttype[$step_key] );
		} else {
			$posttype = explode( ',', $posttype );
		}

		$user_id = $user->get_id();

		// Build $include array
		if ( !is_array( $include ) ) {
			$include = explode( ',', $include );
		}

		// Build $exclude array
		if ( !is_array( $exclude ) ) {
			$exclude = explode( ',', $exclude );
		}

		// Initialize our output and counters
		$achievements = '';
		$achievement_count = 0;
		$query_count = 0;

		// Grab our hidden badges (used to filter the query)
		$hidden = badgeos_get_hidden_achievement_ids( $posttype );

		// If we're polling all sites, grab an array of site IDs
		if( $wpms && $wpms != 'false' )
			$sites = badgeos_get_network_site_ids();
		// Otherwise, use only the current site
		else
			$sites = array( $blog_id );

		$achievement_posts = [];

		// Loop through each site (default is current site only)
		foreach( $sites as $site_blog_id ) {

			// If we're not polling the current site, switch to the site we're polling
			if ( $blog_id != $site_blog_id ) {
				switch_to_blog( $site_blog_id );
			}

			// Grab our earned badges (used to filter the query)
			$earned_ids = badgeos_get_user_earned_achievement_ids( $user_id, $posttype );

			// Query Achievements
			$args = array(
				'post_type'	  => $posttype,
				'orderby'		=> $orderby,
				'order'		  => $order,
				'posts_per_page' => $limit,
				'offset'		 => $offset,
				'post_status'	=> 'publish',
				'post__not_in'   => array_diff( $hidden, $earned_ids )
			);

			// Filter - query completed or non completed achievements
			if ( $filter == 'completed' ) {
				$args[ 'post__in' ] = array_merge( array( 0 ), $earned_ids );
			}elseif( $filter == 'not-completed' ) {
				$args[ 'post__not_in' ] = array_merge( $hidden, $earned_ids );
			}

			if ( '' !== $meta_key && '' !== $meta_value ) {
				$args[ 'meta_key' ] = $meta_key;
				$args[ 'meta_value' ] = $meta_value;
			}

			// Include certain achievements
			if ( !empty( $include ) ) {
				$args[ 'post__not_in' ] = array_diff( $args[ 'post__not_in' ], $include );
				$args[ 'post__in' ] = array_merge( array( 0 ), array_diff( $include, $args[ 'post__in' ] ) );
			}

			// Exclude certain achievements
			if ( !empty( $exclude ) ) {
				$args[ 'post__not_in' ] = array_merge( $args[ 'post__not_in' ], $exclude );
			}

			// Search
			if ( $search ) {
				$args[ 's' ] = $search;
			}

			// Loop Achievements
			$achievement_posts = new WP_Query( $args );

			wp_reset_query();

		}

		if ($type=='json') {
			$dir = $user->get_personaldata_dir() . DIRECTORY_SEPARATOR . 'json';
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			if (isset($achievement_posts->posts)) {
				$badges_content = json_encode($achievement_posts->posts, JSON_PRETTY_PRINT);	
			} else {
				$badges_content = json_encode($achievement_posts, JSON_PRETTY_PRINT);
			}

			$file_badges = $dir . DIRECTORY_SEPARATOR . 'badges.json';

			file_put_contents($file_badges, $badges_content);
		} else {
			// human readable
		}
	}

	public static function sidebar_navigation($selected='profile') 
	{
		$data = array();
		$path = ($selected == 'profile') ? '' : '../';

		$menus = array(
			'index' => [
				'label' => __('Personal Data', 'peepso-core'),
				'url' => $path . 'index.htm',
				'selected' => ($selected == 'profile') ? ' class="selected"' : ''
			],
			'activity' =>[
				'label' => __('Activity', 'peepso-core'),
				'url' => $path . 'html/activity.htm',
				'selected' => ($selected == 'activity') ? ' class="selected"' : ''
			],
			'photos' => [
				'label' => __('Photos', 'peepso-core'),
				'url' => $path . 'html/photos.htm',
				'selected' => ($selected == 'photos') ? ' class="selected"' : ''
			],
			'videos' => [
				'label' => __('Videos', 'peepso-core'),
				'url' => $path . 'html/videos.htm',
				'selected' => ($selected == 'videos') ? ' class="selected"' : ''
			],
			'friends' => [
				'label' => __('Friends', 'peepso-core'),
				'url' => $path . 'html/friends.htm',
				'selected' => ($selected == 'friends') ? ' class="selected"' : ''
			],
			'messages' => [
				'label' => __('Messages', 'peepso-core'),
				'url' => $path . 'html/messages.htm',
				'selected' => ($selected == 'messages') ? ' class="selected"' : ''
			],
		);
		unset($menus['videos']);
		if (!class_exists('PeepSoMessagesPlugin')) {
			unset($user['messages']);
		}

		// export friends
		if (!class_exists('PeepSoFriendsPlugin')) {
			unset($user['friends']);
		}

		// export photos
		if (!class_exists('PeepSoSharePhotos')) {
			unset($user['photos']);
		}
		$data['menus'] = $menus;

		$sidebar = PeepSoTemplate::exec_template('account', 'sidebar-menu', $data, TRUE);
		
		return $sidebar;
	}


	/**
	 * Fetches all request data queued up on the database.
	 * @param  int $limit  How many records to fetch.
	 * @param  int $offset Fetch records beginning from this index.
	 * @param  string  $order  Order by column.
	 * @param  string  $dir	The sort direction, defaults to 'asc'
	 * @return array Array of the result set.
	 */
	public static function fetch_all($limit = NULL, $offset = 0, $order = NULL, $dir = 'asc')
	{
		global $wpdb;

		$query = 'SELECT *				
			FROM `' . self::get_table_name() . '` ';

		if (isset($order))
			$query .= ' ORDER BY `' . $order . '` ' . $dir;

		if (isset($limit))
			$query .= ' LIMIT ' . $offset . ', ' . $limit;

		return ($wpdb->get_results($query, ARRAY_A));
	}

	/*
	 * Return list of request data queue items based on the status
	 * @param string $status The status to filter with.
	 * @return array Returns array of MailQueue items in chronologic order
	 */
	public static function get_by_status($status, $limit = 25)
	{
		global $wpdb;

		$sql = 'SELECT * 
				FROM `' . self::get_table_name() . '`
				WHERE `request_status` = %d
				ORDER BY `request_created_at` ASC
				LIMIT %d ';
		$res = $wpdb->get_results($wpdb->prepare($sql, $status, $limit));
		return ($res);
	}

	/*
	 * Return list of request data queue items based on the status
	 * @param string $status The status to filter with.
	 * @return array Returns array of MailQueue items in chronologic order
	 */
	public static function get_expired_data($status, $limit = 1)
	{
		global $wpdb;

		$sql = 'SELECT * 
				FROM `' . self::get_table_name() . '`
				WHERE `request_status` = %d
				AND DATE_ADD(request_updated_at, INTERVAL 1 WEEK) < NOW()
				ORDER BY `request_created_at` ASC
				LIMIT %d ';
		$res = $wpdb->get_results($wpdb->prepare($sql, $status, $limit));
		return ($res);
	}

	/*
	 * Return exists request 
	 * @param integer $user_id The requester.
	 * @return bool Returns boolean of status request
	 */
	public static function request_exists($user_id)
	{
		global $wpdb;

		$array_status = [
			self::STATUS_PENDING,
			self::STATUS_RETRY,
			self::STATUS_PROCESSING,
			self::STATUS_SUCCESS,
			self::STATUS_READY,
			self::STATUS_DELAY
		];

		$sql = 'SELECT *
				FROM `' . self::get_table_name() . '`
				WHERE `request_status` IN ('.implode(',', $array_status).')
				AND `request_user_id` = ' . $user_id . ' 
				ORDER BY `request_created_at` ASC';
		$res = $wpdb->get_results($sql);
		return $res;
	}

	/*
	 * Get a count of the pending items in the request data queue
	 * @return int A count of the items
	 */
	public static function get_pending_item_count()
	{
		global $wpdb;

		$sql = 'SELECT COUNT(*) AS `val`
				FROM `' . self::get_table_name() . '`
				WHERE `request_status`=%d ';
		$msg_count = $wpdb->get_var($wpdb->prepare($sql, self::STATUS_PENDING));

		return ($msg_count);
	}

	/**
	 * Convenience function to return the mailqueue table name as a string.
	 * @return string The table name.
	 */
	public static function get_table_name()
	{
		global $wpdb;

		return ($wpdb->prefix . self::TABLE);
	}

	public static function is_dir_empty($dir) {
		if (!is_readable($dir)) return NULL; 
		return (count(scandir($dir)) == 2);
	}
}

// EOF
