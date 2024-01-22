<?php
/**
 * Google Calendar Integration class.
 *
 * @uathor WC_Vendors
 * @package WC_Vendors_WooCommerce_Bookings/Includes
 * @version 1.2.1
 * @since   1.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Google Calendar Integration.
 *
 * @version 1.2.1
 * @since   1.2.1
 */
class WC_Marketking_Bookings_Google_Calendar_Integration {

	const CONNECT_WOOCOMMERCE_URL = WC_BOOKINGS_CONNECT_WOOCOMMERCE_URL;

	const TOKEN_TRANSIENT_TIME = 3500;

	const DAYS_OF_WEEK = array(
		1 => 'monday',
		2 => 'tuesday',
		3 => 'wednesday',
		4 => 'thursday',
		5 => 'friday',
		6 => 'saturday',
		7 => 'sunday',
	);

	/**
	 * The single instance of the class.
	 *
	 * @var $_instance
	 * @since 1.2.1
	 */
	protected static $_instance = null;

	/**
	 * Name for nonce to update calendar settings.
	 *
	 * @since 1.2.1
	 * @var string self::SETTINGS_NONCE_NAME
	 */
	const SETTINGS_NONCE_NAME = 'mtk_wcb_calendar_settings_nonce';

	/**
	 * Action name for nonce to update calendar settings.
	 *
	 * @since 1.2.1
	 * @var string self::SETTINGS_NONCE_ACTION
	 */
	const SETTINGS_NONCE_ACTION = 'submit_mtk_wcb_calendar_settings';

	/**
	 * Name of nonce to disconnect account
	 *
	 * @since 1.2.1
	 * @var string self::DISCONNECT_NONCE_NAME
	 */
	const DISCONNECT_NONCE_NAME = 'mtk_wcb_disconnect_nonce';

	/**
	 * Action name for nonce to disconnect account
	 */
	const DISCONNECT_NONCE_ACTION = 'mtk_wcb_disconnect_account';

	/**
	 * If the service is currently is a poll operation with google.
	 *
	 * @var bool
	 */
	protected $polling = false;

	/**
	 * WooCommerce Logger instance.
	 *
	 * @var WC_Logger_Interface
	 */
	protected $log;

	/**
	 * Google Service from SDK.
	 *
	 * @var Google_Service_Calendar
	 */
	protected $service;

	/**
	 * If form_fields has been initialized.
	 *
	 * @var bool
	 */
	private $form_fields_initialized = false;

	/**
	 * Current vendor id.
	 *
	 * @var integer
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	public int $vendor_id = 0;


	/**
	 * @var string
	 */
	private string $id;

