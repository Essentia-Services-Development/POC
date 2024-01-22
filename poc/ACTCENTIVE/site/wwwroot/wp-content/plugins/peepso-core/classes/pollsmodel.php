<?php

Class PeepSoPollsModel {

	CONST TABLE = 'peepso_polls_user_answers';

	public function save_user_polls($params)
	{
		global $wpdb;

		$wpdb->insert($wpdb->prefix . self::TABLE, array(
			'pu_poll_id' => $params['poll_id'],
			'pu_user_id' => $params['user_id'],
			'pu_value' => $params['poll']
		));
	}

	public function is_voted($user_id, $poll_id)
	{
		if ($user_id === 0) {
			return FALSE;
		}

		global $wpdb;

		$sql = $wpdb->prepare("SELECT count(*) as total FROM " . $wpdb->prefix . self::TABLE . " WHERE pu_user_id = %d AND pu_poll_id = %d", $user_id, $poll_id);
		$total = (int) $wpdb->get_row($sql)->total;

		if ($total === 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	public function get_user_polls($user_id, $poll_id)
	{
		if ($user_id === 0) {
			return FALSE;
		}

		global $wpdb;

		$sql = $wpdb->prepare("SELECT pu_value FROM " . $wpdb->prefix . self::TABLE . " WHERE pu_user_id = %d AND pu_poll_id = %d", $user_id, $poll_id);
		return $wpdb->get_col($sql);
	}

	public function delete_user_polls($user_id, $poll_id)
	{
		if ($user_id === 0) {
			return FALSE;
		}

		global $wpdb;
		$sql = $wpdb->prepare("DELETE FROM " . $wpdb->prefix . self::TABLE . " WHERE pu_user_id = %d AND pu_poll_id = %d", $user_id, $poll_id);
		$wpdb->query($sql);
	}

}
