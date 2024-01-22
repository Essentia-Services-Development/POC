<?php

class PeepSoModalComments extends PeepSoAjaxCallback
{
    /**
     * Called from PeepSoAjaxHandler
     * Declare methods that don't need auth to run
     * @return array
     */
    public function ajax_auth_exceptions()
    {
        return array(
            'get_object',
        );
    }

	/**
	 * Gets the comments to be displayed for a commentable object.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function get_object_comments(PeepSoAjaxResponse $resp)
	{
		$object_id = $this->_input->int('object_id', NULL);
		$type = $this->_input->value('type', NULL, FALSE); // SQL Safe

		if (is_null($object_id) || is_null($type)) {
			$resp->success(FALSE);
			$resp->error(__('Could not find the post.', 'peepso-core'));
		} else {
			$object_data = apply_filters('peepso_get_object_comments', array());
			$resp->success(TRUE);
		}
	}

	/**
	 * Gets the HTML to be displayed for a commentable object.
	 * @param  PeepSoAjaxResponse $resp
	 */
	public function get_object(PeepSoAjaxResponse $resp)
	{
		global $post;

		// The act_id - since comments are joined from the activities table
		$act_external_id = $this->_input->int('object_id', NULL);
		$type = $this->_input->value('type', NULL, FALSE); // SQL Safe

		if (is_null($act_external_id) || is_null($type)) {
			$resp->success(FALSE);
			$resp->error(__('Could not find the post.', 'peepso-core'));
		} else {
			// The format must be
			//   $objects = array(
			//   	ACT_EXTERNAL_ID => array(
			//   		'module_id' => MODULE_ID
			//   		'content' => CONTENT,
			//   		'post' => WP_POST data
			//   	);
			//   );
			// so we can get which index to display first.
			$objects = apply_filters('peepso_get_object_' . $type, array(), $act_external_id);
			$activity = PeepSoActivity::get_instance();

			$total_objects = count($objects);
			$index = 0;
			$count = 0;

			add_filter('peepso_post_filters', array(&$this, 'add_post_actions'));

			// Wrap each object in a div
			foreach ($objects as $key => &$object) {
				$act_id = isset($object['act_id']) ? $object['act_id'] : NULL;
				$act_description = isset($object['act_description']) ? $object['act_description'] : NULL;
				$act = $activity->get_activity_data($key, $object['module_id']);
				$act_post_data = array_merge((array) $object['post'], (array) $act);
				$object = array_merge($object, $act_post_data);

				// find index
				if (isset($object['object_index_key']) && ($object['object_index_key'] == $act_external_id)) {
					$index = $count;
				}
				$count++;

				$object['ID'] = $key;
				$object['type'] = $type;
				$object['object_id'] = $act_external_id;
				$activity->post_data = $object;
				$object['_total_objects'] = $total_objects;
				$object['post_parent'] = isset($object['post']) ? $object['post']->act_id : FALSE;

				// disable privacy for multiple object, should follow parent (post) object
				$object['disable_privacy'] = $total_objects > 1 ? TRUE : FALSE;

				// object caption should be the post content in case of single object
				$_total_objects = apply_filters('peepso_get_total_object_' . $type, $total_objects, $act_external_id);
				$post = get_post($object['post']->ID);
				if ($_total_objects == 1 && !isset($object['using_activity_desc'])) {
					// $object['post_attachments'] = $activity->get_content_attachments($object['post']);
					// $object['post_attachments'] = $activity->format_content_attachments($object['post_attachments']);
					// remove_all_filters('peepso_activity_content_attachments');
					$object['act_description'] = apply_filters('peepso_activity_content_before', $activity->content($object['post'], FALSE));
				} else {
					// PeepSo/peepso#2588 do not execute shortcode on peepso activity or comment
					// $object['act_description'] = do_shortcode($act_description);
					$act_description = substr(PeepSoSecurity::strip_content($act_description), 0, PeepSo::get_option('site_status_limit', 4000));

					$psnewline = 'PEEPSONEWLINE';
					if(defined('PEEPSO_NEWLINE')) {
						$psnewline = PEEPSO_NEWLINE;
					}

					$act_description = strip_tags($act_description, '<a>');
					$act_description = str_replace("\n",$psnewline, $act_description);
					$act_description = convert_smilies($act_description);
					$act_description = str_replace($psnewline, "<br/>", $act_description);

					$object['act_description'] = apply_filters('peepso_activity_content_before', $act_description);
					$object['act_id'] = $act_id !== NULL ? $act_id : $object['act_id'];
				}

				unset($object['post']);
				$post = (object) $object;// convert $post from array to object
				setup_postdata($post);

				#6585 Fix missing custom `author_id` data.
				$post->author_id = $post->post_author;

				$object['content'] = PeepSoTemplate::exec_template('activity', 'comment-modal', $object, TRUE);
				$object['attachment'] = PeepSoTemplate::exec_template('activity', 'comment-modal-attachment', $object, TRUE);
			}

			remove_filter('peepso_post_filters', array(&$this, 'add_post_actions'));

			$resp->set('index', $index);
			$resp->set('objects', array_values($objects)); // Reindex array, needed for javascript index (displays which item was clicked)
			$resp->success(TRUE);
		}
	}

	/**
	 * Adds edit and delete option to objects on the modal.
	 * @param array $options The default options per post
	 * @return  array
	 */
	public function add_post_actions($options)
	{
		$post = $options['post'];

		$user_id = get_current_user_id();

		$PeepSoActivity = new PeepSoActivity();
		$parent_act_id = $post->post_parent;
		$parent_post = $PeepSoActivity->get_activity_post($parent_act_id);

		if (PeepSo::check_permissions(intval($post->author_id), PeepSo::PERM_POST_DELETE, $user_id)) {
			$delete_script = 'return activity.delete_activity(' . $post->act_id . ');';

			if ($post->_total_objects == 1) {
				$delete_script = 'return activity.action_delete(' . $post->ID . ');';
			}

			$options['acts']['delete'] = array(
				'href' => 'javscript:void(0);',
				'label' => __('Delete', 'peepso-core'),
				'icon' => 'gcis gci-trash',
				'click' => $delete_script,
				'extra' => 'data-lightbox-action="1"',
			);
		}

		// only add this if current_user == owner_id or it's an admin
		if (PeepSo::check_permissions(intval($post->author_id), PeepSo::PERM_POST_EDIT, $user_id)) {
			$options['acts']['edit'] = array(
				'href' => 'javscript:void(0);',
				'label' => __('Edit Caption', 'peepso-core'),
				'icon' => 'gcis gci-edit',
				'click' => 'activity.edit_activity_description(' . $post->act_id . ', \"' . $post->type . '\", \"' . $post->object_id . '\"); return false',
				'extra' => 'data-lightbox-action="1"',
			);
		}

        unset($options['acts']['pin']);
        unset($options['acts']['unpin']);
        unset($options['acts']['sub_pin_indefinitely']);
        unset($options['acts']['sub_pin_date']);

		// Edit enable/disable comments based on the parent post.
		if ($parent_post->ID != $post->ID) {
			$options['acts'] = $PeepSoActivity->post_options_add_toggle_comments($options['acts'], $parent_post);
		}

		return ($options);
	}
}

// EOF
