<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$vendor_id = get_current_user_id();

if ( marketking()->is_vendor_team_member() ) {
	$vendor_id = marketking()->get_team_member_parent();
}
?>
<div class="nk-content marketking_add_booking_orders_page"
     style="margin-top: 70px;">
<div class="container-fluid">
<div class="nk-content-inner">
<div class="nk-content-body">
<div class="wrap woocommerce">
<h4><?php esc_html_e( 'Add Booking Order', 'marketking' ); ?></h4>

<p><?php esc_html_e( 'You can create a new booking for a customer here. This form will create a booking for the user, and optionally an associated order. Created orders will be marked as pending payment.', 'marketking' ); ?></p>

<?php $this->show_errors(); ?>
<div class="nk-block">
<div class="card">
<div class="card-content">
<div class="card-inner">
<form method="POST"
      data-nonce="<?php echo esc_attr( wp_create_nonce( 'find-booked-day-blocks' ) ); ?>">
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row">
				<label for="customer_id"><?php esc_html_e( 'Customer', 'marketking' ); ?></label>
			</th>
			<td>
				<!--<select name="customer_id" id="customer_id"
									        class="wc-customer-search"
									        data-placeholder="<?php /*esc_attr_e( 'Guest', 'marketking' ); */ ?>"
									        data-allow_clear="true">
									</select >-->

				<?php


				?>
				<select name="customer_id" id="customer_id" class="chosen_select"
				        data-placeholder="<?php _e( 'Guest', 'marketking' ); ?>"
				        data-allow_clear="true" style="width:100%;">
					<option value="0"><?php _e( 'Guest', 'marketking' ); ?></option>
					<?php


					$vendor_orders = get_posts( array(
						'post_type'   => 'shop_order',
						'post_status' => 'any',
						'numberposts' => - 1,
						'author'      => $vendor_id,
						'fields'      => 'ids',

					) );


					$customer_ids = array();
					foreach ( $vendor_orders as $order_id ) {

						$orderobj = wc_get_order( $order_id );

						foreach ( (array) $orderobj->get_customer_id() as $obj ) {

							$customer_ids[] = $obj;


						}

					}

					foreach ( array_unique( $customer_ids ) as $customer_id ) {

						$data  = get_userdata( $customer_id );
						$email = ! empty( $data->user_email ) ? $data->user_email : '';

						// if guest user, show name by order

						if ( $data !== false ) {
							$name = esc_html( $data->first_name . ' ' . $data->last_name );


							?>
							<option value="<?php echo $customer_id; ?>"><?php
								echo $name . ' (#' . $customer_id . ' ' . $data->display_name . ' - '
								     . $email . ')'; ?></option>
							<?php
						}
						?>

						<?php

					}

					?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="bookable_product_id"><?php esc_html_e( 'Bookable Product', 'marketking' ); ?></label>
			</th>
			<td>
				<select id="bookable_product_id" name="bookable_product_id"
				        class="chosen_select" style="width: 300px">
					<option value=""><?php esc_html_e( 'Select a bookable product...', 'marketking' ); ?></option>
					<?php foreach ( WC_Bookings_Admin::get_booking_products() as $product ) :


						if ( ! in_array( $product->get_id(), marketking()->get_vendor_products(

							$vendor_id ) ) ) {

							continue;

						}

						?>
						<option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( sprintf( '%s (#%s)', $product->get_name(), $product->get_id() ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="create_order"><?php esc_html_e( 'Create Order', 'marketking' ); ?></label>
			</th>
			<td>
				<p>
					<label>
						<input type="radio" name="booking_order" value="new"
						       class="checkbox"/>
						<?php esc_html_e( 'Create a new corresponding order for this new booking. Please note - the booking will not be active until the order is processed/completed.', 'marketking' ); ?>
					</label>
				</p>
				<p>
					<label>
						<input type="radio" name="booking_order"
						       value="existing" class="checkbox"/>
						<?php esc_html_e( 'Assign this booking to an existing order with this ID:', 'marketking' ); ?>
						<?php if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) : ?>
							<input type="text" name="booking_order_id" value=""
							       class="text" size="15"/>
						<?php else : ?>
							<input type="number" name="booking_order_id"
							       value="" class="text" size="10"/>
						<?php endif; ?>
					</label>
				</p>
				<!--<p>
					<label>
						<input type="radio" name="booking_order" value="" class="checkbox"
						       checked="checked"/>
						<?php /*esc_html_e( 'Don\'t create an order for this booking.', 'marketking' ); */ ?>
					</label>
				</p>-->
			</td>
		</tr>
		<?php do_action( 'woocommerce_bookings_after_create_booking_page' ); ?>
		<tr valign="top">
			<th scope="row">&nbsp;</th>
			<td>
				<input type="submit" name="create_booking"
				       class="button-primary"
				       value="<?php esc_attr_e( 'Next', 'marketking' ); ?>"/>
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
