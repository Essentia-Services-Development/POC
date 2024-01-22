<?php

class MailsterSendGrid {

	private $plugin_path;
	private $plugin_url;

	public function __construct() {

		$this->plugin_path = plugin_dir_path( MAILSTER_SENDGRID_FILE );
		$this->plugin_url  = plugin_dir_url( MAILSTER_SENDGRID_FILE );

		register_activation_hook( MAILSTER_SENDGRID_FILE, array( &$this, 'activate' ) );
		register_deactivation_hook( MAILSTER_SENDGRID_FILE, array( &$this, 'deactivate' ) );

		load_plugin_textdomain( 'mailster-sendgrid' );

		add_action( 'init', array( &$this, 'init' ), 1 );
	}


	/*
	 * init the plugin
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		if ( ! function_exists( 'mailster' ) ) {

			add_action( 'admin_notices', array( &$this, 'notice' ) );

		} else {

			add_filter( 'mailster_delivery_methods', array( &$this, 'delivery_method' ) );
			add_action( 'mailster_deliverymethod_tab_sendgrid', array( &$this, 'deliverytab' ) );

			add_filter( 'mailster_verify_options', array( &$this, 'verify_options' ) );

			if ( mailster_option( 'deliverymethod' ) == 'sendgrid' ) {
				add_action( 'mailster_initsend', array( &$this, 'initsend' ) );
				add_action( 'mailster_presend', array( &$this, 'presend' ) );
				add_action( 'mailster_dosend', array( &$this, 'dosend' ) );
				add_action( 'mailster_sendgrid_cron', array( &$this, 'reset' ) );
				add_action( 'mailster_cron_worker', array( &$this, 'check_bounces' ), -1 );
				add_action( 'mailster_check_bounces', array( &$this, 'check_bounces' ) );
				add_action( 'mailster_section_tab_bounce', array( &$this, 'section_tab_bounce' ) );

				if ( mailster_option( 'sendgrid_user' ) ) {
					mailster_notice( sprintf( __( 'SendGrid now requires an API key. Please visit the %s and update your information!', 'mailster-sendgrid' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings&mailster_remove_notice=sendgrid_update_required#delivery">Settings Page</a>' ), 'info', 7200, 'sendgrid_update_required' );
				}
			}
		}

	}


	/**
	 * initsend function.
	 *
	 * uses mailster_initsend hook to set initial settings
	 *
	 * @access public
	 * @return void
	 * @param mixed $mailobject
	 */
	public function initsend( $mailobject ) {

		$method = mailster_option( 'sendgrid_api' );

		if ( $method == 'smtp' ) {

			$secure = mailster_option( 'sendgrid_secure' );

			$mailobject->mailer->Mailer        = 'smtp';
			$mailobject->mailer->SMTPSecure    = $secure ? 'ssl' : 'none';
			$mailobject->mailer->Host          = 'smtp.sendgrid.net';
			$mailobject->mailer->Port          = $secure ? 465 : 587;
			$mailobject->mailer->SMTPAuth      = true;
			$mailobject->mailer->Username      = 'apikey';
			$mailobject->mailer->Password      = mailster_option( 'sendgrid_apikey' );
			$mailobject->mailer->SMTPKeepAlive = true;

		} elseif ( $method == 'web' ) {

		}

		// SendGrid will handle DKIM integration
		$mailobject->dkim = false;

	}


