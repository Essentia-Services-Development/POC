<?php

class PeepSoMailQueue
{
	const TABLE = 'peepso_mail_queue';

	// values for the `mail_status` column
	const STATUS_PENDING = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_DELAY = 2;
	const STATUS_FAILED = 3;
//	const STATUS_SENT = 4;					// TODO: not used yet
	const STATUS_NOW = 5;
	const STATUS_RETRY = 6;

	private static $_max_exec_time = 30;

	/**
	 * Returns the privately defined $_max_exec_time variable value.
	 * @return int
	 */
	private static function get_max_exec_time()
	{
	    $timeout = self::$_max_exec_time;

	    if(isset($_GET['timeout'])) {
            $timeout = $_GET['timeout'];
        }

		return ($timeout);
	}

	/*
	 * Adds an item to the mail queue. NOTE: This should only be called from add_message()
	 * @param int $user_id The user id of the recipient
	 * @param string $email The email address of the recipient
	 * @param string $subject The email subject line
	 * @param string $content The contents of the email to send to the recipent
	 * @param string $type The type of notification settings to be checked if email is allowed or not
	 * @param int $module The module id originating the message
	 * @param int $now If set to 1 email message will be sent immediately
	 */
	private static function add($user_id, $email, $subject, $content, $type, $module = 0, $now = 0)
	{
		// the new_user and user_approved emails are not configurable for ignoring
		// TODO: better approach would be to ignore these when writing the settings, instead of every time we're using them.
		if (!in_array($type, array('new_user', 'user_approved', 'register', 'welcome', 'password_recover', 'password_changed', 'new_user_registration', 'reported_content'))) {
			$notifications = get_user_meta($user_id, 'peepso_notifications');
			// do not send any notification when it's disabled
			
			if (
				isset($notifications[0]) &&
				in_array($type . '_email', $notifications[0]))
				return FALSE;
		}

		$aCols = array(
			'mail_user_id' => intval($user_id),
			'mail_recipient' => $email,
			'mail_subject' => wp_specialchars_decode($subject,ENT_QUOTES),
			'mail_message' => wp_specialchars_decode($content,ENT_QUOTES),
			'mail_module_id' => $module,
		);

		if (1 === $now) 
			$aCols['mail_status'] = self::STATUS_NOW;

		global $wpdb;
		$wpdb->insert($wpdb->prefix . self::TABLE, $aCols);
		return TRUE;
	}


    /*
     * Wrapper for add_message, used to queue notification emails (since 2.7.2)
     * Contains additional logic to decide whether the email should be enqueued or not, based on user preferences
     */
    public static function add_notification($user_id, $data, $subject, $template, $type, $module = 0, $now = 0, $send=1, $run_action=1) {

        $do_send = TRUE;

        // $send might be 0 if we are only trying to trigger the actions
        if($send) {

            // Do not enqueue the email notification if user preference is anything other than "real time"
            $email_preference = PeepSoNotificationsIntensity::user_email_notifications_intensity($user_id);

            if($email_preference !=0 ) {
                $do_send = FALSE;
            }
        }

        if($do_send) {
            return self::add_message($user_id, $data, $subject, $template, $type, $module, $now, $send, $run_action);
        }

        return NULL;
    }

