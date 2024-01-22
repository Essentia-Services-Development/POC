<?php

class PeepSoAdminConfigFields extends PeepSoAjaxCallback
{
	public function set_prop(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$id = $this->_input->int('id');
		$prop = $this->_input->value('prop','',false); // SQL safe, admin only
		$value = $this->_input->value('value','',false);// SQL safe, admin only

		$post = array(
			'ID'    => $id,
			$prop   => $value,
		);

		wp_update_post($post);

		if('post_title' == $prop ) {
			delete_post_meta($id, 'default_title');
		}

		$resp->success(TRUE);
	}

	public function set_meta(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$id = $this->_input->int('id');
		$prop = $this->_input->value('prop','',false);// SQL safe, admin only

		$value = $this->_input->value('value','',false);// SQL safe, admin only

		if(1 == $this->_input->int('json',0)) {
			$value = htmlspecialchars_decode($value);
			$value = json_decode($value, TRUE);
		}

		$key = $this->_input->value('key', NULL, false);// SQL safe, admin only

		$meta_value = get_post_meta($id, $prop, 1);

		if( NULL !== $key) {
			if(!is_array($meta_value)) {
				$meta_value = array();
			}
			$meta_value[$key] = $value;
		} else {
			$meta_value = $value;
		}

		update_post_meta($id, $prop, $meta_value);
		$resp->success(TRUE);
	}

	public function set_order(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		if( $fields = json_decode($this->_input->value('fields','',false)) ) { // SQL safe, admin only
			$i = 1;
			foreach( $fields as $id ) {
				update_post_meta( $id, 'order', $i);
				$i++;
			}
		}

		$resp->success(TRUE);
	}

	public function set_admin_box_status(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$id 	= $this->_input->value('id','',false); // SQL safe, admin only
		$status = $this->_input->int('status', 0);

		$id = json_decode($id);

		foreach($id as $field_id) {
			update_user_meta(get_current_user_id(), 'peepso_admin_profile_field_open_' . $field_id, $status);
		}

		$resp->success(TRUE);
	}
}

// EOF
