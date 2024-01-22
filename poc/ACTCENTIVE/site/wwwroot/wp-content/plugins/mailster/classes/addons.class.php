<?php

class MailsterAddons {

	private $endpoint = 'https://mailster.co/addons.json';

	private $addon_fields = array(
		'ID'               => null,
		'name'             => null,
		'slug'             => null,
		'image'            => null,
		'imagex2'          => null,
		'description'      => null,
		'index'            => null,
		'url'              => null,
		'version'          => null,
		'author'           => null,
		'author_profile'   => null,
		'requires'         => '3.0',
		'is_active'        => null,
		'author_profile'   => null,
		'download'         => null,
		'download_url'     => null,
		'price'            => null,
		'envato_item_id'   => null,
		'update_available' => false,
	);


	public function __construct() {

		add_action( 'init', array( &$this, 'init' ) );
	}


	public function init() {

		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 50 );
	}


	public function admin_menu() {

		$page = add_submenu_page( 'edit.php?post_type=newsletter', esc_html__( 'Add Ons', 'mailster' ), esc_html__( 'Add Ons', 'mailster' ), 'mailster_manage_addons', 'mailster_addons', array( &$this, 'addons' ) );
		add_action( 'load-' . $page, array( &$this, 'scripts_styles' ) );
	}


	public function download_addon( $url, $slug = null ) {

		$download_url = rawurldecode( $url );
		$slug         = isset( $slug ) ? rawurldecode( $slug ) : null;

		if ( ! function_exists( 'download_url' ) ) {
			include ABSPATH . 'wp-admin/includes/file.php';
		}

		$tempfile = download_url( $download_url );
		if ( is_wp_error( $tempfile ) ) {
			return $tempfile;
		}

		$result = $this->unzip_addon( $tempfile, $slug, true, true );

		if ( is_wp_error( $result ) ) {
			return $result;
		} else {
			$redirect = admin_url( 'edit.php?post_type=newsletter&page=mailster_addons' );
			$redirect = add_query_arg( array( 'new' => $slug ), $redirect );

			$this->schedule_screenshot( $slug, 'index.html', true );

			return $redirect;
		}

		return false;
	}


	public function remove_addon( $slug, $file = null ) {

		$location = $this->path . '/' . $slug;

		if ( ! is_null( $file ) ) {
			$location .= '/' . $file;
		}

		$wp_filesystem = mailster_require_filesystem();

		if ( $wp_filesystem->delete( $location, true ) ) {

			$this->reset_query_cache();
			return true;

		}

		return false;
	}


	public function addons() {

		include MAILSTER_DIR . 'views/addons.php';
	}


	/**
	 *
	 *
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function get_available_addons( $force = false ) {

		if ( $force || ! ( $available_addons = get_transient( 'mailster_addons_all' ) ) ) {

			$cachetime = HOUR_IN_SECONDS * 6;

			$url = add_query_arg( array( 'page' => -1 ), $this->endpoint );

			$response      = wp_remote_get( $url );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( $response_code != 200 || is_wp_error( $response ) ) {
				$cachetime = 12;
			} else {

				$response_body = wp_remote_retrieve_body( $response );

				$response_result = json_decode( $response_body, true );

				$available_addons = $response_result['items'];

			}

			set_transient( 'mailster_addons_all', $available_addons, $cachetime );

		}

		return $available_addons;
	}


	/**
	 *
	 *
	 * @param unknown $force     (optional)
	 * @return unknown
	 */
	public function get_addons( $force = false ) {

		$all_addons = $this->get_available_addons( $force );

		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins      = get_plugins();
		$all_plugin_slugs = array_keys( $all_plugins );
		$addons           = array_intersect_key( $all_addons, array_flip( $all_plugin_slugs ) );

		// add the version to the list
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( $addons as $slug => $value ) {
			$dir                        = plugin_dir_path( MAILSTER_DIR ) . $slug;
			$plugin_data                = get_plugin_data( $dir );
			$addons[ $slug ]['version'] = $plugin_data['Version'];
		}

		return $addons;
	}

	/**
	 *
	 *
	 * @param unknown $slug (optional)
	 * @return unknown
	 */
	public function get_versions( $slug = null ) {

		$addons   = $this->get_addons();
		$versions = array();
		foreach ( $addons as $s => $data ) {

			$versions[ $s ] = $data['version'];
		}

		return ! is_null( $slug ) ? ( isset( $versions[ $slug ] ) ? $versions[ $slug ] : null ) : $versions;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_updates() {

		if ( ! current_user_can( 'mailster_update_addons' ) ) {
			return 0;
		}

		return (int) get_option( 'mailster_addons_updates' );
	}


	public function check_for_updates( $force = false ) {

		$result = $this->query( array(), $force );

		if ( ! is_wp_error( $result ) ) {
			$updates = array_sum( wp_list_pluck( $result['items'], 'update_available' ) );
			update_option( 'mailster_addons_updates', $updates );
		}
	}

	/**
	 *
	 *
	 * @param unknown $file (optional)
	 * @return unknown
	 */
	public function get_raw_addon( $file = 'index.html' ) {
		if ( ! file_exists( $this->path . '/' . $this->slug . '/' . $file ) ) {
			return false;
		}

		return file_get_contents( $this->path . '/' . $this->slug . '/' . $file );
	}


	public function scripts_styles() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		wp_register_style( 'mailster-addons', MAILSTER_URI . 'assets/css/addons-style' . $suffix . '.css', array( 'themes' ), MAILSTER_VERSION );
		wp_enqueue_style( 'mailster-addons' );
		wp_enqueue_style( 'mailster-codemirror', MAILSTER_URI . 'assets/css/libs/codemirror' . $suffix . '.css', array(), MAILSTER_VERSION );
		wp_enqueue_script( 'mailster-codemirror', MAILSTER_URI . 'assets/js/libs/codemirror' . $suffix . '.js', array(), MAILSTER_VERSION, true );
		wp_enqueue_script( 'mailster-addons', MAILSTER_URI . 'assets/js/addons-script' . $suffix . '.js', array( 'mailster-script' ), MAILSTER_VERSION, true );

		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'thickbox' );

		mailster_localize_script(
			'addons',
			array(
				'downloading'  => esc_html__( 'Downloading...', 'mailster' ),
				'installing'   => esc_html__( 'Installing...', 'mailster' ),
				'activating'   => esc_html__( 'Activating...', 'mailster' ),
				'deactivating' => esc_html__( 'Deactivating...', 'mailster' ),
				'updating'     => esc_html__( 'Updating...', 'mailster' ),
				'downloaded'   => esc_html__( 'Add-on loaded!', 'mailster' ),
				'installed'    => esc_html__( 'Add-on installed!', 'mailster' ),
				'activated'    => esc_html__( 'Add-on activated!', 'mailster' ),
				'deactivated'  => esc_html__( 'Add-on deactivated!', 'mailster' ),
				'updated'      => esc_html__( 'Add-on has been updated!', 'mailster' ),
			)
		);
	}


	public function download_envato_addon() {

		if ( ! isset( $_GET['mailster_nonce'] ) ) {
			return;
		}

		if ( wp_verify_nonce( $_GET['mailster_nonce'], 'envato-activate' ) ) {

			$redirect = admin_url( 'edit.php?post_type=newsletter&page=mailster_addons&more' );

			if ( isset( $_GET['mailster_error'] ) ) {

				$error = urldecode( $_GET['mailster_error'] );
				// thanks Envato :(
				if ( 'The purchase you have requested is not downloadable at this time.' == $error ) {
					$error .= '<p>' . esc_html__( 'Please make sure you have signed in to the account you have purchased the addon!', 'mailster' ) . '</p>';
					$error .= '<p>';
					if ( isset( $_GET['mailster_slug'] ) ) {
						$addon  = $this->get_mailster_addons( sanitize_key( $_GET['mailster_slug'] ) );
						$error .= '<a href="' . esc_url( $addon['uri'] ) . '" class="external button button-primary">' . sprintf( esc_html__( 'Buy %1$s from %2$s now!', 'mailster' ), $addon['name'], 'Envato' ) . '</a> ';
						$error .= esc_html__( 'or', 'mailster' ) . ' <a href="https://account.envato.com/" class="external">' . esc_html__( 'Visit Envato Account', 'mailster' ) . '</a>';
					}
					$error .= '</p>';
				}

				$error = sprintf( 'There was an error loading the addon: %s', $error );
				mailster_notice( $error, 'error', true );
			}

			if ( isset( $_GET['mailster_download_url'] ) ) {
				$download_url = urldecode( $_GET['mailster_download_url'] );
				$slug         = isset( $_GET['mailster_slug'] ) ? urldecode( $_GET['mailster_slug'] ) : null;

				if ( ! function_exists( 'download_url' ) ) {
					include ABSPATH . 'wp-admin/includes/file.php';
				}

				$tempfile = download_url( $download_url );

				$result = $this->unzip_addon( $tempfile, $slug, true, true );
				if ( is_wp_error( $result ) ) {
					mailster_notice( sprintf( 'There was an error loading the addon: %s', $result->get_error_message() ), 'error', true );
				} else {
					mailster_notice( esc_html__( 'Add-on successful loaded!', 'mailster' ), 'success', true );
					$redirect = admin_url( 'edit.php?post_type=newsletter&page=mailster_addons' );
					$redirect = add_query_arg( array( 'new' => $slug ), $redirect );
					// force a reload
					update_option( 'mailster_addons', false );
				}
			}
		}

		mailster_redirect( $redirect );
		exit;
	}


	/**
	 *
	 *
	 * @param unknown $new
	 */
	public function on_activate( $new ) {
	}



	public function reset_query_cache() {
		global $wpdb;

		$wpdb->query( "UPDATE {$wpdb->options} SET option_value = 0 WHERE option_name LIKE '_transient_timeout_mailster_addons_%'" );
	}



	public function query( $query_args = array(), $force = false ) {

		$query_args = wp_parse_args(
			rawurlencode_deep( $query_args ),
			array(
				's'      => '',
				'type'   => 'keyword',
				'browse' => 'all',
				'page'   => 1,
			)
		);

		$cache_key = 'mailster_addons_' . $query_args['browse'] . '_' . md5( serialize( $query_args ) . MAILSTER_VERSION );

		if ( $force || ! ( $result = get_transient( $cache_key ) ) ) {

			$cachetime = HOUR_IN_SECONDS * 6;

			$result = array(
				'total' => 0,
				'items' => array(),
				'error' => null,
			);

			$args = array(
				'timeout' => 5,
				'headers' => array( 'hash' => sha1( mailster_option( 'ID' ) ) ),
			);

			$url = add_query_arg( $query_args, $this->endpoint );

			$response      = wp_remote_get( $url, $args );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( $response_code != 200 || is_wp_error( $response ) ) {
				$result['error'] = esc_html__( 'We are currently not able to handle your request. Please try again later.', 'mailster' );
				$cachetime       = 12;
			} else {

				$response_body = wp_remote_retrieve_body( $response );

				$response_result = json_decode( $response_body, true );

				$result['items'] = array_replace_recursive( ( $result['items'] ), ( $response_result['items'] ) );
				$result['total'] = max( count( $result['items'] ), $response_result['total'] );

			}

			$result = $this->prepare_results( $result );

			set_transient( $cache_key, $result, $cachetime );

		}

		return $result;
	}

	public function prepare_results( $result ) {

		$addons = $this->get_addons();

		foreach ( $result['items'] as $slug => $item ) {

			// fill response with default values
			$result['items'][ $slug ]                 = array_merge( $this->addon_fields, $result['items'][ $slug ] );
			$result['items'][ $slug ]['description']  = wpautop( $result['items'][ $slug ]['description'] );
			$result['items'][ $slug ]['is_supported'] = empty( $result['items'][ $slug ]['requires'] ) || version_compare( $result['items'][ $slug ]['requires'], MAILSTER_VERSION, '<=' );

			if ( $result['items'][ $slug ]['installed'] = isset( $addons[ $slug ] ) ) {
				$result['items'][ $slug ] = array_merge( $addons[ $slug ], array_filter( $result['items'][ $slug ] ) );

				$result['items'][ $slug ]['is_active']        = is_plugin_active( $result['items'][ $slug ]['wpslug'] );
				$result['items'][ $slug ]['update_available'] = isset( $result['items'][ $slug ]['new_version'] ) && version_compare( $result['items'][ $slug ]['new_version'], $result['items'][ $slug ]['version'], '>' );

			}
		}

		return $result;
	}

	public function result_to_html( $result ) {

		ob_start();

		foreach ( $result['items'] as $slug => $item ) {
			include MAILSTER_DIR . 'views/addons/addon.php';
		}

		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}
}
