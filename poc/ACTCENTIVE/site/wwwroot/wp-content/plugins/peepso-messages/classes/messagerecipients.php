<?php

class PeepSoMessageRecipients
{
	const TABLE = 'peepso_message_recipients';

	private $unread_messages_count = array();


    private static $instance;

    public static function get_instance() {
        if(!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
	 * Adds a message to an existing thread.
	 * @param int $msg_id  The message thread ID
	 *
	 * @return boolean.
	 */
	public function add_message($msg_id, $parent_id = 0)
	{
		global $wpdb;

		if (0 === $parent_id) {
			$parent_id = $msg_id;
		}

		// Get all participants and add an entry for each
		$message_participants_model = new PeepSoMessageParticipants();
		$message_participants = $message_participants_model->get_participants($parent_id, get_current_user_id());

		foreach ($message_participants as $participant) {
			$data = array(
				'mrec_user_id' => $participant,
				'mrec_msg_id' => $msg_id,
				'mrec_parent_id' => $parent_id
			);

			$insert = $wpdb->insert($wpdb->prefix . self::TABLE, $data);

			if (FALSE === $insert) {
				return (FALSE);
			}

			do_action('peepso_action_add_message_recipient_after', $data);
		}

		return (TRUE);
	}

	/**
	 * Marks a message as viewed
	 * @param  int $user_id
	 * @param  int $msg_id
	 * @param  boolean $viewed
	 * @return boolean
	 */
	public function mark_as_viewed($user_id, $msg_id, $viewed = TRUE)
	{
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . self::TABLE,
			array('mrec_viewed' => $viewed),
			array('mrec_user_id' => $user_id, 'mrec_msg_id' => $msg_id)
		);

		$wpdb->update(
			$wpdb->prefix . self::TABLE,
			array('mrec_viewed' => $viewed),
			array('mrec_user_id' => $user_id, 'mrec_parent_id' => $msg_id)
		);

		return (TRUE);
	}

	/**
	 * Mark all messages as viewed
	 * @param  int $user_id
	 */
	public function mark_all_as_viewed($user_id)
	{
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . self::TABLE,
			array('mrec_viewed' => 1),
			array('mrec_user_id' => $user_id)
		);

		return (TRUE);
	}

	/**
	 * Return a count object of unread messages.
	 * @param  int $user_id
	 * @return int
	 */
	public function get_unread_messages_count($user_id = NULL)
	{
	    if(NULL == $user_id) {
	        $user_id = get_current_user_id();
        }

        if(!array_key_exists($user_id, $this->unread_messages_count)) {
            global $wpdb;

            $muted = PeepSoMessageParticipants::get_muted_conversations($user_id);
            $muted = implode(',', $muted);

            $recipients_table = $wpdb->prefix . self::TABLE;

            $sql = 'SELECT `mrec_msg_id` FROM `' . $recipients_table . '`
				WHERE `mrec_user_id` = %d AND `mrec_viewed` = 0 and `mrec_deleted` = 0 AND `mrec_parent_id` NOT IN 
				(SELECT mpart_msg_id FROM `' . $wpdb->prefix . 'peepso_message_participants` WHERE mpart_user_id NOT IN (SELECT ID FROM ' . $wpdb->users . '))';
            if (strlen($muted)) {
                $sql .= ' AND `mrec_parent_id` NOT IN (' . $muted . ') ';
            }

            $sql .= ' GROUP BY `mrec_parent_id`';

			$result = $wpdb->get_col($wpdb->prepare($sql, $user_id));

            $this->unread_messages_count[$user_id] = count($result);
        }

        return $this->unread_messages_count[$user_id];
	}

	/**
	 * Return a count object of unread messages.
	 * @param  int $user_id
	 * @return int
	 */
	public static function get_unread_messages_count_in_conversation($user_id, $parent_id)
	{
		global $wpdb;

		$recipients_table = $wpdb->prefix . self::TABLE;

		$sql = 'SELECT `mrec_msg_id` FROM `' . $recipients_table . '`
				WHERE `mrec_user_id` = %d AND `mrec_viewed` = 0 AND `mrec_parent_id` = %d';

		$result = $wpdb->get_results($wpdb->prepare($sql, $user_id, $parent_id));

		return (count($result));
	}



	/**
	 * Deletes a message from a user's inbox/sent items only. 
	 * Performs a soft delete on the recipients table.
	 *
	 * @param  int $user_id
	 * @param  int $message_id The message ID.	 
	 * @return mixed FALSE on error | on success it returns the number of affected rows.
	 */
	public function delete_from_inbox($user_id, $message_id)
	{
		global $wpdb;

		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation($message_id);

		$success = $wpdb->update($wpdb->prefix . self::TABLE, 
			array('mrec_deleted' => 1),
			array('mrec_parent_id' => $parent_id, 'mrec_user_id' => $user_id)
		);

		if ($success) {
			/**
			 * @param int $message_id The message ID.
			 * @param int $user_id The user ID.
			 * @param boolean $permanent Whether the message was permanently deleted.
			 */
			do_action('peepso_messages_message_deleted', $message_id, $user_id, TRUE);
		}

		return ($success);
	}

	/**
	 * Deletes a conversation message for a user's view only. 
	 * Performs a soft delete on the recipients table.
	 *
	 * @param  int $user_id
	 * @param  int $message_id The message ID.	 
	 * @return mixed FALSE on error | on success it returns the number of affected rows.
	 */
	public function delete_from_conversation($user_id, $message_id)
	{
		global $wpdb;

		$success = $wpdb->delete($wpdb->prefix . self::TABLE,
			array('mrec_msg_id' => $message_id, 'mrec_user_id' => $user_id)
		);

		// Delete from wp_posts if this is the last message.
		if ($success) {
			do_action('peepso_delete_content', $message_id);

			$sql = 'SELECT COUNT(`mrec_msg_id`) FROM `' . $wpdb->prefix . self::TABLE . '` WHERE `mrec_msg_id` = %d';

			$result = $wpdb->get_var($wpdb->prepare($sql, $message_id));

			if (0 === intval($result))
				wp_delete_post($message_id);
		}

		return ($success);
	}	

	/**
	 * Permanently removes a user from a conversation
	 *
	 * @param  int $user_id
	 * @param  int $message_id The message ID.	 
	 * @return mixed FALSE on error | on success it returns the number of affected rows.
	 */
	public function remove_recipients($user_id, $message_id)
	{
		global $wpdb;

		$model = new PeepSoMessagesModel();
		$parent_id = $model->get_root_conversation($message_id);

		$success = $wpdb->delete($wpdb->prefix . self::TABLE,
			array('mrec_parent_id' => $parent_id, 'mrec_user_id' => $user_id)
		);

		// Delete from wp_posts if this is the last message.
		if ($success) {
			$sql = 'SELECT COUNT(`mrec_msg_id`) FROM `' . $wpdb->prefix . self::TABLE . '` WHERE `mrec_msg_id` = %d';

			$result = $wpdb->get_var($wpdb->prepare($sql, $message_id));

			if (0 === intval($result))
				wp_delete_post($message_id);
		}

		return ($success);
	}	
}

// EOF
