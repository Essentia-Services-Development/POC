<?php

class PeepSoMessageParticipants
{
	const TABLE = 'peepso_message_participants';

	/**
	 * Adds a participant
	 * @param int $msg_id The message thread ID
	 * @param array $participants IDs
	 * @param int $parent_id The parent's message thread ID
	 * @return boolean returns TRUE
	 */
	public function add_participants($msg_id, $participants, $parent_id = 0)
	{
		foreach ($participants as $participant)
			$this->add_participant($msg_id, $participant, $parent_id);

		return (TRUE);
	}

	public static function update_last_activity( $msg_id )
	{
		global $wpdb;
		$msg_id = (int) $msg_id;
		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation($msg_id);
		$timestamp = time()+date("Z");
		$update = $wpdb->update($wpdb->prefix . self::TABLE,
			array(
				'mpart_last_activity' => gmdate('Y-m-d H:i:s', $timestamp),
			),

			array(
				'mpart_msg_id' => $parent_id,
			));

		if ($update) {
			return (TRUE);
		}

		// admin-ajax.php?action=peepso_should_get_chats
		$self = new self();
		$participants = $self->get_participants($msg_id);

		foreach($participants as $participant_user_id) {
			update_user_option($participant_user_id, 'peepso_should_get_chats', TRUE);
			PeepSoSSEEvents::trigger('get_chats', $participant_user_id);
		}

		return (FALSE);

	}

	/**
	 * Adds a participant/message to an existing thread.
	 * @param int $msg_id  The message thread ID
	 * @param int $user_id The user to be added
	 *
	 * @return boolean.
	 */
	public function add_participant($msg_id, $user_id)
	{
		global $wpdb;

		$mpar_id = $this->in_conversation($user_id, $msg_id);

		// Return the ID if a user is already part of the conversation
		if ($mpar_id)
			return ($mpar_id);

		$insert = $wpdb->insert($wpdb->prefix . self::TABLE,
			array(
				'mpart_msg_id' => $msg_id,
				'mpart_user_id' => $user_id,
				'mpart_read_notif' => 1 // default is enable read notification
			)
		);

		self::update_last_activity($msg_id);

		if ($insert)
			return (TRUE);
		return (FALSE);
	}

	/**
	 * Returns TRUE or FALSE whether the user is part of a conversation.
	 * @param  int $user_id The user ID
	 * @param  int $msg_id  The message thread ID
	 * @return boolean
	 */
	public function in_conversation($user_id, $msg_id)
	{
		global $wpdb;

		$sql = "SELECT count(`mpart_msg_id`) FROM `{$wpdb->prefix}" . self::TABLE . "`
			WHERE `mpart_user_id` = %d AND `mpart_msg_id` = %d";

		$result = $wpdb->get_var($wpdb->prepare($sql, $user_id, $msg_id));

		return (intval($result) > 0);
	}

	/**
	 * Return user ID's of all participants in a conversation
	 * @param  int $msg_id The message ID
	 * @param  int $user_id A user ID used to alter the query
	 * @return array
	 */
	public function get_participants($msg_id, $user_id = NULL)
	{
		global $wpdb;

		$select = 'SELECT DISTINCT(`mpart_user_id`) ';
		$from = ' FROM `' . $participants_table = $wpdb->prefix . self::TABLE . '` `mpart` ';
		$where = ' WHERE `mpart`.`mpart_msg_id` = %d';

		if (NULL !== $user_id) {
			// exclude blocks
			$block_query = ' LEFT JOIN `' . $wpdb->prefix . PeepSoBlockUsers::TABLE . '` `block`
					ON (
						(`mpart`.`mpart_user_id` = `block`.`blk_user_id` AND `block`.`blk_blocked_id` = %d)
						OR
						(`mpart`.`mpart_user_id` = `block`.`blk_blocked_id` AND `block`.`blk_user_id` = %d)
					) ';
			$from .= $wpdb->prepare($block_query, $user_id, $user_id);
			$where .= ' AND `blk_blocked_id` IS NULL ';
		}

		$sql = $select . $from . $where;

		$result = $wpdb->get_col($wpdb->prepare($sql, $msg_id));

		return ($result);
	}

