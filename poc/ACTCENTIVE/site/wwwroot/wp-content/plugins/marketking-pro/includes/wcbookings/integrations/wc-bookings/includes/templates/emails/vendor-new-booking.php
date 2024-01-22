<?php
/**
 * Vendor new booking email
 *
 * @since   1.0.0
 * @version 1.1.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( wc_booking_order_requires_confirmation( $booking->get_order() ) && $booking->get_status() == 'pending-confirmation' ) {
	$opening_paragraph = __( 'A booking has been made by %s and is awaiting your approval. The details of this booking are as follows:', 'marketking-multivendor-marketplace-for-woocommerce' );
} else {
	$opening_paragraph = __( 'A new booking has been made by %s. The details of this booking are as follows:', 'marketking-multivendor-marketplace-for-woocommerce' );
}
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<?php if ( $booking->get_order() && $booking->get_order()->get_billing_first_name() && $booking->get_order()->get_billing_last_name() ) : ?>
	<p><?php printf( $opening_paragraph, $booking->get_order()->get_billing_first_name() . ' ' . $booking->get_order()->get_billing_last_name() ); ?></p>
<?php endif; ?>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1"
       bordercolor="#eee">
	<tbody>
	<tr>
		<th scope="row"
		    style="text-align:left; border: 1px solid #eee;"><?php _e( 'Booked Product', 'marketking-multivendor-marketplace-for-woocommerce' ); ?></th>
		<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_product()->get_title(); ?></td>
	</tr>
	<tr>
		<th style="text-align:left; border: 1px solid #eee;"
		    scope="row"><?php _e( 'Booking ID', 'marketking-multivendor-marketplace-for-woocommerce' ); ?></th>
		<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_id(); ?></td>
	</tr>
	<?php if ( $booking->has_resources() && ( $resource = $booking->get_resource() ) ) : ?>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;"
			    scope="row"><?php _e( 'Booking Type', 'marketking-multivendor-marketplace-for-woocommerce' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $resource->post_title; ?></td>
		</tr>
	<?php endif; ?>
	<tr>
		<th style="text-align:left; border: 1px solid #eee;"
		    scope="row"><?php _e( 'Booking Start Date', 'marketking-multivendor-marketplace-for-woocommerce' ); ?></th>
		<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_start_date(); ?></td>
	</tr>
	<tr>
		<th style="text-align:left; border: 1px solid #eee;"
		    scope="row"><?php _e( 'Booking End Date', 'marketking-multivendor-marketplace-for-woocommerce' ); ?></th>
		<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_end_date(); ?></td>
	</tr>
	<?php if ( $booking->has_persons() ) : ?>
		<?php
		foreach ( $booking->get_persons() as $id => $qty ) :
			if ( 0 === $qty ) {
				continue;
			}

			$person_type = ( 0 < $id ) ? get_the_title( $id ) : __( 'Person(s)', 'marketking-multivendor-marketplace-for-woocommerce' );
			?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;"
				    scope="row"><?php echo $person_type; ?></th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo $qty; ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>

<?php if ( wc_booking_order_requires_confirmation( $booking->get_order() ) && $booking->get_status() == 'pending-confirmation' ) : ?>
	<p><?php _e( 'This booking is awaiting your approval. Please check it and inform the customer if the date is available or not.', 'marketking-multivendor-marketplace-for-woocommerce' ); ?></p>
<?php endif; ?>

<p><?php echo make_clickable( sprintf( __( 'You can view and edit this booking in the dashboard here: %s', 'marketking-multivendor-marketplace-for-woocommerce' ), esc_attr( trailingslashit(get_page_link( get_option( 'marketking_vendordash_page_setting', 'disabled' ) )) . 'edit-booking-order/' . $booking->get_id() ) ) ); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>
