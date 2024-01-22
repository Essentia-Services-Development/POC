<?php



if ( ! defined( 'ABSPATH' ) ) {
	exit; // Direct access not allowed.
}
if ( ! class_exists( 'Marketking_WC_Bookings_Resources' ) ) {
	class Marketking_WC_Bookings_Resources {

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
		 * @return Marketking_WC_Bookings_Resources
		 * @since 1.0.0
		 */
		public static function get_instance(): Marketking_WC_Bookings_Resources {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}


		function __construct() {

		}


		public static function meta_box_inner(): void {

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


			$resource = new WC_Product_Booking_Resource( $value );

			wp_enqueue_script( 'wc_bookings_admin_js' );

			wp_nonce_field( 'bookable_resource_details_meta_box', 'bookable_resource_details_meta_box_nonce' );
			?>
			<style type="text/css">
			  #minor-publishing-actions, #visibility {
				display : none;
				}
			</style>
			<div class="woocommerce_options_panel woocommerce">
				<div class="panel-wrap" id="bookings_availability">
					<div class="options_group">
						<?php
						woocommerce_wp_text_input( array(
							'id'                => '_wc_booking_qty',
							'label'             => __( 'Available Quantity', 'marketking' ),
							'description'       => __( 'The quantity of this resource available at any given time.', 'marketking' ),
							'value'             => max( $resource->get_qty( 'edit' ), 1 ),
							'desc_tip'          => true,
							'type'              => 'number',
							'custom_attributes' => array(
								'min'  => '',
								'step' => '1',
							),
							'style'             => 'width: 50px;',
						) );
						?>
					</div>
					<div class="options_group">
						<div class="table_grid">
							<table class="widefat">
								<thead>
								<tr>
									<th class="sort" width="1%">&nbsp;</th>
									<th><?php esc_html_e( 'Range type', 'marketking' ); ?></th>
									<th><?php esc_html_e( 'Range', 'marketking' ); ?></th>
									<th></th>
									<th></th>
									<th><?php esc_html_e( 'Bookable', 'marketking' ); ?>&nbsp;<a
												class="tips"
												data-tip="<?php echo wc_sanitize_tooltip( __( 'If not bookable, users won\'t be able to choose this block for their booking.', 'marketking' ) ); ?>">[?]</a>
									</th>
									<th><?php esc_html_e( 'Priority', 'marketking' ); ?>&nbsp;<a
												class="tips"
												data-tip="<?php echo wc_sanitize_tooltip( get_wc_booking_priority_explanation() ); ?>">[?]</a>
									</th>
									<th class="remove" width="1%">&nbsp;</th>
								</tr>
								</thead>
								<tfoot>
								<tr>
									<th colspan="6">
										<a href="#" class="button add_row" data-row="<?php
										ob_start();
										include( 'views/html-booking-availability-fields.php' );
										$html = ob_get_clean();
										echo esc_attr( $html );
										?>"><?php esc_html_e( 'Add Range', 'marketking' ); ?></a>
										<span class="description"><?php 
										$priority_rules = get_wc_booking_rules_explanation();
										foreach ( $priority_rules as $priority_rule ) {
											echo esc_html( $priority_rule ) . '<br>';
										}
										 ?></span>
									</th>
								</tr>
								</tfoot>
								<tbody id="availability_rows">
								<?php
								$values = $resource->get_availability( 'edit' );
								if ( ! empty( $values ) && is_array( $values ) ) {
									foreach ( $values as $availability ) {
										include( 'views/html-booking-availability-fields.php' );
									}
								}
								?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php
		}
	}
}

/**
 * Unique access to instance
 *
 * @return Marketking_WC_Bookings_Resources
 */
function Marketking_WC_Bookings_Resources(): Marketking_WC_Bookings_Resources { //phpcs:ignore
	return Marketking_WC_Bookings_Resources::get_instance();
}

Marketking_WC_Bookings_Resources();