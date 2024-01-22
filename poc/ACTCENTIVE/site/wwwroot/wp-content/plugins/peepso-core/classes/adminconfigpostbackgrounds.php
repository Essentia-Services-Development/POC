<?php

class PeepSoAdminConfigPostBackgrounds extends PeepSoAjaxCallback
{
    public function create(PeepSoAjaxResponse $resp)
    {
        if (!PeepSo::is_admin()) {
            $resp->success(false);
            $resp->error(__('Insufficient permissions.', 'peepso-core'));
            return;
        }

        // Insert a new peepso_user_field
        $post_data = array(
            'post_title' => __('New post background', 'peepso-core'),
            'post_name' => '',
            'post_content' => json_encode([
                'image' => '1.png',
                'background_color' => '#ba2f31',
                'text_color' => '#ffffff',
                'text_shadow_color' => 'rgba(255,255,255,0.2)',
				'custom' => 1
            ]),
            'post_type' => 'peepso_post_bg',
            'post_status' => 'private',
        );

        if ($post_id = wp_insert_post($post_data)) {

            // Make sure the box is open for this administrator
            add_user_meta(get_current_user_id(), 'peepso_admin_post_backgrounds_open_' . $post_id, '1', true);

            // Make sure new post is sorted at the end
            $post_data = array(
                'ID' => $post_id,
                'menu_order' => $post_id,
            );

            // Mark the field as having a default title
            add_post_meta($post_id, 'default_title', 1, true);

            wp_update_post($post_data);

            // Prepare Data & HTML output
            $model = new PeepSoPostBackgroundsModel();
            ob_start();
            PeepSoTemplate::exec_template('post-backgrounds', 'admin_post_backgrounds_item', array('post_backgrounds' => $model->post_backgrounds($post_id), 'force_open' => 1));
            $html = ob_get_clean();

            // Set response
            $resp->set('id', $post_id);
            $resp->set('html', $html);
            $resp->success(true);
        }
    }

    public function update(PeepSoAjaxResponse $resp)
    {
        if (!PeepSo::is_admin()) {
            $resp->success(false);
            $resp->error(__('Insufficient permissions.', 'peepso-core'));
            return;
        }

        $id = $this->_input->value('id', '', false);
        $prop = $this->_input->value('prop', '', false);
        $val = $this->_input->value('value', '', false);

		// Opening and closing boxes
        if ('box_status' == $prop) {
            $status = $this->_input->int('status', 0);

            $id = json_decode(html_entity_decode($id));

            foreach ($id as $post_id) {
                update_user_meta(get_current_user_id(), 'peepso_admin_post_backgrounds_open_' . $post_id, $status);
            }

            $resp->success(true);
            return (true);
        } else if (strpos($prop, 'post_content') !== FALSE) {
			$post = get_post($id);
			$key = explode('|', $prop)[1];
			$post_content = json_decode($post->post_content, true);
			$post_content[$key] = $val;
			$prop = 'post_content';
			$val = json_encode($post_content);
		}

        // Modifying post data
        $post = array(
            'ID' => (int) $id,
            $prop => $val,
        );

        wp_update_post($post);

        if ('post_title' == $prop) {
            delete_post_meta($post['ID'], 'default_title');
        }

        $resp->set('message', "{$post['ID']}->{$prop}=$val");
        $resp->success(true);
    }

    public function delete(PeepSoAjaxResponse $resp)
    {
        if (!PeepSo::is_admin()) {
            $resp->success(false);
            $resp->error(__('Insufficient permissions.', 'peepso-core'));
            return;
        }

        $post = WP_Post::get_instance($this->_input->int('id'));
        wp_delete_post($post->ID);
        $resp->success(true);
    }

    public function reorder(PeepSoAjaxResponse $resp)
    {
        if (!PeepSo::is_admin()) {
            $resp->success(false);
            $resp->error(__('Insufficient permissions.', 'peepso-core'));
            return;
        }

        // SQL safe, admin only & JSON
        if ($id = json_decode($this->_input->value('id', '', false))) {
            $i = 1;
            foreach ($id as $post_id) {
                $post = array(
                    'ID' => $post_id,
                    'menu_order' => $i++,
                );

                wp_update_post($post);
            }
        }
        $resp->success(true);
    }
}
// EOF
