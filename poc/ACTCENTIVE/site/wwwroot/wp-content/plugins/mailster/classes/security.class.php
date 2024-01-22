<?php

class MailsterSecurity {


	public function __construct() {

		add_action( 'mailster_verify_new_subscriber', array( $this, 'verify_subscriber' ) );
		add_action( 'mailster_add_subscriber', array( $this, 'flood' ) );
	}

	/**
	 *
	 *
	 * @param unknown $entry
	 * @return unknown
	 */
	public function verify_subscriber( $entry ) {

		if ( is_wp_error( $entry ) ) {
			return $entry;
		}

		if ( ! isset( $entry['email'] ) ) {
			return $entry;
		}

		$is_valid = $this->verify( $entry );
		if ( is_wp_error( $is_valid ) ) {
			return $is_valid;
		}

		return $entry;
	}

	/**
	 *
	 *
	 * @param unknown $email
	 * @return unknown
	 */
	private function verify( $entry ) {

		$email = $entry['email'];
		$ip    = mailster_get_ip();

		if ( ! is_email( $email ) ) {
			// checked somewhere else
			return $entry;
		}

		list( $user, $domain ) = explode( '@', $email );

		// check for email addresses
		if ( $this->match( $email, mailster_option( 'blocked_emails' ) ) ) {
			return new WP_Error( 'error_blocked', mailster_text( 'blocked_email' ), 'blocked' );
		}

		// check for white listed
		if ( $this->match( $domain, mailster_option( 'safe_domains' ) ) ) {
			return true;
		}

		// check for domains
		if ( $this->match( $domain, mailster_option( 'blocked_domains' ) ) ) {
			return new WP_Error( 'error_blocked', mailster_text( 'blocked_domain' ), 'blocked' );
		}

		// check for domains
		if ( $this->match( $ip, mailster_option( 'blocked_ips' ), "\n", true ) ) {
			return new WP_Error( 'error_blocked', mailster_text( 'blocked_ip' ), 'blocked' );
		}

		// check DEP
		if ( mailster_option( 'reject_dep' ) && $this->match( $domain, $this->get_dep_domains() ) ) {
			return new WP_Error( 'error_blocked', mailster_text( 'blocked_email' ), 'blocked' );
		}

		// check IP record
		if ( mailster_option( 'check_ip' ) && ! is_user_logged_in() && $this->ip_has_pending_subscriber( $ip ) ) {
			return new WP_Error( 'error_blocked', mailster_text( 'blocked_ip' ), 'blocked' );
		}

		// check MX record
		if ( mailster_option( 'check_mx' ) && function_exists( 'checkdnsrr' ) ) {
			if ( ! checkdnsrr( $domain, 'MX' ) ) {
				return new WP_Error( 'error_blocked', mailster_text( 'smtp_mx_check' ), 'blocked' );
			}
		}

		// check via SMTP server
		if ( mailster_option( 'check_smtp' ) ) {
			if ( ! $this->smtp_check( $email ) ) {
				return new WP_Error( 'error_blocked', mailster_text( 'smtp_mx_check' ), 'blocked' );
			}
		}

		// check via Akismet if enabled
		if ( $this->is_akismet_block( $email, $ip ) ) {
			return new WP_Error( 'error_blocked', mailster_text( 'general_checks' ), 'blocked' );
		}

		// check Antiflood
		if ( mailster_option( 'antiflood' ) && $timestamp = $this->is_flood( $ip ) ) {
			$t = ( $timestamp - time() > 60 ) ? human_time_diff( $timestamp ) : sprintf( esc_html__( '%d seconds', 'mailster' ), $timestamp - time() );
			return new WP_Error( 'error_antiflood', sprintf( esc_html__( 'Please wait %s for the next signup.', 'mailster' ), $t ), 'email' );
		}

		// check country
		if ( mailster_option( 'track_location' ) ) {

			$country = mailster_ip2Country();

			// it's blocked
			if ( $this->match( $country, mailster_option( 'blocked_countries' ), ',' ) ) {
				return new WP_Error( 'error_blocked', mailster_text( 'blocked_country' ), 'blocked' );
			}

			if ( mailster_option( 'allowed_countries' ) && ! $this->match( $country, mailster_option( 'allowed_countries' ), ',' ) ) {
				return new WP_Error( 'error_blocked', mailster_text( 'blocked_country' ), 'blocked' );
			}
		}

		return true;
	}




