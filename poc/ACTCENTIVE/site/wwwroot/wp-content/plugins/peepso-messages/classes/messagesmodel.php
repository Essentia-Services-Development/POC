<?php

class PeepSoMessagesModel
{
	private $_limit = 0;
	private $_offset = 0;
	private $_query = '';
	private $_total = 0; // stores temporary total rows found for a query
	private $_iterator = NULL;
    public $_messages;

	private $_default_message_data = array(
		'post_title' => '',
		'post_content' => '',
		'post_author' => 0,
		'post_type' => PeepSoMessagesPlugin::CPT_MESSAGE,
		'post_status' => 'publish'
	);

	/**
	 * Returns TRUE|FALSE whether the current user has messages.
	 * @param  string $type inbox|sent
	 * @return boolean
	 */
	public function has_messages($type = 'inbox', $user_id = NULL)
	{
		if ($user_id === NULL) {
			$user_id = get_current_user_id();
		}

		$messages = $this->get_messages($type, $user_id);

		return ($messages->count() > 0);
	}

	/**
	 * Retrieves the last message from all conversations
	 * @param  string  $type    The type of messages to get, options are 'sent' or 'inbox'.
	 * @param  int  $user_id The user retrieving the messages
	 * @param  int $limit   The number of messages per page to retrieve
	 * @param  int $offset  Get the rows starting from this index
	 * @param  string  $query   A string to search the message content, title and recipient values
	 * @return ArrayObject       A php ArrayObject instance of the query results
	 */
	public function get_messages($type, $user_id, $limit = 40, $offset = 0, $query = NULL, $unread_only = 0)
	{
		global $wpdb, $wp_version;

		// Grab post IDs
		$sql ='SELECT MAX(`tmp_table`.`mrec_msg_id`) FROM (
		SELECT  *
		FROM  `'. $wpdb->prefix . PeepSoMessageRecipients::TABLE .'` `mrec` ';
		$sql .= 'WHERE `mrec`.`mrec_user_id` = ' . intval($user_id) . ' ';

		$sql .= 'AND `mrec`.`mrec_msg_id` IN (
		SELECT `posts`.`ID` FROM `' . $wpdb->posts . '` `posts`';

		if (FALSE === empty($query)) {
			$sql .= ' LEFT JOIN `' . $wpdb->users . '` `users`ON `posts`.`post_author` = `users`.`ID` ';
			$sql .= " LEFT JOIN (SELECT GROUP_CONCAT(DISTINCT `meta_value` ORDER BY `meta_key` ASC SEPARATOR ' ') AS `full_name`, `user_id` FROM `$wpdb->usermeta` WHERE `meta_key` IN ('first_name', 'last_name') GROUP BY `user_id`) `psmeta` ON `users`.`ID` = `psmeta`.`user_id`";
		}

		$qwhere_unread_only = '';
		if ($unread_only) {
			$qwhere_unread_only = ' AND `mrec`.`mrec_viewed` = 0 and `mrec`.`mrec_deleted` = 0 ';
		}

		$sql .=	' 	WHERE (`post_type` = "' . PeepSoMessagesPlugin::CPT_MESSAGE . '" OR `post_type` = "' . PeepSoMessagesPlugin::CPT_MESSAGE_INLINE_NOTICE . '") ';
		$sql .= $qwhere_unread_only;
		$sql .= 'AND `mrec`.`mrec_parent_id` NOT IN (SELECT `mpart_msg_id` FROM `' . $wpdb->prefix . 'peepso_message_participants` WHERE mpart_user_id NOT IN (SELECT ID FROM ' . $wpdb->users . '))';

		if (FALSE === empty($query)) {

			if (version_compare($wp_version, '4.0', 'lt')) {
				$search = '%' . like_escape($query) . '%';
			} else {
				$search = '%' . $wpdb->esc_like($query) . '%';
			}

			$sql .= $wpdb->prepare(' AND (`post_title` LIKE %s ', $search);
			$sql .= $wpdb->prepare(' OR `post_content` LIKE %s ', $search);

			$sql .= $wpdb->prepare(' OR `users`.`user_login` LIKE %s ', $search);
			$sql .= $wpdb->prepare(' OR `users`.`display_name` LIKE %s ', $search);
			$sql .= $wpdb->prepare(' OR `full_name` LIKE %s ', $search);

			$sql .= ')';
		}

		$sql .= '
								)
							AND `mrec`.`mrec_deleted` <> 1
							ORDER BY `mrec`.`mrec_msg_id` DESC
						) `tmp_table`
					GROUP BY `tmp_table`.`mrec_parent_id`';


		$ids = $wpdb->get_col($sql);

		if(!count($ids)) {
			return new ArrayObject(array());
		}

		$ids=implode(',',$ids);

		// Grab the messages
		$sql = 'SELECT `posts`.*, `mrec`.*
				FROM `' . $wpdb->posts . '` `posts`
				LEFT JOIN `'. $wpdb->prefix . PeepSoMessageRecipients::TABLE . '` `mrec`
					ON `mrec`.`mrec_msg_id` = `posts`.`ID` AND `mrec`.`mrec_user_id` = ' . $user_id .'
				LEFT JOIN `' . $wpdb->prefix . PeepSoMessageParticipants::TABLE .'` `mpart`
					ON `mrec`.`mrec_parent_id` = `mpart`.`mpart_msg_id` AND `mpart`.`mpart_user_id` = `mrec`.`mrec_user_id`
				WHERE
					`mpart`.`mpart_msg_id` IS NOT NULL';

		if( strlen($ids) ) $sql .= ' AND `posts`.`ID` IN ('.  $ids .')';

		$sql .= ' GROUP BY `posts`.`ID` ORDER BY `posts`.`post_date` DESC ';

		$total_query = $wpdb->get_results($sql);
		$this->_total = count($total_query);
		$this->_limit = $limit;
		$this->_offset = $offset;
		$this->_query = !empty($query) ? stripslashes($query) : '';

		$sql .= sprintf(' LIMIT %d, %d', $offset, $limit);

		$this->_messages = new ArrayObject($wpdb->get_results($sql));
		$this->_iterator =  $this->_messages->getIterator();

		return ($this->_messages);
	}

	/**
	 * Returns child posts of the parent conversation.
	 * @param  int $message_id A post ID.
	 * @param  int $user_id The user viewing the message.
	 * @return WP_Query
	 */
	public function get_messages_in_conversation($message_id, $user_id = 0, $from_id = 0, $direction = NULL, $post_type = NULL)
	{
		global $wpdb;

		// https://github.com/peepso/peepso/issues/1515
		// remove all 'pre_get_posts' action that caused error while get messages

		remove_all_actions('pre_get_posts');

		if(!is_array($post_type)) {
			$post_type = array(PeepSoMessagesPlugin::CPT_MESSAGE, PeepSoMessagesPlugin::CPT_MESSAGE_INLINE_NOTICE);
		}

		$sql = "SELECT `mrec_msg_id`
					FROM `" . $wpdb->prefix . PeepSoMessageRecipients::TABLE . "`
					WHERE (`mrec_parent_id` = $message_id OR `mrec_msg_id`= $message_id ) AND `mrec_user_id` = $user_id";

		$limit = 2 *  PeepSo::get_option('site_activity_posts', 20);

		if( $from_id > 0 ) {

			// New messages on the bottom
			if ('new' == $direction) {
				$sql .= " AND `mrec_msg_id` > $from_id";
				$limit = -1;
			}

			// Infinite scroll of old messages on top
			if ('old' == $direction) {
				$sql .= " AND `mrec_msg_id` < $from_id";
				$limit = PeepSo::get_option('site_activity_posts', 20);
			}
		}

		$sql .= " ORDER BY `mrec_msg_id` DESC ";
		if($limit > 0) $sql.=" LIMIT $limit ";


		$ids = $wpdb->get_col($sql, 0);

		// Set the ids to -1 so that it doesn't return all messages when there are none found.
		if (count($ids) < 1) {
			$ids = array(-1);
		}

		$args = array(
			'post__in' => $ids,
			'post_type' => $post_type,
			'orderby' => 'date',
			'order' => 'ASC',
			'posts_per_page' => $limit,
			'post_status' => 'publish'
		);

		return new WP_Query(
			$args
		);
	}

	/**
	 * Template tag - displays the pagination("n-n of n") text.
	 */
	public function display_totals()
	{
		$stats = array();

		$page = ceil($this->_offset / $this->_limit);
		$page++;
		$stats['page'] = $page;
		$max = $this->_limit * $page;

		if ($max > $this->_total)
			$max = $this->_total;
		$stats['max'] = $max;
		$stats['total'] = $this->_total;
		$stats['offset'] = $this->_offset;
		return ($stats);
	}

	/**
	 * Get search string used in last query.
	 * @return string The last SQL query used to retrieve the messages.
	 */
	public function get_query()
	{
		return ($this->_query);
	}

	/**
	 * Get message iterator
	 * @return ArrayIterator The ArrayIterator instance for the current query
	 */
	public function get_iterator()
	{
		return ($this->_iterator);
	}

	/**
	 * Get total message count from last query.
	 * @return int The total posts returned from the last query
	 */
	public function get_total()
	{
		return ($this->_total);
	}

	/**
	 * Adds a new message thread.
	 * @param  int $creator_id The user ID of the one initiating the thread.
	 * @param  string $message The initial message.
	 * @param  string $title The subject line.
	 * @param  array $paraticipants An array of user ID's to add to the participant list
	 * @return mixed msg_id on success, WP_Error on failure, returns FALSE if the conversation isn't created
	 */
	public function create_new_conversation($creator_id, $message, $title = '', $participants = array(), $message_data = array())
	{
		if (empty($creator_id))
			return new WP_Error('no-author', __('No author set.', 'msgso'));

		if (empty($participants))
			return new WP_Error('no-participants', __('No participants added.', 'msgso'));

		// Load PeepSoActivity - we need the peepso_activity_post_content filters
		PeepSoActivity::get_instance();

		if (!in_array($creator_id, $participants))
			$participants[] = $creator_id;

		$default_message_data = array(
			'post_title' => substr($title, 0, 250),
			'post_content' => $message,
			'post_author' => $creator_id,
		);

		$default_message_data = array_merge($this->_default_message_data, $default_message_data);

		$message_data = array_merge($default_message_data, $message_data);

		// Check for existing conversation between the two participants
		if (2 === count($participants) &&	// First, check if only two participants before doing the rest of the checks
			NULL !== ($_parent_id = $this->get_conversation_between($participants[0], $participants[1])) &&	// check if there's an existing conversation and returns the parent ID
			$_msg_id = $this->add_to_conversation($creator_id, $_parent_id, $message, $participants)	// returns FALSE on error, add to the existing conversation
			) {
			$user_author = PeepSoUser::get_instance($creator_id);
			$from_fields = $user_author->get_template_fields('from');
			$peepso_messages = PeepSoMessages::get_instance();
			$data = array('permalink' => $peepso_messages->get_message_url($_msg_id));

			foreach ($participants as $participant_user_id) {

				// admin-ajax.php?action=peepso_should_get_chats
				update_user_option($participant_user_id, 'peepso_should_get_chats', TRUE);
                PeepSoSSEEvents::trigger('get_chats', $participant_user_id);

				if ($participant_user_id == $creator_id)
					continue;

				$mayfly = "msgso_notif_{$participant_user_id}_{$_msg_id}";
				PeepSo3_Mayfly::set($mayfly,1, 24*3600);

				$user_owner = PeepSoUser::get_instance($participant_user_id);
				$data = array_merge($data, $from_fields, $user_owner->get_template_fields('user'));

                $i18n = __('You received a new message', 'msgso');
                $message = 'You received a new message';
                $args = ['msgso'];

				PeepSoMailQueue::add_notification_new($participant_user_id, $data, $message, $args, 'new_message', 'new_message', PeepSoMessagesPlugin::MODULE_ID);
			}

			return ($_msg_id);
		} // Exit the function
		// else Continue adding it as a new conversation

		$message_id = wp_insert_post($message_data);

		// TODO: why the need for wp_update_post()? Isn't setting post_cotent in the wp_insert_post() enough?
		wp_update_post(array('ID' => $message_id, 'post_content' => apply_filters('peepso_activity_post_content', $message, $message_id)));

		$act_id = $this->add_to_activity_stream($creator_id, $message_id);

		do_action('peepso_activity_after_add_post', $message_id, $act_id);

		if ($message_id) {
			$peepso_messages = PeepSoMessages::get_instance();
			$peepso_recipients = 	new PeepSoMessageRecipients();
			$peepso_participants = 	new PeepSoMessageParticipants();
			$peepso_participants->add_participants($message_id, $participants);

			$peepso_recipients->add_message($message_id);
			$peepso_recipients->mark_as_viewed($creator_id, $message_id);

			$user_author = PeepSoUser::get_instance($creator_id);
			$from_fields = $user_author->get_template_fields('from');

			$data = array('permalink' => $peepso_messages->get_message_url($message_id));

			foreach ($participants as $participant_user_id) {

				// admin-ajax.php?action=peepso_should_get_chats
				update_user_option($participant_user_id, 'peepso_should_get_chats', TRUE);
				PeepSoSSEEvents::trigger('get_chats', $participant_user_id);

				if ($participant_user_id == $creator_id)
					continue;

				$mayfly = "msgso_notif_{$participant_user_id}_{$message_id}";
				PeepSo3_Mayfly::set($mayfly,1, 24*3600);

				$user_owner = PeepSoUser::get_instance($participant_user_id);
				$data = array_merge($data, $from_fields, $user_owner->get_template_fields('user'));

                $i18n = __('You received a new message', 'msgso');
                $message = 'You received a new message';
                $args = ['msgso'];

				PeepSoMailQueue::add_notification_new($participant_user_id, $data, $message, $args, 'new_message', 'new_message', PeepSoMessagesPlugin::MODULE_ID);
			}

			PeepSoMessageParticipants::update_last_activity($message_id);

			/**
			 * New conversation has been created.
			 *
			 * @since 1.4.0
			 *
			 * @param int  $message_id id of the conversation.
			 */
			do_action('peepso_messages_new_conversation', $message_id); // @documented:1

			// Force a group conversation if there are more than two people in it
			if( count($participants) > 2 ) {
				$peepso_participants->set_group($message_id);
			}

			return ($message_id);
		}
		return (FALSE);
	}

	/**
	 * Adds a new message thread, QUIETLY
	 * No notifications, no actions, just silently add db entry
	 * @param  int $creator_id The user ID of the one initiating the thread.
	 * @param  array $to_id recipient ID
	 * @return mixed msg_id on success, WP_Error on failure, returns FALSE if the conversation isn't created
	 */
	public function create_empty_conversation($creator_id, $to_id)
	{
		if (empty($creator_id)) {
			return new WP_Error('no-author', __('No author set.', 'msgso'));
		}

		if (empty($to_id)) {
			return new WP_Error('no-participants', __('No participants added.', 'msgso'));
		}

		// if there's an existing conversation return the parent ID
		if( $parent_id = $this->get_conversation_between($creator_id, $to_id) ) {
			return $parent_id;
		}

		$participants = array($creator_id, $to_id);

		$message_data = array_merge( $this->_default_message_data, array('post_author' => $creator_id) );

		$message_id = wp_insert_post($message_data);

		$this->add_to_activity_stream($creator_id, $message_id);

		if ($message_id) {
			#$peepso_messages = PeepSoMessages::get_instance();
			#$peepso_recipients = 	new PeepSoMessageRecipients();
			$peepso_participants = 	new PeepSoMessageParticipants();
			$peepso_participants->add_participants($message_id, $participants);

			#$peepso_recipients->add_message($message_id);
			#$peepso_recipients->mark_as_viewed($creator_id, $message_id);

			PeepSoMessageParticipants::update_last_activity($message_id);

			return ($message_id);
		}
		return (FALSE);
	}

	private function add_to_activity_stream( $creator_id, $message_id )
	{
		// add data to Activity Stream data table
		$aActData = array(
			'act_owner_id' => $creator_id,
			'act_module_id' => PeepSoMessagesPlugin::MODULE_ID,
			'act_external_id' => $message_id,
			'act_access' => PeepSo::ACCESS_PUBLIC,
			'act_ip' => PeepSo::get_ip_address()
		);

		global $wpdb;
		$wpdb->insert($wpdb->prefix . PeepSoActivity::TABLE_NAME, $aActData);

		return $wpdb->insert_id;
	}

	/**
	 * Get a conversation between two users, if any.
	 * @param  int $user_one A participant
	 * @param  int $user_two A participant
	 * @return mixed The conversation's parent message ID, NULL if none
	 */
	public function get_conversation_between($user_one, $user_two)
	{
		global $wpdb;

		$sql = 'SELECT `mpart_msg_id` FROM `' . $wpdb->prefix . PeepSoMessageParticipants::TABLE . '` `mpart`
					GROUP BY `mpart`.`mpart_msg_id`
					HAVING SUM(`mpart`.`mpart_user_id` NOT IN (%d, %d)) = 0
						AND COUNT(DISTINCT `mpart_user_id`) = 2
					LIMIT 1';
		return ($wpdb->get_var($wpdb->prepare($sql, $user_two, $user_one)));
	}

	/**
	 * Adds a new message to thread.
	 * @param  int $author_id The user ID
	 * @param  int $parent_id The parent conversation's post ID
	 * @param  string $message The message.
	 *
	 * @return mixed msg_id on success, FALSE on failure.
	 */
	public function add_to_conversation($author_id, $parent_id, $message)
	{
		// Load PeepSoActivity - we need the peepso_activity_post_content filters
		PeepSoActivity::get_instance();

		$message_data = array(
			'post_parent' => $parent_id,
			'post_content' => $message,
			'post_author' => $author_id,
			'post_type' => PeepSoMessagesPlugin::CPT_MESSAGE,
			'post_status' => 'publish'
		);

		$message_id = wp_insert_post($message_data);

		add_filter( 'peepso_allow_embed', '__return_false' );
		wp_update_post(array('ID' => $message_id, 'post_content' => apply_filters('peepso_activity_post_content', $message, $message_id)));
		remove_filter( 'peepso_allow_embed', '__return_false' );

		// add data to Activity Stream data table
		$aActData = array(
			'act_owner_id' => $author_id,
			'act_module_id' => PeepSoMessagesPlugin::MODULE_ID,
			'act_external_id' => $message_id,
			'act_access' => PeepSo::ACCESS_PUBLIC,
			'act_ip' => PeepSo::get_ip_address()
		);

		global $wpdb;
		$wpdb->insert($wpdb->prefix . PeepSoActivity::TABLE_NAME, $aActData);

		do_action('peepso_activity_after_add_post', $message_id, $wpdb->insert_id);

		if ($message_id) {

			$peepso_recipients = new PeepSoMessageRecipients();
			$peepso_recipients->add_message($message_id, $parent_id);
			$peepso_recipients->mark_as_viewed($author_id, $message_id);

			PeepSoMessageParticipants::update_last_activity($message_id);
			$peepso_participants = new PeepSoMessageParticipants();
			$participants = $peepso_participants->get_participants($parent_id);

			$peepso_messages = PeepSoMessages::get_instance();

			$data = array('permalink' => $peepso_messages->get_message_url($message_id));
			$user_author = PeepSoUser::get_instance($author_id);

			$from_fields = $user_author->get_template_fields('from');
			foreach ($participants as $participant_user_id) {

				// admin-ajax.php?action=peepso_should_get_chats
				update_user_option($participant_user_id, 'peepso_should_get_chats', TRUE);
				PeepSoSSEEvents::trigger('get_chats', $participant_user_id);

				if ($participant_user_id == $author_id) {
					continue;
				}

				// if conversation is muted, skip
				if ($peepso_participants->mute_get($parent_id, $participant_user_id)) {
					continue;
				}

				// Doesn't actually send the message, just fires up the filters inside
                $i18n = __('You received a new message', 'msgso');
                $message = 'You received a new message';
                $args = ['msgso'];

				PeepSoMailQueue::add_notification_new($participant_user_id, $data, $message, $args, 'new_message', 'new_message', PeepSoMessagesPlugin::MODULE_ID,0,0,1);

				// if this user was recently notified, skip
				$mayfly_recent = "msgso_notif_{$participant_user_id}_{$parent_id}";
				if (PeepSo3_Mayfly::get($mayfly_recent)) {
					continue;
				}



				PeepSo3_Mayfly::set($mayfly_recent,1, 24*3600);

				$user_owner = PeepSoUser::get_instance($participant_user_id);

				$data = array_merge($data, $from_fields, $user_owner->get_template_fields('user'));

				// Send message but dont fire peepso_mailqueue_add
                $i18n = __('You received a new message', 'msgso');
                $message = 'You received a new message';
                $args = ['msgso'];

				PeepSoMailQueue::add_notification_new($participant_user_id, $data, $message, $args, 'new_message', 'new_message', PeepSoMessagesPlugin::MODULE_ID,0,1,0);
			}


			/**
			 * New message has been added to a conversation.
			 *
			 * @since 1.4.0
			 *
			 * @param int  $parent_id id of the conversation.
			 */
			do_action( 'peepso_messages_new_message', $parent_id ); // @documented:1

			return ($message_id);
		}
		return (FALSE);
	}

	/**
	 * Get a messages post parent ID
	 * @param  mixed $message A post ID or post object
	 * @return int
	 */
	public function get_root_conversation($message)
	{
		static $messages = array(); // store cache for performance purposes

		if (is_numeric($message)) {
			$message_id = $message;
		} else {
			$message_id = $message->ID;
		}

		if (!isset($messages[$message_id])) {
			$parent_id = intval(wp_get_post_parent_id($message_id));
			if (0 === $parent_id)
				$parent_id = $message_id;
			$messages[$message_id] = $parent_id;
		}

		return ($messages[$message_id]);
	}

	/**
	 * Adds an inline notification to a message thread
	 * @param  int $author_id The user ID
	 * @param  int $parent_id The parent conversation's post ID
	 * @param  string $message The message.
	 *
	 * @return mixed msg_id on success, FALSE on failure.
	 */
	public function add_inline_notification($author_id, $parent_id, $message)
	{
		$message_data = array(
			'post_parent' => $parent_id,
			'post_content' => $message,
			'post_author' => $author_id,
			'post_type' => PeepSoMessagesPlugin::CPT_MESSAGE_INLINE_NOTICE,
			'post_status' => 'publish'
		);

		$message_id = wp_insert_post($message_data);

		if ($message_id) {
			$peepso_recipients = new PeepSoMessageRecipients();

			$peepso_recipients->add_message($message_id, $parent_id);
			$peepso_recipients->mark_as_viewed($author_id, $message_id);

			return ($message_id);
		}
		return (FALSE);
	}


	/**
	 * Check notifications status for user_id
	 * @param int user_id
	 * @param int message_id
	 * @return bool
	 */
	public function get_read_notification_status($user_id, $message_id, $parent_id){
		global $wpdb;

		$peepso_participants = new PeepSoMessageParticipants();
		$participants = $peepso_participants->get_participants($parent_id);
		$num_participants = count($participants)-1;

		$sql = "SELECT COUNT(`mrec_msg_id`)
					FROM `" . $wpdb->prefix . PeepSoMessageRecipients::TABLE . "` a LEFT JOIN `" . $wpdb->prefix . PeepSoMessageParticipants::TABLE . "` b
					ON `a`.`mrec_parent_id` = `b`.`mpart_msg_id` and `a`.`mrec_user_id` = `b`.`mpart_user_id`
					WHERE `a`.`mrec_parent_id` = $parent_id AND `a`.`mrec_msg_id`= $message_id
					AND `a`.`mrec_user_id` <> $user_id AND `a`.`mrec_viewed`=1
					AND `b`.`mpart_read_notif`=1 ";


		$num_read = $wpdb->get_var($sql);

		if($num_participants > 0) {
			#@todo check if conversation is group or one to one
			# maybe we can return read by 2 people
			// if one-to-one conversation
			/*if((count($participants)-1) === 1) {
			}
			// else group conversation
			else {

			}*/
			return ($num_participants === intval($num_read)) ? TRUE : FALSE;
		}

		// no participant
		return FALSE;
	}
}

// EOF
