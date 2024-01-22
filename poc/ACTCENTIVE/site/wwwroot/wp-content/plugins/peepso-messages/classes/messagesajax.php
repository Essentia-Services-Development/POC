<?php

class PeepSoMessagesAjax extends PeepSoAjaxCallback
{
	private static $_peepsomessages = NULL;

	protected function __construct()
	{
		parent::__construct();
		self::$_peepsomessages = PeepSoMessagesPlugin::get_instance();
	}

	/**
	 * Creates a new message thread and adds the first message.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function new_message(PeepSoAjaxResponse $resp)
	{
		$subject = '';
		$message = htmlspecialchars($this->_input->raw('message'));

		// SQL safe, forced to int
		$participants = array_map('intval',$this->_input->value('recipients', array(), FALSE));

		$creator_id = get_current_user_id();

		$model = new PeepSoMessagesModel();
		$msg_id = $model->create_new_conversation($creator_id, $message, $subject, $participants);

		if (is_wp_error($msg_id)) {
			$resp->success(FALSE);
			$resp->error($msg_id->get_error_message());
			$resp->set('subject', $subject);
			$resp->set('creator_id', $creator_id);
		} else {
			$ps_messages = PeepSoMessages::get_instance();
			do_action('peepso_messages_new_message', $msg_id);

			$resp->success(TRUE);
			$resp->notice(__('Message sent.', 'msgso'));
			$resp->set('msg_id', $msg_id);
			$resp->set('url', $ps_messages->get_message_url($msg_id));
		}
	}

	/**
	 * Add a message to an existing conversation.
	 * @param PeepSoAjaxResponse $resp The response object to be sent.
	 */
	public function add_message(PeepSoAjaxResponse $resp)
	{
		$message = htmlspecialchars($this->_input->raw('content'));
		$parent_id = $this->_input->int('parent_id');
		$user_id = get_current_user_id();

		// Make sure the "I am typing" flag is deleted
		$mayfly = 'msgso_typing_' . $user_id. '_' . $parent_id;
		PeepSo3_Mayfly::del($mayfly);

		// And that another one can't be set for another full second
		$mayfly_just_posted = "msgso_posted__{$user_id}_{$parent_id}";
		PeepSo3_Mayfly::set($mayfly_just_posted,1,1);

		$participants = new PeepSoMessageParticipants();

		if (FALSE === $participants->in_conversation($user_id, $parent_id)) {
			$resp->success(FALSE);
			$resp->error(__('You are not part of this conversation.', 'msgso'));
		} else {
			$model = new PeepSoMessagesModel();
			$msg_id = $model->add_to_conversation($user_id, $parent_id, $message);

			if (FALSE === $msg_id) {
				$resp->success(FALSE);
				$resp->error(__('Failed to send message.', 'msgso'));
			} else {
				$resp->success(TRUE);
				$resp->notice(__('Message sent.', 'msgso'));
				$resp->set('msg_id', $msg_id);
			}
		}
	}

	// @todo docbloc
	public function i_am_typing(PeepSoAjaxResponse $resp)
	{
		$msg_id = $this->_input->int('msg_id', 0);
		$user_id = get_current_user_id();

		PeepSoMessageParticipants::update_last_activity($msg_id);

		if( $msg_id > 0) {
			$mayfly = "msgso_typing_{$user_id}_{$msg_id}";

			$mayfly_just_posted = "msgso_posted__{$user_id}_{$msg_id}";
			if(!PeepSo3_Mayfly::get($mayfly_just_posted)){
				$ttl = floor((int) PeepSo::get_option('notification_ajax_delay', 30000) / 1000);
				PeepSo3_Mayfly::set($mayfly, 1, $ttl);
			}
		}


		$resp->success(TRUE);
	}

	// @todo docbloc
	public function mark_read_messages_in_conversation(PeepSoAjaxResponse $resp)
	{
		$recipients = new PeepSoMessageRecipients();
		$recipients->mark_as_viewed(get_current_user_id(), $this->_input->int('msg_id', 0));

		$resp->success( TRUE );
	}

