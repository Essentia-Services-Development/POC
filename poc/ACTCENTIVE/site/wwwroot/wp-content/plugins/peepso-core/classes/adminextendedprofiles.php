<?php

class PeepSoAdminExtendedProfiles extends PeepSoAjaxCallback
{
	public function add_field(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$type = $this->_input->value('type','',false);// SQL safe, admin only

		// This should be the class name of added field
		$class = 'PeepSoField'.$type;

		// If empty or the class can't be found
		if( !strlen($type)  ||  !class_exists($class)) {
			$resp->error(__('Invalid type','peepso-core'));
		}

		// Insert a new peepso_user_field
		$post_data = array(
			'post_title' => $class::$admin_label,
			'post_name' => 'cpf',
			'post_type'=>'peepso_user_field',
			'post_status'=>'publish',
		);

		if($field_id = wp_insert_post($post_data)) {

			// Default the "order" to field_id, ensures the new field will be on the bottom
			add_post_meta($field_id, 'order', 			$field_id, 	TRUE);
			// Attach the class responsible for this field
			add_post_meta($field_id, 'class', 			$type, 		TRUE);
			// Custom fields are never "core"
			add_post_meta($field_id, 'is_core', 		0, 			TRUE);
			// Flag the title as never edited - the UI uses it to empty the title field upon first edit
			add_post_meta($field_id, 'default_title', 	1, 			TRUE);



			// Make sure the box is open for this administrator
			add_user_meta(get_current_user_id(), 'peepso_admin_profile_field_open_'.$field_id,'1',TRUE);

			// Grab the first available privacy level
			$privacy = PeepSoPrivacy::get_instance();
			$access_settings = $privacy->get_access_settings();
			$keys = array_keys($access_settings);
			$default_acc = $keys[0];
			add_post_meta($field_id, 'default_acc', $default_acc, TRUE);

			// Field instance
			$field =  $class::get_field_by_id( $field_id );

			// We need to update some post data after creating it
			$post = get_post( $field_id );

			// Default post_name to post ID - see PeepSoField::__construct()
			$post->post_name = $post->ID;

			// Default description (prompt)
			$post->post_content = $field->default_desc;

			// Store the post data
			wp_update_post($post);

			// Grab the first available render_method
			reset($field->render_methods);
			$render = key($field->render_methods);
			add_post_meta($field_id, 'method',$render, TRUE);

			// Grab the first available render_form_method
			reset($field->render_form_methods);
			$render_form = key($field->render_form_methods);
			add_post_meta($field_id, 'method_form',$render_form, TRUE);

			// Prepare HTML output
			ob_start();
			PeepSoTemplate::exec_template('admin','profiles_field', array('field'=>$field,'force_open' => 1));
			$html = ob_get_clean();

			// Set response
			$resp->set('id', $field_id);
			$resp->set('html', $html);
			$resp->success(TRUE);
			return;
		}
	}

	public function delete_field(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$id = $this->_input->int('id');

		// Grab the post
		$post = get_post($id);

		// If not found, exit
		if(! $post instanceof WP_Post) {
			$resp->error(__('Invalid field ID','peepso-core'));
			return;
		}

		// If not a peepso_user-field, exit
		if('peepso_user_field' != $post->post_type) {
			$resp->error(__('Not a peepso_user_field','peepso-core'));
			return;
		}

		// Clean up usermeta
		global $wpdb;
		// Delete user field values
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'usermeta WHERE `meta_key` LIKE \'peepso_user_field_'.$id.'%\'');

		// Delete admin field properties
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'usermeta WHERE `meta_key` LIKE \'peepso_admin_profile_field%'.$id.'%\'');

		// Force delete (no trash)
		$resp->success(wp_delete_post($id, TRUE));
	}

	public function duplicate_field(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		if (!wp_verify_nonce($_SERVER['HTTP_X_PEEPSO_NONCE'], 'peepso-nonce')) {
			$resp->success(FALSE);
			$resp->error(__('Invalid security challenge code.', 'peepso-core'));
			return;
		}

		$id = $this->_input->int('id');

		// Grab the post
		$post = get_post($id);

		// If not found, exit
		if(! $post instanceof WP_Post) {
			$resp->error(__('Invalid field ID','peepso-core'));
			return;
		}

		// If not a peepso_user-field, exit
		if('peepso_user_field' != $post->post_type) {
			$resp->error(__('Not a peepso_user_field','peepso-core'));
			return;
		}

		$type = get_post_meta($id, 'class', TRUE);

		// This should be the class name of added field
		$class = 'PeepSoField'.$type;

		// If empty or the class can't be found
		if( !strlen($type)  ||  !class_exists($class)) {
			$resp->error(__('Invalid type','peepso-core'));
		}

		// Insert a new peepso_user_field
		$post_data = array(
			'post_title' => $post->post_title . '-' . __('COPY','peepso-core'),
			'post_name' => 'cpf',
			'post_type'=>'peepso_user_field',
			'post_status'=> $post->post_status,
			'post_content'=> $post->post_content,
		);

		if($field_id = wp_insert_post($post_data)) {

			// duplicate postmeta data
			$this->duplicate_meta_field($id, $field_id);

			// #3743 replace option ID with the correct one
			if (in_array($type, array('selectsingle', 'selectmulti'))) {
				$select_options = get_post_meta($field_id, 'select_options', TRUE);
				$new_options = [];
				if (count($select_options) > 0) {
					foreach ($select_options as $key => $value) {
						$new_key = str_replace('option_' . $id, 'option_' . $field_id, $key);
						$new_options[$new_key] = $value;
					}
				}
				update_post_meta($field_id, 'select_options', $new_options);
			}

			// Default the "order" to field_id, ensures the new field will be on the bottom
			update_post_meta($field_id, 'order', 			$field_id, 	TRUE);

			// Make sure the box is open for this administrator
			add_user_meta(get_current_user_id(), 'peepso_admin_profile_field_open_'.$field_id,'1',TRUE);

			// Field instance
			$field =  $class::get_field_by_id( $field_id );

			// We need to update some post data after creating it
			$post = get_post( $field_id );

			// Default post_name to post ID - see PeepSoField::__construct()
			$post->post_name = $post->ID;

			// Store the post data
			wp_update_post($post);

			// Prepare HTML output
			ob_start();
			PeepSoTemplate::exec_template('admin','profiles_field', array('field'=>$field,'force_open' => 1));
			$html = ob_get_clean();

			// Set response
			$resp->set('id', $field_id);
			$resp->set('html', $html);
			$resp->success(TRUE);
			return;
		}
	}

	public function reset_privacy(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$id = $this->_input->int('id');

		$field = PeepSoField::get_field_by_id($id);

		if(!$field instanceof PeepSoField) {
			$resp->error(__('Invalid field ID','peepso-core'));
			return;
		}

		$key_acc = PeepSoField::user_meta_key_add($field->key).'_acc';

		delete_metadata('user',0, $key_acc, FALSE, TRUE);

		$resp->success( TRUE );
	}

	private function duplicate_meta_field($source_post_id, $dest_post_id) {
		global $wpdb;

		$insert = $wpdb->query("INSERT INTO `$wpdb->postmeta`( post_id, meta_key, meta_value )
						SELECT ".$dest_post_id." post_id, meta_key, meta_value
						FROM  `$wpdb->postmeta`
						WHERE post_id =".$source_post_id." ");

		return $insert;
	}
}

//EOF