	/**
	 * Init and hook in the integration.
	 */
	private function __construct() {


		$this->id = 'mtk_wcb_google_calendar_integration';

		$this->vendor_id = get_current_user_id();

		// Actions.
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'oauth_redirect' ) );

		add_action( 'disconnect_google_account_integration', array( $this, 'disconnect_account' ) );

		add_action( 'template_redirect', array( $this, 'maybe_save_settings' ) );

		add_action( 'init', array( $this, 'register_booking_update_hooks' ) );

		add_action( 'woocommerce_before_booking_global_availability_object_save', array(
			$this,
			'sync_global_availability'
		) );

		add_action( 'woocommerce_bookings_before_delete_global_availability', array(
			$this,
			'delete_global_availability'
		) );

		add_action( 'trashed_post', array( $this, 'remove_booking' ) );
		add_action( 'untrashed_post', array( $this, 'sync_untrashed_booking' ) );
		add_action( 'wc-booking-poll-google-cal', array( $this, 'poll_google_calendar_events' ) );

		add_action( 'shutdown', array( $this, 'maybe_schedule_poller' ) );
	}

	/**
	 * Get the current vendor id.
	 *
	 * @return  int
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	protected function get_vendor_id(): int {
		return $this->vendor_id;
	}

	/**
	 * Get configured calendar id.
	 *
	 * @return string
	 */
	protected function get_calendar_id(): string {
		return $this->get_meta( 'mtk_wcb_calendar_id' );
	}

	/**
	 * Get configured sync preference
	 *
	 * @return string
	 */
	protected function get_sync_preference(): string {
		return $this->get_meta( 'mtk_wcb_sync_preference' );
	}

	/**
	 * Get WC_Logger if enabled.
	 *
	 * @return WC_Logger|null
	 */
	protected function get_logger() {
		if ( null === $this->log && 'yes' === $this->get_meta( 'debug' ) ) {
			if ( class_exists( 'WC_Logger' ) ) {
				$this->log = new WC_Logger();
			} else {
				$this->log = WC()->logger();
			}
		}

		return $this->log;
	}

	/**
	 * Logging method.
	 *
	 * @param string $message Log message.
	 * @param array $context Optional. Additional information for log handlers.
	 * @param string $level Log level.
	 *                        Available options: 'emergency', 'alert',
	 *                        'critical', 'error', 'warning', 'notice',
	 *                        'info' and 'debug'.
	 *                        Defaults to 'info'.
	 */
	private function log( $message, $context = array(), $level = WC_Log_Levels::NOTICE ) {
		$logger = $this->get_logger();
		if ( is_null( $logger ) ) {
			return;
		}

		if ( ! isset( $context['source'] ) ) {
			$context['source'] = $this->id;
		}

		$logger->log( $level, $message, $context );
	}

	/**
	 * Override parent to only init form_fields if needed.
	 *
	 * @return array
	 */
	public function get_form_fields() {
		if ( ! $this->form_fields_initialized ) {
			$this->form_fields_initialized = true; // We intentionally set this before init so we avoid any infinite loops.
			$this->init_form_fields();
		}

		return $this->form_fields;
	}

	/**
	 * Returns WC_Bookings_Google_Calendar_Settings singleton
	 *
	 * Ensures only one instance of WC_Bookings_Google_Calendar_Settings is created.
	 *
	 * @return WC_Marketking_Bookings_Google_Calendar_Integration - Main instance.
	 * @since 1.2.1
	 */
	public static function instance(): WC_Marketking_Bookings_Google_Calendar_Integration {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Update settings values from form.
	 *
	 * @since 1.2.1
	 */
	public function maybe_save_settings() {
		if ( isset( $_POST[ self::SETTINGS_NONCE_NAME ] )
		     && isset( $_POST['mtk_wcb_calendar_id'] )
		     && wp_verify_nonce( wc_clean( wp_unslash( $_POST[ self::SETTINGS_NONCE_NAME ] ) ), self::SETTINGS_NONCE_ACTION ) ) {

			$this->process_vendor_settings();

			if ( isset( $_POST['Submit'] ) ) {
				do_action( 'mtk_wcb_calendar_options_saved', $this );
				wc_print_notice( esc_html__( 'Settings saved', 'marketking-multivendor-marketplace-for-woocommerce' ), 'success' );
			}
		}
	}

	/**
	 * Update google calendar settings for vendors
	 *
	 * @return void
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	private function process_vendor_settings() {
		$this->update_meta( 'mtk_wcb_calendar_id', sanitize_text_field( wp_unslash( $_POST['mtk_wcb_calendar_id'] ) ) );
		$this->update_meta( 'mtk_wcb_sync_preference', sanitize_text_field( wp_unslash( $_POST['mtk_wcb_sync_preference'] ) ) );
	}

	/**
	 * Generates full HTML form for the instance settings.
	 *
	 * @since 1.2.1
	 */
	public static function generate_form_html(): void {
		self::instance()->maybe_save_settings();
		?>
		<form method="post" action="" id="bookings_settings">
			<?php self::instance()->admin_options(); ?>
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary"
				       value="<?php esc_attr_e( 'Save Changes', 'marketking-multivendor-marketplace-for-woocommerce' ); ?>"/>
				<?php wp_nonce_field( self::SETTINGS_NONCE_ACTION, self::SETTINGS_NONCE_NAME ); ?>
			</p>
		</form>
		<?php
	}

	/**
	 * Attempt to schedule/unschedule poller once AS is ready.
	 */
	public function maybe_schedule_poller(): void {
		if ( ! $this->is_integration_active() || 'both_ways' !== $this->get_sync_preference() ) {
			as_unschedule_action( 'wc-booking-poll-google-cal', array(), 'bookings' );

			return;
		}

		if ( ! as_next_scheduled_action( 'wc-booking-poll-google-cal' ) ) {
			$poll_interval = 1; // minutes.
			as_schedule_recurring_action( time(), $poll_interval * MINUTE_IN_SECONDS, 'wc-booking-poll-google-cal', array(), 'bookings' );
		}
	}

	/**
	 * Registers booking object lifecycle events.
	 * Needs to happen after init because of the dynamic hook names.
	 */
	public function register_booking_update_hooks(): void {
		foreach ( $this->get_booking_is_paid_statuses() as $status ) {
			// We have to do it this way because of the dynamic hook name.
			add_action( 'woocommerce_booking_' . $status, array( $this, 'sync_new_booking' ) );
		}

		add_action( 'woocommerce_booking_cancelled', array( $this, 'remove_booking' ) );
		add_action( 'woocommerce_booking_process_meta', array( $this, 'sync_edited_booking' ) );
	}

	/**
	 * Returns an authorized API client.
	 *
	 * @return Google_Client the authorized client object
	 */
	protected function get_client(): Google_Client {
		$client = new Google_Client();
		$client->setApplicationName( 'WooCommerce Bookings Google Calendar Integration' );
		$client->setScopes( Google_Service_Calendar::CALENDAR );
		$access_token  = get_transient( 'mtk_wcb_gcalendar_access_token_' . $this->get_vendor_id() );
		$refresh_token = $this->get_meta( 'mtk_wcb_gcalendar_refresh_token' );

		$client->setAccessType( 'offline' );

		do_action( 'mtk_wcb_update_google_client', $client );

		$client->setRedirectUri( WC()->api_request_url( $this->id ) );


		// Refresh the token if it's expired. Note that we need a refresh token for this.
		if ( $refresh_token && empty( $access_token ) ) {
			$access_token = $this->renew_access_token( $refresh_token, $client );
			if ( $access_token && isset( $access_token['access_token'] ) ) {
				unset( $access_token['refresh_token'] ); // unset this since we store it in an option.
				set_transient( 'mtk_wcb_gcalendar_access_token_' . $this->get_vendor_id(), $access_token, self::TOKEN_TRANSIENT_TIME );
			} else {
				$this->log(
					sprintf(
						'Unable to fetch access token with refresh token. Google sync disabled until re-authenticated. Error: "%s", "%s"',
						isset( $access_token['error'] ) ? $access_token['error'] : '',
						isset( $access_token['error_description'] ) ? $access_token['error_description'] : ''
					),
					array(),
					WC_Log_Levels::ERROR
				);
			}
		}

		// It may be empty, e.g. in case refresh token is empty.
		if ( ! empty( $access_token ) ) {
			$access_token['refresh_token'] = $refresh_token;
			try {
				$client->setAccessToken( $access_token );
			} catch ( InvalidArgumentException $e ) {
				// Something is wrong with the access token, customer should try to connect again.
				$this->log( sprintf( 'Invalid access token. Reconnect with Google necessary. Code %s. Message: %s.', $e->getCode(), $e->getMessage() ) );
			}
		}

		return $client;
	}

	/**
	 * Set a new sync token (used when Google returns one)
	 *
	 * @param string $sync_token Google sync token.
	 */
	protected function set_sync_token( $sync_token ) {
		set_transient( 'mtk_wcb_gcalendar_sync_token_' . $this->get_vendor_id(), $sync_token, self::TOKEN_TRANSIENT_TIME );
	}

	/**
	 * Get sync token.
	 *
	 * @return string
	 */
	protected function get_sync_token() {
		return get_transient( 'mtk_wcb_gcalendar_sync_token_' . $this->get_vendor_id() );
	}

	/**
	 * This is called by API requesters. We are not doing it on the constructor
	 * as it takes some time to init the service, so only init when necessary.
	 */
	protected function maybe_init_service() {
		if ( empty( $this->service ) ) {
			$this->service = new Google_Service_Calendar( $this->get_client() );
		}
	}

	/**
	 * Get Google Events (paginated)
	 *
	 * @param array $params Current parameters.
	 *
	 * @return array
	 */
	protected function get_event_page( $params = array() ) {
		$this->maybe_init_service();

		$request_params = array(
			'timeZone' => wc_booking_get_timezone_string(),
		);
		if ( ! empty( $this->page_token ) ) {
			$request_params              = $params;
			$request_params['pageToken'] = $this->page_token;
		} else {
			$sync_token = $this->get_sync_token();
			if ( ! empty( $sync_token ) ) {
				$request_params['syncToken'] = $sync_token;
				if ( isset( $params['maxResults'] ) ) {
					$request_params['maxResults'] = $params['maxResults'];
				}
			} else {
				$request_params = $params;
			}
		}

		try {
			$results = $this->service->events->listEvents( $this->get_calendar_id(), $request_params );
		} catch ( Exception $e ) {
			return array(
				'events'   => array(),
				'has_next' => false,
				'error'    => $e->getCode(),
			);
		}

		$this->page_token = $results->getNextPageToken();

		$sync_token = $results->getNextSyncToken();
		if ( ! empty( $sync_token ) ) {
			$this->set_sync_token( $sync_token );
		}

		return array(
			'events'   => $results->getItems(),
			'has_next' => empty( $sync_token ),
			'error'    => 0,
		);
	}

	/**
	 * Get a list of calendar events.
	 *
	 * @return array
	 */
	public function get_events() {
		$events = array();

		$params = apply_filters(
			'woocommerce_bookings_gcal_events_request',
			array(
				'singleEvents' => false,
				'timeMin'      => date( 'c' ),
				'timeMax'      => date( 'c', strtotime( 'now +2 years' ) ),
				'timeZone'     => wc_booking_get_timezone_string(),
			)
		);

		do {
			$page_result = $this->get_event_page( $params );

			// Full sync case.
			if ( 410 === (int) $page_result['error'] ) {
				$page_result['has_next'] = true;
				$this->set_sync_token( '' ); // Unset expired token.
				continue; // Repeat same request.
			}

			if ( 0 !== (int) $page_result['error'] ) {
				$this->log( $page_result['error'] );

				// TODO: Unhandled error. Handle it somehow.
			}

			$events = array_merge( $events, $page_result['events'] );
		} while ( $page_result['has_next'] ); // Final page will include a syncToken.

		return $events;
	}

	/**
	 * Method for polling data from Google API.
	 *
	 * Sync path: Google API -> Bookings
	 * The sync path Bookings -> Google API will be handled by `action` and `filter` events.
	 */
	public function poll_google_calendar_events() {
		if ( 'both_ways' !== $this->get_sync_preference() || ! $this->is_integration_active() ) {
			return;
		}

		$this->polling = true;
		try {
			$this->log( 'Getting Google Calendar List from Google Calendar API...' );

			/**
			 * Global Availability Data store instance.
			 *
			 * @var WC_Global_Availability_Data_Store $global_availability_data_store
			 */
			$global_availability_data_store = WC_Data_Store::load( WC_Global_Availability::DATA_STORE );

			$events = $this->get_events();

			foreach ( $events as $event ) {
				$availabilities = $global_availability_data_store->get_all(
					array(
						array(
							'key'     => 'gcal_event_id',
							'value'   => $event['id'],
							'compare' => '=',
						),
					)
				);

				if ( empty( $availabilities ) ) {

					$booking_ids = WC_Booking_Data_Store::get_booking_ids_by( array( 'google_calendar_event_id' => $event['id'] ) );

					if ( ! empty( $booking_ids ) ) {
						// Google event is an existing booking not a manually created event for the global availability.
						// Ignore changes for now in future we may allow editing bookings from google calendar.
						continue;
					}

					// If no global availability found, just create one.
					$global_availability = new WC_Global_Availability();
					if ( 'cancelled' !== $event->getStatus() ) {
						$this->update_global_availability_from_event( $global_availability, $event );
						$global_availability->save();
					}

					continue;
				}

				foreach ( $availabilities as $availability ) {
					$event_date        = new WC_DateTime( $event['updated'] );
					$availability_date = $availability->get_date_modified();

					if ( $event_date > $availability_date ) {
						// Sync Google Event -> Global Availability.
						if ( 'cancelled' !== $event->getStatus() ) {

							$this->update_global_availability_from_event( $availability, $event );
							$availability->save();
						} else {
							$availability->delete();
						}
					}
				}
			}
		} catch ( Exception $e ) {
			$this->log( 'Error while getting list of events' );
		}
		$this->polling = false;
	}

	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'mtk_wcb_authorization'   => array(
				'title' => __( 'Authorization', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'  => 'google_calendar_authorization',
			),
			'mtk_wcb_calendar_id'     => array(
				'title'         => __( 'Calendar', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'type'          => 'select',
				'description'   => __( 'Enter with your Calendar.', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'desc_tip'      => true,
				'default'       => '',
				'options'       => $this->get_calendar_list_options(),
				'display_check' => array( $this, 'display_connection_settings' ),
			),
			'mtk_wcb_sync_preference' => array(
				'type'          => 'select',
				'title'         => __( 'Sync Preference', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'options'       => array(
					'both_ways' => __( 'Sync both ways - between Store and Google', 'marketking-multivendor-marketplace-for-woocommerce' ),
					'one_way'   => __( 'Sync one way - from Store to Google', 'marketking-multivendor-marketplace-for-woocommerce' ),
				),
				'description'   => __( 'Manage the sync flow between your Store calendar and Google calendar.', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'desc_tip'      => true,
				'default'       => 'one_way',
				'display_check' => array( $this, 'display_connection_settings' ),
			)
		);
	}

	/**
	 * Generate Settings HTML.
	 *
	 * Extends base class html generation to add 'display_check' parameter to each
	 * field. 'display_check', is a callable that enables/disables the display of
	 * the field.
	 *
	 * @param array $form_fields (default: array()) Array of form fields.
	 * @param bool $echo Echo or return.
	 *
	 * @return string the html for the settings
	 * @since  1.2.1
	 */
	public function generate_settings_html( $form_fields = array(), $echo = true ) {
		if ( empty( $form_fields ) ) {
			$form_fields = $this->get_form_fields();
		}
		foreach ( $form_fields as $index => $field ) {
			// Delete fields if they have an "enable_check" function that returns false.
			if ( isset( $field['display_check'] ) && ! call_user_func( $field['display_check'] ) ) {
				unset( $form_fields[ $index ] );
			}
		}

		return $this->generate_settings_html_output( $form_fields, $echo );
	}

	/**
	 * Generate settings html output
	 *
	 * @param array $form_fields The form fields to display.
	 * @param array $echo Whether to return or output the html.
	 *
	 * @return string
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	public function generate_settings_html_output( $form_fields, $echo = true ) {
		global $post;

		ob_start();
		foreach ( $form_fields as $field_name => $field ) :
			switch ( $field['type'] ) {
				case 'google_calendar_authorization':
					echo $this->generate_google_calendar_authorization_html( $field_name, $field );
					break;
				case 'title':
					?>
					<tr>
						<td colspan="2" style="">
							<?php echo esc_attr( $field['title'] ); ?>
							<?php if ( '' === $field['description'] ): ?>
								<p><?php echo esc_attr_html( $description ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<?php
					break;
				default:
					?>
					<tr>
						<td><?php echo esc_attr( $field['title'] ); ?></td>
						<td style="padding: 10px 0 10px 30px;">
							<?php $this->render_input( $field_name, $field ); ?>
						</td>
					</tr>
					<?php
					break;
			}

		endforeach;

		if ( ! $echo ) {
			return ob_get_clean();
		}

		echo ob_get_clean();
	}

	/**
	 * Output the custom fields
	 *
	 * @param string $field_name The name of the field.
	 * @param array $field The field option.
	 *
	 * @return  void
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	public static function render_input( $field_name, $field ) {
		$value          = get_user_meta( get_current_user_id(), $field_name, true );
		$field['value'] = $value;
		switch ( $field['type'] ) {
			case 'text':
			case 'number':
			case 'email':
				?>
				<input name="<?php echo esc_attr( $field_name ); ?>"
				       id="<?php echo esc_attr( $field_name ); ?>"
				       type="<?php echo $field['type']; ?>"
				       value="<?php echo esc_attr( $value ); ?>"/>
				<?php
				break;
			case 'select':
				?>
				<select name="<?php echo esc_attr( $field_name ); ?>" class="select2">
					<?php foreach ( $field['options'] as $_value => $label ) { ?>
						<option
						value="<?php echo esc_attr( $_value ); ?>" <?php selected( $_value, $field['value'] ); ?>><?php echo esc_attr( $label ); ?></option><?php
					}
					?>
				</select>
				<?php
				break;
			default:
				do_action( 'mtk_wcb_input_type_' . $field['type'], $field );
				break;
		}
	}

	/**
	 * Whether or not connection settings should be displayed.
	 *
	 * Checks Google connection status so connection settings can be hidden
	 * if there is no active action.
	 *
	 * @return bool Whether or not connection settings should be displayed.
	 * @since  1.2.1
	 */
	protected function display_connection_settings() {
		$access_token  = $this->get_client()->getAccessToken();
		$refresh_token = $this->get_meta( 'mtk_wcb_gcalendar_refresh_token' );

		return $access_token && $refresh_token;
	}

	/**
	 * Get a user's meta key.
	 *
	 * @param string $option_name The option name to get.
	 * @param string $vendor_id The optional vendor's id.
	 *
	 * @return  mixed
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	public function get_meta( $option_name, $vendor_id = 0 ) {
		if ( 0 === $vendor_id ) {
			$vendor_id = get_current_user_id();
		}

		if ( ! $vendor_id ) {
			return '';
		}

		return apply_filters( 'get_vendor_meta_' . $option_name, get_user_meta( $vendor_id, $option_name, true ), $vendor_id );
	}

	/**
	 * Update user meta.
	 *
	 * @param string $meta_key The meta key to update.
	 * @param string $meta_value The value to save
	 * @param integer $vendor_id The user to save meta for.
	 *
	 * @return mixed
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	public function update_meta( $meta_key, $meta_value, $vendor_id = 0 ) {
		if ( 0 === $vendor_id ) {
			$vendor_id = $this->get_vendor_id();
		}

		if ( ! $vendor_id ) {
			return false;
		}

		update_user_meta( $vendor_id, $meta_key, $meta_value );
	}

	/**
	 * Delete a user's meta details.
	 *
	 * @param string $meta_key Key of the meta to delete.
	 * @param integer $vendor_id The id of the user.
	 *
	 * @return void
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	public function delete_meta( $meta_key, $vendor_id = 0 ) {
		if ( 0 === $vendor_id ) {
			$vendor_id = $this->get_vendor_id();
		}

		if ( ! $vendor_id ) {
			return false;
		}
		delete_user_meta( $vendor_id, $meta_key );
	}

	/**
	 * Returns an array to feed the calendar list select input
	 *
	 * @return array
	 */
	private function get_calendar_list_options() {
		$this->maybe_init_service();
		$options = array( '' => __( 'Select a calendar from the list', 'marketking-multivendor-marketplace-for-woocommerce' ) );

		if ( $this->is_integration_active() ) {
			try {
				return array_reduce(
					$this->service->calendarList->listCalendarList()->items,
					function ( $carry, $item ) {
						$carry[ $item['id'] ] = $item['summary'];

						return $carry;
					},
					$options
				);
			} catch ( Exception $e ) {
				$this->log( 'Error while getting the list of calendars: ' . $e->getMessage() );
			}
		}

		return $options;
	}

	/**
	 * Validate the Google Calendar Authorization field.
	 * Really it performs the oauth_logout if the disconnect button is clicked.
	 *
	 * @param string $key Current Key.
	 * @param string $value Value of field.
	 *
	 * @return string
	 */
	public function disconnect_account( $value ) {
		if ( ! isset( $_POST[ $this->get_field_key() ] ) ) {
			return '';
		}

		if ( isset( $_POST[ self::DISCONNECT_NONCE_NAME ] ) && ! wp_verify_nonce( $_POST[ self::DISCONNECT_NONCE_NAME ], self::DISCONNECT_NONCE_ACTION ) ) {
			return '';
		}
		if ( 'logout' === $value ) {
			$this->oauth_logout();
		}

		return '';
	}

	/**
	 * Generate the Google Calendar Authorization field.
	 *
	 * @param mixed $key
	 * @param array $data
	 *
	 * @return string
	 */
	public function generate_google_calendar_authorization_html( $key, $data ) {
		$access_token  = $this->get_client()->getAccessToken();
		$refresh_token = $this->get_meta( 'mtk_wcb_gcalendar_refresh_token' );
		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php echo wp_kses_post( $data['title'] ); ?>
			</th>
			<td class="forminp" style="padding-left: 30px; padding-bottom:20px;">
				<?php if ( ! $refresh_token ) : ?>
					<p class="submit">
						<a class="button button-primary"
						   href="<?php echo esc_attr( $this->get_google_auth_url() ); ?>">
							<?php esc_html_e( 'Connect with Google', 'marketking-multivendor-marketplace-for-woocommerce' ); ?>
						</a>
					</p>
				<?php elseif ( $access_token ) : ?>
					<p><?php esc_html_e( 'Successfully authenticated.', 'marketking-multivendor-marketplace-for-woocommerce' ); ?></p>
					<p class="submit">
						<?php wp_nonce_field( self::DISCONNECT_NONCE_ACTION, self::DISCONNECT_NONCE_NAME ); ?>
						<button class="button button-primary"
						        name="<?php echo esc_attr( $this->get_field_key() ); ?>"
						        value="logout">
							<?php esc_html_e( 'Disconnect', 'marketking-multivendor-marketplace-for-woocommerce' ); ?>
						</button>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'Unable to authenticate.' ); ?></p>
					<p class="submit">
						<a class="button button-primary"
						   href="<?php echo esc_attr( $this->get_google_auth_url() ); ?>">
							<?php esc_html_e( 'Re-Connect with Google', 'marketking-multivendor-marketplace-for-woocommerce' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

	/**
	 * Admin Options.
	 */
	public function admin_options() {
		echo '<p>' . esc_html__( 'To start syncing with Google Calendar, click the "Connect with Google" button below to authorize your store to access your Google calendar.', 'marketking-multivendor-marketplace-for-woocommerce' ) . '</p>';

		echo '<table class="form-table" style="margin-top: 10px;margin-bottom:60px;">';
		$this->generate_settings_html();
		echo '</table>';
	}

	/**
	 * OAuth Logout.
	 *
	 * @return bool
	 */
	protected function oauth_logout() {
		$this->log( 'Leaving the Google Calendar app...' );

		$client       = $this->get_client();
		$access_token = $client->getAccessToken();

		if ( ! empty( $access_token['access_token'] ) ) {
			if ( $client->getClientId() ) {
				$body = $client->revokeToken( $access_token );
			} else {
				$response = wp_remote_post(
					self::CONNECT_WOOCOMMERCE_URL . '/revoke/google',
					array(
						'body' => array( 'access_token' => $access_token ),
					)
				);
				$body     = json_decode( wp_remote_retrieve_body( $response ), true );
			}
			if ( $body['success'] ) {
				wc_print_notice(
					'<strong>' . esc_html__( 'Google Calendar', 'marketking-multivendor-marketplace-for-woocommerce' ) . '</strong> ' . esc_html__( 'Account disconnected successfully!', 'marketking-multivendor-marketplace-for-woocommerce' ),
					'success'
				);
			} else {
				wc_print_notice(
					'<strong>' . esc_html__( 'Google Calendar', 'marketking-multivendor-marketplace-for-woocommerce' ) . '</strong> ' . esc_html__( 'Failed to disconnect to your account, please try again, if the problem persists, turn on Debug Log option and see what is happening.', 'marketking-multivendor-marketplace-for-woocommerce' ),
					'error'
				);
			}
		} else {
			wc_print_notice(
				'<strong>' . esc_html__( 'Google Calendar', 'marketking-multivendor-marketplace-for-woocommerce' ) . '</strong> ' . esc_html__( 'Account not properly connected, reset successfully!', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'notice'
			);
		}

		$this->delete_meta( 'mtk_wcb_gcalendar_refresh_token' );
		delete_transient( 'mtk_wcb_gcalendar_sync_token_' . $this->get_vendor_id() );
		delete_transient( 'mtk_wcb_gcalendar_access_token_' . $this->get_vendor_id() );

		$logger = $this->get_logger();
		if ( $logger ) {
			$logger->add( $this->id, 'Left the Google Calendar App. successfully' );
		}

		return true;
	}

	/**
	 * Process the oauth redirect.
	 *
	 * @return void
	 */
	public function oauth_redirect(): void {

		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You must be logged in to perform this action.', 'marketking-multivendor-marketplace-for-woocommerce' ) );
		}


		if ( ! marketking()->is_vendor( $this->vendor_id ) ) {
			wp_die( esc_html__( 'Permission denied! You must be an administrator or a 
			vendor', 'marketking-multivendor-marketplace-for-woocommerce' ) );
		}


		$access_token = array(
			'access_token'  => $_GET['access_token'],
			'expires_in'    => $_GET['expires_in'],
			'scope'         => $_GET['scope'],
			'token_type'    => $_GET['token_type'],
			'created'       => $_GET['created'],
			'refresh_token' => $_GET['refresh_token'],
		);

		$client = $this->get_client();
		$client->setAccessToken( $access_token );

		unset( $access_token['refresh_token'] ); // unset this since we store it in an option.

		set_transient( 'mtk_wcb_gcalendar_access_token_' . $this->get_vendor_id(), $access_token, self::TOKEN_TRANSIENT_TIME );

		$this->update_meta( 'mtk_wcb_gcalendar_refresh_token', $client->getRefreshToken() );

		if ( ! empty( $access_token['access_token'] ) ) {
			wc_add_notice(
				'<strong>' . esc_html__( 'Google Calendar', 'marketking-multivendor-marketplace-for-woocommerce' ) . '</strong> ' . esc_html__( 'Account connected successfully!', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'success'
			);
			$this->log( 'Google Oauth successful.' );
		} else {
			$this->log(
				sprintf(
					'Google Oauth failed with "%s", "%s"',
					isset( $_GET['error'] ) ? $_GET['error'] : '',
					isset( $_GET['error_description'] ) ? $_GET['error_description'] : ''
				),
				array(),
				WC_Log_Levels::ERROR
			);
			wc_add_notice(
				'<strong>' . esc_html__( 'Google Calendar', 'marketking-multivendor-marketplace-for-woocommerce' ) . '</strong> ' . esc_html__( 'Failed to connect to your account, please try again, if the problem persists, turn on Debug Log option and see what is happening.', 'marketking-multivendor-marketplace-for-woocommerce' ),
				'error'
			);
		}

		wp_safe_redirect(
			esc_url(
				trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) .
				'calendar-google-integration/'
			)
		);

		exit;
	}

	/**
	 * Sync new Booking with Google Calendar.
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return void
	 */
	public function sync_new_booking( $booking_id ) {
		if ( $this->is_edited_from_meta_box() || 'wc_booking' !== get_post_type( $booking_id ) ) {
			return;
		}
		$this->sync_booking( $booking_id );
	}

	/**
	 * Check if Google Calendar settings are supplied and we're authenticated.
	 *
	 * @return bool True is calendar is set, false otherwise.
	 */
	public function is_integration_active() {
		$refresh_token = $this->get_meta( 'mtk_wcb_gcalendar_refresh_token' );

		return ! empty( $refresh_token );
	}

	/**
	 * Sync an event resource with Google Calendar.
	 * https://developers.google.com/google-apps/calendar/v3/reference/events
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return  object|boolean Parsed JSON data from the http request or false if error
	 */
	public function get_event_resource( $booking_id ) {
		if ( $booking_id < 0 ) {
			return false;
		}

		$booking  = get_wc_booking( $booking_id );
		$event_id = $booking->get_google_calendar_event_id();
		$event    = false;

		$this->maybe_init_service();

		try {
			$event = $this->service->events->get( $this->get_calendar_id(), $event_id );
		} catch ( Exception $e ) {
			$this->log( 'Error while getting event for Booking ' . $booking_id . ': ' . $e->getMessage() );
		}

		return $event;
	}

	/**
	 * Sync Booking with Google Calendar.
	 *
	 * @param int $booking_id Booking ID.
	 */
	public function sync_booking( $booking_id ) {
		if ( ! $this->is_integration_active() || 'wc_booking' !== get_post_type( $booking_id ) ) {
			return;
		}

		$this->maybe_init_service();

		// Booking data.
		$booking         = get_wc_booking( $booking_id );
		$event_id        = $booking->get_google_calendar_event_id();
		$product_id      = $booking->get_product_id();
		$order           = $booking->get_order();
		$product         = wc_get_product( $product_id );
		$booking_product = get_wc_product_booking( $product_id );
		$resource        = $booking_product->get_resource( $booking->get_resource_id() );
		$timezone        = wc_booking_get_timezone_string();
		$description     = '';
		$customer        = $booking->get_customer();

		$booking_data = array(
			__( 'Booking ID', 'marketking-multivendor-marketplace-for-woocommerce' )   => $booking_id,
			__( 'Booking Type', 'marketking-multivendor-marketplace-for-woocommerce' ) => is_object( $resource ) ? $resource->get_title() : '',
			__( 'Persons', 'marketking-multivendor-marketplace-for-woocommerce' )      => $booking->has_persons() ? array_sum( $booking->get_persons() ) : 0,
		);

		foreach ( $booking_data as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			$description .= sprintf( '%s: %s', rawurldecode( $key ), rawurldecode( $value ) ) . PHP_EOL;
		}


		$edit_booking_url = esc_url(
			trailingslashit(get_page_link( $this->get_meta( 'marketking_vendordash_page_setting', 'disabled' ) ) ). 'edit-booking-order/' . $booking_id
		);
		// Add read-only message.
		/* translators: %s URL to edit booking */
		$description .= PHP_EOL . sprintf( __( 'NOTE: this event cannot be edited in Google Calendar. If you need to make changes, <a href="%s" target="_blank">please edit this booking in WooCommerce</a>.', 'marketking-multivendor-marketplace-for-woocommerce' ), $edit_booking_url );

		if ( is_a( $order, 'WC_Order' ) ) {
			foreach ( $order->get_items() as $order_item_id => $order_item ) {
				if ( $order_item_id !== WC_Booking_Data_Store::get_booking_order_item_id( $booking_id ) ) {
					continue;
				}
				foreach ( $order_item->get_meta_data() as $order_meta_data ) {
					$the_meta_data = $order_meta_data->get_data();

					if ( is_serialized( $the_meta_data['value'] ) ) {
						continue;
					}

					$description .= sprintf( '%s: %s', html_entity_decode( $the_meta_data['key'] ), html_entity_decode( $the_meta_data['value'] ) ) . PHP_EOL;
				}
			}
		}

		$event = $this->get_event_resource( $booking_id );
		if ( empty( $event ) ) {
			$event = new Google_Service_Calendar_Event();
		}

		// If the user edited the description on the Google Calendar side we want to keep that data intact.
		if ( empty( trim( $event->getDescription() ) ) ) {
			$event->setDescription( wp_kses_post( $description ) );
		}

		// Set the event data.
		$product_title = $product ? html_entity_decode( $product->get_title() ) : __( 'Booking', 'marketking-multivendor-marketplace-for-woocommerce' );
		$event->setSummary( wp_kses_post( sprintf( "%s, %s - #%s", $customer->name, $product_title, $booking->get_id() ) ) );

		// Set the event start and end dates.
		$start = new Google_Service_Calendar_EventDateTime();
		$end   = new Google_Service_Calendar_EventDateTime();

		if ( $booking->is_all_day() ) {
			// 1440 min = 24 hours. Bookings includes 'end' in its set of days, where as GCal uses that
			// as the cut off, so we need to add 24 hours to include our final 'end' day.
			// https://developers.google.com/google-apps/calendar/v3/reference/events/insert
			$start->setDate( date( 'Y-m-d', $booking->get_start() ) );
			$end->setDate( date( 'Y-m-d', $booking->get_end() + 1440 ) );
		} else {
			$start->setDateTime( date( 'Y-m-d\TH:i:s', $booking->get_start() ) );
			$start->setTimeZone( $timezone );
			$end->setDateTime( date( 'Y-m-d\TH:i:s', $booking->get_end() ) );
			$end->setTimeZone( $timezone );
		}

		$event->setStart( $start );
		$event->setEnd( $end );

		/**
		 * Update Google event before sync.
		 *
		 * Optional filter to allow third parties to update content of Google event when a booking is created or updated.
		 *
		 * @param Google_Service_Calendar_Event $event Google event object being added or updated.
		 * @param WC_Booking $booking Booking object being synced to Google calendar.
		 */
		$event = apply_filters( 'woocommerce_bookings_gcalendar_sync', $event, $booking );

		try {
			if ( empty( $event->getId() ) ) {
				$event = $this->service->events->insert( $this->get_calendar_id(), $event );
			} else {
				$this->service->events->update( $this->get_calendar_id(), $event->getId(), $event );
			}

			$booking->set_google_calendar_event_id( wc_clean( $event->getId() ) );

			update_post_meta( $booking->get_id(), '_mtk_wcb_gcalendar_event_id', $event->getId() );
		} catch ( Exception $e ) {
			$this->log( 'Error while adding/updating Google event: ' . $e->getMessage() );
		}
	}

	/**
	 * Sync Booking with Google Calendar when booking is edited.
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return void
	 */
	public function sync_edited_booking( $booking_id ) {
		if ( ! $this->is_edited_from_meta_box() ) {
			return;
		}
		$this->maybe_sync_booking_from_status( $booking_id );
	}

	/**
	 * Sync Booking with Google Calendar when booking is untrashed.
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return void
	 */
	public function sync_untrashed_booking( $booking_id ) {
		$this->maybe_sync_booking_from_status( $booking_id );
	}

	/**
	 * Remove/cancel the booking in Google Calendar
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return void
	 */
	public function remove_booking( $booking_id ) {
		if ( 'wc_booking' !== get_post_type( $booking_id ) ) {
			return;
		}

		$this->maybe_init_service();

		$booking  = get_wc_booking( $booking_id );
		$event_id = $booking->get_google_calendar_event_id();

		if ( $event_id ) {
			try {
				$resp = $this->service->events->delete( $this->get_calendar_id(), $event_id );

				if ( 204 === $resp->getStatusCode() ) {
					$this->log( 'Booking removed successfully!' );

					// Remove event ID.
					update_post_meta( $booking->get_id(), '_mtk_wcb_gcalendar_event_id', '' );
				} else {
					$this->log( 'Error while removing the booking #' . $booking->get_id() . ': ' . print_r( $resp, true ) );
				}
			} catch ( Exception $e ) {
				$this->log( 'Error while deleting event from Google: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Maybe remove / sync booking based on booking status.
	 *
	 * @param int $booking_id Booking ID.
	 *
	 * @return void
	 */
	public function maybe_sync_booking_from_status( $booking_id ) {
		global $wpdb;

		$status = $wpdb->get_var( $wpdb->prepare( "SELECT post_status FROM $wpdb->posts WHERE post_type = 'wc_booking' AND ID = %d", $booking_id ) );

		if ( 'cancelled' === $status ) {
			$this->remove_booking( $booking_id );
		} elseif ( in_array( $status, $this->get_booking_is_paid_statuses(), true ) ) {
			$this->sync_booking( $booking_id );
		}
	}

	/**
	 * Get booking's post statuses considered as paid.
	 *
	 * @return array
	 */
	private function get_booking_is_paid_statuses() {
		/**
		 * Use this filter to add custom booking statuses that should be considered paid.
		 *
		 * @param array $statuses All booking statuses considered to be paid.
		 *
		 * @since 1.2.1
		 *
		 */
		return apply_filters( 'woocommerce_booking_is_paid_statuses', array(
			'confirmed',
			'paid',
			'complete'
		) );
	}

	/**
	 * Is edited from post.php's meta box.
	 *
	 * @return bool
	 */
	public function is_edited_from_meta_box() {
		return (
			! empty( $_POST['mtk_wcb_details_meta_box_nonce'] )
			&&
			wp_verify_nonce( $_POST['mtk_wcb_details_meta_box_nonce'], 'mtk_wcb_details_meta_box' )
		);
	}

	/**
	 * Maybe delete Global Availability from Google.
	 *
	 * @param WC_Global_Availability $availability Availability to delete.
	 */
	public function delete_global_availability( WC_Global_Availability $availability ) {
		$this->maybe_init_service();

		if ( $availability->get_gcal_event_id() ) {
			try {
				$this->service->events->delete( $this->get_calendar_id(), $availability->get_gcal_event_id() );
			} catch ( Exception $e ) {
				$this->log( 'Error while deleting event from Google: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Sync Global Availability to Google.
	 *
	 * @param WC_Global_Availability $availability Global Availability object.
	 */
	public function sync_global_availability( WC_Global_Availability $availability ) {
		if ( ! $this->is_integration_active() ) {
			return;
		}

		if ( ! $availability->get_changes() ) {
			// nothing changed don't waste time syncing.
			return;
		}

		if ( $this->polling ) {
			// Event is coming from google don't send it back.
			return;
		}

		$this->maybe_init_service();

		if ( $availability->get_gcal_event_id() ) {
			try {
				$event     = $this->service->events->get( $this->get_calendar_id(), $availability->get_gcal_event_id() );
				$supported = $this->update_event_from_global_availability( $event, $availability );
				if ( $supported ) {
					$this->service->events->update( $this->get_calendar_id(), $event->getId(), $event );
				}
			} catch ( Exception $e ) {
				$this->log( 'Error while syncing global availability to Google: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Update global availability object with data from google event object.
	 *
	 * @param WC_Global_Availability $availability WooCommerce Global Availability object.
	 * @param Google_Service_Calendar_Event $event Google calendar event object.
	 *
	 * @return bool
	 */
	private function update_global_availability_from_event( WC_Global_Availability $availability, Google_Service_Calendar_Event $event ) {
		$availability->set_gcal_event_id( $event->getId() )
		             ->set_title( $event->getSummary() )
		             ->set_bookable( 'no' )
		             ->set_priority( 10 )
		             ->set_ordering( 0 );

		// TODO: check timezones.
		if ( $event->getRecurrence() ) {
			$availability->set_range_type( 'rrule' );
			$availability->set_rrule( join( "\n", $event->getRecurrence() ) );
			if ( $event->getStart()->getDateTime() ) {
				$availability->set_from_range( $event->getStart()->getDateTime() );
				$availability->set_to_range( $event->getEnd()->getDateTime() );
			} else {
				$availability->set_from_range( $event->getStart()->getDate() );
				$availability->set_to_range( $event->getEnd()->getDate() );
			}
		} elseif ( $event->getStart()->getDateTime() ) {

			$start_date = new WC_DateTime( $event->getStart()->getDateTime() );
			$end_date   = new WC_DateTime( $event->getEnd()->getDateTime() );

			try {
				// Our date ranges are inclusive, Google's are not, so shift the range (e.g. [10:00, 11:00] -> [10:01. 10:59])
				$start_date->add( new DateInterval( 'PT60S' ) );
				$end_date->sub( new DateInterval( 'PT1S' ) );
			} catch ( Exception $e ) {
				$this->log( $e->getMessage() );
				// Should never happen.
			}

			$availability->set_range_type( 'custom:daterange' )
			             ->set_from_date( $start_date->format( 'Y-m-d' ) )
			             ->set_to_date( $end_date->format( 'Y-m-d' ) )
			             ->set_from_range( $start_date->format( 'H:i' ) )
			             ->set_to_range( $end_date->format( 'H:i' ) );

		} else {

			$start_date = new WC_DateTime( $event->getStart()->getDate() );
			$end_date   = new WC_DateTime( $event->getEnd()->getDate() );

			try {
				// Our date ranges are inclusive, Google's are not.
				$end_date->sub( new DateInterval( 'P1D' ) );
			} catch ( Exception $e ) {
				$this->log( $e->getMessage() );
				// Should never happen.
			}

			$availability->set_range_type( 'custom' )
			             ->set_from_range( $start_date->format( 'Y-m-d' ) )
			             ->set_to_range( $end_date->format( 'Y-m-d' ) );

		}

		return true;
	}

	/**
	 * Update google event object with data from global availability object.
	 *
	 * @param Google_Service_Calendar_Event $event Google calendar event object.
	 * @param WC_Global_Availability $availability WooCommerce Global Availability object.
	 *
	 * @return bool
	 */
	private function update_event_from_global_availability( Google_Service_Calendar_Event $event, WC_Global_Availability $availability ) {
		$event->setSummary( $availability->get_title() );
		$timezone        = wc_booking_get_timezone_string();
		$start           = new Google_Service_Calendar_EventDateTime();
		$end             = new Google_Service_Calendar_EventDateTime();
		$start_date_time = new WC_DateTime();
		$end_date_time   = new WC_DateTime();

		switch ( $availability->get_range_type() ) {
			case 'custom:daterange':
				$start_date_time = new WC_DateTime( $availability->get_from_date() . ' ' . $availability->get_from_range() );
				$start->setDateTime( $start_date_time->format( 'Y-m-d\TH:i:s' ) );
				$start->setTimeZone( $timezone );
				$event->setStart( $start );

				$end_date_time = new WC_DateTime( $availability->get_to_date() . ' ' . $availability->get_to_range() );
				$end->setDateTime( $end_date_time->format( 'Y-m-d\TH:i:s' ) );
				$end->setTimeZone( $timezone );
				$event->setEnd( $end );
				break;
			case 'custom':
				$start_date_time = new WC_DateTime( $availability->get_from_range() );
				$start->setDate( $start_date_time->format( 'Y-m-d' ) );
				$event->setStart( $start );

				$end_date_time = new WC_DateTime( $availability->get_to_range() );
				$end_date_time->add( new DateInterval( 'P1D' ) );
				$end->setDate( $end_date_time->format( 'Y-m-d' ) );
				$event->setEnd( $end );
				break;
			case 'months':
				$start_date_time->setDate(
					date( 'Y' ),
					$availability->get_from_range(),
					1
				);

				$start->setDate( $start_date_time->format( 'Y-m-d' ) );
				$event->setStart( $start );

				$number_of_months = 1 + intval( $availability->get_to_range() ) - intval( $availability->get_from_range() );

				$end_date_time = $start_date_time->add( new DateInterval( 'P' . $number_of_months . 'M' ) );

				$end->setDate( $end_date_time->format( 'Y-m-d' ) );
				$event->setEnd( $end );

				$event->setRecurrence(
					array(
						'RRULE:FREQ=YEARLY',
					)
				);

				break;
			case 'weeks':
				$start_date_time->setDate(
					date( 'Y' ),
					1,
					1
				);

				$end_date_time->setDate(
					date( 'Y' ),
					1,
					2
				);

				$all_days     = join( ',', array_keys( \RRule\RRule::$week_days ) );
				$week_numbers = join( ',', range( $availability->get_from_range(), $availability->get_to_range() ) );
				$rrule        = "RRULE:FREQ=YEARLY;BYWEEKNO=$week_numbers;BYDAY=$all_days";

				$start->setDate( $start_date_time->format( 'Y-m-d' ) );
				$event->setStart( $start );

				$end->setDate( $end_date_time->format( 'Y-m-d' ) );
				$event->setEnd( $end );

				$event->setRecurrence(
					array(
						$rrule,
					)
				);
				break;
			case 'days':
				$start_day = intval( $availability->get_from_range() );
				$end_day   = intval( $availability->get_to_range() );

				$start_date_time->modify( 'this ' . self::DAYS_OF_WEEK[ $start_day ] );
				$start->setDate( $start_date_time->format( 'Y-m-d' ) );
				$event->setStart( $start );

				$end_date_time = $start_date_time->modify( 'this ' . self::DAYS_OF_WEEK[ $end_day ] );

				$end->setDate( $end_date_time->format( 'Y-m-d' ) );
				$event->setEnd( $end );

				$event->setRecurrence(
					array(
						'RRULE:FREQ=WEEKLY',
					)
				);

				break;
			case 'time:1':
			case 'time:2':
			case 'time:3':
			case 'time:4':
			case 'time:5':
			case 'time:6':
			case 'time:7':
				list( , $day_of_week ) = explode( ':', $availability->get_range_type() );

				$start_date_time->modify( 'this ' . self::DAYS_OF_WEEK[ $day_of_week ] );
				$end_date_time->modify( 'this ' . self::DAYS_OF_WEEK[ $day_of_week ] );
				$rrule = 'RRULE:FREQ=WEEKLY';

			// fall through please.
			case 'time':
				if ( ! isset( $rrule ) ) {
					$rrule = 'RRULE:FREQ=DAILY';
				}

				list( $start_hour, $start_min ) = explode( ':', $availability->get_from_range() );
				$start_date_time->setTime( $start_hour, $start_min );

				list( $end_hour, $end_min ) = explode( ':', $availability->get_to_range() );
				$end_date_time->setTime( $end_hour, $end_min );

				$start->setDateTime( $start_date_time->format( 'Y-m-d\TH:i:s' ) );
				$start->setTimeZone( $timezone );
				$event->setStart( $start );

				$end->setDateTime( $end_date_time->format( 'Y-m-d\TH:i:s' ) );
				$end->setTimeZone( $timezone );
				$event->setEnd( $end );

				$event->setRecurrence(
					array(
						$rrule,
					)
				);
				break;

			default:
				// That should be everything, anything else is not supported.
				return false;
		}

		return true;
	}

	/**
	 * Renew access token with refresh token. Must pass through connect.woocommerce.com middleware.
	 *
	 * @param string $refresh_token Refresh Token.
	 * @param Google_Client $client Google Client Object.
	 *
	 * @return array
	 */
	private function renew_access_token( $refresh_token, $client ) {

		if ( $client->getClientId() ) {
			return $client->fetchAccessTokenWithRefreshToken( $refresh_token );
		}

		$response     = wp_remote_post(
			self::CONNECT_WOOCOMMERCE_URL . '/renew/google',
			array(
				'body' => array( 'refresh_token' => $refresh_token ),
			)
		);
		$access_token = json_decode( wp_remote_retrieve_body( $response ), true );

		return $access_token;
	}

	/**
	 * Get google login url from connect.woocommerce.com.
	 *
	 * @return string
	 */
	private function get_google_auth_url(): string {
		$client = $this->get_client();

		if ( $client->getClientId() ) {
			return $client->createAuthUrl();
		}

		return add_query_arg(
			array(
				'redirect' => WC()->api_request_url( $this->id ),

			),
			self::CONNECT_WOOCOMMERCE_URL . '/login/google'
		);
	}

	/**
	 * Get settings option key
	 *
	 * @return string
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	public function get_field_key(): string {
		return $this->id . '_settings';
	}
}