	// @todo docbloc
	public function get_messages_in_conversation(PeepSoAjaxResponse $resp)
	{
		$PeepSoMessages = PeepSoMessages::get_instance();

		$msg_id                 = (int) $this->_input->int('msg_id', 0);
		$from_id                = (int) $this->_input->int('from_id', 0);
		$direction              = $this->_input->value('direction', NULL, array('new','old'));
		$user_id                = get_current_user_id();

		// chat related
		$chat                   = (int) $this->_input->int('chat', 0);
		$get_participants       = (int) $this->_input->int('get_participants', 0);
		$get_messages           = (int) $this->_input->int('get_messages', 1);
		$get_recently_deleted   = (int) $this->_input->int('get_recently_deleted', 0);
		$get_unread             = (int) $this->_input->int('get_unread', 0);
		$get_options            = (int) $this->_input->int('get_options', 0);

		/** Get messages **/
		if( 1 === $get_messages ) {
			ob_start();
			$ids = array();

			if ($PeepSoMessages->has_messages_in_conversation(compact('msg_id', 'from_id', 'direction'))) {
				while ($PeepSoMessages->get_next_message_in_conversation()) {

					global $post;
					$ids[] = $post->ID;
					if (!isset($first)) {
						$first = $post->ID;
					}

					if (1 === $chat) {
						$PeepSoMessages->show_message_in_conversation_chat();
					} else {
						$PeepSoMessages->show_message_in_conversation();
					}
				}
			}

			$html = ob_get_contents();
			ob_end_clean();

			$resp->set('ids', $ids);
			$resp->set('html', $html);
		}

		/** Enter to send **/

		if( 0 === $chat ) {
			$enter_to_send = 1;
			$enter_to_send_meta = get_user_meta($user_id, 'msgso_enter_to_send', TRUE);
			if (strlen($enter_to_send_meta)) {
				$enter_to_send = $enter_to_send_meta;
			}

			$resp->set('enter_to_send', $enter_to_send);
		}

		/** Recently deleted **/
		if( 1 === $get_recently_deleted) {
			$mayfly = 'msgso_del_' . $user_id;
			$recently_deleted = PeepSo3_Mayfly::get($mayfly);

			if (!empty($recently_deleted)) {
				$recently_deleted = json_decode($recently_deleted);
				$resp->set('recently_deleted', $recently_deleted);
			} else {
				$resp->set('recently_deleted', NULL);
			}
		}

		/** Currently typing **/

		$peepso_participants = new PeepSoMessageParticipants();
		$participants = $peepso_participants->get_participants($msg_id, $user_id);

		$currently_typing_html = '';
		foreach($participants as $check_user) {
			if( $check_user === $user_id ) continue;

			$mayfly = 'msgso_typing_' . $check_user . '_' . $msg_id;

			if( $is_typing = PeepSo3_Mayfly::get($mayfly)) {
				if ($check_user == $user_id) {
					continue;
				}

				$user = PeepSoUser::get_instance($check_user);

				if( 0 === $chat ) {
					$currently_typing_html .= PeepSoTemplate::exec_template('messages', 'currently-typing', array('user' => $user), TRUE);
				} else {
					$currently_typing_html .= PeepSoTemplate::exec_template('messages', 'currently-typing-chat', array('user' => $user), TRUE);
				}
			}
		}


		$resp->set('currently_typing', $currently_typing_html);


		/** Get participants **/
		if( 1 === $get_participants) {

			$users = array();
			foreach ($participants as $participant_user_id) {

				if($participant_user_id != $user_id) {

                    $user = PeepSoUser::get_instance($participant_user_id);

					ob_start();
		            do_action('peepso_action_render_user_name_before', $user->get_id());
		            $before_fullname = ob_get_clean();

		            ob_start();
		            do_action('peepso_action_render_user_name_after', $user->get_id());
		            $after_fullname = ob_get_clean();

					$users[] = array(
						'id' => $user->get_id(),
						'url' => $user->get_profileurl(),
						'name_full' => $before_fullname . $user->get_fullname() . $after_fullname,
						'name_first' => $user->get_firstname(),
						'online' 	=> PeepSo3_Mayfly::get('peepso_cache_'.$user->get_id().'_online'),
						'last_seen'	=> $user->get_last_online(),
						'avatar'     => $user->get_avatar(),
					);
				}
			}

			$in_conversation = count($participants) - 1;

			ob_start();
			$PeepSoMessages->display_participant_summary();
			$participant_summary = ob_get_clean();

			$resp->set('html_participants', $participant_summary);
			$resp->set('users', $users);
		}

		/** Get unread message count **/
		if( 1 === $get_unread) {
			$unread = 0;
			$no_receipt = FALSE;
			foreach ($participants as $participant_user_id) {
				if ($participant_user_id != $user_id) {
					$receipt = $peepso_participants->read_notification_get($msg_id, $participant_user_id);
					if( 0 === (int) $receipt) {
						$no_receipt = TRUE;
						$unread = 0;
						break;
					}
					$unread += PeepSoMessageRecipients::get_unread_messages_count_in_conversation($participant_user_id, $msg_id);
				}
			}

			$send_receipt = $peepso_participants->read_notification_get($msg_id, $user_id);

			$resp->set('unread', $unread);
			$resp->set('receipt', (int) $no_receipt ? FALSE : TRUE);
			$resp->set('send_receipt', $send_receipt);
		}

		if (1 === $get_options) {
			$post = get_post($msg_id);
			setup_postdata($post);

			$resp->set('html_options', $PeepSoMessages->get_conversation_options());
		}

		$resp->success(TRUE);
	}

