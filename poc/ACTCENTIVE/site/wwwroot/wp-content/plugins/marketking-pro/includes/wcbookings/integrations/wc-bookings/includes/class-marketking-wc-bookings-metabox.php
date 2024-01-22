<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
if ( ! class_exists( 'Marketking_WC_Bookings_Metabox' ) ) {
	class Marketking_WC_Bookings_Metabox {

		/**
		 * Stores Bookings/Availability.
		 *
		 * @var array Mixed type of WC_Global_Availability and WC_Booking
		 */


		const ABSPATH = __DIR__ . '/';


		protected static $instance;



		/**
		 * Returns single instance of the class
		 *
		 * @return Marketking_WC_Bookings_Metabox
		 * @since 1.0.0
		 */
		public static function get_instance(): Marketking_WC_Bookings_Metabox {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}


		function __construct() {
//			if ( get_query_var( 'dashpage' ) === 'edit-booking-product' ) {
				add_action( 'woocommerce_after_order_itemmeta', array( $this, 'booking_display' ), 10, 3 );
				add_action( 'admin_init', array( $this, 'init_tabs' ) );
				add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data' ], 11 );
//			}
		}


		public function get_marketking_template( $template ){
			ob_start();

			include( apply_filters( 'marketking_dashboard_template', self::ABSPATH . "{$template}.php" ) );

			?>

			<?php if ( $template === 'edit-product' && ! class_exists( 'query-monitor' ) ): ?>
				<div id="marketking_footer_hidden">
					<?php
					wp_footer();
					?>
				</div>
			<?php endif; ?>
			<?php

			return ob_get_clean();
		}


		public static function output_metabox(): void {

			new WC_Bookings_Admin();
			( new Marketking_WC_Bookings_Metabox )->init_tabs();
		}


		public function init_tabs(): void {
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'register_tab' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'booking_panels' ) );
		}

		/**
		 * Show booking data if a line item is linked to a booking ID.
		 */
		public function booking_display( $item_id, $item, $product ): void {
			$booking_ids = WC_Booking_Data_Store::get_booking_ids_from_order_item_id( $item_id );

			/*wc_get_template(
				'order/admin/booking-display.php',
				array(
					'booking_ids'       => $booking_ids,
					'hide_item_details' => true,
				),
				'marketking',
				WC_BOOKINGS_TEMPLATE_PATH
			);*/

			//mytheme template_path
			wc_get_template(
				'order/admin/booking-display.php',
				array(
					'booking_ids'       => $booking_ids,
					'hide_item_details' => true,
				),
				'',
				MARKETKINGPRO_DIR . 'includes/wcbookings/integrations/wc-bookings/includes/templates/',
			);

		}

		/**
		 * Add tabs to WC 2.6+
		 *
		 * @param array $tabs
		 *
		 * @return array
		 */
		public function register_tab( $tabs ): array {
			$tabs['bookings_resources']    = array(
				'label'  => __( 'Resources', 'marketking' ),
				'target' => 'bookings_resources',
				'class'  => array(
					'show_if_booking',
				),
			);
			$tabs['bookings_availability'] = array(
				'label'  => __( 'Availability', 'marketking' ),
				'target' => 'bookings_availability',
				'class'  => array(
					'show_if_booking',
				),
			);
			$tabs['bookings_pricing']      = array(
				'label'  => __( 'Costs', 'marketking' ),
				'target' => 'bookings_pricing',
				'class'  => array(
					'show_if_booking',
				),
			);
			$tabs['bookings_persons']      = array(
				'label'  => __( 'Persons', 'marketking' ),
				'target' => 'bookings_persons',
				'class'  => array(
					'show_if_booking',
				),
			);
			$tabs['bookings_export']       = array(
				'label'  => __( 'Export', 'marketking' ),
				'target' => 'bookings_export',
				'class'  => array(
					'show_if_booking',
				),
			);

			return $tabs;
		}

		/**
		 * Show the booking panels views
		 */
		public function booking_panels(): void {
			global $post, $bookable_product;



			if ( empty( $bookable_product ) || $bookable_product->get_id() !== $post->ID ) {
				$bookable_product = get_wc_product_booking( $post->ID );

			}

			$restricted_meta = $bookable_product->get_restricted_days();

			for ( $i = 0; $i < 7; $i ++ ) {

				if ( $restricted_meta && in_array( $i, $restricted_meta ) ) {
					$restricted_days[ $i ] = $i;
				} else {
					$restricted_days[ $i ] = false;
				}
			}

			wp_enqueue_script( 'wc_bookings_admin_js' );

			include 'views/html-booking-resources.php';
			include 'views/html-booking-availability.php';
			include 'views/html-booking-pricing.php';
			include 'views/html-booking-persons.php';
			include 'views/html-booking-export.php';
		}

		public function product_data( $tabs ): array {
			?>

			<style>
			  #woocommerce-product-data ul.wc-tabs li.bookings_export_tab a{
				display: flex;
				align-items: center;
				position: relative;
			    }

			  #woocommerce-product-data ul.wc-tabs li.bookings_export_tab a::before {
				content: "\f316";
				font-size: 16px;
				color:#5664b4;
				}

			  #woocommerce-product-data .postbox-header .product-data-wrapper .show_if_booking:not(.show_if_simple),
			  #general_product_data.woocommerce_options_panel:not(.hidden) .options_group.show_if_booking {
				display : none;
				}

			</style>


			<?php

			$hide_args = array(
				'hide_if_simple',
				'hide_if_grouped',
				'hide_if_subscription',
				'hide_if_external',
				'hide_if_variable',
				'hide_if_listing_quote',
				'hide_if_job_package',
				'hide_if_promotion_package',
				'show_if_booking',
			);

			//$tabs['attribute']['class']             = ' hide_if_booking';
			$tabs['bookings_resources']['class']    = $hide_args;
			$tabs['bookings_availability']['class'] = $hide_args;
			$tabs['bookings_pricing']['class']      = $hide_args;
			$tabs['bookings_persons']['class']      = $hide_args;
			$tabs['bookings_export']['class']       = $hide_args;

			return $tabs;
		}

		private static function vendor_id(): int {
			$vendor_id = get_current_user_id();

			if ( marketking()->is_vendor_team_member() ) {
				$vendor_id = marketking()->get_team_member_parent();
			}

			return $vendor_id;
		}

		/**
		 * Get booking product resources.
		 *
		 * @return array
		 */
		public static function get_booking_resources() {

			$resources = array();

			$all_resources = get_posts( array(
				'post_type'      => 'bookable_resource',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'orderby'        => 'menu_order',
				'order'          => 'asc',
				'author'         => self::vendor_id(),
			) );

			foreach ( $all_resources as $resource ) {
				$id = $resource->ID;

				$resources[] = new WC_Product_Booking_Resource( $id );


			}

			return $resources;
		}

	}
}

/**
 * Unique access to instance
 *
 * @return Marketking_WC_Bookings_Metabox
 */
function Marketking_WC_Bookings_Metabox(): Marketking_WC_Bookings_Metabox { //phpcs:ignore
	return Marketking_WC_Bookings_Metabox::get_instance();
}

Marketking_WC_Bookings_Metabox();