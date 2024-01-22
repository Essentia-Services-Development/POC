<?php

class PeepSoPollsAjax extends PeepSoAjaxCallback
{
	private static $_peepsopolls = NULL;

	protected function __construct()
	{
		parent::__construct();
		self::$_peepsopolls = PeepSoPolls::get_instance();
	}

	public function submit_vote(PeepSoAjaxResponse $resp)
	{
		$post_id = $this->_input->int('poll_id', 0);
		$post = get_post($post_id);

		$user_id = $this->_input->int('user_id', 0);
		$new_polls = $this->_input->value('polls', array(), FALSE); // SQL safe

		$max_answers = (int) get_post_meta($post_id, 'max_answers', TRUE);

		$polls_model = new PeepSoPollsModel();
		$old_polls = $polls_model->get_user_polls($user_id, $post_id);

		if ($user_id === 0 || $user_id !== get_current_user_id()) {
			$error_message = __('Invalid user id', 'peepso-core');
		} else if ($post_id === 0) {
			$error_message = __('Invalid poll id', 'peepso-core');
		} else if (count($new_polls) === 0) {
			$error_message = __('No poll selected', 'peepso-core');
		} else if ($max_answers === 1 && count($new_polls) > 1) {
			$error_message = __('Max 1 selected', 'peepso-core');
		} else if ($polls_model->is_voted($user_id, $post_id) && FALSE == (PeepSo::is_admin() || $post->post_author==get_current_user_id() || PeepSo::get_option('polls_changevote', FALSE) )) {
			$error_message = __('Already voted', 'peepso-core');
		}

		if (isset($error_message)) {
			$resp->success(FALSE);
			$resp->error($error_message);
			return;
		}

		// Remove existing rows in database for current polls.
		$polls_model->delete_user_polls($user_id, $post_id);

		// Update total options voted.
		$options = unserialize(get_post_meta($post_id, 'select_options', TRUE));
		foreach ($options as $key => $value) {
			if (in_array($key, $old_polls)) {
				$options[$key]['total_user_poll']--;
			}
			if (in_array($key, $new_polls)) {
				$options[$key]['total_user_poll']++;
				$polls_model->save_user_polls(array(
					'user_id' => $user_id,
					'poll_id' => $post_id,
					'poll' => $key
				));
			}
		}
		update_post_meta($post_id, 'select_options', serialize($options));

		// Update total users voted.
		$total_user_poll = get_post_meta($post_id, 'total_user_poll', TRUE);

		$total_user_poll = 0;

		foreach($options as $key => $value) {
            $total_user_poll += $options[$key]['total_user_poll'];
		}

        update_post_meta($post_id, 'total_user_poll', $total_user_poll);

		$resp->success(TRUE);
		$data = array(
			'id' => $post_id,
			'options' => (is_array($options) && count($options) > 1) ? $options : array(),
			'type' => $max_answers === 0 ? 'checkbox' : 'radio',
			'enabled' => FALSE,
			'is_voted' => TRUE,
			'total_user_poll' => $total_user_poll,
			'user_polls' => $new_polls
		);

		$resp->set('html', PeepSoTemplate::exec_template('polls', 'content-media', $data, TRUE));
	}

	public function change_vote(PeepSoAjaxResponse $resp)
	{
		$post_id = $this->_input->int('poll_id', 0);
		$user_id = $this->_input->int('user_id', 0);

		if ($user_id === 0 || $user_id !== get_current_user_id()) {
			$error_message = __('Invalid user id', 'peepso-core');
		} else if ($post_id === 0) {
			$error_message = __('Invalid poll id', 'peepso-core');
		}

		if (isset($error_message)) {
			$resp->success(FALSE);
			$resp->error($error_message);
			return;
		}

		$polls_model = new PeepSoPollsModel();
		$user_polls = $polls_model->get_user_polls($user_id, $post_id);

		$max_answers = (int) get_post_meta($post_id, 'max_answers', TRUE);
		$options = unserialize(get_post_meta($post_id, 'select_options', TRUE));
		$total_user_poll = get_post_meta($post_id, 'total_user_poll', TRUE);

		$resp->success(TRUE);
		$data = array(
			'id' => $post_id,
			'options' => (is_array($options) && count($options) > 1) ? $options : array(),
			'type' => $max_answers === 0 ? 'checkbox' : 'radio',
			'enabled' => TRUE,
			'is_voted' => FALSE,
			'total_user_poll' => $total_user_poll,
			'user_polls' => $user_polls
		);

		$resp->set('html', PeepSoTemplate::exec_template('polls', 'content-media', $data, TRUE));
	}

	public function unvote(PeepSoAjaxResponse $resp)
	{
		$post_id = $this->_input->int('poll_id', 0);
		$user_id = $this->_input->int('user_id', 0);

		if ($user_id === 0 || $user_id !== get_current_user_id()) {
			$error_message = __('Invalid user id', 'peepso-core');
		} else if ($post_id === 0) {
			$error_message = __('Invalid poll id', 'peepso-core');
		}

		if (isset($error_message)) {
			$resp->success(FALSE);
			$resp->error($error_message);
			return;
		}

		$polls_model = new PeepSoPollsModel();
		$user_polls = $polls_model->get_user_polls($user_id, $post_id);

		// Update total options voted.
		$options = unserialize(get_post_meta($post_id, 'select_options', TRUE));
		foreach ($options as $key => $value) {
			if (in_array($key, $user_polls)) {
				$options[$key]['total_user_poll']--;
			}
		}
		update_post_meta($post_id, 'select_options', serialize($options));

		// Update total users voted.
		$total_user_poll = get_post_meta($post_id, 'total_user_poll', TRUE);
		update_post_meta($post_id, 'total_user_poll', --$total_user_poll);

		// Finally, remove rows in database for current polls.
		$polls_model->delete_user_polls($user_id, $post_id);

		$max_answers = (int) get_post_meta($post_id, 'max_answers', TRUE);

		$resp->success(TRUE);
		$data = array(
			'id' => $post_id,
			'options' => (is_array($options) && count($options) > 1) ? $options : array(),
			'type' => $max_answers === 0 ? 'checkbox' : 'radio',
			'enabled' => TRUE,
			'is_voted' => FALSE,
			'total_user_poll' => $total_user_poll,
			'user_polls' => array()
		);

		$resp->set('html', PeepSoTemplate::exec_template('polls', 'content-media', $data, TRUE));
	}

}