	public function delete_from_conversation(PeepSoAjaxResponse $resp)
	{
		$msg_id = $this->_input->int('msg_id', 0);
		$user_id = get_current_user_id();

		$message_recipients = new PeepSoMessageRecipients();
		if( $message_recipients->delete_from_conversation($user_id, $msg_id)) {
			$mayfly = 'msgso_del_'.$user_id;

			if($recently_deleted = PeepSo3_Mayfly::get($mayfly)) {
				$recently_deleted = json_decode($recently_deleted, TRUE);
			} else {
				$recently_deleted = array();
			}

			$recently_deleted[]=$msg_id;

			$recently_deleted = json_encode($recently_deleted);

			PeepSo3_Mayfly::set($mayfly, $recently_deleted, 5*60);

			PeepSoMessageParticipants::update_last_activity($msg_id);
			$resp->success(TRUE);
			return;
		}

		$resp->success(FALSE);
	}

	/**
	 * Used in the notification popup to render the list items.
	 * @param PeepSoAjaxResponse $resp The response object to be sent.
	 */
	public function get_latest(PeepSoAjaxResponse $resp)
	{
		global $post;
		$notifications = array();
		$peepsomessages = PeepSoMessages::get_instance();

		$per_page = $this->_input->int('per_page', 10);
		$per_page_counter = 0;

		if ($peepsomessages->has_messages()) {
			while (++$per_page_counter <= $per_page && $peepsomessages->get_next_message())
				$notifications[] = PeepSoTemplate::exec_template('messages', 'notification-popover-item', (array) $post, TRUE);

			$resp->success(TRUE);
			$resp->set('notifications', $notifications);
		} else {
			$resp->success(FALSE);
			$resp->error(__('No notifications.', 'msgso'));
		}
	}

	/**
	 * Checks if the current user is part of the conversation, if not the response will be false.
	 * Else, it returns the users that may be added to the conversation.
	 * @param PeepSoAjaxResponse $resp The response object to be sent.
	 */
	public function get_available_recipients(PeepSoAjaxResponse $resp)
	{
		$parent_id = $this->_input->int('parent_id', 0);
		$keyword = trim( $this->_input->value('keyword', '', FALSE) );
		$page = $this->_input->int('page', 1);
		$user_id = $this->_input->int('user_id', FALSE);

		$peepso_participants = new PeepSoMessageParticipants();

		if (0 != $parent_id && FALSE === $peepso_participants->in_conversation(get_current_user_id(), $parent_id)) {
			$resp->success(FALSE);
		} else {
			$available_participants = self::$_peepsomessages->get_available_recipients($parent_id, $keyword, $page, $user_id);
			$resp->set('available_participants', $available_participants);
			$resp->success(TRUE);
		}
	}

	/**
	 * Adds participants to a conversation
	 * @param PeepSoAjaxResponse $resp The response object to be sent.
	 */
	public function add_participants(PeepSoAjaxResponse $resp)
	{
		global $post;

		$peepso_participants = new PeepSoMessageParticipants();
		$PeepSoMessages = PeepSoMessages::get_instance();

		$parent_id = $this->_input->int('parent_id', NULL);
		$user_id = get_current_user_id();

		$is_group = $peepso_participants->is_group($parent_id);

		if (FALSE === $peepso_participants->in_conversation($user_id, $parent_id) &&
			wp_verify_nonce('add-participant', 'add-participant-nonce')) {
			$resp->success(FALSE);
			return;
		}

		// SQL safe, forced to int
		$new_participants = array_map('intval',$this->_input->value('participants', array(), FALSE));

		// if this is not a group conversation, we need to spawn a new one
		if(0 == $is_group) {
			$all_participants = $peepso_participants->get_participants($parent_id, $user_id);

			$all_participants = array_merge($all_participants, $new_participants);

			$message = __('Created a new group conversation', 'msgso');

			$model = new PeepSoMessagesModel();
			$msg_id = $model->create_new_conversation($user_id, $message, '', $all_participants, array('post_type' => PeepSoMessagesPlugin::CPT_MESSAGE_INLINE_NOTICE));

			$url = PeepSoMessages::get_message_id_url($msg_id);
			$resp->set('new_conversation_redirect', $url);
			$resp->set('new_conversation_id', $msg_id);
			$resp->success(1);
			$peepso_participants->set_group($msg_id);
			return;
		} else {

			$_participants = array();
			foreach ($new_participants as $participant) {
				$u = PeepSoUser::get_instance($participant);
				$_participants[$participant] = $u->get_fullname();
			};

			$peepso_participants->add_participants($parent_id, $new_participants);
			$available_participants = self::$_peepsomessages->get_available_recipients($parent_id);

			$resp->success(TRUE);
			$resp->set('available_participants', $available_participants);
			$resp->set('participants', $_participants);
			$resp->notice(
				sprintf(__('%1$s %2$s added to the conversation', 'msgso'),
					count($_participants),
					_n('person', 'people', count($_participants), 'msgso')
				)
			);

			$post = get_post($parent_id);

			ob_start();
			$PeepSoMessages->display_participant_summary();
			$resp->set('summary', ob_get_clean());
			$peepso_participants->set_group($parent_id);
		}
	}