	/**
	 * presend function.
	 *
	 * uses the mailster_presend hook to apply settings before each mail
	 *
	 * @access public
	 * @return void
	 * @param mixed $mailobject
	 */
	public function presend( $mailobject ) {

		$method = mailster_option( 'sendgrid_api' );

		if ( $method == 'smtp' ) {

			if ( ! empty( $xsmtpapi ) ) {
				$mailobject->add_header( 'X-SMTPAPI', json_encode( $xsmtpapi ) );
			}

			// use pre_send from the main class
			$mailobject->pre_send();

		} elseif ( $method == 'web' ) {

			$mailobject->pre_send();

			$recipients = array();

			if ( ! is_array( $mailobject->to ) ) {
				$mailobject->to = array( $mailobject->to );
			}

			foreach ( $mailobject->to as $i => $to ) {
				$recipients[] = (object) array(
					'name'  => $mailobject->to_name[ $i ] ? $mailobject->to_name[ $i ] : null,
					'email' => $mailobject->to[ $i ] ? $mailobject->to[ $i ] : null,
				);

			}

			$mailobject->sendgrid_object = array(
				'personalizations' => array(
					array(
						'to' => $recipients,
					),
				),
				'from'             => array(
					'email' => $mailobject->from,
					'name'  => $mailobject->from_name,
				),
				'subject'          => $mailobject->subject,
				'content'          => array(
					array(
						'type'  => 'text/plain',
						'value' => $mailobject->mailer->AltBody,
					),
					array(
						'type'  => 'text/html',
						'value' => $mailobject->mailer->Body,
					),
				),
				'custom_args'      => (object) array(
					'mailster_id'   => (string) mailster_option( 'ID' ),
					'campaign_id'   => (string) $mailobject->campaignID,
					'index'         => (string) $mailobject->index,
					'subscriber_id' => (string) $mailobject->subscriberID,
					'message_id'    => (string) $mailobject->messageID,
				),

			);

			$reply_to = is_array( $mailobject->reply_to ) ? reset( $mailobject->reply_to ) : $mailobject->reply_to;

			if ( $reply_to ) {
				$mailobject->sendgrid_object['reply_to'] = array(
					'email' => $reply_to,
				);
			}

			$categories = mailster_option( 'sendgrid_categories' );
			if ( ! empty( $categories ) ) {
				$mailobject->sendgrid_object['categories'] = array_slice( array_map( 'trim', explode( ',', $categories ) ), 0, 10 );
			}

			if ( ! empty( $mailobject->headers ) ) {
				if ( isset( $mailobject->headers['X-Mailster-Campaign'] ) ) {
					$mailobject->headers['X-Mailster-Campaign'] = (string) $mailobject->headers['X-Mailster-Campaign'];
				}
				$mailobject->sendgrid_object['headers'] = $mailobject->headers;
			}

			$attachments = $mailobject->mailer->getAttachments();

			if ( ! empty( $attachments ) ) {
				$attachments_holder = array();

				foreach ( $attachments as $attachment ) {
					if ( file_exists( $attachment[0] ) ) {
						$object = (object) array(
							'content'     => base64_encode( file_get_contents( $attachment[0] ) ),
							'filename'    => $attachment[1],
							'type'        => $attachment[4],
							'disposition' => $attachment[6],
						);

						if ( 'inline' == $attachment[6] ) {
							$object->content_id = $attachment[7];
						}

						$attachments_holder[] = $object;
					}
				}

				if ( ! empty( $attachments_holder ) ) {
					$mailobject->sendgrid_object['attachments'] = $attachments_holder;
				}
			}
		}

	}


	/**
	 * dosend function.
	 *
	 * uses the mailster_dosend hook and triggers the send
	 *
	 * @access public
	 * @param mixed $mailobject
	 * @return void
	 */
	public function dosend( $mailobject ) {

		$method = mailster_option( 'sendgrid_api' );

		if ( $method == 'smtp' ) {

			// use send from the main class
			$mailobject->do_send();

		} elseif ( $method == 'web' ) {

			if ( ! isset( $mailobject->sendgrid_object ) ) {
				$mailobject->set_error( __( 'SendGrid options not defined', 'mailster-sendgrid' ) );
				return;
			}
			if ( empty( $mailobject->sendgrid_object['subject'] ) ) {
				$mailobject->set_error( __( 'SendGrid requires a subject', 'mailster-sendgrid' ) );
				return;
			}

			$response = $this->do_post( 'mail/send', $mailobject->sendgrid_object );

			if ( is_wp_error( $response ) ) {
				$mailobject->set_error( $response->get_error_message() );
				$mailobject->sent = false;
			} else {
				$mailobject->sent = true;
			}
		}
	}




	/**
	 * reset function.
	 *
	 * resets the current time
	 *
	 * @access public
	 * @param mixed $message
	 * @return array
	 */
	public function reset() {
		update_option( '_transient__mailster_send_period_timeout', false );
		update_option( '_transient__mailster_send_period', 0 );

	}




