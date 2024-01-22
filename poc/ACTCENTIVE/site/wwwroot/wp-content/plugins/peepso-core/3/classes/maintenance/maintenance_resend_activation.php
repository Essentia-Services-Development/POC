<?php
if(class_exists('PeepSoMaintenanceFactory')) {
	class PeepSo3_Maintenance_Resend_Activation extends PeepSoMaintenanceFactory {

		public static function resendActivation() {

			$completed = 0;

			$batch_size = 10;

			$resend_activation_interval = PeepSo::get_option_new('resend_activation_interval'); // how often to resend, in seconds
			$resend_activation_max_attempts = PeepSo::get_option_new('resend_activation_max_attempts'); // max resend attempts

			$min_resend_interval = 60; // FOR DEBUGGING

			global $wpdb;

			// Grab $batch_size amount of random unconfirmed users
			$sql = "SELECT * FROM {$wpdb->users} ";

			$sql .= " JOIN `{$wpdb->prefix}" . PeepSoUser::TABLE . "` ON `{$wpdb->users}`.ID = `usr_id` ";
			$sql .= " AND `usr_role`='register' ORDER BY RAND() LIMIT $batch_size";

			$users = $wpdb->get_results($sql);


			if(count($users)) {
				foreach($users as $user) {
					$date = PeepSo3_Mayfly::get('user_' . $user->ID .'_send_activation_last_attempt_date');
					$count = PeepSo3_Mayfly::get('user_' . $user->ID .'_send_activation_count');
					$trigger = PeepSo3_Mayfly::get('user_' . $user->ID .'_send_activation_last_attempt_trigger');


					// fallback for users who never activated their account before the auto-resend was implemented / enabled
					if(in_array(NULL, [$date, $count, $trigger])) {
						$count =1;
						$trigger = $user->ID;
						$date ='2020-01-01 01:01:01';
					}

					// the first attempt is never a "retry", so we should not count it
					$count--;

					if($count >= $resend_activation_max_attempts) {
						continue;
					}

					$timestamp_curr = current_time('timestamp');
					$timestamp_then = strtotime($date);
					$timestamp_diff = $timestamp_curr - $timestamp_then;

					if($timestamp_diff < $resend_activation_interval) {
						continue;
					}

					$PeepSoUser = PeepSoUser::get_instance($user->ID);
					$PeepSoUser->send_activation($user->user_email);

					PeepSo3_Mayfly::set('user_'.$user->ID.'_send_activation_last_attempt_trigger', 'CRON');

					$completed++;
				}
			}

			PeepSoMailQueue::process_mailqueue(1);

			// var_dump($sql);

			return $completed;
		}
	}
}

// EOF