    /*
	 * Wrapper for add_message, used to queue notification emails (since 2.7.2)
	 * Contains additional logic to decide whether the email should be enqueued or not, based on user preferences
     * Since 4.0.1.0 new param $args added for translations with PeepSoMultiLang
	 */
    public static function add_notification_new($user_id, $data, $subject, $args, $template, $type, $module = 0, $now = 0, $send=1, $run_action=1) {

        $do_send = TRUE;

        // $send might be 0 if we are only trying to trigger the actions
        if($send) {

            // Do not enqueue the email notification if user preference is anything other than "real time"
            $email_preference = PeepSoNotificationsIntensity::user_email_notifications_intensity($user_id);

            if($email_preference !=0 ) {
                $do_send = FALSE;
            }
        }

        // @TODO this code is a duplicate in canvaspushhelper - we should centralize "not id to translated string" somewhere
        // Attempt subject translation
        if(is_array($args) && count($args)) {
            $textdomain = array_shift($args);
            $subject = PeepSo3_MultiLang__($subject, $textdomain, $user_id);

            $args = apply_filters('peepso_filter_notification_args', $args);

            // fire translation on args as well
            if (count($args)) {
                foreach ($args as &$arg) {
                    $arg = __($arg, $textdomain);
                }

                $subject = vsprintf($subject, $args);
            }
        }
        // @todo EOF duplicate code

        if($do_send) {
            return self::add_message($user_id, $data, $subject, $template, $type, $module, $now, $send, $run_action);
        }

        return FALSE;
    }

	/*
	 * add item to the mail queue
	 * @param int $user_id The user id of the recipient
	 * @param array $data The tokens for the email template
	 * @param string $subject The email subject line
	 * @param string $template The template name to send to the recipent
	 * @param string $type The type of notification settings to be checked if email is allowed or not
	 * @param int $module The module id originating the message
	 * @param int $now If set to 1 the email will be sent immediately
	 * @param int $send If set to 1 the email will be enqueued. If 0, only the related action will be fired.
	 * @param int $run_action If se to 1 the related action will be fired
	 */
	public static function add_message($user_id, $data, $subject, $template, $type, $module = 0, $now = 0, $send=1, $run_action=1)
	{
		if ($user_id !== 0) {
			if (!apply_filters('peepso_mailqueue_allowed', $user_id, TRUE)) {
				return FALSE;
			}
		}
		if(1 == $run_action) {
			do_action('peepso_mailqueue_add', $user_id, $data, $subject, $template, $type, $module, $now);
		}

		if(0 == $send) {
			return FALSE;
		}

		$data = apply_filters( 'peepso_mailqueue_add_data', $data, $user_id, $subject, $template, $type, $module );
		
		$override = PeepSo::get_option('emails_override_entire_html','');
        if(strlen($override)) {
            $contents = stripslashes($override);
        } else { 
        	// load the general email template
			$contents = PeepSoTemplate::exec_template('general', 'email', ['user_id'=>$user_id], TRUE);
        }

		// load the translated contents of the email if possible
        $locale = PeepSo3_MultiLang_User_Locale($user_id);
        $message = get_option('peepso_email_' . $template.'_'.strtolower($locale));
        if(!strlen($message)) {
            // Default language fallback
            $message = get_option('peepso_email_' . $template);
        }

		// set recipient name
		if (!isset($data['currentuserfullname']) && isset($data['userfullname'])) {
			$data['currentuserfullname'] = $data['userfullname'];
		}

        if(!isset($data['useremail'])) {
            $data['useremail'] = PeepSoUser::get_instance($user_id)->get_email();
        }

        if(!isset($data['firstname'])) {
            $data['firstname'] = PeepSoUser::get_instance($user_id)->get_firstname();
        }

		// setup the template replacement data
		$em = new PeepSoEmailTemplate();
		$em->set_tokens($data);

		$msg = $em->replace_tokens($contents, $message);
		$subject = $em->replace_content_tokens($subject);

		$ret = self::add($user_id, $data['useremail'], $subject, $msg, $type, $module, $now);

		if(1 === $now)
		{
			self::process_mailqueue(1);
		}

		return $ret;
	}