	/**
	 *
	 *
	 * @param unknown $check (optional)
	 * @return unknown
	 */
	private function match( $string, $haystack, $separator = "\n", $net_match = false ) {
		if ( empty( $haystack ) ) {
			return false;
		}
		$lines = is_array( $haystack ) ? $haystack : explode( $separator, $haystack );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			if ( $line == $string ) {
				return true;
			}
			if ( $net_match ) {
				if ( $this->net_match( $line, $string ) ) {
					return true;
				}
			} elseif ( preg_match( '/^' . preg_quote( $line ) . '$/', $string ) ) {
				return true;
			}
		}

		return false;
	}

	private function net_match( $network, $ip ) {
		$network      = trim( $network );
		$orig_network = $network;
		$ip           = trim( $ip );
		if ( $ip == $network ) {
			return true;
		}
		$network = str_replace( ' ', '', $network );
		if ( strpos( $network, '*' ) !== false ) {
			if ( strpos( $network, '/' ) !== false ) {
				$asParts = explode( '/', $network );
				$network = @ $asParts[0];
			}
			$nCount  = substr_count( $network, '*' );
			$network = str_replace( '*', '0', $network );
			if ( $nCount == 1 ) {
				$network .= '/24';
			} elseif ( $nCount == 2 ) {
				$network .= '/16';
			} elseif ( $nCount == 3 ) {
				$network .= '/8';
			} elseif ( $nCount > 3 ) {
				return true; // *.*.*.*
			}
		}

		$d = strpos( $network, '-' );
		if ( $d === false ) {
			$ip_arr = explode( '/', $network );
			if ( ! preg_match( '@\d*\.\d*\.\d*\.\d*@', $ip_arr[0], $matches ) ) {
				$ip_arr[0] .= '.0';
			}
			if ( ! isset( $ip_arr[1] ) ) {
				$ip_arr[1] = '32';
			}
			$network_long = ip2long( $ip_arr[0] );
			$x            = ip2long( $ip_arr[1] );
			$mask         = long2ip( $x ) == $ip_arr[1] ? $x : ( 0xffffffff << ( 32 - $ip_arr[1] ) );
			$ip_long      = ip2long( $ip );

			$match = ( $ip_long & $mask ) == ( $network_long & $mask );
		} else {
			$from  = trim( ip2long( substr( $network, 0, $d ) ) );
			$to    = trim( ip2long( substr( $network, $d + 1 ) ) );
			$ip    = ip2long( $ip );
			$match = ( $ip >= $from and $ip <= $to );
		}

		return $match;
	}

	public function get_dep_domains() {

		include MAILSTER_DIR . 'includes/dep.php';

		return apply_filters( 'mailster_dep_domains', $dep_domains );
	}


	public function flood( $subscriber_id ) {
		if ( ! is_admin() && $time = mailster_option( 'antiflood' ) ) {
			$ip = mailster_get_ip();
			set_transient( 'mailster_ip_check_' . md5( ip2long( $ip ) ), time() + $time, $time );
		}
	}


	public function is_flood( $ip ) {

		return get_transient( 'mailster_ip_check_' . md5( ip2long( $ip ) ) );
	}


	public function smtp_check( $email, $from = null ) {
		if ( is_null( $from ) ) {
			$from = mailster_option( 'from' );
		}
		list( $user, $domain ) = explode( '@', $email );

		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Validator.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/Exception.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/NoHelo.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/NoResponse.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/NoTimeout.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/Timeout.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/NoConnection.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/NoMailFrom.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/NoTLS.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/SendFailed.php';
		require_once MAILSTER_DIR . 'classes/libs/smtp-validate-email/Exceptions/UnexpectedResponse.php';

		$validator    = new SMTPValidateEmail\Validator( $email, $from );
		$smtp_results = $validator->validate();
		$valid        = ( isset( $smtp_results[ $email ] ) && 1 == $smtp_results[ $email ] ) || array_sum( $smtp_results['domains'][ $domain ]['mxs'] );

		return (bool) $valid;
	}

	private function ip_has_pending_subscriber( $ip ) {
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT ID) FROM `{$wpdb->prefix}mailster_subscribers` WHERE (ip_confirm = %s OR ip_signup = %s) AND status = 0";

		if ( $wpdb->get_var( $wpdb->prepare( $sql, $ip, $ip ) ) ) {
			return true;
		}

		return false;
	}

	private function is_akismet_block( $email, $ip ) {
		if ( ! class_exists( 'Akismet' ) ) {
			return false;
		}

		$agent    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$referrer = wp_get_referer();

		$response = Akismet::http_post(
			Akismet::build_query(
				array(
					'blog'                 => home_url(),
					'referrer'             => $referrer,
					'user_agent'           => $agent,
					'comment_type'         => 'signup',
					'comment_author_email' => $email,
					'user_ip'              => $ip,
				)
			),
			'comment-check'
		);

		if ( $response && $response[1] == 'true' ) {
			return true;
		}
		return false;
	}
}
