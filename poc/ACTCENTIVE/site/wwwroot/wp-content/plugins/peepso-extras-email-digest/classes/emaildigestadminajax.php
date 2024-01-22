<?php

class PeepSoEmailDigestAdminAjax extends PeepSoAjaxCallback {

	public function show_log(PeepSoAjaxResponse $resp) {
		global $wpdb;

		$input = new PeepSoInput();

		$page = $input->int('page', 1);
		$limit = intval(PeepSo::get_option('site_activity_posts', 20));
		$offset = ($page - 1) * $limit;

		$ed = PeepSoEmailDigest::get_instance();
		$sql = $wpdb->prepare("SELECT * FROM `$ed->log_table` ORDER BY edl_id DESC limit %d, %d", $offset, $limit);
		$results = $wpdb->get_results($sql);

		if (count($results) > 0 && $page <= 5) {
			// Prepare HTML output
			ob_start();
			if ($page == 1) {
				echo '<style>@media(min-width:1200px){.memberdiv{min-height:100px;}}@media(min-width: 992px) and (max-width: 1199px){.memberdiv{min-height:150px;}}@media(min-width: 768px) and (max-width: 991px){.memberdiv{min-height:150px;}}.log-wrapper{max-height:65vh;overflow-y:scroll;}</style>';
			}
			foreach ($results as $item) {
				$user = PeepSoUser::get_instance($item->edl_user_id);

				$data = array(
					'edc_id' => $item->edl_content_id,
					'name' => $user->get_fullname() . ' (' . $user->get_username() . ')',
					'url' => $user->get_profileurl(),
					'avatar_url' => $user->get_avatar(),
					'last_login' => strtotime($item->edl_user_last_login) < 0 ? '-' : date(get_option('date_format'), strtotime($item->edl_user_last_login)),
					'last_email_sent' => strtotime($item->edl_sent) < 0 ? '-' : date(get_option('date_format'), strtotime($item->edl_sent))
				);
				PeepSoTemplate::exec_template('general', 'email-digest-recipient', $data);
			}

			$html = ob_get_clean();
			
			// Set response
			$resp->set('html', $html);
			$resp->success(TRUE);
			return;
		} else {
			$resp->success(FALSE);
			$resp->error(__('No Log Found.', 'peepso-email-digest'));
		}
	}
	
	public function preview_email(PeepSoAjaxResponse $resp) {
		$input = new PeepSoInput();
		$ed = PeepSoEmailDigest::get_instance();
		
		if ($ed->preview_email($input->int('edc_id'))) {
			$resp->success(TRUE);
		} else {
			$resp->success(FALSE);
		}
	}
}
