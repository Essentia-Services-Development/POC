<?php

class PeepSoAdminPostBackgrounds
{
	public static function administration()
	{
		self::enqueue_scripts();

		if(isset($_GET['reinstall'])) {
		   global $wpdb;
		   var_dump($wpdb->delete($wpdb->posts, ['post_type'=>'peepso_post_bg']));
		   PeepSo::redirect(admin_url('admin.php?page=peepso-manage&tab=post-backgrounds'));
        }

		if (isset($_GET['action']) && $_GET['action'] == 'reset-post-backgrounds' && isset($_GET['id']) && wp_verify_nonce($_GET['_wpnonce'], 'reset-post-backgrounds-nonce')) {
			$post = get_post($_GET['id']);

			if ($post->post_type == 'peepso_post_bg') {
				$post_data = [
					'ID' => $_GET['id'],
					'post_content' => $post->post_excerpt
				];

				wp_update_post($post_data);
			}

			// prevent deletion on refreshed page
            nocache_headers();
			wp_redirect(admin_url('admin.php?page=peepso-manage&tab=post-backgrounds'));
		}

		$PeepSoPostBackgroundsModel = new PeepSoPostBackgroundsModel();

		PeepSoTemplate::exec_template('post-backgrounds','admin_post_backgrounds', $PeepSoPostBackgroundsModel->post_backgrounds);
	}

	public static function enqueue_scripts()
	{
		//
		//  Colorpicker alpha script
		//
		wp_enqueue_style( 'wp-color-picker' );
		wp_register_script( 'wp-color-picker-alpha', PeepSo::get_asset('js/admin/wp-color-picker-alpha.min.js'), array( 'wp-color-picker' ), PeepSo::PLUGIN_VERSION, TRUE );

		wp_deregister_script('peepso-admin-manage-post-backgrounds');
		wp_enqueue_script('peepso-admin-manage-post-backgrounds', PeepSo::get_asset('js/admin/manage-post-backgrounds.js'),
			array('peepso', 'jquery-ui-sortable', 'wp-color-picker-alpha'), PeepSo::PLUGIN_VERSION, TRUE);
	}
}