	/**
	 * embedd_images function.
	 *
	 * prepares the array for embedded images
	 *
	 * @access public
	 * @param mixed $message
	 * @return array
	 */
	public function embedd_images( $message ) {

		$return = array(
			'files'   => array(),
			'content' => array(),
			'html'    => $message,
		);

		$upload_folder = wp_upload_dir();
		$folder        = $upload_folder['basedir'];

		preg_match_all( "/(src|background)=[\"']([^\"']+)[\"']/Ui", $message, $images );

		if ( isset( $images[2] ) ) {

			foreach ( $images[2] as $i => $url ) {
				if ( empty( $url ) ) {
					continue;
				}
				if ( substr( $url, 0, 7 ) == 'http://' ) {
					continue;
				}
				if ( substr( $url, 0, 8 ) == 'https://' ) {
					continue;
				}
				if ( ! file_exists( $folder . '/' . $url ) ) {
					continue;
				}
				$filename  = basename( $url );
				$directory = dirname( $url );
				if ( $directory == '.' ) {
					$directory = '';
				}
				$cid                            = md5( $folder . '/' . $url . time() );
				$return['html']                 = str_replace( $url, 'cid:' . $cid, $return['html'] );
				$return['files'][ $filename ]   = file_get_contents( $folder . '/' . $url );
				$return['content'][ $filename ] = $cid;
			}
		}
		return $return;
	}




	/**
	 * delivery_method function.
	 *
	 * add the delivery method to the options
	 *
	 * @access public
	 * @param mixed $delivery_methods
	 * @return void
	 */
	public function delivery_method( $delivery_methods ) {
		$delivery_methods['sendgrid'] = 'SendGrid';
		return $delivery_methods;
	}


	/**
	 * deliverytab function.
	 *
	 * the content of the tab for the options
	 *
	 * @access public
	 * @return void
	 */
	public function deliverytab() {

		$verified = mailster_option( 'sendgrid_verified' );

		include $this->plugin_path . '/views/settings.php';

	}

	/**
	 * verify_options function.
	 *
	 * some verification if options are saved
	 *
	 * @access public
	 * @param unknown $apikey  (optional)
	 * @return void
	 */
	public function verify( $apikey = null ) {

		$this->apikey = $apikey;

		$response = $this->do_get( 'scopes' );

		if ( is_wp_error( $response ) ) {
			return false;
		} else {
			return true;
		}

	}



	/**
	 * verify_options function.
	 *
	 * some verification if options are saved
	 *
	 * @access public
	 * @param mixed $options
	 * @return void
	 */
	public function verify_options( $options ) {

		if ( $timestamp = wp_next_scheduled( 'mailster_sendgrid_cron' ) ) {
			wp_unschedule_event( $timestamp, 'mailster_sendgrid_cron' );
		}

		if ( $options['deliverymethod'] == 'sendgrid' ) {

			$old_apikey = mailster_option( 'sendgrid_apikey' );

			if ( $old_apikey != $options['sendgrid_apikey']
				|| ! mailster_option( 'sendgrid_verified' ) || ! $options['sendgrid_apikey'] ) {

				$options['sendgrid_verified'] = $this->verify( $options['sendgrid_apikey'] );

				if ( $options['sendgrid_verified'] ) {
					add_settings_error( 'mailster_options', 'mailster_options', sprintf( __( 'Please update your sending limits! %s', 'mailster-sendgrid' ), '<a href="https://app.sendgrid.com/settings/billing" class="external">SendGrid Dashboard</a>' ) );

				}
			}

			if ( ! wp_next_scheduled( 'mailster_sendgrid_cron' ) ) {
				// reset on 00:00 PST ( GMT -8 ) == GMT +16
				$timeoffset = strtotime( 'midnight' ) + ( ( 24 - 8 ) * HOUR_IN_SECONDS );
				if ( $timeoffset < time() ) {
					$timeoffset + ( 24 * HOUR_IN_SECONDS );
				}
				wp_schedule_event( $timeoffset, 'daily', 'mailster_sendgrid_cron' );
			}

			if ( $options['sendgrid_api'] == 'smtp' ) {
				if ( function_exists( 'fsockopen' ) ) {
					$host = 'smtp.sendgrid.net';
					$port = $options['sendgrid_secure'] ? 465 : 587;
					$conn = fsockopen( $host, $port, $errno, $errstr, 15 );

					if ( is_resource( $conn ) ) {

						fclose( $conn );

					} else {

						add_settings_error( 'mailster_options', 'mailster_options', sprintf( __( 'Not able to use SendGrid with SMTP API cause of the blocked port %s! Please send with the WEB API or choose a different delivery method!', 'mailster-sendgrid' ), $port ) );

					}
				}
			} else {

				if ( $options['sendgrid_bouncehandling'] != 'sendgrid' ) {
					add_settings_error( 'mailster_options', 'mailster_options', __( 'It is currently not possible to handle bounces with Mailster when using the WEB API', 'mailster-sendgrid' ) );
					$options['sendgrid_bouncehandling'] = 'sendgrid';
				}
			}

			if ( $options['sendgrid_bouncehandling'] != 'sendgrid' ) {
				add_settings_error( 'mailster_options', 'mailster_options', sprintf( __( 'Please make sure your SendGrid Account "preserve headers" otherwise Mailster is not able to handle bounces', 'mailster-sendgrid' ), $port ) );
			}
		}

		return $options;
	}


