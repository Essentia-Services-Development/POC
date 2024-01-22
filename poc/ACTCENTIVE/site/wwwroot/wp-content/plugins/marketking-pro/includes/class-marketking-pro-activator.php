<?php

class Marketkingpro_Activator {

	public static function activate() {

		// clear marketkingpro transients
		global $wpdb;
		$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%transient_marketkingpro%'" );
		foreach( $plugin_options as $option ) {
		    delete_option( $option->option_name );
		}
		wp_cache_flush();

		// prevent option update issues due to caching
		wp_cache_delete ( 'alloptions', 'options' );


		// Trigger post types and endpoints functions
		require_once ( MARKETKINGPRO_DIR . 'admin/class-marketking-pro-admin.php' );
		require_once ( MARKETKINGPRO_DIR . 'public/class-marketking-pro-public.php' );
		$adminobj = new Marketkingpro_Admin;
		$publicobj = new Marketkingpro_Public;
		$adminobj->marketking_register_post_type_custom_option();
		$adminobj->marketking_register_post_type_custom_field();

		if (apply_filters('marketking_use_credit_product', true)){

			// Create B2B credit product if it doesn't exist
			$credit_id = intval(get_option('marketking_credit_product_id_setting', 0));
			if ( !get_post_status ( $credit_id ) ) {
				$credit_id = array(
				    'post_title' => 'Credit',
				    'post_status' => 'publish',
				    'post_type' => 'product',
				    'post_author' => 1,
				);
				$product_id = wp_insert_post($credit_id);
				//Set product hidden: 
				$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
				wp_set_object_terms( $product_id, $terms, 'product_visibility' );
				wp_set_object_terms( $product_id, 'simple', 'product_type' );
				update_post_meta( $product_id, '_visibility', 'hidden' );
				update_post_meta( $product_id, '_stock_status', 'instock');
				update_post_meta( $product_id, '_regular_price', '' );
				update_post_meta( $product_id, '_sale_price', '' );
				update_post_meta( $product_id, '_purchase_note', '' );
				update_post_meta( $product_id, '_product_attributes', array() );
				update_post_meta( $product_id, '_sale_price_dates_from', '' );
				update_post_meta( $product_id, '_sale_price_dates_to', '' );
				update_post_meta( $product_id, '_price', '1' );
				update_post_meta( $product_id, '_sold_individually', '' );
				update_post_meta( $product_id, '_tax_status', 'none' );
				update_post_meta( $product_id, '_tax_class', 'zero-rate' );
				update_post_meta( $product_id, '_virtual', 'yes' );
				update_post_meta( $product_id, '_downloadable', 'yes' );

				// set option to product id
				update_option( 'marketking_credit_product_id_setting', intval($product_id) );
			}
		}
		
		// Flush rewrite rules
		if (apply_filters('marketkingpro_flush_permalinks', true)){
			// Flush rewrite rules
			flush_rewrite_rules();
		}

		// Set admin notice state to enabled ('activate woocommerce' notice)
		update_user_meta(get_current_user_id(), 'marketkingpro_dismiss_activate_woocommerce_notice', 0);
		
	}

}
