<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$post_id = sanitize_text_field( Marketking_WC_Bookings::get_pagenr_query_var() );

$post = get_post( $post_id );

Marketking_WC_Bookings_Order_Metabox::edit_order_meta_box_inner( $post );