	/**
	 * check_bounces function.
	 *
	 * checks for bounces and reset them if needed
	 *
	 * @access public
	 * @return void
	 */
	public function check_bounces() {

		if ( get_transient( 'mailster_check_bounces_lock' ) || ! mailster_option( 'sendgrid_verified' ) ) {
			return false;
		}

		$now = time();

		if ( ! ( $last_bounce_check = get_transient( '_mailster_sendgrid_last_bounce_check' ) ) ) {
			set_transient( '_mailster_sendgrid_last_bounce_check', $now );
			$last_bounce_check = $now;
		}

		$collection    = array();
		$errormessages = array();

		$response = $this->do_get( 'suppression/bounces', array( 'start_time' => $last_bounce_check ) );

		if ( is_wp_error( $response ) ) {
			$errormessages[]       = sprintf( __( 'Cannot read Bounces: %s', 'mailster-sendgrid' ), $response->get_error_message() );
			$collection['bounces'] = array();
		} else {
			$collection['bounces'] = (array) $response;
		}

		$response = $this->do_get( 'suppression/blocks', array( 'start_time' => $last_bounce_check ) );

		if ( is_wp_error( $response ) ) {
			$errormessages[]      = sprintf( __( 'Cannot read Blocks: %s', 'mailster-sendgrid' ), $response->get_error_message() );
			$collection['blocks'] = array();
		} else {
			$collection['blocks'] = (array) $response;
		}

		$response = $this->do_get( 'suppression/spam_reports', array( 'start_time' => $last_bounce_check ) );

		if ( is_wp_error( $response ) ) {
			$errormessages[]            = sprintf( __( 'Cannot read Spam reports: %s', 'mailster-sendgrid' ), $response->get_error_message() );
			$collection['spam_reports'] = array();
		} else {
			$collection['spam_reports'] = (array) $response;
		}

		$response = $this->do_get( 'suppression/unsubscribes', array( 'start_time' => $last_bounce_check ) );

		if ( is_wp_error( $response ) ) {
			$errormessages[]            = sprintf( __( 'Cannot read Unsubscribes: %s', 'mailster-sendgrid' ), $response->get_error_message() );
			$collection['unsubscribes'] = array();
		} else {
			$collection['unsubscribes'] = (array) $response;
		}

		foreach ( $collection as $type => $messages ) {

			foreach ( $messages as $message ) {

				$subscriber = mailster( 'subscribers' )->get_by_mail( $message->email );

				// only if user exists
				if ( $subscriber && $subscriber->status == 1 ) {

					$campaigns    = mailster( 'subscribers' )->get_sent_campaigns( $subscriber->ID );
					$campaigns    = array_reverse( $campaigns );
					$campaign_ids = wp_list_pluck( $campaigns, 'campaign_id' );

					if ( 'unsubscribes' == $type ) {

						$campaign_id = isset( $campaigns[0] ) ? $campaigns[0]->campaign_id : null;

						mailster( 'subscribers' )->unsubscribe( $subscriber->ID, $campaign_id );

					} else {

						// any code with 5 eg 5.x.x or a spamreport or bocks
						$is_hard_bounce = in_array( $type, array( 'spam_reports', 'blocks' ) ) || substr( $message->status, 0, 1 ) == 5;

						if ( ! empty( $campaigns ) ) {
							foreach ( $campaigns as $i => $campaign ) {

								// only the last 20 campaigns
								if ( $i >= 20 ) {
									break;
								}

								mailster( 'subscribers' )->bounce( $subscriber->ID, $campaign->campaign_id, $is_hard_bounce, $message->reason );

							}
						} else {
							mailster( 'subscribers' )->bounce( $subscriber->ID, null, $is_hard_bounce, $message->reason );
						}
					}
				} else {
				}
			}
		}

		if ( ! empty( $errormessages ) ) {
			mailster_notice( __( 'There is a problem while requesting suppressions from SendGrid: ', 'mailster-sendgrid' ) . '<br>' . implode( '<br>', $errormessages ), 'error', false, 'sendgrid_suppressions_error' );
		} else {
			mailster_remove_notice( 'sendgrid_suppressions_error' );
		}

		set_transient( '_mailster_sendgrid_last_bounce_check', $now );
	}


