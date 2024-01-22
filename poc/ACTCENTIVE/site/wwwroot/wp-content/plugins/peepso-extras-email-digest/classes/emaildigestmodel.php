<?php

class PeepSoEmailDigestModel
{

	/**
	 * Check if user has receive email digest in preferences
	 *
	 * @param $user_id id of the user
	 * @return int
	 */
	public static function receive_enabled($user_id)
	{
		$email_digest_receive_enabled = get_user_meta($user_id, 'peepso_email_digest_receive_enabled', true);

		// default to "1"
		if (!strlen($email_digest_receive_enabled) || !in_array($email_digest_receive_enabled, array(0, 1))) {
			$email_digest_receive_enabled = 1;
			update_user_meta($user_id, 'peepso_email_digest_receive_enabled', 1);
		}

		return ( (1 == $email_digest_receive_enabled) ? TRUE : FALSE );
	}

}

// EOF
