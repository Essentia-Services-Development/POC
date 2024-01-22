<?php

class MailsterConvert {

	public function __construct() {

		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 100 );
		add_action( 'wp_version_check', array( &$this, 'notice' ) );
	}

	public function admin_menu() {

		if ( get_option( 'mailster_freemius' ) ) {
			return;
		}

		$page = add_submenu_page( 'edit.php?post_type=newsletter', esc_html__( 'Convert License', 'mailster' ), esc_html__( 'Convert License', 'mailster' ), 'manage_options', 'mailster_convert', array( &$this, 'convert_page' ) );
		add_action( 'load-' . $page, array( &$this, 'script_styles' ) );
	}

	public function convert_page() {

		remove_action( 'admin_notices', array( mailster(), 'admin_notices' ) );
		include MAILSTER_DIR . 'views/convert.php';
	}

	public function script_styles() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'mailster-welcome', MAILSTER_URI . 'assets/css/convert-style' . $suffix . '.css', array(), MAILSTER_VERSION );
		wp_enqueue_script( 'mailster-convert', MAILSTER_URI . 'assets/js/convert-script' . $suffix . '.js', array( 'mailster-script' ), MAILSTER_VERSION, true );
	}

	public function notice() {

		if ( get_option( 'mailster_freemius' ) ) {
			return;
		}

		$msg  = '<h2>' . esc_html__( '[Action Required] We need to transfer your Mailster license!', 'mailster' ) . '</h2>';
		$msg .= '<p>' . esc_html__( 'Hey there! Just wanted to give you a heads up that we\'re changing our license provider.', 'mailster' ) . '</p>';
		$msg .= '<p>' . esc_html__( 'As part of the process, we\'ll need your consent for a quick manual step. Thanks for your help in advance!', 'mailster' ) . '</p>';
		$msg .= '<p><a class="button button-primary button-hero" href="' . admin_url( 'admin.php?page=mailster_convert' ) . '">' . esc_html__( 'Convert now', 'mailster' ) . '</a> or <a href="' . mailster_url( 'https://kb.mailster.co/63fe029de6d6615225474599' ) . '" data-article="63fe029de6d6615225474599">' . esc_html__( 'read more about it', 'mailster' ) . '</a></p>';

		mailster_notice( $msg, 'info', false, 'mailster_freemius' );
	}

	public function convert( $email = null, $license = null, $is_marketing_allowed = null ) {

		$user = wp_get_current_user();
		if ( is_null( $email ) ) {
			$email = mailster()->get_email( $user->user_email );
		}

		if ( is_null( $license ) ) {
			$license = mailster()->get_license();
		}

		$endpoint = apply_filters( 'mailster_updatecenter_endpoint', 'https://update.mailster.co/' );
		$endpoint = trailingslashit( $endpoint ) . 'wp-json/freemius/v1/api/get';

		$args = array(
			'version'     => MAILSTER_VERSION,
			'license'     => $license,
			'email'       => $email,
			'whitelabel'  => $user->user_email != $email,
			'redirect_to' => rawurlencode( admin_url( 'edit.php?post_type=newsletter&page=mailster-account' ) ),
		);

		if ( ! is_null( $is_marketing_allowed ) ) {
			$args['marketing'] = $is_marketing_allowed;
		}

		$url = add_query_arg( $args, $endpoint );

		$response = wp_remote_get( $url, array( 'timeout' => 30 ) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code     = wp_remote_retrieve_response_code( $response );
		$body     = wp_remote_retrieve_body( $response );
		$response = json_decode( $body );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'no_json', 'Response is invalid: ' . json_last_error_msg() );
		}

		if ( $code !== 200 ) {
			return new WP_Error( $code, $response->message );
		}

		$this->clear_fs_cache();
		$migrate = mailster_freemius()->activate_migrated_license( $response->data->secret_key, (bool) $response->data->marketing );

		if ( is_wp_error( $migrate ) ) {
			return $migrate;
		}

		$response->migrate = $migrate;

		return $response;
	}

	private function clear_fs_cache( $slug = 'mailster', $plugin_id = null ) {

		if ( $fs_accounts = get_option( 'fs_accounts' ) ) {

			if ( is_null( $plugin_id ) ) {
				$ids       = wp_list_pluck( $fs_accounts['id_slug_type_path_map'], 'slug' );
				$plugin_id = array_search( $slug, $ids );
			}
			if ( empty( $plugin_id ) ) {
				return;
			}
			if ( isset( $fs_accounts['id_slug_type_path_map'][ $plugin_id ] ) ) {
				unset( $fs_accounts['id_slug_type_path_map'][ $plugin_id ] );
			}
			if ( isset( $fs_accounts['user_id_license_ids_map'][ $plugin_id ] ) ) {
				unset( $fs_accounts['user_id_license_ids_map'][ $plugin_id ] );
			}
			if ( isset( $fs_accounts['all_licenses'][ $plugin_id ] ) ) {
				unset( $fs_accounts['all_licenses'][ $plugin_id ] );
			}
			if ( isset( $fs_accounts['updates'][ $plugin_id ] ) ) {
				unset( $fs_accounts['updates'][ $plugin_id ] );
			}
			if ( isset( $fs_accounts['plans'][ $slug ] ) ) {
				unset( $fs_accounts['plans'][ $slug ] );
			}
			if ( isset( $fs_accounts['plugins'][ $slug ] ) ) {
				unset( $fs_accounts['plugins'][ $slug ] );
			}
			if ( isset( $fs_accounts['sites'][ $slug ] ) ) {
				unset( $fs_accounts['sites'][ $slug ] );
			}
			if ( isset( $fs_accounts['plugin_data'][ $slug ] ) ) {
				unset( $fs_accounts['plugin_data'][ $slug ] );
			}
			if ( isset( $fs_accounts['admin_notices'][ $slug ] ) ) {
				unset( $fs_accounts['admin_notices'][ $slug ] );
			}
			update_option( 'fs_accounts', $fs_accounts );
		}
	}
}
