<?php

class PeepSoAdminConfigVipicons extends PeepSoAjaxCallback
{
	public function create(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		// Insert a new peepso_user_field
		$post_data = array(
			'post_title' => __('New VIP Icon', 'peepso-core'),
			'post_name' => '',
			'post_content' => __('VIP Icon description', 'peepso-core'),
			'post_excerpt' => 'def_question.svg',
			'post_type'=>'peepso_vip_user',
			'post_status'=>'publish',
		);

		if($post_id = wp_insert_post($post_data)) {

			// Make sure the box is open for this administrator
			add_user_meta(get_current_user_id(), 'peepso_admin_vipicon_open_'.$post_id,'1',TRUE);

			// Make sure new post is sorted at the end
			$post_data = array(
				'ID'			=> $post_id,
				'menu_order'	=> $post_id,
			);

			wp_update_post($post_data);

			// Prepare Data & HTML output
			$model = new PeepSoVipIconsModel();
			ob_start();
			PeepSoTemplate::exec_template('vip','admin_vipicon', array('icon'=>$model->vipicon($post_id),'force_open' => 1));
			$html = ob_get_clean();



			// Set response
			$resp->set('id', $post_id);
			$resp->set('html', $html);
			$resp->success(TRUE);
		}
	}

	public function update(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$id   = $this->_input->raw('id');

		// SQL safe, only allowed for admin
		$prop = $this->_input->value('prop','',FALSE);

		// SQL injection safe, this endpoint only runs for admins
		$val  = $this->_input->value('value','', FALSE);

		// Opening and closing boxes
		if('box_status' == $prop) {
			$status = $this->_input->int('status', 0);

			$id = json_decode(html_entity_decode($id));

			foreach($id as $post_id ) {
				update_user_meta(get_current_user_id(), 'peepso_admin_vipicon_open_' . (int) $post_id, $status);
			}

			$resp->success(TRUE);
			return(TRUE);
		}

		// Modifying post data
		$post = array(
			'ID' 	=> (int) $id,
			$prop 	=> $val,
		);

		wp_update_post($post);

		$resp->set('message', "{$post['ID']}->{$prop}=$val");
		$resp->success(TRUE);
	}

	public function delete(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$post = WP_Post::get_instance($this->_input->int('id'));

		if ('peepso_vip' == $post->post_type) {
			$resp->success(FALSE);
			$resp->error(__('Cannot delete core vip icon', 'peepso-core'));
			return;
		}

		wp_delete_post($post->ID);

		$resp->success(TRUE);
	}

	public function reorder(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		// SQL safe, expected JSON
		if( $id = json_decode($this->_input->value('id', array(), FALSE)) ) {
			$i = 1;
			foreach( $id as $post_id ) {
				$post = array(
					'ID' 			=> $post_id,
					'menu_order' 	=> $i++,
				);

				wp_update_post($post);
			}
		}
		$resp->success(TRUE);
	}
}
// EOF
