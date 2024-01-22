<?php
if(class_exists('PeepSoMaintenanceFactory')) {
	class PeepSoMaintenanceBannedUsers extends PeepSoMaintenanceFactory {

		public static function destroySessions() {

			$completed = 0;

			global $wpdb;

			// Grab $batch_size amount of random unconfirmed users
			$sql = "SELECT * FROM {$wpdb->users} ";

			$sql .= " JOIN `{$wpdb->prefix}" . PeepSoUser::TABLE . "` ON `{$wpdb->users}`.ID = `usr_id` ";
			$sql .= " AND `usr_role`='ban'";

			$users = $wpdb->get_results($sql);

			if(count($users)) {
				foreach($users as $user) {
					// Destroy all sessions for the user
					$sessions = WP_Session_Tokens::get_instance( $user->ID );
					if (count($sessions->get_all()) > 0) {
						$sessions->destroy_all();
						$completed++;
					}
				}
			}

			return $completed;
		}
	}
}

// EOF