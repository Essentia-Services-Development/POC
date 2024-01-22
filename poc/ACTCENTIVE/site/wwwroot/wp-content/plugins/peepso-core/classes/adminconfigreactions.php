<?php

class PeepSoAdminConfigReactions extends PeepSoAjaxCallback
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
			'post_title' => __('New reaction', 'peepso-core'),
			'post_name' => '',
			'post_content' => __('reacted to', 'peepso-core'),
			'post_excerpt' => 'rest_2753.svg',
			'post_type'=>'peepso_reaction_user',
			'post_status'=>'publish',
		);

		if($post_id = wp_insert_post($post_data)) {

			// Make sure the box is open for this administrator
			add_user_meta(get_current_user_id(), 'peepso_admin_reaction_open_'.$post_id,'1',TRUE);

			// Make sure new post is sorted at the end
			$post_data = array(
				'ID'			=> $post_id,
				'menu_order'	=> $post_id,
			);

			// Mark the field as having a default title
			add_post_meta($post_id, 'default_title', 1, TRUE);

			wp_update_post($post_data);

			// Prepare Data & HTML output
			$model = new PeepSoReactionsModel();
			ob_start();
			PeepSoTemplate::exec_template('reactions','admin_reaction', array('reaction'=>$model->reaction($post_id),'force_open' => 1));
			$html = ob_get_clean();



			// Set response
			$resp->set('id', $post_id);
			$resp->set('html', $html);
			$resp->success(TRUE);

            PeepSo::get_instance()->reactions_rebuild_cache();
		}
	}

	public function update(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}


		$id   = $this->_input->value('id','',FALSE);
		$prop = $this->_input->value('prop','',FALSE);
		$val  = $this->_input->value('value','',FALSE);

		// Opening and closing boxes
		if('box_status' == $prop) {
			$status = $this->_input->int('status', 0);

			$id = json_decode(html_entity_decode($id));

			foreach($id as $post_id ) {
				update_user_meta(get_current_user_id(), 'peepso_admin_reaction_open_' . $post_id, $status);
			}

			$resp->success(TRUE);
			return(TRUE);
		}

        if('emotion' == $prop) {
            update_post_meta($id, 'reaction_emotion', (string) $val);
        }

		// Modifying post data
		$post = array(
			'ID' 	=> (int) $id,
			$prop 	=> $val,
		);

		wp_update_post($post);

		if('post_title' == $prop) {
			delete_post_meta($post['ID'], 'default_title');
		}

		$resp->set('message', "{$post['ID']}->{$prop}=$val");
		$resp->success(TRUE);

		PeepSo::get_instance()->reactions_rebuild_cache();
	}

	public function delete(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		$post = WP_Post::get_instance($this->_input->int('id'));

		if ('peepso_reaction' == $post->post_type) {
			$resp->success(FALSE);
			$resp->error(__('Cannot delete core fields', 'peepso-core'));
			return;
		}

		wp_delete_post($post->ID);


		global $wpdb;
		$sql = "DELETE FROM `{$wpdb->prefix}" . PeepSoReactionsModel::TABLE . "` WHERE `reaction_type`=$post->ID";
		$wpdb->query($sql);

		$resp->success(TRUE);

        PeepSo::get_instance()->reactions_rebuild_cache();
	}

	public function reorder(PeepSoAjaxResponse $resp)
	{
		if (!PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

		// SQL safe, admin only & JSON
		if( $id = json_decode($this->_input->value('id','',FALSE)) ) {
			foreach( $id as $post_id ) {
				$post = array(
					'ID' 			=> $post_id,
					'menu_order' 	=> $i++,
				);

				wp_update_post($post);
			}
		}
		$resp->success(TRUE);

        PeepSo::get_instance()->reactions_rebuild_cache();
	}
}
// EOF
