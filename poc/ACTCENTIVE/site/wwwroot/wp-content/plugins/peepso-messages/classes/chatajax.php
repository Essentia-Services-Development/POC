<?php

class PeepSoChatAjax extends PeepSoAjaxCallback
{
	private static $_user_id = NULL;

	protected function __construct()
	{
		parent::__construct();
		self::$_user_id = get_current_user_id();
	}

	/**
	 * Get the chat stack from the database
	 *
	 * @param PeepSoAjaxResponse $resp
	 */
	public function get_chats(PeepSoAjaxResponse $resp)
	{
		global $wpdb;

		$sql = 'SELECT * FROM ' .
			$wpdb->prefix . PeepSoMessageParticipants::TABLE .
			' WHERE `mpart_user_id`='.self::$_user_id .
			' AND `mpart_chat_state`>0 ORDER BY `mpart_chat_order` ASC';

		$chats_res = $wpdb->get_results($sql);

		$chats = array();
		$read_notif = intval(PeepSo::get_option('messages_read_notification',1));

		if(count($chats_res)) {
			foreach($chats_res as $chat) {
				$muted = FALSE;

				if( TRUE == $chat->mpart_muted ) {
					$participants = new PeepSoMessageParticipants();

					// not using the explicit DB value, because this getter makes sure to check the transient
					$muted = $participants->mute_get($chat->mpart_msg_id, self::$_user_id);
				}

				$unread = PeepSoMessageRecipients::get_unread_messages_count_in_conversation(self::$_user_id, $chat->mpart_msg_id);

				$send_receipt_flag = 0;
				$receipt = FALSE;
				$receipt_unread = 0;

				if( 1 === $read_notif ) {
					$peepso_participants = new PeepSoMessageParticipants();
					$participants = $peepso_participants->get_participants($chat->mpart_msg_id, self::$_user_id);

					$send_receipt_flag = (int) $peepso_participants->read_notification_get($chat->mpart_msg_id, self::$_user_id);

					$receipt = TRUE;
					$receipt_unread = 0;

					foreach ($participants as $participant_user_id) {
						if($participant_user_id != self::$_user_id) {
							$show_receipt = $peepso_participants->read_notification_get($chat->mpart_msg_id, $participant_user_id);
							if( 0 === (int) $show_receipt) {
								$receipt = FALSE;
								$receipt_unread = 0;
								break;
							}
							$receipt_unread += PeepSoMessageRecipients::get_unread_messages_count_in_conversation($participant_user_id, $chat->mpart_msg_id);
						}
					}
				}

				$chats[]=array(
 					'id'             => $chat->mpart_msg_id,
					'state'          => $chat->mpart_chat_state,
					'last_activity'  => $chat->mpart_last_activity,
					'unread'         => $unread,
					'disabled'       => $chat->mpart_chat_disabled,
					'muted'          => intval($muted),
					'send_receipt'   => $send_receipt_flag,
					'receipt'        => $receipt,
					'receipt_unread' => $receipt_unread,
				);
			}
		}

		PeepSoUser::update_last_activity(self::$_user_id);

		$resp->set('chats', $chats);
		$resp->success(TRUE);
	}

	/**
	 * Save the frontend chat stack into database
	 *
	 * @param PeepSoAjaxResponse $resp
	 */
	public function set_chats(PeepSoAjaxResponse $resp)
	{
		global $wpdb;

		// SQL safe, we expect JSON
		$chats = htmlspecialchars_decode($this->_input->value('chats','',FALSE));

		if(!strlen($chats) || !json_decode($chats)) {
			$resp->success(FALSE);
			$resp->error('Malformed params');
			return;
		}

		// reset all chats for the user

		$args = array(
			'chat_state'=>0,
			'chat_order'=>0,
			);
		PeepSoChatModel::set( NULL, self::$_user_id, $args );

		$chats = json_decode($chats, true);

		if(count($chats)) {

			$order = 0;

			// loop through all chats and apply setting
			foreach($chats as $chat) {
				$order++;

				if( !array_key_exists('state', $chat) || 0 == $chat['state'] ) {
					continue;
				}

				$args = array(
					'chat_state'=>$chat['state'],
					'chat_order'=>$order,
				);
				PeepSoChatModel::set( $chat['id'], self::$_user_id, $args );
			}

		}

		$resp->success( TRUE );
	}

	/**
	 * Open a window with 1on1 chat with a given user
	 * Open existing conversation if exists
	 * Spawn new conversation if there is none
	 *
	 * @param PeepSoAjaxResponse $resp
	 */
	public function get_chat_with(PeepSoAjaxResponse $resp)
	{
		$to_id = $this->_input->int('recipient', 0);

		$model = new PeepSoMessagesModel();
		// create an empty conversation or get id of pre-existing one
		$msg_id = $model->create_empty_conversation(self::$_user_id, $to_id);

		if (is_wp_error($msg_id) || FALSE === $msg_id) {
			$resp->success(FALSE);
			$resp->error( is_wp_error($msg_id) ? $msg_id->get_error_message() : __('Error creating conversation','msgso'));
		} else {
			$resp->success(TRUE);
			$resp->set('msg_id', $msg_id);

			// open chat window for this conversation for current user
			$args = array(
				'chat_state'=>1,
				'chat_order'=>0,
				);
			PeepSoChatModel::set( $msg_id, self::$_user_id, $args );
		}
	}

	/**
	 * Disable/Enable chat for a given conversation
	 *
	 * @param PeepSoAjaxResponse $resp
	 */
	public function set_chat_disabled(PeepSoAjaxResponse $resp)
	{
		$args = array(
			'chat_disabled'=>$this->_input->int('disabled', 1),
		);
		PeepSoChatModel::set( $this->_input->int('msg_id', 0), self::$_user_id, $args );
		$resp->success(TRUE);
	}

	/**
	 * Disable/Enable notification for a given conversation
	 *
	 * @param PeepSoAjaxResponse $resp
	 */
	public function set_chat_read_notification(PeepSoAjaxResponse $resp)
	{
		$args = array(
			'read_notif'=>$this->_input->int('read_notif', 1),
		);
		PeepSoChatModel::set($this->_input->int('msg_id', 1), self::$_user_id, $args);
		$resp->success(TRUE);
	}
}

// EOF
