<?php

class PeepSoNotificationsQueue {

	private static $_instance = NULL;

	public $limit = 100;

	public $table = 'peepso_notifications_queue_log';

	public static function get_instance()
	{
		if (NULL === self::$_instance) {
			self::$_instance = new self();
		}
		return (self::$_instance);
	}

	private function __construct() {

	}

	public function debug() {
		$this->cron(20);
	}


	public function get_batches($limit=NULL) {

		if(!$limit) {
			$limit = $this->limit;
		}

		global $wpdb;
		$pf = $wpdb->prefix;

		$batches = array();

		// Get all possible notification levels

		$levels_in = array();
		$levels = PeepSoNotificationsIntensity::email_notifications_intensity_levels();

		unset($levels[999999]);
		unset($levels[0]);
		foreach($levels as $level_id=>$level) {
			$levels_in[]=$level_id;
		}

		// Base SQL: users who have the digest enabled and have unread notifications
		$base_sql = "SELECT u.ID as user_id, umeta.meta_value as email_intensity 
        FROM {$pf}users as u, {$pf}usermeta as umeta 
        WHERE (u.ID=umeta.user_id and umeta.meta_key='peepso_email_intensity' 
        AND umeta.meta_value IN (".implode(',',$levels_in)."))
        AND 
        
        ( EXISTS (SELECT id FROM {$pf}peepso_notifications WHERE not_user_id=u.ID AND not_read=0)        
        ";



		if(class_exists('PeepSoFriends')) {
			$base_sql .=" OR EXISTS (SELECT freq_id FROM {$pf}peepso_friend_requests WHERE freq_friend_id=u.ID AND freq_viewed=0) ";
		}

		if(class_exists('PeepSoMessages')) {

			$recipients_table = $wpdb->prefix . PeepSoMessageRecipients::TABLE;
			$base_sql .=" OR EXISTS (SELECT `mrec_msg_id` FROM `$recipients_table`
				WHERE `mrec_user_id` = u.ID AND `mrec_viewed` = 0 and `mrec_deleted` = 0 AND `mrec_parent_id` NOT IN 
				(SELECT mpart_msg_id FROM `{$pf}peepso_message_participants` WHERE mpart_user_id NOT IN (SELECT ID FROM {$wpdb->users})) ";

			$base_sql .= ' GROUP BY `mrec_parent_id`)';
		}

		$base_sql .=" ) ";

		// Users without a log entry
		$sql = $base_sql . " AND NOT EXISTS (SELECT id FROM {$pf}{$this->table} WHERE user_id=u.ID AND archived=0) LIMIT $limit";
		$batches['no_log_entry'] = $wpdb->get_results($sql, ARRAY_A);


		// Schedule based
		foreach($levels_in as $level) {
			$sql = $base_sql. " AND umeta.meta_value=$level AND EXISTS (SELECT id FROM {$pf}{$this->table} WHERE archived=0 AND user_id=u.ID AND sent<DATE_SUB(NOW(), INTERVAL $level MINUTE)) LIMIT $limit";
			$batches['interval_'.$level.'_minutes'] = $wpdb->get_results($sql, ARRAY_A);
		}

		foreach($batches as $interval => $users) {
			if(!count($users)) {
				unset($batches[$interval]);
			}
		}

		$limit_per_section = (int)PeepSo::get_option('notification_digest_limit_per_section', 5);
		$result = array();

		if(count($batches)) {

			foreach($batches as $batchname => $batch) {

				if(count($batch)) {

					foreach($batch as $user) {

						/***** NOTIFICATIONS *****/

						$note = new PeepSoNotifications();
						$notifications = $note->get_by_user($user['user_id'], $limit_per_section, 0, 1);

						foreach($notifications as $not) {

							$data = array();

							$not = get_object_vars($not);

							//var_dump($not);

							// INIT
							$PeepSoUser = PeepSoUser::get_instance($not['not_from_user_id']);
							$PeepSoProfile = PeepSoProfile::get_instance();


							// MESSAGE
							ob_start();
							//echo $PeepSoUser->get_fullname() . ' ' . trim($not['not_message'], ' .');
                            echo $PeepSoUser->get_fullname() . ' ';
                            echo PeepSoNotifications::parse($not);
							$PeepSoProfile->notification_link(TRUE, $not);
							$data['message'] = $this->sanitize(ob_get_clean());

							// PREVIEW
							ob_start();
							$PeepSoProfile->notification_human_friendly($not);
							$data['preview'] = $this->sanitize(ob_get_clean());

							// DATE
							ob_start();
							$PeepSoProfile->notification_age($not);
							$data['age'] = $this->sanitize(ob_get_clean());

							// URL
							$data['url'] = $PeepSoProfile->notification_link(FALSE, $not);

							// AVATAR
							$data['image'] = $PeepSoUser->get_avatar('');

							// ID
							$data['not_id'] = $not['not_id'];

							$result[$user['user_id']]['notifications'][]=$data;
						}

						/***** CHAT *****/

						if(class_exists('PeepSoMessages')) {

							if($count = PeepSoMessageRecipients::get_instance()->get_unread_messages_count($user['user_id'])) {

								$PeepSoMessagesModel = new PeepSoMessagesModel();
								$PeepSoMessages = PeepSoMessages::get_instance();
								$messages = $PeepSoMessagesModel->get_messages('inbox', $user['user_id'], $limit_per_section, 0, NULL, TRUE);

								foreach ( $messages as $msg ) {
									$data = array();

									global $post;
									$post = $msg;
									// INIT
									$PeepSoUser    = PeepSoUser::get_instance( $msg->post_author );

//									// MESSAGE
									ob_start();
									$args = array(
										'post_author' => $msg->post_author, 'post_id' => $msg->ID, 'current_user_id' => $user['user_id']
									);
									$PeepSoMessages->get_recipient_name($args);

									$data['message'] = $this->sanitize(ob_get_clean());

									// PREVIEW
									ob_start();
									$PeepSoMessages->get_last_author_name($args);
									echo $PeepSoMessages->get_conversation_title();
									$data['preview'] = $this->sanitize(ob_get_clean());

									// DATE
									ob_start();
									$item_date = mysql2date('U', $msg->post_date_gmt, FALSE);
									$curr_date = date('U', current_time('timestamp', 1));
									echo PeepSoTemplate::time_elapsed($item_date, $curr_date);

									$data['age'] = trim(strip_tags(ob_get_clean()), " \n\t");

									// URL
									$root =  $PeepSoMessagesModel->get_root_conversation($msg);

									$data['url'] = $PeepSoMessages->get_message_url();

									// AVATAR
									$data['image'] = $PeepSoUser->get_avatar( '' );

									// ID
									$data['not_id'] = $msg->ID;

									$result[ $user['user_id'] ]['chat'][] = $data;
								}
							}
						}

						/** FRIENDS **/
						if(class_exists('PeepSoFriends')) {

							$PeepSoFriendsRequests = PeepSoFriendsRequests::get_instance();
							$requests = $PeepSoFriendsRequests->get_received_requests($user['user_id']);
							if(count($requests)) {

								$requests = array_slice($requests, 0, $limit_per_section);

								foreach ( $requests as $req ) {
									$data = array();

									// INIT
									$PeepSoUser    = PeepSoUser::get_instance( $req['freq_user_id'] );

									// MESSAGE
									$data['message'] = $this->sanitize($PeepSoUser->get_fullname());

									// PREVIEW
									$data['preview'] = '&nbsp;';

									// DATE
									ob_start();
									$item_date = mysql2date('U', $req['freq_created'], FALSE);
									$curr_date = date('U', current_time('timestamp', 1));
									echo PeepSoTemplate::time_elapsed($item_date, $curr_date);

									$data['age'] = $this->sanitize(ob_get_clean());

									// URL
									$data['url'] = $PeepSoUser->get_profileurl();

									// AVATAR
									$data['image'] = $PeepSoUser->get_avatar( '' );

									// ID
									$data['not_id'] = $req['freq_id'];

									$result[ $user['user_id'] ]['friend_requests'][] = $data;
								}
							}
						}
					}
				} else {
					// no users
				}
			}
		} else {
			// nothing found
		}

		return $result;
	}

	public function cron($limit = NULL) {

		global $wpdb;
		$pf=$wpdb->prefix;

		$t=$wpdb->get_row("SELECT NOW() as current_datetime");

		if(!$limit) {
			$limit = $this->limit;
		}

		$batches = $this->get_batches($limit);

		$limit_per_section = (int)PeepSo::get_option('notification_digest_limit_per_section', 5);

		foreach($batches as $user_id => $sections) {

			$count_chat = $count_friend_requests = $count_notifications =0;

			$PeepSoUser          = PeepSoUser::get_instance( $user_id );
			$PeepSoNotifications = PeepSoNotifications::get_instance();

			$notifications_html = '';

			$count_notifications = $PeepSoNotifications->get_unread_count_for_user( $user_id );
			if(class_exists('PeepSoFriendsRequests')) {
				$count_friend_requests = count( PeepSoFriendsRequests::get_instance()->get_received_requests( $user_id ) );
			}

			if(class_exists('PeepSoMessageRecipients')) {
				$count_chat = PeepSoMessageRecipients::get_instance()->get_unread_messages_count( $user_id );
			}

			$count = $count_chat + $count_notifications + $count_friend_requests;

			foreach($sections as $section=>$notifications) {

				$count_section = ${"count_$section"};
				if('notifications' == $section) {
					$section = __('Notifications', 'peepso-core');
					if($count_friend_requests+$count_chat == 0) {
						$section='';
					}
				}

				$notifications_html .= str_replace( ["\n","\t"], ['',' '], PeepSoTemplate::exec_template( 'notifications', 'email-previews-before', [ 'title' => apply_filters( 'peepso_notification_digest_section_title', $section, $user_id ), 'count_section' => $count_section ], true ) );
				$notifications_html .= str_replace( ["\n","\t"], ['',' '], PeepSoTemplate::exec_template( 'notifications', 'email-previews', array( 'notifications' => $notifications ), true ) );


			}



			$levels                  = PeepSoNotificationsIntensity::email_notifications_intensity_levels();
			$level                   = $levels[ PeepSoNotificationsIntensity::user_email_notifications_intensity( $user_id ) ];
			$notifications_intensity = '<center><span id="notification_intentity_text" style="margin:auto;color:#80848a;font-size: 12px;">' . $level['desc'] . '</span></center>';



			$data = array(
				'userlogin'                          => $PeepSoUser->get_username(),
				'userfullname'                       => trim( strip_tags( $PeepSoUser->get_fullname() ) ),
				'userfirstname'                      => $PeepSoUser->get_firstname(),
				'notifications'                      => $notifications_html,
				'notification_intensity_description' => $notifications_intensity,
				'count'                              => sprintf( _n( '%d unread notification', '%d unread notifications', $count, 'peepso-core' ), $count ),
			);

			PeepSoMailQueue::add_message( $user_id, $data, __( 'You have unread notifications', 'peepso-core' ), 'notification_digest', 'notification_digest' );

			// Mark old log entries archived
			$wpdb->update( $wpdb->prefix . $this->table, array( 'archived' => 1 ), array( 'user_id' => $user_id ) );

			// Create a new log entry
			$data = array(
				'user_id'  => $user_id,
				'archived' => 0,
				//'sent'=>current_time('mysql', 1),
				'html'     => $notifications_html,
			);

			$wpdb->insert( $wpdb->prefix . $this->table, $data );

		}
	}

    private function sanitize($text) {

        // Remove all line breaks, tabs and double spaces
        $text = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $text);

        // Remove HTML
        $text = strip_tags($text);

        // Trim length
        $text = truncateHtml($text, PeepSo::get_option('notification_preview_length', 50), PeepSo::get_option('notification_preview_ellipsis','...'), FALSE, FALSE);

        return trim($text);
    }
}