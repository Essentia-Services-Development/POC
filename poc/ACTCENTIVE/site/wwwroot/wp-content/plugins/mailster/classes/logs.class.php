<?php

class MailsterLogs {

	/**
	 *
	 *
	 * @param unknown $method
	 * @param unknown $args
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( &$this, 'init' ) );
	}


	public function init() {

		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 55 );
		add_action( 'mailster_cron_cleanup', array( &$this, 'cleanup' ), 100 );
		add_filter( 'set-screen-option', array( &$this, 'save_screen_options' ), 10, 3 );
	}


	public function admin_menu() {

		if ( ! mailster_option( 'logging' ) ) {
			return;
		}

		$page = add_submenu_page( 'edit.php?post_type=newsletter', esc_html__( 'Logs', 'mailster' ), esc_html__( 'Logs', 'mailster' ), 'mailster_view_logs', 'mailster_logs', array( &$this, 'view_logs' ) );

		add_action( 'load-' . $page, array( &$this, 'script_styles' ) );

		if ( isset( $_GET['ID'] ) ) :

			add_action( 'load-' . $page, array( &$this, 'edit_entry' ), 99 );

		else :

			add_action( 'load-' . $page, array( &$this, 'screen_options' ), 99 );
			add_action( 'load-' . $page, array( &$this, 'bulk_actions' ), 99 );
			add_filter( 'manage_' . $page . '_columns', array( &$this, 'get_columns' ) );

		endif;
	}


	public function script_styles() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';

		if ( isset( $_GET['ID'] ) ) :

			wp_enqueue_style( 'mailster-log-detail', MAILSTER_URI . 'assets/css/log-style' . $suffix . '.css', array(), MAILSTER_VERSION );
			wp_enqueue_script( 'mailster-log-detail', MAILSTER_URI . 'assets/js/log-script' . $suffix . '.js', array( 'mailster-script' ), MAILSTER_VERSION, true );

		else :

			wp_enqueue_style( 'mailster-logs-table', MAILSTER_URI . 'assets/css/logs-table-style' . $suffix . '.css', array(), MAILSTER_VERSION );
			wp_enqueue_script( 'mailster-logs-table', MAILSTER_URI . 'assets/js/logs-table-script' . $suffix . '.js', array( 'mailster-script' ), MAILSTER_VERSION, true );

		endif;
	}

	public function view_logs() {

		if ( isset( $_GET['ID'] ) ) :

			include MAILSTER_DIR . 'views/logging/detail.php';

		else :

			$this->cleanup();
			include MAILSTER_DIR . 'views/logging/overview.php';

		endif;
	}

	public function save_screen_options( $status, $option, $value ) {

		if ( 'mailster_logs_per_page' == $option ) {
			update_user_option( get_current_user_id(), 'mailster_logs_per_page', (int) $value );
			return $value;
		}

		return $status;
	}

	public function screen_options() {

		require_once MAILSTER_DIR . 'classes/logs.table.class.php';

		$screen = get_current_screen();

		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Logs', 'mailster' ),
				'default' => 200,
				'option'  => 'mailster_logs_per_page',
			)
		);
	}

	public function edit_entry() {
	}

	public function bulk_actions() {

		if ( empty( $_POST ) ) {
			return;
		}

		if ( isset( $_POST['action'] ) && -1 != $_POST['action'] ) {
			$action = $_POST['action'];
		}

		if ( isset( $_POST['action2'] ) && -1 != $_POST['action2'] ) {
			$action = $_POST['action2'];
		}

		if ( isset( $_GET['action'] ) ) {
			$action = $_GET['action'];
		}
	}

	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'subject'   => esc_html__( 'Subject', 'mailster' ),
			'timestamp' => esc_html__( 'Time', 'mailster' ),
			'receivers' => esc_html__( 'Receivers', 'mailster' ),
			'campaign'  => esc_html__( 'Campaign', 'mailster' ),
		);

		return $columns;
	}

	public function add( $obj ) {

		if ( ! mailster_option( 'logging' ) ) {
			return;
		}

		global $wpdb;

		$data = array(
			'subject'       => $obj->subject,
			'timestamp'     => time(),
			'campaign_id'   => $obj->campaignID,
			'subscriber_id' => $obj->subscriberID,
			'receivers'     => serialize( array_keys( $obj->mailer->getAllRecipientAddresses() ) ),
			'html'          => $obj->mailer->Body,
			'text'          => $obj->mailer->AltBody,
			'raw'           => $obj->mailer->getSentMIMEMessage(),
			'message_id'    => $obj->mailer->getLastMessageID(),
		);

		$wpdb->insert( "{$wpdb->prefix}mailster_logs", $data );
	}

	public function cleanup() {

		if ( ! mailster_option( 'logging' ) ) {
			return;
		}

		global $wpdb;

		$max_entries = mailster_option( 'logging_max' );
		$max_days    = mailster_option( 'logging_days' );

		if ( $max_entries ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}mailster_logs WHERE ID NOT IN ( SELECT ID FROM ( SELECT ID FROM {$wpdb->prefix}mailster_logs ORDER BY ID DESC LIMIT %d  ) x ) ", $max_entries ) );
		}
		if ( $max_days ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}mailster_logs WHERE timestamp < UNIX_TIMESTAMP( DATE_SUB( NOW(), INTERVAL %d DAY ) ) ", $max_days ) );
		}
	}

	public function get( $id ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}mailster_logs WHERE ID = %d";

		return $wpdb->get_row( $wpdb->prepare( $sql, $id ) );
	}

	public function get_html( $log ) {

		$base = mailster()->get_base_link( $log->campaign_id );
		preg_match_all( '# (src|href)=(\'|")?((' . preg_quote( $base ) . ')[^\'"]+)(\'|")?#', $log->html, $links );

		$html = $log->html;

		foreach ( $links[3] as $link ) {
			$replace = preg_replace( '/\/([0-9a-f]{32})\//', '/' . str_repeat( '0', 32 ) . '/', $link );
			$html    = str_replace( $link, $replace, $html );
		}

		$html = str_replace( '<a ', '<a target="mailster_preview" ', $html );

		return sprintf( '<iframe class="html-preview" src="data:text/html;base64,%s" scrolling="auto" frameborder="0"></iframe>', base64_encode( $html ) );
	}
}
