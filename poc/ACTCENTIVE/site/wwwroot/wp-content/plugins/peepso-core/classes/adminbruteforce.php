<?php

class PeepSoAdminBruteForce extends PeepSoAjaxCallback
{
	/** 
	 * DIsplays the table for managing the Request data queue.
	 */
	public static function administration()
	{
		if (isset($_GET['action']) && 'clear-brute-force-logs' === $_GET['action']
			&& check_admin_referer('clear-brute-force-logs-nonce')) {
			PeepSoBruteForce::clear_logs();

			$aProcessed = get_option('peepso_clear_brute_force_history');
			$aLastProcessed = end($aProcessed);

			if ($aLastProcessed['processed'] > 0) {
				$iCountProcessed = $aLastProcessed['processed'];
				$fElpasedTime = round($aLastProcessed['elapsed'], 2);

				PeepSoAdmin::get_instance()->add_notice(
					// @todo count failures
					sprintf(__('%1$d %2$s deleted in %3$d seconds', 'peepso-core'),
						$iCountProcessed,
						_n('login attempt', 'login attempts', $iCountProcessed, 'peepso-core'),
						$fElpasedTime),
					'note');
			} else {
				PeepSoAdmin::get_instance()->add_notice(__('The login attempts logs is empty.', 'peepso-core'), 'note');
			}

			PeepSo::redirect(admin_url('admin.php?page=peepso-manage&tab=brute-force'));
		}

		$oPeepSoListTable = new PeepSoBruteForceListTable();
		$oPeepSoListTable->prepare_items();

		#echo "<div id='peepso' class='wrap'>";
		// PeepSoAdmin::admin_header(__('Brute Force Attempts Logs', 'peepso-core'));

		echo '<form id="form-brute-force" method="post">';
		wp_nonce_field('bulk-action', 'brute-force-nonce');
		$oPeepSoListTable->display();
		echo '</form>';
	}

	/**
	 * AJAX callback - Dismisses the selected item identified by $_POST['rep_id']
	 * and sets the proper response.
	 *
	 * @param  PeepSoAjaxResponse $resp The response is_object
	 * @return void
	 */
	public function delete(PeepSoAjaxResponse $resp)
	{
		if (FALSE === PeepSo::is_admin()) {
			$resp->success(FALSE);
			$resp->error(__('Insufficient permissions.', 'peepso-core'));
			return;
		}

        // SQL safe, WP sanitizes it
		if (FALSE === wp_verify_nonce($this->_input->value('_wpnonce','',FALSE), 'bulk-action')) {
			$resp->error(__('Could not verify nonce.', 'peepso-core'));
			$resp->success(FALSE);
		} else {
			$bruteForce = new PeepSoBruteForce();

			$success = $bruteForce->delete_logs($this->_input->int('attempts_id'));
            $resp->notice(__('Deleted successfully.', 'peepso-core'));
			$resp->set('count', $success);
			$resp->success($success);
		}
	}
}