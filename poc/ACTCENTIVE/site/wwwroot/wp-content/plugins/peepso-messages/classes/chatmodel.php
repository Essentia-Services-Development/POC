<?php

class PeepSoChatModel
{
	static private $chat_states = array(
		0, // completely closed
		1, // open
		2, // open but minimised
	);

	static private $chat_fields = array(
		'chat_state',
		'chat_disabled',
		'muted',
		'chat_order',
		'read_notif'
	);

	public static function set( $msg_id, $user_id, $args, $args_where = array() )
	{
		// default to logged in user if no user_id is passed
		$user_id = ( NULL === $user_id ) ? get_current_user_id() : $user_id;
		$msg_id  = (int) $msg_id;

		$where = array(
			'mpart_user_id' => $user_id,
		);

		if (0 != $msg_id) {
			$where['mpart_msg_id'] = $msg_id;
		}

		if (array_key_exists( 'old_state', $args )) {
			$where['mpart_chat_state'] = $args['old_state'];
		}

		if (count( $args_where ) ) {
			foreach ($args_where as $key=>$value) {
				if (in_array($key, self::$chat_fields, true)) {
					$where['mpart_' . $key] = $value;
				}
			}
		}

		foreach($args as $key=>$value) {
			if(in_array($key, self::$chat_fields, true)) {
				$what["mpart_".$key] = $value;
			}
		}

		// admin-ajax.php?action=peepso_should_get_chats
		update_user_option(get_current_user_id(), 'peepso_should_get_chats', TRUE);
		update_user_option($user_id, 'peepso_should_get_chats', TRUE);

        PeepSoSSEEvents::trigger('get_chats');
        PeepSoSSEEvents::trigger('get_chats', $user_id);

		global $wpdb;
		return $wpdb->update($wpdb->prefix . PeepSoMessageParticipants::TABLE, $what, $where);
	}

	/**
	 * Check if user has chat enabled in preferences
	 *
	 * @param $user_id id of the user
	 * @return int
	 */
	public static function chat_enabled( $user_id )
	{
		if(!PeepSo::get_option('messages_chat_enable', 1)) {
			return FALSE;
		}

		$chat_enabled = get_user_meta( $user_id, 'peepso_chat_enabled', true );

		// default to "1"
		if(!strlen($chat_enabled) || !in_array($chat_enabled, array(0,1))) {
			$chat_enabled = 1;
			update_user_meta($user_id, 'peepso_chat_enabled', 1);
		}

		return ( (1 == $chat_enabled) ? TRUE : FALSE );
	}

    public static function chat_new_minimized( $user_id )
    {
        $minimized = get_user_meta( $user_id, 'peepso_chat_new_minimized', true );
        return ( (1 == $minimized) ? TRUE : FALSE );
    }

    public static function chat_friends_only( $user_id )
    {
        if(!class_exists('PeepSoFriends')) {
            return FALSE;
        }

        $friends_only = get_user_meta( $user_id, 'peepso_chat_friends_only', true );
        $default = FALSE;
        if ($friends_only === "") {
        	$default = intval(PeepSo::get_option('messages_friends_only', 1));
        	$default = ( (1 == $default) ? TRUE : FALSE );
        }

        return ( (1 == $friends_only) ? TRUE : $default );
    }



	// @todo docblock
	public static function chat_enabled_conversation( $user_id )
	{
		if( !self::check_chat_enabled( $user_id ) ){
			return( FALSE );
		}

		// @todo add per-conversation stuff

		return( TRUE );
	}

}
//EOF