	/*
	 * Add item to the mail queue directly to an email address. This method is used when the recipient may not be a member.
	 * @param mixed $email The email address of the recipient
	 * @param array $data The tokens for the email template
	 * @param string $subject The email subject line
	 * @param string $message The message to send to the recipent
	 * @param string $type The type of notification settings to be checked if email is allowed or not
	 * @param int $module The module id originating the message
	 * @return int $sent The number of sent messages
	 */
	public static function send_message($emails, $data, $subject, $message, $type, $module = 0)
	{
		if (!is_array($emails))
			$emails = array($emails);

		// load the general email template
		$contents = file_get_contents(PeepSoTemplate::get_template('general', 'email'));

		// setup the template replacement data
		$em = new PeepSoEmailTemplate();

		$sent = 0;
		foreach ($emails as $email) {
			if (!is_email($email))
				continue;

			$data['userfullname'] = $email;
			$data['useremail'] = $email;

			$em->set_tokens($data);

			$msg = $em->replace_tokens($contents, $message);
			$subject = $em->replace_content_tokens($subject);

			self::add(0, $email, $subject, $msg, $type, $module);
			++$sent;
		}

		return ($sent);
	}

	/*
	 * Processes the items in the mail queue.
	 */
	public static function process_mailqueue($now = 0)
	{
        // Allow other plugins to run their code when MailQueue is processed
        do_action('peepso_action_process_mailqueue');

	    // NotifQ
        $PeepSoNotificationsQueue= PeepSoNotificationsQueue::get_instance();
        $PeepSoNotificationsQueue->cron();

        // MailQ

		global $wpdb;

		$sStartTime = microtime(true);

		$em = new PeepSoEmailTemplate();
		$from = PeepSo::get_option('site_emails_sender');
		$from = $em->replace_content_tokens($from);

		$headers[] = 'From: ' . $from . ' <' . PeepSo::get_option('site_emails_admin_email') . '>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		add_filter('wp_mail_content_type', array(__CLASS__, 'html_content_type'));

		if (1 === $now) {
			$status = self::STATUS_NOW;
		} else {
			$status = self::STATUS_PENDING;
		}

		// Get 10 items to retry
		$queue_retry = self::get_by_status( self::STATUS_RETRY, 10 );

		$limit = apply_filters('peepso_process_mailq_limit',50);

		if(isset($_GET['limit'])) {
		    $limit = $_GET['limit'];
        }


		// get regular items minus the amount of delayed we found
		$queue = self::get_by_status($status, $limit - count($queue_retry));

		$queue = array_merge($queue, $queue_retry);

		$iProcessed = 0;
		$sCurrentRunTime = 0;
		foreach ($queue as $mail) {

			// same WHERE clause is used multiple times
			$where = array('mail_id' => $mail->mail_id);

			$mail->mail_attempts +=1;

			$wpdb->update(self::get_table_name(), array('mail_status' => self::STATUS_PROCESSING), $where);
			$wpdb->update(self::get_table_name(), array('mail_attempts' => $mail->mail_attempts), $where);

			$table_name = self::get_table_name();

            add_action('wp_mail_failed', function($wp_error) use ($wpdb, $where, $table_name, &$mail) {
                $mail->mail_error_log .= "\n".json_encode($wp_error);
                $wpdb->update($table_name, array('mail_error_log' => $mail->mail_error_log), $where);
            }, 10, 1);

			$success = wp_mail($mail->mail_recipient, wp_specialchars_decode ( $mail->mail_subject ), $mail->mail_message, $headers);

			// update `mail_status` on failure
			if (FALSE === $success) {

				// set retry by default
				$status = self::STATUS_RETRY;

				// if X attempts have been made, quit
                $max_attempts = apply_filters('peepso_process_mailq_max_attempts',5);

                if(isset($_GET['max_attempts'])) {
                    $max_attempts = $_GET['max_attempts'];
                }

                if($mail->mail_attempts > $max_attempts) {
					$status = self::STATUS_FAILED;
				}

				$wpdb->update(self::get_table_name(), array('mail_status' => $status), $where);
			}
			else {
				do_action('peepso_mailqueue_after', $mail);
				$wpdb->delete(self::get_table_name(), $where);
			}

			++$iProcessed;

			$sCurrentRunTime = microtime(true) - $sStartTime;
			if ($sCurrentRunTime  > self::get_max_exec_time())
				break;
		}

		remove_filter('wp_mail_content_type', array(__CLASS__, 'html_content_type'));

		$aBatchHistory = array('elapsed' => $sCurrentRunTime, 'processed' => $iProcessed);

		$aPeepSoMailqueueHistory = get_option('peepso_mailqueue_history');

		if (!$aPeepSoMailqueueHistory)
			$aPeepSoMailqueueHistory = array();

		if (count($aPeepSoMailqueueHistory) >= 25)
			array_shift($aPeepSoMailqueueHistory);

		$aPeepSoMailqueueHistory[] = $aBatchHistory;

		update_option('peepso_mailqueue_history', $aPeepSoMailqueueHistory);
	}


