<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="inside">

	<?php


	require_once WC_BOOKINGS_ABSPATH . 'includes/admin/class-wc-bookings-admin.php';
	require_once WC_BOOKINGS_ABSPATH . 'includes/booking-form/class-wc-booking-form.php';
	require_once( MARKETKINGPRO_DIR. 'includes/wcbookings/integrations/wc-bookings/includes/class-marketking-wc-bookings-order-create.php' );

	$wc_bookings = new Marketking_WC_Bookings_Order_Create_Metabox();
	$wc_bookings->add_order_metabox_inner();
	// Initialize Timezone.
	WC_Bookings_Timezone_Settings::instance();
	?>
</div>