	public function do_get( $endpoint, $args = array(), $timeout = 15 ) {
		return $this->do_call( 'GET', $endpoint, $args, $timeout );
	}
	public function do_post( $endpoint, $args = array(), $timeout = 15 ) {
		return $this->do_call( 'POST', $endpoint, $args, $timeout );
	}
	public function do_delete( $endpoint, $args = array(), $timeout = 15 ) {
		return $this->do_call( 'DELETE', $endpoint, $args, $timeout );
	}


	/**
	 *
	 * @access public
	 * @param unknown $apikey  (optional)
	 * @return void
	 */
	private function do_call( $method, $endpoint, $args = array(), $timeout = 15 ) {

		$url = 'https://api.sendgrid.com/v3/' . $endpoint;

		$args   = wp_parse_args( $args, array() );
		$body   = null;
		$apikey = isset( $this->apikey ) ? $this->apikey : mailster_option( 'sendgrid_apikey' );

		if ( 'GET' == $method ) {
			$url = add_query_arg( $args, $url );
		} elseif ( 'POST' == $method ) {
			$body = json_encode( $args );
		} else {
			return new WP_Error( 'method_not_allowed', 'This method is not allowed' );
		}

		$headers = array(
			'Authorization' => 'Bearer ' . $apikey,
			'Content-Type'  => 'application/json',
		);

		$response = wp_remote_request(
			$url,
			array(
				'method'  => $method,
				'headers' => $headers,
				'timeout' => $timeout,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( $code != 200 && $code != 202 && $code != 204 ) {
			$errors = array_unique( wp_list_pluck( $body->errors, 'message' ) );
			return new WP_Error( $code, implode( ', ', $errors ) );
		}

		return $body;

	}


	/**
	 * section_tab_bounce function.
	 *
	 * displays a note on the bounce tab
	 *
	 * @access public
	 * @param mixed $options
	 * @return void
	 */
	public function section_tab_bounce() {

		if ( mailster_option( 'sendgrid_bouncehandling' ) != 'sendgrid' ) {
			return;
		}

		?>
		<div class="error inline"><p><strong><?php esc_html_e( 'Bouncing is handled by SendGrid so all your settings will be ignored', 'mailster-sendgrid' ); ?></strong></p></div>

		<?php
	}



	/**
	 * Notice if Mailster is not available
	 *
	 * @access public
	 * @return void
	 */
	public function notice() {
		?>
	<div id="message" class="error">
	  <p>
	   <strong>SendGrid integration for Mailster</strong> requires the <a href="https://mailster.co/?utm_campaign=wporg&utm_source=SendGrid+integration+for+Mailster&utm_medium=plugin">Mailster Newsletter Plugin</a>, at least version <strong><?php echo MAILSTER_SENDGRID_REQUIRED_VERSION; ?></strong>.
	  </p>
	</div>
		<?php
	}



	/**
	 * activate function
	 *
	 * @access public
	 * @return void
	 */
	public function activate() {

		if ( function_exists( 'mailster' ) ) {

			mailster_notice( sprintf( __( 'Change the delivery method on the %s!', 'mailster-sendgrid' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings&mailster_remove_notice=delivery_method#delivery">Settings Page</a>' ), '', 360, 'delivery_method' );

			$defaults = array(
				'sendgrid_apikey'         => null,
				'sendgrid_api'            => 'web',
				'sendgrid_bouncehandling' => 'sendgrid',
				'sendgrid_verified'       => false,
			);

			$mailster_options = mailster_options();

			foreach ( $defaults as $key => $value ) {
				if ( ! isset( $mailster_options[ $key ] ) ) {
					mailster_update_option( $key, $value );
				}
			}

			$this->reset();
		}
	}


	/**
	 * deactivate function
	 *
	 * @access public
	 * @return void
	 */
	public function deactivate() {

		if ( function_exists( 'mailster' ) ) {
			if ( mailster_option( 'deliverymethod' ) == 'sendgrid' ) {
				mailster_update_option( 'deliverymethod', 'simple' );
				mailster_notice( sprintf( __( 'Change the delivery method on the %s!', 'mailster-sendgrid' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings&mailster_remove_notice=delivery_method#delivery">Settings Page</a>' ), '', 360, 'delivery_method' );
			}
		}
	}


}