	/*
	 * Sets the content type for email messages
	 * @return string The SMTP content type for the email
	 */
    public static function html_content_type($content_type)
    {
        //return ('text/html; charset=UTF-8');
        // #4279 @ 2.7.12 - the default encoding for wp_mail is UTF-8 anyway, and the fancy string seems to break MailPoet
        return ('text/html');
    }


	/**
	 * Fetches all mail queued up on the database.
	 * @param  int $limit  How many records to fetch.
	 * @param  int $offset Fetch records beginning from this index.
	 * @param  string  $order  Order by column.
	 * @param  string  $dir    The sort direction, defaults to 'asc'
	 * @return array Array of the result set.
	 */
	public static function fetch_all($limit = NULL, $offset = 0, $order = NULL, $dir = 'asc')
	{
		global $wpdb;

		$query = 'SELECT *				
			FROM `' . self::get_table_name() . '` ';

		if (isset($order))
			$query .= ' ORDER BY `' . $order . '` ' . $dir;

		if (isset($limit))
			$query .= ' LIMIT ' . $offset . ', ' . $limit;

		return ($wpdb->get_results($query, ARRAY_A));
	}

	/*
	 * Return list of MailQueue items based on the status
	 * @param string $status The status to filter with.
	 * @return array Returns array of MailQueue items in chronologic order
	 */
	public static function get_by_status($status, $limit = 25)
	{
		global $wpdb;

		$sql = 'SELECT * 
				FROM `' . self::get_table_name() . '`
				WHERE `mail_status` = %d
				ORDER BY `mail_created_at` ASC
				LIMIT %d ';
		$res = $wpdb->get_results($wpdb->prepare($sql, $status, $limit));
		return ($res);
	}

	/*
	 * Get a count of the pending items in the mail queue
	 * @return int A count of the items
	 */
	public static function get_pending_item_count()
	{
		global $wpdb;

		$sql = 'SELECT COUNT(*) AS `val`
				FROM `' . self::get_table_name() . '`
				WHERE `mail_status`=%d ';
		$msg_count = $wpdb->get_var($wpdb->prepare($sql, self::STATUS_PENDING));

		return ($msg_count);
	}

	/*
	 * Get number of seconds to complete mail queue
	 * @return int
	 */
	public static function get_completion_estimate()
	{
		$queue_history = get_option('peepso_mailqueue_history');

		if (!$queue_history)
			return (0);

		$history_count = count($queue_history);
		$pending_item_count = self::get_pending_item_count();
		$run_time = 0;
		$processed = 0;

		// Get average running time and emails sent per process
		foreach ($queue_history as $history) {
			$run_time += $history['elapsed'];
			$processed += $history['processed'];
		}

		$average_run_time = $run_time / $history_count;
		$average_processed = $processed / $history_count;

		$runs_left = $pending_item_count / max($average_processed, 1);
		// Multiply	runs left to time interval and average run time
		$schedule = wp_get_schedule(PeepSo::CRON_MAILQUEUE);
		$schedules = wp_get_schedules();
		$interval = $schedules[$schedule]['interval'];

		$estimate_time = ($interval * $runs_left) + ($average_run_time * $runs_left);

		return ($estimate_time);
	}

	/**
	 * Convenience function to return the mailqueue table name as a string.
	 * @return string The table name.
	 */
	public static function get_table_name()
	{
		global $wpdb;

		return ($wpdb->prefix . self::TABLE);
	}
}

// EOF
