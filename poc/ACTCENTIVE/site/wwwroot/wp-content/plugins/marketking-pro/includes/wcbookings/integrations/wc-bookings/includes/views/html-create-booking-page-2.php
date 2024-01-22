<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="nk-content marketking_add_booking_orders_page"
     style="margin-top: 70px;">
	<div class="container-fluid">
		<div class="nk-content-inner">
			<div class="nk-content-body">
				<div class="wrap woocommerce">
					<h4><?php esc_html_e( 'Add Booking Order', 'marketking' ); ?></h4>

					<?php $this->show_errors(); ?>
					<div class="nk-block">
						<div class="card">
							<div class="card-content">
								<div class="card-inner">
									<form method="POST"
									      data-nonce="<?php echo esc_attr( wp_create_nonce( 'find-booked-day-blocks' ) ); ?>"
									      id="wc-bookings-booking-form">
										<table class="form-table">
											<tbody>
											<tr>
												<th scope="row">
													<label><?php esc_html_e( 'Booking Data', 'marketking' ); ?></label>
												</th>
												<td>
													<div class="wc-bookings-booking-form">
														<?php $booking_form->output(); ?>
														<div class="wc-bookings-booking-cost"
														     style="display:none"></div>
													</div>
												</td>
											</tr>
											<tr valign="top">
												<th scope="row">&nbsp;</th>
												<td>
													<input type="submit" name="create_booking_2"
													       class="button-primary add_custom_booking"
													       value="<?php esc_attr_e( 'Add Booking', 'marketking' ); ?>"/>
													<input type="hidden" name="customer_id"
													       value="<?php echo esc_attr( $customer_id ); ?>"/>
													<input type="hidden" name="bookable_product_id"
													       value="<?php echo esc_attr( $bookable_product_id ); ?>"/>
													<input type="hidden" name="add-to-cart"
													       value="<?php echo esc_attr( $bookable_product_id ); ?>" class="wc-booking-product-id"/>
													<input type="hidden" name="booking_order"
													       value="<?php echo esc_attr( $booking_order ); ?>"/>
													<?php wp_nonce_field( 'create_booking_notification' ); ?>
												</td>
											</tr>
											</tbody>
										</table>
									</form>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

