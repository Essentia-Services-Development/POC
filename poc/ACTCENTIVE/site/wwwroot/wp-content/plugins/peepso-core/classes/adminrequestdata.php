<?php

class PeepSoAdminRequestData
{
	/** 
	 * DIsplays the table for managing the Request data queue.
	 */
	public static function administration()
	{
		if (isset($_GET['action']) && 'process-request-data' === $_GET['action']
			&& check_admin_referer('process-request-data-nonce')) {
			PeepSoGdpr::process_export_data();
			PeepSoGdpr::process_cleanup_data();
			PeepSo::redirect(admin_url('admin.php?page=peepso-gdpr-request-data'));
		}

		$oPeepSoListTable = new PeepSoGdprListTable();
		$oPeepSoListTable->prepare_items();

		echo '<form id="form-request-data" method="post">';
		wp_nonce_field('bulk-action', 'request-data-nonce');
		$oPeepSoListTable->display();
		echo '</form>';

		echo PeepSoTemplate::exec_template('admin', 'queue-status-description');
		#echo "</div>";
	}
}