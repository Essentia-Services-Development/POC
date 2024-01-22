<?php



if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
if ( ! class_exists( 'Marketking_WC_Bookings_Order_Metabox' ) ) {
	class Marketking_WC_Bookings_Order_Metabox {

		/**
		 * Stores Bookings/Availability.
		 *
		 * @var array Mixed type of WC_Global_Availability and WC_Booking
		 */


		const ABSPATH = __DIR__ . '/';


		protected static $instance;

		/**
		 * Stores errors.
		 *
		 * @var array
		 */
		private $errors = array();
		private $post_id;

		/**
		 * Returns single instance of the class
		 *
		 * @return Marketking_WC_Bookings_Order_Metabox
		 * @since 1.0.0
		 */
		public static function get_instance(): Marketking_WC_Bookings_Order_Metabox {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}


		function __construct() {

			$this->post_id = sanitize_text_field( Marketking_WC_Bookings::get_pagenr_query_var() );

			add_action( 'woocommerce_process_product_meta', [
				$this,
				'save_product_custom_fields'
			] );
		}


		function save_product_custom_fields( $post_id ) {

			$readonly = isset( $_POST['_enable_readonly'] ) ? esc_attr( $_POST['_enable_readonly'] ) : '';
			update_post_meta( $post_id, '_enable_readonly', $readonly );

		}

		private function sanity_check_notices( $booking, $product ) {
			if ( $booking->get_start() && $booking->get_start() > strtotime( '+ 2 year', current_time( 'timestamp' ) ) ) {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'This booking is scheduled over 2 years into the future. Please ensure this is correct.', 'marketking' ) . '</p></div>';
			}

			if ( $product && is_callable( array( $product, 'get_max_date' ) ) ) {
				$max      = $product->get_max_date();
				$max_date = strtotime( "+{$max['value']} {$max['unit']}", current_time( 'timestamp' ) );
				if ( $booking->get_start() > $max_date || $booking->get_end() > $max_date ) {
					/* translators: 1: maximum bookable date */
					echo '<div class="notice notice-warning"><p>' . sprintf( esc_html__( 'This booking is scheduled over the product\'s allowed max booking date (%s). Please ensure this is correct.', 'marketking' ), esc_html( date_i18n( wc_bookings_date_format(), $max_date ) ) ) . '</p></div>';
				}
			}

			if ( ! $product || ( $booking->get_product_id() && ! wc_get_product( $booking->get_product_id() ) ) ) {
				echo '<div class="error"><p>' . esc_html__( 'It appears the booking product associated with this booking has been removed.', 'marketking' ) . '</p></div>';

				return;
			}

			if ( $product && is_callable( array(
					$product,
					'is_skeleton'
				) ) && $product->is_skeleton() ) {
				/* translators: 1: product type */
				echo '<div class="error"><p>' . esc_html( sprintf( __( 'This booking is missing a required add-on (product type: %s). Some information is shown below but might be incomplete. Please install the missing add-on through the plugins screen.', 'marketking' ), $product->get_type() ) ) . '</p></div>';
			}


		}

		/**
		 * @param $post
		 *
		 * @return void
		 */
		public static function edit_order_meta_box_inner( $post ): void {
			global $booking;

			include_once WC()->plugin_path() . '/includes/admin/wc-meta-box-functions.php';
			$value = get_query_var( 'pagenr', 1 );


			if ( $value === 'add' ) {
				global $marketking_product_add_id;

				if ( empty( $marketking_product_add_id ) || $marketking_product_add_id === 'add' ) {
					// if empty, create new product and assign it
					$productid = marketking()->get_product_standby();

					$marketking_product_add_id = $productid;
				}


				$value = $marketking_product_add_id;
			}


			wp_nonce_field( 'mtk_wcb_details_meta_box', 'mtk_wcb_details_meta_box_nonce' );
//			wp_nonce_field( 'wc_bookings_details_meta_box', 'wc_bookings_details_meta_box_nonce' );


			if ( ! is_a( $booking, 'WC_Booking' ) || $booking->get_id() !== $post->ID ) {

				$booking = new WC_Booking( $post->ID );

			}

			$vendor_id = get_current_user_id();


			$order = $booking->get_order();

			$product_id  = $booking->get_product_id( 'edit' );
			$resource_id = $booking->get_resource_id( 'edit' );
			$customer_id = $booking->get_customer_id( 'edit' );
			$product     = $booking->get_product( $product_id );
			$customer    = $booking->get_customer();

			$statuses          = array_unique( array_merge( get_wc_booking_statuses( null, true ), get_wc_booking_statuses( 'user', true ), get_wc_booking_statuses( 'cancel', true ) ) );
			$bookable_products = array( '' => __( 'N/A', 'marketking' ) );


			foreach ( WC_Bookings_Admin::get_booking_products() as $bookable_product ) {


				if ( in_array( $bookable_product->get_id(), marketking()->get_vendor_products(

					$vendor_id ) ) ) {

					$bookable_products[ $bookable_product->get_id() ] = $bookable_product->get_name();


					$resources = $bookable_product->get_resources();

					foreach ( $resources as $resource ) {

						$bookable_products[ $bookable_product->get_id() . '=>' . $resource->get_id() ] = '&nbsp;&nbsp;&nbsp;' . $resource->get_name();
					}
				}
			}

			( new Marketking_WC_Bookings_Order_Metabox )->sanity_check_notices( $booking, $product );

			include( "views/html-booking-order-detail.php" );


		}

		/**
		 * @param $post
		 *
		 * @return void
		 */
		public static function customer_meta_box_inner( $post ): void {
			global $booking;

			if ( ! is_a( $booking, 'WC_Booking' ) || $booking->get_id() !== $post->ID ) {
				$booking = new WC_Booking( $post->ID );
			}

			$has_data = false;
			?>
			<table class="booking-customer-details">
				<?php
				$booking_customer_id = $booking->get_customer_id();
				$user                = $booking_customer_id ? get_user_by( 'id', $booking_customer_id ) : false;

				if ( $booking_customer_id && $user ) {
					?>
					<tr>
						<th><?php esc_html_e( 'Name:', 'marketking' ); ?></th>
						<td><?php echo esc_html( $user->last_name && $user->first_name ? $user->first_name . ' ' . $user->last_name : '&mdash;' ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Email:', 'marketking' ); ?></th>
						<td><?php echo wp_kses_post( make_clickable( sanitize_email( $user->user_email ) ) ); ?></td>
					</tr>
					<!--<tr class="view">
						<th>&nbsp;</th>
						<td><a class="button button-small" target="_blank"
						       href="<?php /*echo esc_url( admin_url( 'user-edit.php?user_id=' . absint( $user->ID ) ) ); */ ?>"><?php /*echo esc_html( 'View User', 'marketking' ); */ ?></a>
						</td>
					</tr>-->
					<?php
					$has_data = true;
				}

				$booking_order_id = $booking->get_order_id();
				$order            = $booking_order_id ? wc_get_order( $booking_order_id ) : false;

				if ( $booking_order_id && $order ) {
					?>
					<tr>
						<th><?php esc_html_e( 'Address:', 'marketking' ); ?></th>
						<td><?php echo wp_kses( $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __( 'No billing address set.', 'marketking' ), array( 'br' => array() ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Billing Email:', 'marketking' ); ?></th>
						<td><?php echo wp_kses_post( make_clickable( sanitize_email( is_callable( array(
								$order,
								'get_billing_email'
							) ) ? $order->get_billing_email() : $order->billing_email ) ) ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Phone:', 'marketking' ); ?></th>
						<td><?php echo esc_html( is_callable( array(
								$order,
								'get_billing_phone'
							) ) ? $order->get_billing_phone() : $order->billing_phone ); ?></td>
					</tr>
					<tr class="view">
						<th>&nbsp;</th>
						<td><a class="button button-small" target="_blank"
						       href="<?php echo esc_url(
							       trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'manage-order/' . $booking_order_id
						       );
						       ?>"><?php echo esc_html( 'View Order', 'marketking' ); ?></a>
						</td>
					</tr>
					<?php
					$has_data = true;
				}

				if ( ! $has_data ) {
					?>
					<tr>
						<td colspan="2"><?php esc_html_e( 'N/A', 'marketking' ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
			<?php
		}


		public function scripts() {

			wc_enqueue_js( "
			$( '#_booking_all_day' ).change( function () {
				if ( $( this ).is( ':checked' ) ) {
					$( '#booking_start_time, #booking_end_time' ).closest( 'p' ).hide();
				} else {
					$( '#booking_start_time, #booking_end_time' ).closest( 'p' ).show();
				}
			}).change();

			$( '.date-picker-field' ).datepicker({
				dateFormat: 'yy-mm-dd',
				firstDay: " . get_option( 'start_of_week' ) . ",
				numberOfMonths: 1,
				showButtonPanel: true,
			});
		" );

// Select2 handling
			wc_enqueue_js( "
			$( '#_booking_order_id' ).filter( ':not(.enhanced)' ).each( function() {
				var select2_args = {
					allowClear:  true,
					placeholder: $( this ).data( 'placeholder' ),
					minimumInputLength: 1,
					escapeMarkup: function( m ) {
						return m;
					},
					ajax: {
						url:         '" . admin_url( 'admin-ajax.php' ) . "',
						dataType:    'json',
						quietMillis: 250,
						data: function( params ) {
							return {
								term:     params.term,
								action:   'wc_bookings_json_search_order',
								security: '" . wp_create_nonce( 'search-booking-order' ) . "'
							};
						},
						processResults: function( data ) {
							var terms = [];
							if ( data ) {
								$.each( data, function( id, text ) {
									terms.push({
										id: id,
										text: text
									});
								});
							}
							return {
								results: terms
							};
						},
						cache: true
					},
					multiple: false
				};
				$( this ).select2( select2_args ).addClass( 'enhanced' );
			});
		" );
		}
	}
}

/**
 * Unique access to instance
 *
 * @return Marketking_WC_Bookings_Order_Metabox
 */
function Marketking_WC_Bookings_Order_Metabox(): Marketking_WC_Bookings_Order_Metabox { //phpcs:ignore
	return Marketking_WC_Bookings_Order_Metabox::get_instance();
}

Marketking_WC_Bookings_Order_Metabox();