	/**
	 * Returns the list of messages, also performs a search if a query is sent.
	 * @param PeepSoAjaxResponse $resp The response object to be sent.
	 */
	public function get_messages(PeepSoAjaxResponse $resp)
	{
		$user_id = get_current_user_id();

		$per_page = $this->_input->int('per_page', 10);
		$type = $this->_input->value('type','',array('inbox','sent'));
        $page = $this->_input->int('page', 1);
        $unread_only = $this->_input->int('unread_only', 0);
		$query = $this->_input->raw('query', NULL);

		$peepso_messages = PeepSoMessages::get_instance();
		$model = $peepso_messages->_messages_model;

		$page = max(1, $page) - 1;

		$offset = ceil($per_page * $page);

		// perform the search
		$model->get_messages($type, $user_id, $per_page, $offset, $query, $unread_only);

		$resp->success(TRUE);

		$resp->set('html', $peepso_messages->get_message_list($type));
		$resp->set('total', $model->get_total());
		$resp->set('total_pages', ceil($model->get_total() / $per_page));
	}

	function set_mute(PeepSoAjaxResponse $resp)
	{
		global $wpdb;

		// parameter is in hours, we need seconds
		$mute_period = intval( 60 * 60 * $this->_input->int('mute', 1) );
		$parent_id = $this->_input->int('parent_id');

		// SQL safe, not used in queries
		if($this->_input->value('debug',FALSE,FALSE)) {
			$mute_period = 60 * $this->_input->int('mute', 1);
			$parent_id = $this->_input->int('parent_id');
		}

		$user_id = get_current_user_id();

		$participants = new PeepSoMessageParticipants();
		$participants->mute_set($parent_id, $user_id, $mute_period);

		$resp->success(TRUE);
	}
	public function enter_to_send(PeepSoAjaxResponse $resp)
	{
		$enter_to_send = ( 1 == $this->_input->int('enter_to_send', 0) ) ? 1 : 0;
		$user_id = get_current_user_id();

		update_user_meta( $user_id, 'msgso_enter_to_send', $enter_to_send);

		$resp->success(TRUE);
	}

	/**
	 * Perform bulk actions.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function bulk_action(PeepSoAjaxResponse $resp)
	{
	    // SQL safe, WP sanitizes it
		if ('POST' === $_SERVER['REQUEST_METHOD'] && wp_verify_nonce($this->_input->value('_messages_nonce','',FALSE), 'messages-bulk-action')) {
			$peepso_messages_recipients = new PeepSoMessageRecipients();
			$action = $this->_input->value('action','',array('delete','markread','markunread'));

			// SQL safe, forced to int
			$message_ids = array_map('intval',$this->_input->value('messages', array(), FALSE));

			if (0 === count($message_ids))
				return;

			$user_id = get_current_user_id();

			switch ($action)
			{
			case 'delete':
				foreach ($message_ids as $id)
					$peepso_messages_recipients->delete_from_inbox($user_id, intval($id));
				break;

			case 'markread':
				foreach ($message_ids as $id)
					$peepso_messages_recipients->mark_as_viewed($user_id, intval($id));
				break;

			case 'markunread':
				foreach ($message_ids as $id)
					$peepso_messages_recipients->mark_as_viewed($user_id, intval($id), FALSE);
				break;
			}
		}
	}

	/**
	 * Disable/Enable notification for a given conversation
	 *
	 * @param PeepSoAjaxResponse $resp
	 */
	public function set_message_read_notification(PeepSoAjaxResponse $resp)
	{
		$model = new PeepSoMessagesModel();

		$msg_id = $this->_input->int('msg_id', 0);
		$parent_id = $model->get_root_conversation(intval($msg_id));
		$user_id = get_current_user_id();

		$default = intval(PeepSo::get_option('messages_read_notification',1));
		$read_notif = $this->_input->int('read_notif', $default);

		$peepso_participants = new PeepSoMessageParticipants();
		$peepso_participants->read_notification_set($parent_id, $user_id, intval($read_notif));

		$resp->success(TRUE);
	}
}

// EOF
