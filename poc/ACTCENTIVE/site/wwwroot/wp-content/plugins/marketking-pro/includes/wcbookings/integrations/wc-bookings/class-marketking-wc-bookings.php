<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
if ( ! class_exists( 'Marketking_WC_Bookings' ) ) {

	class Marketking_WC_Bookings {


		const ABSPATH = __DIR__ . '/';


		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return Marketking_WC_Bookings
		 * @since 1.0.0
		 */
		public static function get_instance(): Marketking_WC_Bookings {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		function __construct() {

			add_filter( 'views_edit-product', [
				$this,
				'remove_hidden_products_admin_count'
			], 10, 1 );
		}




		private function get_marketking_template( $template ){
			ob_start();

			include( apply_filters( 'marketking_dashboard_template', self::ABSPATH . "{$template}.php" ) );
			?>

			<?php if ( $template === 'edit-booking-order' && ! class_exists( 'query-monitor' ) ||
			           $template === 'add-booking-order' && ! class_exists( 'query-monitor' ) ||
			           $template === 'edit-product' && ! class_exists( 'query-monitor' ) ||
			           $template === 'edit-booking-product' && ! class_exists( 'query-monitor' ) ||
			           $template === 'edit-resource' && ! class_exists( 'query-monitor' ) ):
				?>
				<div id="marketking_footer_hidden">
					<?php
					wp_footer();
					?>
				</div>
			<?php endif; ?>
			<?php

			return ob_get_clean();
		}

		public static function get_pagenr_query_var() {
			$value    = get_query_var( 'pagenr', 1 );
			$dashpage = get_query_var( 'dashpage' );

			if ( $dashpage === 'edit-product' || $dashpage === 'edit-booking-product' || $dashpage
			                                                                            === 'edit-resource' ) {
				if ( $value === 'add' ) {
					global $marketking_product_add_id;

					if ( empty( $marketking_product_add_id ) || $marketking_product_add_id === 'add' ) {
						// if empty, create new product and assign it
						$productid = marketking()->get_product_standby();

						$marketking_product_add_id = $productid;
					}


					$value = $marketking_product_add_id;
				}
			}

			return $value;
		}


		function create_page( $page ) {
			if ( class_exists( 'WC_Bookings' ) ) {

				if ( $page === 'bookings' ) {
					echo $this->get_marketking_template( 'booking-orders/booking-orders' );

				} elseif ( $page === 'edit-booking-product' ) {
					echo $this->get_marketking_template( 'booking-products/edit-booking-product' );

				} elseif ( $page === 'booking-calendar' ) {
					echo $this->get_marketking_template( 'booking-calendar/calendar' );

				} elseif ( $page === 'calendar-google-integration' ) {
					echo $this->get_marketking_template( 'booking-calendar/calendar-google-integration' );

				} elseif ( $page === 'booking-orders' ) {
					echo $this->get_marketking_template( 'booking-orders/booking-orders' );

				} elseif ( $page === 'edit-booking-order' ) {
					echo $this->get_marketking_template( 'booking-orders/edit-booking-order' );

				} elseif ( $page === 'add-booking-order' ) {
					echo $this->get_marketking_template( 'booking-orders/add-booking-order' );

				} elseif ( $page === 'bookable-resources' ) {
					echo $this->get_marketking_template( 'booking-resources/bookable-resources' );

				} elseif ( $page === 'edit-resource' ) {
					echo $this->get_marketking_template( 'booking-resources/edit-resource' );

				}

			}
		}


		public function remove_hidden_products_admin_count( $views ) {
			global $wp;
			$current_page = get_query_var( 'dashpage' );

			if ( 'edit-booking-product' === $current_page ) {
				global $user_ID, $wpdb;

				$total = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE (post_status = 'publish' OR post_status = 'draft' OR post_status = 'pending' OR post_status = 'private') AND (post_type = 'product') " );

				$views['all'] = preg_replace( '/\(.+\)/U', '(' . $total . ')', $views['all'] );
			}

			return $views;
		}

		public function styles() {

			$current_page = get_query_var( 'dashpage' );

			wp_enqueue_style( 'jquery-ui-style', WC_BOOKINGS_PLUGIN_URL . '/dist/css/jquery-ui-styles.css', array(), '1.11.4-wc-bookings.' . WC_BOOKINGS_VERSION );
			wp_enqueue_style( 'wc_bookings_admin_styles', WC_BOOKINGS_PLUGIN_URL . '/dist/css/admin.css', null, WC_BOOKINGS_VERSION );

			wp_enqueue_style( 'wc_bookings_admin_calendar_css' );

			if ( 'booking-calendar' === $current_page ) {
				wp_enqueue_style( 'wc_bookings_admin_calendar_css', WC_BOOKINGS_PLUGIN_URL . '/dist/css/admin-calendar-gutenberg.css', null, WC_BOOKINGS_VERSION );
			}

			/*if ( 'wc_booking_page_wc_bookings_settings' === $screen->id ) {
				wp_enqueue_style( 'wc_bookings_admin_store_availability_css', WC_BOOKINGS_PLUGIN_URL . '/dist/css/admin-store-availability.css', null, WC_BOOKINGS_VERSION );
			}*/

			if ( 'edit-booking-order' === $current_page || 'add-booking-order' === $current_page ) {
				wp_enqueue_style( 'jquery-ui-style', WC_BOOKINGS_PLUGIN_URL . '/dist/css/jquery-ui-styles.css', array(), '1.11.4-wc-bookings.' . WC_BOOKINGS_VERSION );
				wp_enqueue_style( 'wc-bookings-styles', WC_BOOKINGS_PLUGIN_URL . '/dist/css/frontend.css', null, WC_BOOKINGS_VERSION );
			}
		}

		public function scripts() {


			$version = '1';
			$suffix  = 'min';

			wp_enqueue_script( 'moment', includes_url( 'js/dist/vendor/moment.min.js' ), array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch'
			), false, 1 );

			$current_page                 = get_query_var( 'dashpage' );
			$marketking_wc_bookings_pages = [
				'bookings',
				'edit-booking-product',
				'edit-product',
				'edit-resource',
				'edit-booking-order',
				'add-booking-order',
				'booking-calendar',
				'bookable-resources'
			];
			// Don't enqueue styles and JS on non-WC BOOKING.
			if ( ! in_array( $current_page, $marketking_wc_bookings_pages, true ) ) {
				return;
			}

			if('edit-booking-product' === $current_page && class_exists('WC_Product_Accommodation_Booking')){
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				wp_enqueue_script( 'wc_accommodation_bookings_writepanel_js', WC_ACCOMMODATION_BOOKINGS_PLUGIN_URL . '/assets/js/writepanel' . $suffix . '.js', array( 'jquery' ), WC_ACCOMMODATION_BOOKINGS_VERSION, true );
			}


			if ( 'add-booking-order' === $current_page || 'edit-resource' === $current_page || 'edit-product' === $current_page || 'edit-booking-product' === $current_page || 'edit-booking-order' === $current_page || 'bookable-resources' === $current_page ) {


				if ( version_compare( get_bloginfo( 'version' ), '5.0.0', '<' ) ) {
					wp_register_script( 'wc-bookings-moment', WC_BOOKINGS_PLUGIN_URL . '/dist/js/lib/moment-with-locales.js', array(), WC_BOOKINGS_VERSION, true );
					wp_register_script( 'wc-bookings-moment-timezone', WC_BOOKINGS_PLUGIN_URL . '/dist/js/lib/moment-timezone-with-data.js', array(), WC_BOOKINGS_VERSION, true );
					wp_register_script( 'wc-bookings-date', false, array(
						'wc-bookings-moment',
						'wc-bookings-moment-timezone'
					) );
				} else {
					wp_register_script( 'wc-bookings-date', false, array( 'wp-date' ) );
				}


				wp_enqueue_script(
					'marketking_wc_bookings_script_integration',
					MARKETKINGPRO_URL . 'includes/wcbookings/assets/js/wc-bookings.js',
					array(),
					false
				);

				wp_localize_script(
					'marketking_wc_bookings_script_integration',
					'marketking_wc_bookings_display_settings',
					array(
						'security' => wp_create_nonce( 'marketking_security_nonce' ),
						'ajaxurl'  => admin_url( 'admin-ajax.php' ),

						'booking_product_edit_link'       => esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-booking-product/' ),
						'booking_product_must_name'       => esc_html__( 'The booking product must have a name (title)!', 'marketking' ),
						'sure_delete_booking_product'     => esc_html__( 'Are you sure you want to delete this booking product?', 'marketking' ),
						'booking_products_dashboard_page' => esc_attr( trailingslashit(get_page_link( apply_filters(
								'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post', true ) ) )) . 'bookings',

						'resource_edit_link'       => esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-resource/' ),
						'resource_must_name'       => esc_html__( 'The resource must have a name (title)!', 'marketking' ),
						'sure_delete_resource'     => esc_html__( 'Are you sure you want to delete this resource?', 'marketking' ),
						'resources_dashboard_page' => esc_attr( trailingslashit(get_page_link( apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post', true ) )) ) . 'bookable-resources',

						'booking_order_edit_link' => esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-booking-order/' ),
					)
				);

			}


			if ( 'add-booking-order' === $current_page || 'edit-booking-order' === $current_page
			     || 'bookings' === $current_page || 'edit-booking-product' === $current_page || 'edit-resource'
			                                                                                    ===
			                                                                                    $current_page ) {

//				QM::debug($current_page);
				wp_register_script( 'wc-admin-product-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-product' . $suffix . '.js', array(
					'wc-admin-meta-boxes',
					'media-models'
				) );
				wp_register_script( 'wc-admin-variation-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-product-variation' . $suffix . '.js', array(
					'wc-admin-meta-boxes',
					'serializejson',
					'media-models'
				) );

				wp_enqueue_script( 'wc-admin-product-meta-boxes' );
				wp_enqueue_script( 'wc-admin-variation-meta-boxes' );
			}


			wp_enqueue_script( 'jquery-ui-datepicker' );


			wp_register_script( 'wc_bookings_admin_js', WC_BOOKINGS_PLUGIN_URL . '/dist/admin.js', array(
				'jquery',
				'jquery-ui-datepicker',
				'jquery-ui-sortable',
				'wp-element'
			), WC_BOOKINGS_VERSION, true );

			wp_enqueue_script( 'wc_bookings_admin_js' );


//			wp_enqueue_script( 'wc_bookings_admin_calendar_js' );

//		if ( 'wc_booking_page_create_booking' === $screen->id ) {
			wp_enqueue_script( 'wc-bookings-date' );
			wp_register_script( 'wc_bookings_admin_time_picker_js', WC_BOOKINGS_PLUGIN_URL . '/dist/admin-time-picker.js', null, WC_BOOKINGS_VERSION, true );
//		}

			if ( 'booking-calendar' === $current_page ) {
				if ( WC_BOOKINGS_GUTENBERG_EXISTS ) {
					wp_register_script( 'wc_bookings_admin_calendar_gutenberg_js', WC_BOOKINGS_PLUGIN_URL . '/dist/admin-calendar-gutenberg.js', array(
						'wc_bookings_admin_js',
						'wp-components',
						'wp-element'
					), WC_BOOKINGS_VERSION, true );

				}

				wp_register_script( 'wc_bookings_admin_calendar_js', WC_BOOKINGS_PLUGIN_URL . '/dist/admin-calendar.js', array(), WC_BOOKINGS_VERSION, true );
				wp_enqueue_script( 'wc_bookings_admin_calendar_gutenberg_js' );
				wp_enqueue_script( 'wc_bookings_admin_calendar_js' );
			}

//			if ( 'wc_booking_page_wc_bookings_settings' === $screen->id ) {
//			   if ( WC_BOOKINGS_GUTENBERG_EXISTS ) {
			wp_register_script( 'wc_bookings_admin_store_availability_js', WC_BOOKINGS_PLUGIN_URL . '/dist/admin-store-availability.js', array(
				'wc_bookings_admin_js',
				'wp-components',
				'wp-element'
			), WC_BOOKINGS_VERSION, true );
//			   }
//		   }

			if ( 'add-booking-order' === $current_page || 'edit-booking-order' === $current_page ) {
				wp_enqueue_script( 'wc_bookings_admin_edit_booking_js', WC_BOOKINGS_PLUGIN_URL . '/dist/admin-edit-booking.js', array( 'jquery' ), WC_BOOKINGS_VERSION, true );
			}

			if ( 'add-booking-order' === $current_page || 'edit-booking-order' === $current_page || 'edit-product' === $current_page || 'edit-booking-product' === $current_page || 'edit-resource' === $current_page ) {
				wp_enqueue_script( 'wc_bookings_admin_edit_bookable_product_js', WC_BOOKINGS_PLUGIN_URL . '/dist/admin-edit-bookable-product.js', array( 'jquery' ), WC_BOOKINGS_VERSION, true );
			}

			$params = array(
				'i18n_remove_person'            => esc_js( __( 'Are you sure you want to remove this person type?', 'marketking' ) ),
				'nonce_unlink_person'           => wp_create_nonce( 'unlink-bookable-person' ),
				'nonce_add_person'              => wp_create_nonce( 'add-bookable-person' ),
				'i18n_remove_resource'          => esc_js( __( 'Are you sure you want to remove this resource?', 'marketking' ) ),
				'nonce_delete_resource'         => wp_create_nonce( 'delete-bookable-resource' ),
				'nonce_add_resource'            => wp_create_nonce( 'add-bookable-resource' ),
				'i18n_minutes'                  => esc_js( __( 'minutes', 'marketking' ) ),
				'i18n_hours'                    => esc_js( __( 'hours', 'marketking' ) ),
				'i18n_days'                     => esc_js( __( 'days', 'marketking' ) ),
				'i18n_new_resource_name'        => esc_js( __( 'Enter a name for the new resource', 'marketking' ) ),
				'post'                          => isset( $post->ID ) ? $post->ID : '',
				'plugin_url'                    => WC()->plugin_url(),
				'ajax_url'                      => admin_url( 'admin-ajax.php' ),
				'calendar_image'                => WC_BOOKINGS_PLUGIN_URL . '/dist/images/calendar.png',
				'i18n_view_details'             => esc_js( __( 'View details', 'marketking' ) ),
				'i18n_customer'                 => esc_js( __( 'Customer', 'marketking' ) ),
				'i18n_resource'                 => esc_js( __( 'Resource', 'marketking' ) ),
				'i18n_persons'                  => esc_js( __( 'Persons', 'marketking' ) ),
				'i18n_max_booking_overwridden'  => esc_js( __( 'This setting is being overridden at the resource level.', 'marketking' ) ),
				'i18n_limited_hours'            => esc_js( __( 'A duration greater than 24 hours is not allowed when Availability is "not-available by default".', 'marketking' ) ),
				'i18n_limited_hours_in_gen_tab' => esc_js( __( 'The booking duration has been set to 24 as a duration greater than 24 hours is not allowed when Availability is "not-available by default".', 'marketking' ) ),
				'bookings_version'              => WC_BOOKINGS_VERSION,
				'bookings_db_version'           => WC_BOOKINGS_DB_VERSION,
				'start_of_week'                 => get_option( 'start_of_week' ),
			);

			wp_localize_script( 'wc_bookings_admin_js', 'wc_bookings_admin_js_params', $params );

			$params = array(
				'nonce_add_store_availability_rule'     => wp_create_nonce( 'add-store-availability-rule' ),
				'nonce_get_store_availability_rules'    => wp_create_nonce( 'get-store-availability-rules' ),
				'nonce_update_store_availability_rule'  => wp_create_nonce( 'update-store-availability-rule' ),
				'nonce_delete_store_availability_rules' => wp_create_nonce( 'delete-store-availability-rules' ),
				'ajax_url'                              => WC()->ajax_url(),
			);

			wp_localize_script( 'wc_bookings_admin_store_availability_js', 'wc_bookings_admin_store_availability_js_params', $params );

			$params = array(
				'invalid_start_end_date' => __( '"Start and end date" should be of the format yyyy-mm-dd and cannot be empty.', 'marketking' ),
				'date_range_invalid'     => __( 'Start date cannot be greater than end date.', 'marketking' ),
			);

			wp_localize_script( 'wc_bookings_admin_edit_booking_js', 'wc_bookings_admin_edit_booking_params', $params );

			$params = array(
				'wc_bookings_invalid_min_duration' => esc_html__( 'Minimum duration needs to be less than or equal to maximum duration.', 'marketking' ),
				'wc_bookings_invalid_max_duration' => esc_html__( 'Maximum duration needs to be greater than or equal to the minimum duration.', 'marketking' ),
			);

			wp_localize_script( 'wc_bookings_admin_edit_bookable_product_js', 'wc_bookings_admin_edit_booking_params', $params );
		}

	}
}


/**
 * Unique access to instance
 *
 * @return Marketking_WC_Bookings
 */
function Marketking_WC_Bookings(): Marketking_WC_Bookings { //phpcs:ignore
	return Marketking_WC_Bookings::get_instance();
}

Marketking_WC_Bookings();