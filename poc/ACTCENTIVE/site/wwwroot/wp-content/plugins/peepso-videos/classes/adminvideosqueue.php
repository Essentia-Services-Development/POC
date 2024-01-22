<?php

class PeepSoAdminVideosQueue
{
	/** 
	 * DIsplays the table for managing the Request data queue.
	 */
	public static function administration()
	{

		$oPeepSoListTable = new PeepSoAdminVideosQueueListTable();
		$oPeepSoListTable->prepare_items();

		echo '<form id="form-request-data" method="post">';
		wp_nonce_field('bulk-action', 'videos-queue-nonce');
		$oPeepSoListTable->display();
		echo '</form>';


        echo PeepSoTemplate::exec_template('admin', 'queue-status-description');
	}
}