<?php
/**
 * Loop Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if(!function_exists('wc_review_ratings_enabled')){
	return;
}

if ( !wc_review_ratings_enabled() ) {
	echo rh_woo_get_editor_rating();
}else{
	$average_rating = $product->get_average_rating();
	if ($average_rating > 0){
		echo wc_get_rating_html($average_rating);
	}
	else{
		echo rh_woo_get_editor_rating();
	}
}