	/**
	 * Permanently removes a user from a conversation
	 * @param  int $user_id The user to be removed
	 * @param  int $msg_id  Any message ID from a conversation
	 * @return mixed FALSE on error.
	 */
	public function remove_participant($user_id, $msg_id)
	{
		global $wpdb;

		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation($msg_id);

		self::update_last_activity($msg_id);
		return ($wpdb->delete($wpdb->prefix . self::TABLE, array('mpart_msg_id' => $parent_id, 'mpart_user_id' => $user_id)));
	}

	public function is_group($msg_id)
	{
		global $wpdb;

		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation($msg_id);

		$sql = 'SELECT DISTINCT(`mpart_is_group`)  FROM `' .  $wpdb->prefix . self::TABLE . '` WHERE `mpart_msg_id` = '.$parent_id . ' LIMIT 1';
		return $wpdb->get_var($sql);
	}

	public function mute_get($msg_id, $user_id)
	{
		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation($msg_id);

		$mayfly = "msgso_mute_{$user_id}_{$parent_id}";
		if (PeepSo3_Mayfly::get($mayfly)) {
			return TRUE;
		}

		// if the transient expired, hard reset the conversation to "unmuted"
		// @todo this might be called too often;
		$this->mute_set($parent_id, $user_id, 0);
	}

	public function mute_set($msg_id, $user_id, $mute_period)
	{
		global $wpdb;
		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation((int) $msg_id);

		$mayfly = 'msgso_mute_'.$user_id.'_'.$parent_id;

		// unmute by default
		$mute_value = 0;
		PeepSo3_Mayfly::del($mayfly);

		// mute for...
		if( 0 != $mute_period ) {
			$mute_value = 1;
			PeepSo3_Mayfly::set($mayfly, 1, $mute_period);
		}

		$where = array(
				'mpart_user_id' => $user_id,
				'mpart_msg_id' => $parent_id,
		);

		$what= array(
				'mpart_muted' => $mute_value,
		);

		$wpdb->update($wpdb->prefix . PeepSoMessageParticipants::TABLE, $what, $where);
	}

	public static function get_muted_conversations( $user_id )
	{
		global $wpdb;
		$user_id = intval($user_id);

		$sql = 'SELECT `mpart_msg_id`  FROM `' .  $wpdb->prefix . self::TABLE . '` WHERE `mpart_muted` = 1 and `mpart_user_id` = ' . $user_id;
		return $result = $wpdb->get_col($sql);
	}

	public function set_group($msg_id)
	{
		global $wpdb;

		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation($msg_id);

		$sql = 'UPDATE `' .  $wpdb->prefix . self::TABLE . '` SET `mpart_is_group`=1 WHERE `mpart_msg_id` = '.$parent_id;
		return $wpdb->query($sql);
	}

	public function read_notification_get($msg_id, $user_id)
	{
		global $wpdb;
		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation((int) $msg_id);

		$sql = 'SELECT `mpart_read_notif`  FROM `' .  $wpdb->prefix . self::TABLE . '` WHERE `mpart_msg_id` = ' . $parent_id . ' and `mpart_user_id` = ' . $user_id;
		return $result = $wpdb->get_var($sql);
	}

	public function read_notification_set($msg_id, $user_id, $notif)
	{
		global $wpdb;
		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation((int) $msg_id);

		$sql = 'UPDATE `' .  $wpdb->prefix . self::TABLE . '` SET `mpart_read_notif` = ' . intval($notif).' WHERE `mpart_msg_id` = ' . $parent_id . ' and `mpart_user_id` = ' . $user_id;
		return $wpdb->query($sql);
	}
}

// EOF
