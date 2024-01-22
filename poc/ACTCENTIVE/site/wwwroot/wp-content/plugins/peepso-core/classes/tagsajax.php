<?php

class PeepSoTagsAjax extends PeepSoAjaxCallback
{
	/**
	 * Get `taggable` users based on the Friends add-on or users that have commented on a post.
	 * Returns a json string in the following format
	 */
	public function get_taggable(PeepSoAjaxResponse $resp)
	{
		// { id:1, name:'Daniel Zahariev',  'img':'http://example.com/img1.jpg', 'type':'user' }
		$user_id = get_current_user_id();

		$profile = PeepSoActivity::get_instance();

		$taggable = array();

		// Get activity participants first, if available
		$act_id = $this->_input->int('act_id', NULL);

		if (!is_null($act_id) && FALSE === is_null($activity = $profile->get_activity_post($act_id))) {
			// add author as default
			$author = PeepSoUser::get_instance($activity->post_author);

			// prevent user from tagged by himself
			if ($author->get_id() !== get_current_user_id()) {
				$taggable[$author->get_id()] = array(
						'id' => $author->get_id(),
						'name' => $author->get_fullname(),
						'avatar' => $author->get_avatar(),
						'icon' => $author->get_avatar(),
						'type' => 'author'
				);
			}

			// if is reply
			if ($activity->post_type == PeepSoActivityStream::CPT_COMMENT) {
				$parent_activity = $profile->get_activity_data($activity->act_comment_object_id, $activity->act_comment_module_id);

				if (is_object($parent_activity)) {

					$parent_post = $profile->get_activity_post($parent_activity->act_id);
					$parent_id = $parent_post->act_external_id;

					// check if parent post is a comment
					if($parent_post->post_type == 'peepso-comment') {
						$comment_activity = $profile->get_activity_data($activity->act_comment_object_id, $activity->act_comment_module_id);
						$post_activity = $profile->get_activity_data($comment_activity->act_comment_object_id, $comment_activity->act_comment_module_id);

						$parent_comment = $profile->get_activity_post($comment_activity->act_id);
						$parent_post = $profile->get_activity_post($post_activity->act_id);
					} 

					if (!in_array($parent_post->post_author, $taggable) && intval($parent_post->post_author) !== get_current_user_id()) {
						$parent_post_author = PeepSoUser::get_instance($parent_post->post_author);

						$taggable[$parent_post_author->get_id()] = array(
								'id' => $parent_post_author->get_id(),
								'name' => $parent_post_author->get_fullname(),
								'avatar' => $parent_post_author->get_avatar(),
								'icon' => $parent_post_author->get_avatar(),
								'type' => 'author'
						);
					}
				}

				// $parent_activity = PeepSoActivity::get_instance();
				// $parent_activity_data = $parent_activity->get_activity_data($activity->act_comment_object_id);
				// $parent_post = $parent_activity->get_activity_post($parent_activity_data->act_id);

				// if (!in_array($parent_post->post_author, $taggable) && intval($parent_post->post_author) !== get_current_user_id()) {
				// 	$parent_post_author = PeepSoUser::get_instance($parent_post->post_author);

				// 	$taggable[$parent_post_author->get_id()] = array(
				// 			'id' => $parent_post_author->get_id(),
				// 			'name' => $parent_post_author->get_fullname(),
				// 			'avatar' => $parent_post_author->get_avatar(),
				// 			'icon' => $parent_post_author->get_avatar(),
				// 			'type' => 'author'
				// 	);
				// }
			}

			$users = $profile->get_comment_users($activity->act_external_id, $activity->act_module_id);

			while ($users->have_posts()) {

				$users->next_post();

				// skip if user was already found
				if (in_array($users->post->post_author, $taggable))
					continue;

				$user = PeepSoUser::get_instance($users->post->post_author);

				if (!$user->is_accessible('profile'))
					continue;

				$taggable[$user->get_id()] = array(
						'id' => $user->get_id(),
						'name' => trim(strip_tags($user->get_fullname())),
						'avatar' => $user->get_avatar(),
						'icon' => $user->get_avatar(),
						'type' => 'friend'
				);
			}
		}

		// Also get friends if available
		if (class_exists('PeepSoFriendsPlugin')) {
			$peepso_friends = PeepSoFriends::get_instance();

			while ($friend = $peepso_friends->get_next_friend($user_id)) {

				// skip if user was already found
				if (in_array($friend->get_id(), $taggable)) {
					continue;
				}

				if (!$friend->is_accessible('profile'))
					continue;

				$taggable[$friend->get_id()] = array(
					'id' => $friend->get_id(),
					'name' => $friend->get_fullname(),
					'avatar' => $friend->get_avatar(),
					'icon' => $friend->get_avatar(),
					'type' => 'friend'
				);
			}
		}
		
		// check on profile page
		$username = str_replace(PeepSo::get_page('profile') . '?', '', $_SERVER['HTTP_REFERER']);
		$username = explode('/', $username);
		$username = $username[0];

		$user = get_user_by('login', $username);
		if (is_object($user) && !in_array($user->ID, $taggable)) {
			$user = PeepSoUser::get_instance($user->ID);
			$taggable[$user->get_id()] = array(
				'id' => $user->get_id(),
				'name' => trim(strip_tags($user->get_fullname())),
				'avatar' => $user->get_avatar(),
				'icon' => $user->get_avatar(),
				'type' => 'profile'
			);		
		}
		
		$taggable = apply_filters('peepso_taggable', $taggable, $act_id);

		$resp->success(TRUE);
		$resp->set('users', $taggable);
	}
}
