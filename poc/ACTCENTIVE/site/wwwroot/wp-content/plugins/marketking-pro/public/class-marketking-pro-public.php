<?php

class Marketkingpro_Public{

	function __construct() {

		// filter to remove MarketKing in all API requests:
		require_once ( MARKETKINGPRO_DIR . 'includes/class-marketking-pro-helper.php' );
		$helper = new Marketkingpro_Helper();
		$run_in_api_requests = true;
		if (apply_filters('marketkingpro_force_cancel_api_requests', false)){
			if ($helper->marketkingpro_is_rest_api_request()){
				$run_in_api_requests = false;
			}
		}

		if ($run_in_api_requests){
			add_action('plugins_loaded', function(){

				// Only load if WooCommerce is activated
				if ( class_exists( 'woocommerce' ) && defined('MARKETKINGCORE_DIR') ) {

					// Enqueue resources
					add_action('wp_enqueue_scripts', array($this, 'enqueue_public_resources'));

					// Registration options and fields
					require_once ( MARKETKINGPRO_DIR . 'admin/class-marketking-pro-admin.php' );
					add_action( 'init', array('Marketkingpro_Admin', 'marketking_register_post_type_custom_option'), 0 );
					add_action( 'init', array('Marketkingpro_Admin', 'marketking_register_post_type_custom_field'), 0 );

					/* Add items to "My Account" */
					// Add custom items to My account WooCommerce user menu
					add_filter( 'woocommerce_account_menu_items', array($this, 'marketking_my_account_custom_items'), 10, 1 );
					add_filter("woocommerce_get_query_vars", array($this,'myaccount_query_vars'));

					// Add custom endpoints
					add_action( 'init', array($this, 'marketking_custom_endpoints') );
					add_action( 'init', array($this, 'force_permalinks_rewrite') );

					// Refunds
					if(defined('MARKETKINGPRO_DIR')){
					    if (intval(get_option( 'marketking_enable_refunds_setting', 1 )) === 1){
							// allow customers to request refunds in the frontend my account
							add_action( 'woocommerce_order_details_after_order_table', array($this, 'marketking_refund_button'));

							// Add content to refunds endpoint
							add_action( 'woocommerce_account_'.get_option('marketking_refunds_endpoint_setting','refunds').'_endpoint', array($this, 'marketking_refunds_endpoint_content') );
							// Add content to individual refund endpoint
							add_action( 'woocommerce_account_'.get_option('marketking_refund_endpoint_setting','refund').'_endpoint', array($this, 'marketking_refund_endpoint_content') );

						}
					}

					// Support Button in Order Page
					if(defined('MARKETKINGPRO_DIR')){
					    if (intval(get_option( 'marketking_enable_support_setting', 1 )) === 1){
					    	if (intval(get_option( 'marketking_show_support_order_details_setting', 1 )) === 1){
					    		add_action( 'woocommerce_order_details_after_order_table', array($this, 'marketking_order_support_button'));
					    	}
						}
					}

					// Mark Order as Completed
					if(defined('MARKETKINGPRO_DIR')){
					    if (intval(get_option( 'marketking_enable_shippingtracking_setting', 1 )) === 1){
					    	if (intval(get_option( 'marketking_customers_mark_order_received_setting', 0 )) === 1){
					    		add_action( 'woocommerce_order_details_after_order_table', array($this, 'marketking_mark_order_received_button'));
					    	}
						}
					}


					// if module enabled
					if (intval(get_option( 'marketking_enable_messages_setting', 1 )) === 1){
					//	if (intval(get_option( 'marketking_enable_inquiries_setting', 1 )) === 1){ // can still be needed for support messaging for example
							/* messages */
							// Add content to messages endpoint
							add_action( 'woocommerce_account_'.get_option('marketking_messages_endpoint_setting','messages').'_endpoint', array($this, 'marketking_messages_endpoint_content') );
							// Add content to individual message endpoint
							add_action( 'woocommerce_account_'.get_option('marketking_message_endpoint_setting','message').'_endpoint', array($this, 'marketking_message_endpoint_content') );
					//	}
					}

					// Option for DEFAULT store title as store name (optional)
					add_filter('pre_get_document_title', [$this, 'marketking_default_store_name_optional'], 10);

					// SEO
					if (intval(get_option( 'marketking_enable_storeseo_setting', 1 )) === 1){
						// Apply SEO TITLE to vendor pages
						add_filter('pre_get_document_title', [$this, 'marketking_seo_title'], 100);
						// Meta keywords and description
						add_action('wp_head', [$this,'marketking_seo_deskkey']);
					}

					

					// abuse reports
					if (intval(get_option( 'marketking_enable_abusereports_setting', 1 )) === 1){
						if (is_user_logged_in()){
							// Show vendor in product page and other items such as checkout
							add_action('woocommerce_product_meta_end', [$this, 'marketking_report_abuse_button'], 100);
						}
						add_shortcode('marketking_report_abuse', [$this, 'marketking_report_abuse_button_shortcode']);

					}

					// favorite stores
					if (intval(get_option('marketking_enable_favorite_setting', 1)) === 1){
						add_action( 'woocommerce_account_'.get_option('marketking_favorite_endpoint_setting','favorite').'_endpoint', array($this, 'marketking_favorite_endpoint_content') );
						// shortcode
						add_action( 'init', array($this, 'marketking_favorite_shortcode'));
					}

					// single product multiple vendors
					if (intval(get_option('marketking_enable_spmv_setting', 1)) === 1){

						if (apply_filters('marketking_add_product_to_my_store_enable', true)){
							add_action('woocommerce_after_add_to_cart_button',array($this, 'add_to_my_store_button'), 100);
							add_action('woocommerce_single_product_summary',array($this, 'add_to_my_store_button_out_of_stock'), 100);
						}

						// add panel with multiple offers in the product page
						$position = get_option('marketking_offers_position_setting', 'belowproduct');
						if ($position === 'belowproduct'){
							add_action('woocommerce_after_single_product_summary', array($this,'add_offers_product_page'), 9);
						} else if ($position === 'insideproducttabs'){
							add_action('marketking_show_other_offers', array($this,'add_offers_product_page'), 10);
						} else if ($position === 'belowproducttabs'){
							add_action('woocommerce_after_single_product_summary', array($this,'add_offers_product_page'), 10);

						}
					}

					// Memberships
					// if order includes a membership product, move user to group of membership
					if (intval(get_option( 'marketking_enable_memberships_setting', 1 )) === 1){
						add_action('woocommerce_checkout_order_processed', [$this,'process_membership_purchases'], 10, 3);
						add_action( 'woocommerce_before_cart', array($this,'check_multiple_susbcription_cart'), 100);
						add_action( 'woocommerce_before_checkout_form', array($this,'check_multiple_susbcription_cart'), 100);
						add_filter( 'woocommerce_order_button_html', array($this, 'check_multiple_susbcription_cart_button' ) );
					}

					
					// Store Reviews
					if(defined('MARKETKINGPRO_DIR')){
					    if (intval(get_option( 'marketking_enable_reviews_setting', 1 )) === 1){
					    	// send emails when new review
					    	add_action( 'comment_post', array($this, 'send_email_on_new_review'), 10, 2 );
					    }
					}

					// B2BKING INTEGRATION
					if (defined('B2BKING_DIR') && intval(get_option('marketking_enable_b2bkingintegration_setting', 1)) === 1){
						/* Offers */
						// Add vendor to My Account -> offers in public side
						add_action('b2bking_before_offer_add_to_cart_public', array($this,'add_vendor_offers_public'));
						// Remove offer product after purchased
						add_action('woocommerce_thankyou', array($this,'remove_offer_product'), 10, 1);
						// Exclude offers from dynamic rules
						add_filter('b2bking_get_offer_product_id', array($this, 'exclude_offers_from_rules'), 10, 2);

						/* Messaging */
						add_action('b2bking_start_new_conversation', array($this, 'add_vendor_to_new_message'));
						add_filter( 'query_vars', array($this, 'b2bking_add_query_vars_filter') );
						add_action('b2bking_myaccount_conversations_items_title', array($this, 'add_vendor_to_conversations_title'), 10, 1);
						add_action('b2bking_myaccount_conversations_items_content', array($this, 'add_vendor_to_conversations_content'), 10, 1);

						// minmax val qty apply
						add_action( 'woocommerce_check_cart_items', array($this, 'b2bking_minimum_orders' ));
					
					}

					// Remove product tabs by group
					if (!is_admin()){
						add_filter('woocommerce_product_data_tabs', array($this, 'control_vendor_product_tabs'), 1000);
					}

					// subscription integration, do not allow multiple subscription products from different vendors in cart
					add_action( 'woocommerce_before_cart', array($this,'do_not_allow_multiple_subscription_different_vendors'), 10);
					add_action( 'woocommerce_before_checkout_form', array($this,'do_not_allow_multiple_subscription_different_vendors'), 10);


				}
			});
		}
	}

	function do_not_allow_multiple_subscription_different_vendors() {
		
		if (marketking()->get_number_of_subscription_vendors_cart() > 1){
			wc_print_notice( esc_html__('You cannot purchase multiple subscription products from different sellers in the same order.', 'marketking'), 'error' );
		}

	}

	function b2bking_minimum_orders(){

	    $eachVendorCartTotal = array();
	    $eachVendorCartQty = array();
	    $items = WC()->cart->get_cart();

	    //build the array: [vendor_id][sub_total]
	    foreach ($items as $item => $values) {

	        $product_id = $values['product_id'];
	        $product_qty = $values['quantity'];
	        $product_price = get_post_meta($values['product_id'], '_price', true);
	        if (empty($product_price)){
	        	$product_price = get_post_meta($values['product_id'], '_sale_price', true);
	        	if (empty($product_price)){
	        		$product_price = get_post_meta($values['product_id'], '_regular_price', true);
	        	}
	        }

	        if (empty($product_price)){
	        	$product = wc_get_product($values['product_id']);
	        	$product_price = $product->get_price();
	        }

	        $product_price = $product_price * $product_qty;

	        $vendor_id = get_post_field('post_author', $product_id);

	        if (!array_key_exists($vendor_id, $eachVendorCartTotal)) {
	            $eachVendorCartTotal[$vendor_id] = $product_price;
	        } else {
	            $sub_total = $product_price + $eachVendorCartTotal[$vendor_id];
	            $eachVendorCartTotal[$vendor_id] = $sub_total;
	        }

	        if (!array_key_exists($vendor_id, $eachVendorCartQty)) {
	            $eachVendorCartQty[$vendor_id] = $product_qty;
	        } else {
	            $sub_total = $product_qty + $eachVendorCartQty[$vendor_id];
	            $eachVendorCartQty[$vendor_id] = $sub_total;
	        }

	    }

	    $customer_id = get_current_user_id();
	    $is_b2b = get_user_meta($customer_id,'b2bking_b2buser', true);

	    // MIN and MAX ORDER VALUE
	    if (!empty($eachVendorCartTotal)) {	
	        foreach ($eachVendorCartTotal as $vendor_id => $value) {

	        	if ($is_b2b === 'yes'){
	        		$minorderval = get_user_meta($vendor_id, 'marketking_minordervalb2b', true);
	        		$minorderqty = get_user_meta($vendor_id, 'marketking_minorderqtyb2b', true);	  
	        		$maxorderval = get_user_meta($vendor_id, 'marketking_maxordervalb2b', true);
	        		$maxorderqty = get_user_meta($vendor_id, 'marketking_maxorderqtyb2b', true);	    
	        	} else {
	        		$minorderval = get_user_meta($vendor_id, 'marketking_minordervalb2c', true);
	        		$minorderqty = get_user_meta($vendor_id, 'marketking_minorderqtyb2c', true);
	        		$maxorderval = get_user_meta($vendor_id, 'marketking_maxordervalb2c', true);
	        		$maxorderqty = get_user_meta($vendor_id, 'marketking_maxorderqtyb2c', true);
	        	}


	            $errorMessageMinVal = esc_html__("Your current order total for %s is %s — you must have an order with a minimum of %s to place your order for this vendor",'marketking');
	            $errorMessageMaxVal = esc_html__("Your current order total for %s is %s — you must have an order with a maximum of %s to place your order for this vendor", 'marketking');

	            $store_name = marketking()->get_store_name_display($vendor_id);

	            if(!empty($minorderval)) {
	                $vendor_minimum = !empty($minorderval) ? $minorderval : 0;
	                if ($value < $vendor_minimum) {
	                    if (is_cart()) {

	                        wc_print_notice(
	                            sprintf($errorMessageMinVal,
	                                $store_name,
	                                wc_price($value),
	                                wc_price($vendor_minimum)
	                            ), 'error'
	                        );

	                    } else {
	                        wc_add_notice(
	                            sprintf($errorMessageMinVal,
	                                $store_name,
	                                wc_price($value),
	                                wc_price($vendor_minimum)
	                            ), 'error'
	                        );
	                    }
	                }
	            }
	            if(!empty($maxorderval)) {
	                $vendor_maximum = !empty($maxorderval) ? $maxorderval : 0;
	                if ($value > $vendor_maximum) {
	                    if (is_cart()) {

	                        wc_print_notice(
	                            sprintf($errorMessageMaxVal,
	                                $store_name,
	                                wc_price($value),
	                                wc_price($vendor_maximum)
	                            ), 'error'
	                        );

	                    } else {
	                        wc_add_notice(
	                            sprintf($errorMessageMaxVal,
	                                $store_name,
	                                wc_price($value),
	                                wc_price($vendor_maximum)
	                            ), 'error'
	                        );
	                    }
	                }
	            }
	        }
	    }

	    // MIN and MAX ORDER QTY
	    if (!empty($eachVendorCartQty)) {	
	        foreach ($eachVendorCartQty as $vendor_id => $value) {

	        	if ($is_b2b === 'yes'){
	        		$minorderval = get_user_meta($vendor_id, 'marketking_minordervalb2b', true);
	        		$minorderqty = get_user_meta($vendor_id, 'marketking_minorderqtyb2b', true);	  
	        		$maxorderval = get_user_meta($vendor_id, 'marketking_maxordervalb2b', true);
	        		$maxorderqty = get_user_meta($vendor_id, 'marketking_maxorderqtyb2b', true);	    
	        	} else {
	        		$minorderval = get_user_meta($vendor_id, 'marketking_minordervalb2c', true);
	        		$minorderqty = get_user_meta($vendor_id, 'marketking_minorderqtyb2c', true);
	        		$maxorderval = get_user_meta($vendor_id, 'marketking_maxordervalb2c', true);
	        		$maxorderqty = get_user_meta($vendor_id, 'marketking_maxorderqtyb2c', true);
	        	}


	            $errorMessageMinVal = esc_html__("Your current order quantity for %s is %s — you must have an order with a minimum of %s to place your order for this vendor",'marketking');
	            $errorMessageMaxVal = esc_html__("Your current order quantity for %s is %s — you must have an order with a maximum of %s to place your order for this vendor",'marketking');

	            $store_name = marketking()->get_store_name_display($vendor_id);

	            if(!empty($minorderqty)) {
	                $vendor_minimum = !empty($minorderqty) ? $minorderqty : 0;
	                if ($value < $vendor_minimum) {
	                    if (is_cart()) {

	                        wc_print_notice(
	                            sprintf($errorMessageMinVal,
	                                $store_name,
	                                $value,
	                                $vendor_minimum
	                            ), 'error'
	                        );

	                    } else {
	                        wc_add_notice(
	                            sprintf($errorMessageMinVal,
	                                $store_name,
	                                $value,
	                                $vendor_minimum
	                            ), 'error'
	                        );
	                    }
	                }
	            }
	            if(!empty($maxorderqty)) {
	                $vendor_maximum = !empty($maxorderqty) ? $maxorderqty : 0;
	                if ($value > $vendor_maximum) {
	                    if (is_cart()) {

	                        wc_print_notice(
	                            sprintf($errorMessageMaxVal,
	                                $store_name,
	                                $value,
	                                $vendor_maximum
	                            ), 'error'
	                        );

	                    } else {
	                        wc_add_notice(
	                            sprintf($errorMessageMaxVal,
	                                $store_name,
	                                $value,
	                                $vendor_maximum
	                            ), 'error'
	                        );
	                    }
	                }
	            }
	        }
	    }
	}

	function control_vendor_product_tabs($tabs){

		$user_id = get_current_user_id();
		$user_group = get_user_meta($user_id,'marketking_group', true);
		$selected_options_string = get_post_meta($user_group, 'marketking_group_allowed_tabs_settings', true);
		$removed_options = explode(',', $selected_options_string);

		foreach ($tabs as $index => $arr){
			if (in_array($index, $removed_options)){
				unset($tabs[$index]);
			}
		}

		return $tabs;
	}

	function check_multiple_susbcription_cart_button( $button ) {

		if (is_object( WC()->cart )){

			$product_nr = 0;

			foreach(WC()->cart->get_cart() as $cart_item){
				$product_id = $cart_item['product_id'];
					
				// check if product ID is the product within any of the memberships
				// Get all packages
				$packs = get_posts([
				  'post_type' => 'marketking_mpack',
				  'post_status' => 'publish',
				  'numberposts' => -1,
				  'meta_key' => 'marketking_pack_sort_order',
				  'orderby' => 'meta_value_num',
				  'order' => 'ASC',
				]);
				foreach ($packs as $pack){
					$pack_product_id = get_post_meta($pack->ID,'marketking_pack_product', true);
					if (intval($pack_product_id) === intval($product_id)){
						$product_nr++;
					}
				}
			}

			if ($product_nr > 1){
				$style = 'style="background:Silver !important; color:white !important; cursor: not-allowed !important;"';
		        $button_text = apply_filters( 'woocommerce_order_button_text', esc_html__( 'Place order', 'woocommerce' ) );
		        $button = '<a class="button" '.$style.'>' . $button_text . '</a>';
			}
		}

	    return $button;
	}

	function check_multiple_susbcription_cart(){

		if (is_object( WC()->cart )){

			$product_nr = 0;

			foreach(WC()->cart->get_cart() as $cart_item){
				$product_id = $cart_item['product_id'];
					
				// check if product ID is the product within any of the memberships
				// Get all packages
				$packs = get_posts([
				  'post_type' => 'marketking_mpack',
				  'post_status' => 'publish',
				  'numberposts' => -1,
				  'meta_key' => 'marketking_pack_sort_order',
				  'orderby' => 'meta_value_num',
				  'order' => 'ASC',
				]);
				$have_package = false;
				foreach ($packs as $pack){
					$pack_product_id = get_post_meta($pack->ID,'marketking_pack_product', true);
					if (intval($pack_product_id) === intval($product_id)){
						$have_package = true;
					}
				}
				if ($have_package){
					$product_nr++;
				}
			}

			if ($product_nr > 1){
				wc_print_notice( apply_filters('marketking_multiple_packages_cart_error', esc_html__('You have multiple vendor package products in cart - you can only purchase one at a time', 'marketking')), 'error' );
			}
		}


	}

	function marketking_mark_order_received_button($order){
		if ( ! $order || ! $order->has_status( apply_filters( 'marketking_valid_statuses_support', array( 'completed','processing','on-hold' ) ) ) || ! is_user_logged_in() ) {
			return;
		}

		if (!is_checkout()){
			$received = get_post_meta($order->get_id(),'marked_received', true);
			if ($received === 'yes'){
				?>
				<p><?php esc_html_e('Thank you for confirming that you received this order.','marketking');?></p>
				<?php
			} else {
				?>
				<p>
					<button id="marketking_mark_order_received" value="<?php echo esc_attr($order->get_id());?>"><?php esc_html_e( 'Mark Order as Received', 'marketking' ); ?></button>
				</p>
				<?php
			}
		}
	}

	function marketking_order_support_button($order){
		if ( ! $order || ! $order->has_status( apply_filters( 'marketking_valid_statuses_support', array( 'completed','processing','on-hold' ) ) ) || ! is_user_logged_in() ) {
			return;
		}

		if (!is_checkout()){
			$vendor_id = marketking()->get_order_vendor($order->get_id());
			$support_option = get_user_meta($vendor_id,'marketking_support_option', true);

			if ($support_option === 'external') {
				$support_url = get_user_meta($vendor_id,'marketking_support_url', true);
				?>
				<p>
					<a class="button" target="_blank" rel="noreferrer noopener" href="<?php echo esc_attr($support_url);?>"><?php esc_html_e( 'Request support', 'marketking' ); ?></a>
				</p>
				<?php
			} else {
				?>
				<p>
					<a class="button" id="marketking_request_support_initial_button"><?php esc_html_e( 'Request support', 'marketking' ); ?></a>
				</p>
				<div id="marketking_support_request_panel">
					<input type="hidden" id="marketking_support_order" value="<?php echo esc_attr($order->get_id());?>">
					<span id="marketking_send_inquiry_textarea_abovetext"><?php esc_html_e('Send a support message to the vendor below:', 'marketking');?></span>
					<textarea id="marketking_send_support_textarea"></textarea>

					<button type="button" id="marketking_send_support_button" class="button" value="<?php echo esc_attr($vendor_id); ?>">
						<?php esc_html_e( 'Send support request', 'marketking' ); ?>
					</button>
				</div>
				<?php
			}
		}		
		
	}


	function marketking_refund_button( $order ) {
		if ( ! $order || ! $order->has_status( apply_filters( 'marketking_valid_statuses_refunds', array( 'completed','processing','on-hold' ) ) ) || ! is_user_logged_in() ) {
			return;
		}

		// if is checkout / order received, do not show refund button
		if ( is_checkout() && !empty( is_wc_endpoint_url('order-received') ) ) {
		    return;
		}

		// check time limit for order
		$days_limit = get_option('marketking_refund_time_limit_setting', 90);
		if (empty($days_limit)){
			$days_limit = 90;
		}
		$order_time = strtotime(explode('T',$order->get_date_created())[0]);
		$current_time = time();
		$ordervalue = $order->get_total();

		// if vendor has refunds enabled or is admin
		$vendor_id = marketking()->get_order_vendor($order->get_id());
		$vendoruser = new WP_User($vendor_id);
		if(marketking()->vendor_has_panel('refunds', $vendor_id) || $vendoruser->has_cap( 'manage_woocommerce' ) ){

			if (floatval($order_time+(86400*$days_limit)) > $current_time){

				// check if refund already
				$refund_requests = get_posts( array( 
				    'post_type' => 'marketking_refund',
				    'numberposts' => -1,
				    'post_status'    => 'any',
				    'fields'    => 'ids',
				    'meta_key'   => 'order_id',
				    'meta_value' => $order->get_id(),
				));

				if (apply_filters('marketking_allow_refund_request_order', true, $order->get_id())){
					if (empty($refund_requests)){
						?>
						<p class="order-again">
							<a class="button" id="marketking_request_refund_initial_button"><?php esc_html_e( 'Request refund', 'marketking' ); ?></a>
						</p>

						<div id="marketking_refund_request_panel">
							<input type="hidden" id="marketking_refund_order_id" value="<?php echo esc_attr($order->get_id());?>">

							<label class="marketking_refund_label" for="marketking_refund_request_value"><?php esc_html_e('Request value:','marketking');?></label>
							<select name="marketking_refund_request_value" id="marketking_refund_request_value">
								<option value="full"><?php esc_html_e('Full Order Refund', 'marketking');?></option>
								<option value="partial"><?php esc_html_e('Partial Refund', 'marketking');?></option>
							</select>

							<div id="marketking_refund_partial_container">
								<label class="marketking_refund_label" for="marketking_refund_partial_amount"><?php esc_html_e('Partial refund value:','marketking');?></label>
								<input type="number" name="marketking_refund_partial_amount" id="marketking_refund_partial_amount" step="0.01" max="<?php echo esc_attr($ordervalue); ?>">
							</div>
							
							<label class="marketking_refund_label" for="marketking_refund_request_reason"><?php esc_html_e('Reason for request:','marketking');?></label>
							<textarea id="marketking_refund_request_reason"></textarea>

							<button type="button" id="marketking_refund_request_send" class="button">
								<?php echo apply_filters('marketking_refund_request_send_text', esc_html__('Send refund request','marketking')); ?>
							</button>
						</div>

						<?php
					} else {
						$refund_endpoint_url = wc_get_endpoint_url(get_option('marketking_refund_endpoint_setting','refund'));

						foreach ($refund_requests as $request){
							esc_html_e('A refund request has been submitted for this order.','marketking');
							echo ' <a href="'.esc_attr($refund_endpoint_url).'?id='.esc_attr($request).'">'.esc_html__('Click to View Request.','marketking').'</a>';
							echo '<br><br>';
						}
					}
				}
				
			}
		}
		
	}

	function marketking_default_store_name_optional($title){
		$title_original = $title;
		if (apply_filters('marketking_store_title_default_store_name', false)){
			$vendor_id = marketking()->get_vendor_id_in_store_url();
			if ($vendor_id !== 0){
				$storesid = intval(apply_filters( 'wpml_object_id', get_option( 'marketking_stores_page_setting', 'none' ), 'post' , true) );
				global $post;
				if (isset($post->ID)){
					if ($storesid === $post->ID){
						$titletemp = marketking()->get_store_name_display($vendor_id);
						if (!empty($titletemp)){
							$title = $titletemp;
						}
					}	
				}
			}
		}		
			
		return apply_filters('marketking_default_store_title_display', $title, $title_original);
	}

	function marketking_seo_title($title){

		$vendor_id = marketking()->get_vendor_id_in_store_url();
		if ($vendor_id !== 0){
			$storesid = intval(apply_filters( 'wpml_object_id', get_option( 'marketking_stores_page_setting', 'none' ), 'post' , true) );
			global $post;
			if (isset($post->ID)){
				if ($storesid === $post->ID){
					$titletemp = get_user_meta($vendor_id,'marketking_seotitle', true);
					if (!empty($titletemp)){
						$title = $titletemp;
					}
				}	
			}
					
		}
			
		return $title;
	}

	function process_membership_purchases($order_id, $posted_data, $order){
		foreach ($order->get_items() as $item_id => $item ) {

			// Get the WC_Order_Item_Product object properties in an array
		    $item_data = $item->get_data();

		    if ($item['quantity'] > 0) {
		        // get the WC_Product object
		        $product_id = $item['product_id'];
		        $variation_id = $item['variation_id'];

		        // check if product ID is the product within any of the memberships
		        // Get all packages
		        $packs = get_posts([
		          'post_type' => 'marketking_mpack',
		          'post_status' => 'publish',
		          'numberposts' => -1,
		          'meta_key' => 'marketking_pack_sort_order',
		          'orderby' => 'meta_value_num',
		          'order' => 'ASC',
		        ]);
		        foreach ($packs as $pack){
		        	$pack_product_id = get_post_meta($pack->ID,'marketking_pack_product', true);
		        	if (intval($pack_product_id) === intval($product_id) || intval($pack_product_id) === intval($variation_id)){

		        		$note = '<p>'.esc_html__('This order was placed by a vendor who chose a membership package option.','marketking').'</p>';


		        		// check setting to see if vendor should be added to group now, or on order completed
		        		if (get_option('marketking_memberships_assign_group_time_setting', 'order_placed')  === 'order_placed'){
		        			// this product is indeed a pack product, we must now move the user to group
		        			$pack_group = get_post_meta($pack->ID,'marketking_pack_vendor_group', true);
		        			// check that current user is indeed a vendor

		        			$current_id = get_current_user_id();
		        			if (marketking()->is_vendor_team_member()){
		        				$current_id = marketking()->get_team_member_parent();
		        			}

		        			if (marketking()->is_vendor($current_id)){

	        					// advertising credits, give only once
	        					$changed = $order->get_meta('marketking_membership_credits_given');

	        					if ($changed !== 'yes'){

	        						$order->update_meta_data( 'marketking_membership_credits_given', 'yes' );

	        						$amount = intval(get_post_meta($pack->ID, 'marketking_pack_credits', true));
	        						$user_credits = intval(get_user_meta($current_id, 'marketking_advertising_credits_available', true));
	        						$user_credits += intval($amount);
	        						update_user_meta($current_id, 'marketking_advertising_credits_available', $user_credits);


	        						$note = esc_html__('Purchased via membership, order ','marketking').'#'.$order_id;
	        						// get user history
	        						$user_credit_history = sanitize_text_field(get_user_meta($current_id,'marketking_user_credit_history', true));
	        						// create reimbursed transaction
	        						$date = date_i18n( 'Y/m/d', time()+(get_option('gmt_offset')*3600) ); 
	        						$operation = 'purchase';
	        						$transaction_new = $date.':'.$operation.':'.$amount.':'.$user_credits.':'.$note;

	        						// update credit history
	        						update_user_meta($current_id,'marketking_user_credit_history',$user_credit_history.';'.$transaction_new);
	        					}

	        					update_user_meta($current_id, 'marketking_group', $pack_group);
	        					do_action('marketking_moved_user_group_membership', $current_id, $pack_group);


		        				// SUBSCRIPTIO
		        				$subscriptio_product = get_post_meta($product_id, '_rp_sub:subscription_product', true);
		        				if ($subscriptio_product === 'yes'){

		        					$note.= '<br>'.esc_html__('The vendor has been moved to the following group:','marketking').' '.get_the_title($pack_group);

		        					$subscription_id = intval($order_id)+2;
		        					if (get_post_type($subscription_id) === 'marketking_earning'){
		        						$subscription_id++;
		        					}

		        					$subscription_item = 'subscriptio:'.$subscription_id.':'.$pack_group;

		        					update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        				}

		        				// SUMO
		        				if ($order->get_status() !== 'cancelled' && $order->get_status() !== 'failed'){
		        					$sumo_product = get_post_meta($product_id, 'sumo_susbcription_status', true);
		        					if (intval($sumo_product) === 1){

		        						$note.= '<br>'.esc_html__('The vendor has been moved to the following group:','marketking').' '.get_the_title($pack_group);

		        						$subscription_id = intval($order_id)+1;
		        						while (get_post_type($subscription_id) === 'marketking_earning'){
		        							$subscription_id++;
		        						}

		        						$subscription_item = 'sumo:'.$subscription_id.':'.$pack_group;

		        						update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        					}
		        				}
		        				

		        				// YITH
		        				$yith_product = get_post_meta($product_id, '_ywsbs_subscription', true);
		        				if ($yith_product === 'yes'){

		        					$note.= '<br>'.esc_html__('The vendor has been moved to the following group:','marketking').' '.get_the_title($pack_group);

		        					$subscription_id = intval($order_id)+1;
		        					if (get_post_type($subscription_id) === 'marketking_earning'){
		        						$subscription_id++;
		        					}

		        					$subscription_item = 'yith:'.$subscription_id.':'.$pack_group;

		        					update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        				}

		        				// WooCommerce Subscriptions
		        				$product = wc_get_product($product_id);
		        				$type = $product->get_type();
		        				if ($type === 'subscription' || $type === 'variable-subscription'){
		        					
		        					$note.= '<br>'.esc_html__('The vendor has been moved to the following group:','marketking').' '.get_the_title($pack_group);

		        					$subscription_id = intval($order_id)+1;
		        					if (get_post_type($subscription_id) === 'marketking_earning'){
		        						$subscription_id++;
		        					}

		        					$subscription_item = 'woo:'.$subscription_id.':'.$pack_group;

		        					update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        				}

		        				
		        			}
		        		}

		        		$order->add_order_note( $note );
		        		
		        	}
		        }

		    }
		}

	}


	function marketking_seo_deskkey(){

		$vendor_id = marketking()->get_vendor_id_in_store_url();
		if ($vendor_id !== 0){

			$storesid = intval(apply_filters( 'wpml_object_id', get_option( 'marketking_stores_page_setting', 'none' ), 'post' , true) );
			global $post;
			if (isset($post->ID)){
				if ($storesid === $post->ID){

					$description = get_user_meta($vendor_id,'marketking_metadescription', true);

					if (!empty($description)){
						?><meta name="description" content="<?php echo esc_attr($description);?>"><?php
					}

					$keywords = get_user_meta($vendor_id,'marketking_metakeywords', true);

					if (!empty($keywords)){
						?><meta name="keywords" content="<?php echo esc_attr($keywords);?>"><?php
					}
				} 
			}
			
			
		}


	}

	function send_email_on_new_review($review_id, $comment_approved){
		if( 1 === $comment_approved ){
			if (get_comment_type($review_id) === 'review'){
				$review = get_comment($review_id);

				$productid = $review -> comment_post_ID;
				$product = wc_get_product($productid);
				$product_name = $product->get_title();
				$product_link = $product->get_permalink();

				$product_title = '<a href="'.esc_attr($product_link).'">'.esc_html($product_name).'</a>';

				$comment = $review -> comment_content;

				$rating = get_comment_meta($review_id,'rating', true);

				$vendor_id = marketking()->get_product_vendor($productid);
				$vendor_email = marketking()->get_vendor_email($vendor_id);

				$permission = get_user_meta($vendor_id, 'marketking_receive_new_rating_emails', true);
				if (empty($permission)){
					$permission = 'yes';
				}
				if ($permission === 'yes'){
					do_action( 'marketking_new_rating', $vendor_email, $rating, $comment, $product_title );
				}
			}
		    
		}
		

	}

	function add_offers_product_page(){
		global $post;
		$product_id = $post->ID;

		if (is_product()){
			// if there are other offers 
			$otheroffers = marketking()->get_linkedproducts($product_id, 'array');
			if (!empty($otheroffers)){
				$offers_number = count($otheroffers);
				if ($offers_number > 1){
					$offers_number_text = esc_html__('offers','marketking');
				} else {
					$offers_number_text = esc_html__('offer','marketking');
				}
				?>
				<div id="marketking_product_other_offers">
					<h3 class="marketking_other_offers_header">
					    <?php esc_html_e('Other offers','marketking'); ?>
					    &nbsp;<small>(<?php echo esc_html($offers_number.' '.$offers_number_text); ?>)</small>
					</h3>
					<?php
					// for each offer, show here
					$offers_shown_default = get_option('marketking_offers_shown_default_number_setting', 1);

					// Sort offers based on priority
					$stockpriority = get_option('marketking_stock_priority_setting', 'instock');
					$vendorpriority = get_option('marketking_vendor_priority_setting', 'lowerprice');

					if ($stockpriority === 'instock'){
						// split offers into 2 groups, instock and out of stock
						$instockoffers = marketking()->split_linkedproducts($otheroffers, 'instock');
						$outofstockoffers = marketking()->split_linkedproducts($otheroffers, 'outofstock');

						// merge groups again
						$instockoffers = marketking()->sort_linkedproducts($instockoffers, $vendorpriority);
						$outofstockoffers = marketking()->sort_linkedproducts($outofstockoffers, $vendorpriority);
						$otheroffers = array_merge($instockoffers, $outofstockoffers);
					} else if ($stockpriority === 'none'){
						$otheroffers = marketking()->sort_linkedproducts($otheroffers, $vendorpriority);
					}

					$i = 0;
					foreach ($otheroffers as $offerproduct_id){
						$i++;
						$offerproduct = wc_get_product($offerproduct_id);
						$vendor_id = marketking()->get_product_vendor($offerproduct_id);
						$store_name = marketking()->get_store_name_display($vendor_id);
						$store_link = marketking()->get_store_link($vendor_id);
						$stock_status = $offerproduct->get_stock_status();
						$stocktext = $badge = '';
						if ($stock_status === 'instock'){
						    $badge = 'badge-green';
						    $stocktext = esc_html__('In stock', 'marketking');
						} else if ($stock_status === 'outofstock'){
						    $badge = 'badge-gray';
						    $stocktext = esc_html__('Out of stock', 'marketking');
						} else if ($stock_status === 'onbackorder'){
						    $badge = 'badge-gray';
						    $stocktext = esc_html__('On backorder', 'marketking');
						}

						$rating = marketking()->get_vendor_rating($vendor_id, 1);

						?>
						<div class="marketking_product_other_offer_container <?php if($i > $offers_shown_default){echo 'marketking_offer_hidden_initial';} ?>">
							<?php
							// product image here
							$src = wp_get_attachment_url( $offerproduct->get_image_id() );
							if (empty($src)){
							    $src = wc_placeholder_img_src();
							}
							echo '<a href="'.esc_attr($offerproduct->get_permalink()).'"><img class="marketking_other_offer_product_image" src="'.esc_attr($src).'"></a>';
							?>
							<div class="marketking_product_other_offer_first_column">
								<div class="marketking_product_other_offer_first_column_sold_by">
									<?php echo esc_html__('Product sold by','marketking').' <a class="marketking_offer_store_link" href="'.esc_attr($store_link).'">'.esc_html($store_name); 
									// if there's any rating
									if (intval($rating['count'])!==0){
										echo '&nbsp;<strong>&nbsp;'.esc_html($rating['rating']).'</strong><span class="dashicons dashicons-star-filled marketking_product_other_offer_first_column_sold_by_star"></span>';
									}
									echo '</a>';
									?>

									<br>
									<div class="marketking_product_other_offer_first_column_sold_by_stock">
										<?php  echo '<span class="'.esc_attr($badge).'">'.esc_html($stocktext).'</span>';?>

									</div>
								</div>							
							</div>
							<div class="marketking_product_other_offer_second_third_container">

								<div class="marketking_product_other_offer_second_column">
									<?php echo $offerproduct->get_price_html(); ?>
								</div>
								<div class="marketking_product_other_offer_third_column">
									<?php 
									wc_get_template(
										'single-product/add-to-cart/external.php',
										array(
											'product_url' => $offerproduct->add_to_cart_url(),
											'button_text' => $offerproduct->add_to_cart_text(),
										)
									);
									?>
								</div>
							</div>
						</div>
						<?php					
					}

					if($i > $offers_shown_default){
						// show more items
						?>
						<div class="marketking_offers_show_more"><span class="dashicons dashicons-arrow-down"></span><?php esc_html_e('Show more...','marketking');?></div>
						<?php
					}
					?>
				</div>
				<br><br>

				<?php

				
			}
		}
		
	}

	function add_to_my_store_button(){
		// show button
		if (is_product()){
			global $post;
			if(intval($post->ID) === intval(get_queried_object_id())){
				if (marketking()->is_vendor(get_current_user_id())){	

					// only show if not my product + I don't already have it added in my store
					if (get_current_user_id() !== intval(marketking()->get_product_vendor($post->ID))){
						if(!marketking()->vendor_has_linkedproduct(get_current_user_id(), $post->ID)){
							// if has not already been shown on the page
							global $marketking_add_my_store_has_shown;
							if (empty($marketking_add_my_store_has_shown)){
								$marketking_add_my_store_has_shown = false;
							}
							if ($marketking_add_my_store_has_shown === false){
								ob_start();
								?>
								<button type="button" id="marketking_add_product_to_my_store" name="marketking_add_product_to_my_store" value="<?php echo esc_attr($post->ID);?>"><div id="marketking_add_product_to_my_store_icon"></div><span class="dashicons <?php echo esc_attr(apply_filters('marketking_add_product_to_my_store_icon','dashicons-clipboard'));?>"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo apply_filters('marketking_add_product_to_my_store_text',esc_html__('Add product to my store','marketking'));?></button>
								<?php
								$button = apply_filters('marketking_add_product_to_my_store_button', ob_get_clean(), $post->ID);
								echo $button;
								$marketking_add_my_store_has_shown = true;
							}
							
						}
					}
					
				}
			}
		}
	}

	function add_to_my_store_button_out_of_stock(){
		// show button
		if (is_product()){
			global $post;
			if(intval($post->ID) === intval(get_queried_object_id())){
				if (marketking()->is_vendor(get_current_user_id())){	

					// only show if not my product + I don't already have it added in my store
					if (get_current_user_id() !== intval(marketking()->get_product_vendor($post->ID))){
						if(!marketking()->vendor_has_linkedproduct(get_current_user_id(), $post->ID)){

							// only show if product is not purchasable
							$product = wc_get_product($post->ID);
							if (!$product->is_in_stock() or !$product->is_purchasable()){
								global $marketking_add_my_store_has_shown;
								if (empty($marketking_add_my_store_has_shown)){
									$marketking_add_my_store_has_shown = false;
								}
								if ($marketking_add_my_store_has_shown === false){
									ob_start();
									?>
									<button type="button" id="marketking_add_product_to_my_store" name="marketking_add_product_to_my_store" value="<?php echo esc_attr($post->ID);?>"><div id="marketking_add_product_to_my_store_icon"></div><span class="dashicons <?php echo esc_attr(apply_filters('marketking_add_product_to_my_store_icon','dashicons-clipboard'));?>"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo apply_filters('marketking_add_product_to_my_store_text',esc_html__('Add product to my store','marketking'));?></button>
									<?php
									$button = apply_filters('marketking_add_product_to_my_store_button', ob_get_clean(), $post->ID);
									echo $button;
									$marketking_add_my_store_has_shown = true;
								}
							}
						}
					}
					
				}
			}
		}
	}

	function marketking_report_abuse_button(){
		?>
		<div class="marketking_report_abuse_container">
			<br><div class="marketking_report_abuse_flagtext"><span class="dashicons dashicons-flag"></span><span class="marketking_report_abuse_text"><?php esc_html_e('Report Abuse','marketking'); ?></span></div>
			<div class="marketking_report_abuse_hidden">
				<textarea class="marketking_report_abuse_message" placeholder="<?php esc_html_e('Enter the reason for your report.','marketking');?>"></textarea><br>
				<button class="marketking_send_report_abuse" value="<?php
				global $post;
				echo esc_html($post->ID);

				?>"><?php esc_html_e('Send Report','marketking'); ?></button>
			</div>
		</div>
		<?php
	}

	function marketking_report_abuse_button_shortcode(){
		ob_start();
		if (is_user_logged_in()){
			$this->marketking_report_abuse_button();
		}
		return ob_get_clean();
	}

	function marketking_favorite_shortcode(){
		add_shortcode('marketking_favorite_stores', array($this, 'marketking_favorite_stores_content'));

		add_shortcode('marketking_favorite_stores_products', array($this, 'marketking_favorite_stores_products_content'));

	}
	function marketking_favorite_stores_content($atts = array(), $content = null){
		// get all favorited shops
		$favorite_vendors = marketking()->get_favorite_vendors(get_current_user_id());
		echo marketking()->display_stores_list($favorite_vendors);

	}

	function marketking_favorite_stores_products_content($atts = array(), $content = null){

		ob_start();

		// get all favorited shops
		global $marketking_favorite_stores_products;
		$marketking_favorite_stores_products = 'yes';

		echo do_shortcode('[products limit="12" paginate="true" visibility="visible"]');	

		$content = ob_get_clean();
		return $content;
	}

	function myaccount_query_vars ($vars) {
		    foreach ([get_option('marketking_messages_endpoint_setting','messages'), get_option('marketking_messages_endpoint_setting','favorite'), get_option('marketking_messages_endpoint_setting','refunds')] as $e) {
		        $vars[$e] = $e;
		    }

		    return $vars;
		}


	// Add custom items to My account WooCommerce user menu
	function marketking_my_account_custom_items( $items ) {
		// Get current user
		$user_id = get_current_user_id();
		
		// Add messages
		if (intval(get_option('marketking_enable_messages_setting', 1)) === 1){
		//	if (intval(get_option( 'marketking_enable_inquiries_setting', 1 )) === 1){ // should be enabled anyway because it may be used for support

		    	$items = array_slice($items, 0, 2, true) +
		    	    array(get_option('marketking_messages_endpoint_setting','messages') => esc_html__( 'Messages', 'marketking' )) + 
		    	    array_slice($items, 2, count($items)-2, true);

		 //   }
		}

		// Add favorite stores
		if (intval(get_option('marketking_enable_favorite_setting', 1)) === 1){
	    	$items = array_slice($items, 0, 3, true) +
	    	    array(get_option('marketking_favorite_endpoint_setting','favorite') => esc_html__( 'Favorite Stores', 'marketking' )) + 
	    	    array_slice($items, 3, count($items)-3, true);
		}

		// Add refund requests
		if (intval(get_option('marketking_enable_refunds_setting', 1)) === 1){
	    	$items = array_slice($items, 0, 4, true) +
	    	    array(get_option('marketking_refunds_endpoint_setting','refunds') => esc_html__( 'Refunds', 'marketking' )) + 
	    	    array_slice($items, 4, count($items)-4, true);
		}

	    return $items;

	}

	// Add custom endpoints
	function marketking_custom_endpoints() {
		
		// Add messages endpoints
		if (intval(get_option('marketking_enable_messages_setting', 1)) === 1){
			add_rewrite_endpoint( get_option('marketking_messages_endpoint_setting','messages'), EP_ROOT | EP_PAGES | EP_PERMALINK );
			add_rewrite_endpoint( get_option('marketking_message_endpoint_setting','message'), EP_ROOT | EP_PAGES | EP_PERMALINK );
		}

		// Add favorite endpoints
		if (intval(get_option('marketking_enable_favorite_setting', 1)) === 1){
			add_rewrite_endpoint( get_option('marketking_favorite_endpoint_setting','favorite'), EP_ROOT | EP_PAGES | EP_PERMALINK );
		}

		// Add refunds endpoints
		if (intval(get_option('marketking_enable_refunds_setting', 1)) === 1){
			add_rewrite_endpoint( get_option('marketking_refunds_endpoint_setting','refunds'), EP_ROOT | EP_PAGES | EP_PERMALINK );
			add_rewrite_endpoint( get_option('marketking_refund_endpoint_setting','refund'), EP_ROOT | EP_PAGES | EP_PERMALINK );

		}
		do_action('marketking_extend_endpoints');


	}

	function force_permalinks_rewrite() {

	    $this->marketking_custom_endpoints();
	    
	    if (apply_filters('marketking_flush_permalinks', true)){
	    	// Flush rewrite rules
	    	flush_rewrite_rules();
	    }
	    
	}

	function marketking_favorite_endpoint_content(){
		do_action('marketking_before_favorite_stores');

		echo do_shortcode('[marketking_favorite_stores]');
	}

	function marketking_refunds_endpoint_content(){
		?>
		<h2><?php esc_html_e('Refund Requests','marketking'); ?></h2><br>
		<table class="shop_table my_account_orders table table-striped">

		    <thead>
		        <tr>
		            <th><span><?php esc_html_e('Order','marketking');?></span></th>
		            <th><span><?php esc_html_e('Vendor','marketking');?></span></th>
		            <th class="marketking_refunds_table_type"><span><?php esc_html_e('Type','marketking');?></span></th>
		            <th class="marketking_refunds_table_status"><span><?php esc_html_e('Status','marketking');?></span></th>
		            <th></th>
		        </tr>
		    </thead>
		    <tbody>
		    	<?php
		    	$refund_endpoint_url = wc_get_endpoint_url(get_option('marketking_refund_endpoint_setting','refund'));
		    	$refunds_endpoint_url = wc_get_endpoint_url(get_option('marketking_refunds_endpoint_setting','refunds'));

		    	$posts_per_page = 10;

		    	$current_page = marketking()->get_pagenr_query_var() ? marketking()->get_pagenr_query_var() : 1;
		    	$max_results = count(get_posts( array( 
		    	    'post_type' => 'marketking_refund',
		    	    'numberposts' => -1,
		    	    'post_status'    => 'any',
		    	    'fields'    => 'ids',
		    	    'author'   => get_current_user_id(),
		    	)));
		    	$max_pages = $max_results/$posts_per_page;

		    	// get all refund requests
		    	$refund_requests = get_posts( array( 
		    	    'post_type' => 'marketking_refund',
		    	    'numberposts' => $posts_per_page,
		    	    'paged' => $current_page,
		    	    'post_status'    => 'any',
		    	    'fields'    => 'ids',
		    	    'author'   => get_current_user_id(),
		    	));

		    	foreach ($refund_requests as $request){
		    		$order_id = get_post_meta($request,'order_id', true);
		    		$order = wc_get_order($order_id);

		    		if ($order){
			    		$vendor_id = get_post_meta($request,'vendor_id', true);
			    		$vendor_store = marketking()->get_store_name_display($vendor_id);
			    		$vendor_store_link = marketking()->get_store_link($vendor_id);
			    		$value = get_post_meta($request,'value', true);
			    		$status = get_post_meta($request,'request_status', true);

			    		?>
		                <tr class="order">
			                <td>
			                	<?php echo esc_html__('Order','marketking').' '; ?><a href="<?php echo esc_attr($order->get_view_order_url());?>"><?php echo '#'.esc_html($order_id); ?></a>
			                </td>
			                <td>
			                    <a href="<?php echo esc_attr($vendor_store_link);?>"><?php echo esc_html($vendor_store);?></a>
			                </td>
			                <td class="marketking_refunds_table_type">
			                    <?php 
			                    if ($value === 'full'){
			                    	esc_html_e('Full Refund','marketking');
			                    } else if ($value === 'partial'){
			                    	esc_html_e('Partial Refund','marketking');
			                    }
			                    ?>                       
			                </td>
			                <td style="text-align:left; white-space:nowrap;" class="marketking_refunds_table_status">
			                	<?php
			                	if ($status === 'open'){
			                		esc_html_e('Open','marketking');
			                	} else if ($status === 'closed'){
			                		esc_html_e('Closed','marketking');
			                	} else if ($status === 'approved'){
			                		esc_html_e('Approved','marketking');
			                	} else if ($status === 'rejected'){
			                		esc_html_e('Denied','marketking');
			                	}
			                	?>
			                </td>
			                <td>
			                    <a href="<?php echo esc_attr($refund_endpoint_url).'?id='.esc_attr($request);?>" class="woocommerce-button button view"><?php esc_html_e('View','marketking');?></a>
			                </td>
			            </tr>
			    		<?php
			    	}
		    	}

		    	?>
               
            </tbody>
		</table>
		<div id="marketking_refunds_pagination">
			<?php
			if (intval($current_page) !== 1){
				?>
				<a href="<?php echo esc_attr($refunds_endpoint_url).'?pagenr='.($current_page-1);?>"><?php esc_html_e('Previous Page','marketking'); ?></a>
				<?php
			}

			if ($current_page < $max_pages){
				?>
				<a href="<?php echo esc_attr($refunds_endpoint_url).'?pagenr='.($current_page+1);?>"><?php esc_html_e('Next Page','marketking'); ?></a>
				<?php
			}
		?>
		</div>
		<?php
	}

	function marketking_refund_endpoint_content(){
		?>
		<h2><?php esc_html_e('Refund Request','marketking'); ?></h2><br>

		<?php
		$request = get_query_var('id');
		$order_id = get_post_meta($request,'order_id', true);
		$order = wc_get_order($order_id);
		$vendor_id = get_post_meta($request,'vendor_id', true);
		$vendor_store = marketking()->get_store_name_display($vendor_id);
		$vendor_store_link = marketking()->get_store_link($vendor_id);
		$value = get_post_meta($request,'value', true);
		$reason = get_post_meta($request,'reason', true);
		$status = get_post_meta($request,'request_status', true);

		$request_date = get_the_date('',$request);

		esc_html_e('Here are the details of your refund request:','marketking');
		?>
		<br><br>
		<table class="woocommerce-table shop_table">
	        <tbody>
	            <tr>
	                <td><strong><?php esc_html_e('Order','marketking');?></strong></td>
	                <td><?php echo esc_html__('Order','marketking').' '; ?><a href="<?php echo esc_attr($order->get_view_order_url());?>"><?php echo '#'.esc_html($order_id); ?></a></td>
	            </tr>
	            <tr>
	                <td><strong><?php esc_html_e('Type','marketking');?></strong></td>
	                <td><?php 
                    if ($value === 'full'){
                    	esc_html_e('Full Refund','marketking');
                    } else if ($value === 'partial'){
                    	esc_html_e('Partial Refund: ','marketking');
                    	$partialamount = get_post_meta($request, 'partialamount', true);
                    	echo wc_price($partialamount).' / '.wc_price($order->get_total());
                    }
                    ?></td>
	            </tr>
	            <tr>
	                <td><strong><?php esc_html_e('Vendor','marketking');?></strong></td>
	                <td>
	                	<a href="<?php echo esc_attr($vendor_store_link);?>"><?php echo esc_html($vendor_store);?></a>
	            	</td>
	            </tr>
	            <tr>
	                <td><strong><?php esc_html_e('Status','marketking');?></strong></td>
	                <td><strong><?php
                	if ($status === 'open'){
                		esc_html_e('Open','marketking');
                	} else if ($status === 'closed'){
                		esc_html_e('Closed','marketking');
                	} else if ($status === 'approved'){
                		esc_html_e('Approved','marketking');
                	} else if ($status === 'rejected'){
                		esc_html_e('Denied','marketking');
                	}
                	?></strong></td>
	            </tr>
	        </tbody>
	    </table>

	    <div id="marketking_myaccount_message_endpoint_container_top_header">
	    	<div class="marketking_myaccount_message_endpoint_container_top_header_item"><?php esc_html_e('Conversation','marketking'); ?> <span class="marketking_myaccount_message_endpoint_top_header_text_bold"></span></div>
	    	<div class="marketking_myaccount_message_endpoint_container_top_header_item"><?php esc_html_e('Date Started:','marketking'); ?> <span class="marketking_myaccount_message_endpoint_top_header_text_bold"><?php echo esc_html($request_date); ?></span></div>
	    </div>
	    <?php
	    	// get number of messages
	    	$nr_messages = get_post_meta ($request, 'b2bking_conversation_messages_number', true);
	    	?>
	    	<div id="b2bking_conversation_messages_container">
    		    <div class="b2bking_conversation_message b2bking_conversation_message_self">
    		    	<?php echo '<strong>'.esc_html__('Refund reason:','marketking').'</strong> '.nl2br($reason); ?>
    		    	<div class="b2bking_conversation_message_time">
    		    		<?php
    		    		echo wp_get_current_user()->user_login.' - ';
    		    		echo esc_html($request_date); ?>
    		    	</div>
    		    </div>
    		    <?php

	    		// loop through and display messages
	    		for ($i = 1; $i <= $nr_messages; $i++) {
	    		    // get message details
	    		    $message = get_post_meta ($request, 'b2bking_conversation_message_'.$i, true);
	    		    $author = get_post_meta ($request, 'b2bking_conversation_message_'.$i.'_author', true);
	    		    $time = get_post_meta ($request, 'b2bking_conversation_message_'.$i.'_time', true);
	    		    // check if message author is self, parent, or subaccounts
	    		    $current_user_id = get_current_user_id();
	    		    
	    		    if ($author === wp_get_current_user()->user_login){
	    		    	$self = ' b2bking_conversation_message_self';
	    		    } else {
	    		    	$self = '';
	    		    }
	    		    // build time string
    			    // if today
    			    if((time()-$time) < 86400){
    			    	// show time
    			    	$timestring = date_i18n( 'h:i A', $time+(get_option('gmt_offset')*3600) );
    			    } else if ((time()-$time) < 172800){
    			    // if yesterday
    			    	$timestring = 'Yesterday at '.date_i18n( 'h:i A', $time+(get_option('gmt_offset')*3600) );
    			    } else {
    			    // date
    			    	$timestring = date_i18n( get_option('date_format'), $time+(get_option('gmt_offset')*3600) ); 
    			    }
	    		    ?>
	    		    <div class="b2bking_conversation_message <?php echo esc_attr($self); ?>">
	    		    	<?php echo nl2br($message); ?>
	    		    	<div class="b2bking_conversation_message_time">
	    		    		<?php
	    		    		 $userobj = get_user_by('login', $author);
    				        if (marketking()->is_vendor($userobj->ID)){
    				        	$author = marketking()->get_store_name_display($userobj->ID);
    				        }

	    		    		?>
	    		    		<?php echo esc_html($author).' - '; ?>
	    		    		<?php echo esc_html($timestring); ?>
	    		    	</div>
	    		    </div>
	    		    <?php
	    		}
	    		?>
	    	</div>

	    	<?php
	    	if ($status === 'open'){
	    		?>
	    			<textarea name="b2bking_conversation_user_new_message" id="b2bking_conversation_user_new_message"></textarea><br />
	    			<input type="hidden" id="b2bking_conversation_id" value="<?php echo esc_attr($request); ?>">
	    			<div class="marketking_myaccount_message_endpoint_bottom">
	    		    	<button id="b2bking_conversation_message_submit" class="marketking_myaccount_message_endpoint_button" type="button">
	    		    		<svg class="marketking_myaccount_message_endpoint_button_icon" xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 21 21">
	    				  	<path fill="#fff" d="M5.243 12.454h9.21v4.612c0 .359-.122.66-.368.906-.246.245-.567.377-.964.396H5.243L1.955 21v-2.632h-.651a1.19 1.19 0 01-.907-.396A1.414 1.414 0 010 17.066V8.52c0-.358.132-.67.397-.934.264-.264.567-.387.907-.368h3.939v5.236zM19.696.002c.378 0 .69.123.936.368.245.245.368.566.368.962V9.85c0 .359-.123.66-.368.906a1.37 1.37 0 01-.936.396h-.652v2.632l-3.287-2.632H6.575v-9.82c0-.377.123-.698.368-.962.246-.264.558-.387.936-.368h11.817z"/>
	    					</svg>
	    		    		<?php esc_html_e('Send Message','marketking'); ?>
	    		    	</button>
	    			</div>
	    		<?php
	    	}
	    	
	}

	// messages endpoint content
	function marketking_messages_endpoint_content() {

		// Get user login
		$currentuser = wp_get_current_user();
		$currentuserlogin = $currentuser -> user_login;

		$account_type = get_user_meta($currentuser->ID, 'marketking_account_type', true);
		if ($account_type === 'subaccount'){
			// Check if user has permission to view all account messages
			$permission_view_account_messages = filter_var(get_user_meta($currentuser->ID, 'marketking_account_permission_view_messages', true), FILTER_VALIDATE_BOOLEAN); 
			if ($permission_view_account_messages === true){
				// for all intents and purposes set current user as the subaccount parent
				$parent_user_id = get_user_meta($currentuser->ID, 'marketking_account_parent', true);
				$currentuser = get_user_by('id', $parent_user_id);
				$currentuserlogin = $currentuser -> user_login;
			}
		}

		
		$accounts_login_array = array($currentuserlogin);

		// Add subaccounts to accounts array
		$subaccounts_list = get_user_meta($currentuser->ID, 'marketking_subaccounts_list', true);
		$subaccounts_list = explode(',', $subaccounts_list);
		$subaccounts_list = array_filter($subaccounts_list);
		foreach ($subaccounts_list as $subaccount_id){
			$accounts_login_array[$subaccount_id] = get_user_by('id', $subaccount_id) -> user_login;
		}

		

	    // Define custom query parameters
	    $custom_query_args = array( 'post_type' => 'marketking_message', // only messages
	    					'posts_per_page' => 8,
					        'meta_query'=> array(	// only the specific user's messages
					        	'relation' => 'OR',
			                    array(
			                        'key' => 'marketking_message_user',
			                        'value' => $accounts_login_array, 
			                        'compare' => 'IN'
			                    ),
			                    array(
			                        'key' => 'marketking_message_message_1_author',
			                        'value' => $accounts_login_array, 
			                        'compare' => 'IN'
			                    )

			                ));

	    // Get current page and append to custom query parameters array
	    $custom_query_args['paged'] = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	    // Instantiate custom query
	    $custom_query = new WP_Query( $custom_query_args );

	    // Pagination fix
	    $temp_query = NULL;
	    $wp_query   = NULL;
	    $wp_query   = $custom_query;

	    // Get message Endpoint URL
	    $endpointurl = wc_get_endpoint_url(get_option('marketking_message_endpoint_setting','message'));

		?>
		<div id="marketking_myaccount_messages_container">
			<div id="marketking_myaccount_messages_container_top">
				<div id="marketking_myaccount_messages_title">
					<?php esc_html_e('Messages','marketking'); ?>
				</div>
				
			</div>



			<?php
			// Display each message
			// Output custom query loop
			if ( $custom_query->have_posts() ) {
			    while ( $custom_query->have_posts() ) {
			        $custom_query->the_post();
			        global $post;

			        $message_title = $post->post_title;
			        $message_type = get_post_meta($post->ID, 'marketking_message_type', true);
			        $username = get_post_meta($post->ID, 'marketking_message_user', true);

			        $userobj = get_user_by('login', $username);
			        if ($userobj){
				        if (marketking()->is_vendor($userobj->ID)){
				        	$username = marketking()->get_store_name_display($userobj->ID);
				        }
				    }

			        $nr_messages = get_post_meta ($post->ID, 'marketking_message_messages_number', true);
			        $last_reply_time = intval(get_post_meta ($post->ID, 'marketking_message_message_'.$nr_messages.'_time', true));

			        // build time string
				    // if today
				    if((time()-$last_reply_time) < 86400){
				    	// show time
				    	$message_last_reply = date_i18n( 'h:i A', $last_reply_time+(get_option('gmt_offset')*3600) );
				    } else if ((time()-$last_reply_time) < 172800){
				    // if yesterday
				    	$message_last_reply = 'Yesterday at '.date_i18n( 'h:i A', $last_reply_time+(get_option('gmt_offset')*3600) );
				    } else {
				    // date
				    	$message_last_reply = date_i18n( get_option('date_format'), $last_reply_time+(get_option('gmt_offset')*3600) ); 
				    }

			        ?>
	    			<div class="marketking_myaccount_individual_message_container">
	                    <div class="marketking_myaccount_individual_message_top">
	                    	<div class="marketking_myaccount_individual_message_top_item"><?php esc_html_e('Title','marketking'); ?></div>
	                    	<div class="marketking_myaccount_individual_message_top_item"><?php esc_html_e('Type','marketking'); ?></div>
	                    	<div class="marketking_myaccount_individual_message_top_item"><?php esc_html_e('Vendor','marketking'); ?></div>
	                    	<?php do_action('marketking_myaccount_messages_items_title', $post->ID); ?>
	                    	<div class="marketking_myaccount_individual_message_top_item"><?php esc_html_e('Last Reply','marketking'); ?></div>
	                    </div>
	                    <div class="marketking_myaccount_individual_message_content">
	                    	<div class="marketking_myaccount_individual_message_content_item"><?php echo esc_html($message_title); ?></div>
	                    	<div class="marketking_myaccount_individual_message_content_item"><?php
	                    	switch ($message_type) {
	                    	  case "inquiry":
	                    	    esc_html_e('inquiry','marketking');
	                    	    break;
	                    	  case "message":
	                    	    esc_html_e('message','marketking');
	                    	    break;
	                    	  case "support":
	                    	    esc_html_e('support','marketking');
	                    	    break;
	                    	  case "quote":
	                    	    esc_html_e('quote','marketking');
	                    	    break;
	                    	}
	                    	?></div>
	                    	<div class="marketking_myaccount_individual_message_content_item"><?php echo esc_html($username); ?></div>
	                    	<?php do_action('marketking_myaccount_messages_items_content', $post->ID); ?>
	                    	<div class="marketking_myaccount_individual_message_content_item"><?php echo esc_html($message_last_reply); ?></div>
	                    </div>
	                    <div class="marketking_myaccount_individual_message_bottom">
	                    	<a href="<?php echo esc_url(add_query_arg('id',$post->ID,$endpointurl)); ?>">
	                        	<button class="marketking_myaccount_view_message_button" type="button">
	                        		<svg class="marketking_myaccount_view_message_button_icon" xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 21 21">
	                        		  <path fill="#fff" d="M5.243 12.454h9.21v4.612c0 .359-.122.66-.368.906-.246.245-.567.377-.964.396H5.243L1.955 21v-2.632h-.651a1.19 1.19 0 01-.907-.396A1.414 1.414 0 010 17.066V8.52c0-.358.132-.67.397-.934.264-.264.567-.387.907-.368h3.939v5.236zM19.696.002c.378 0 .69.123.936.368.245.245.368.566.368.962V9.85c0 .359-.123.66-.368.906a1.37 1.37 0 01-.936.396h-.652v2.632l-3.287-2.632H6.575v-9.82c0-.377.123-.698.368-.962.246-.264.558-.387.936-.368h11.817z"/>
	                        		</svg>
	                        		<?php esc_html_e('View conversation','marketking'); ?>
	                        	</button>
	                        </a>
	                    </div>
	    	        </div>

			        <?php

			    }
			} else {
				wc_print_notice(esc_html__('No messages exist.', 'marketking'), 'notice');
			}

			?>

		</div>

		<?php
		
	    // Reset postdata
	    wp_reset_postdata();
	    ?>
	   	<div class="marketking_myaccount_messages_pagination_container">
		    <div class="marketking_myaccount_messages_pagination_button marketking_newer_messages_button">
		    	<?php previous_posts_link( esc_html__('← Newer messages','marketking') ); ?>
		    </div>
		    <div class="marketking_myaccount_messages_pagination_button marketking_older_messages_button">
		    	<?php next_posts_link( esc_html__('Older messages →','marketking'), $custom_query->max_num_pages ); ?>
		    </div>
		</div>
	    <?php

	    // Reset main query object
	    $wp_query = NULL;
	    $wp_query = $temp_query;

	}


	// Individual message endpoint
	function marketking_message_endpoint_content() {

		$message_id = sanitize_text_field( $_GET['id'] );
		$message_title = get_the_title($message_id);
		$message_type = get_post_meta($message_id, 'marketking_message_type',true);
	    $starting_time = intval(get_post_meta ($message_id, 'marketking_message_message_1_time', true));

	    // build time string
	    // if today
	    if((time()-$starting_time) < 86400){
	    	// show time
	    	$message_started_time = date_i18n( 'h:i A', $starting_time+(get_option('gmt_offset')*3600));
	    } else if ((time()-$starting_time) < 172800){
	    // if yesterday
	    	$message_started_time = 'Yesterday at '.date_i18n( 'h:i A', $starting_time+(get_option('gmt_offset')*3600) );
	    } else {
	    // date
	    	$message_started_time = date_i18n( get_option('date_format'), $starting_time+(get_option('gmt_offset')*3600) ); 
	    }

		// Get messages Endpoint URL
		$endpointurl = wc_get_endpoint_url(get_option('marketking_messages_endpoint_setting','messages'));

		?>
		<div id="marketking_myaccount_message_endpoint_container">
			<div id="marketking_myaccount_message_endpoint_container_top">
				<div id="marketking_myaccount_message_endpoint_title">
					<?php echo esc_html($message_title); ?>
				</div>
				<a href="<?php echo esc_url($endpointurl); ?>">
					<button type="button">
						<?php esc_html_e('←  Go Back','marketking'); ?>
					</button>
				</a>
			</div>
			<div id="marketking_myaccount_message_endpoint_container_top_header">
				<div class="marketking_myaccount_message_endpoint_container_top_header_item"><?php esc_html_e('Type:','marketking'); ?> <span class="marketking_myaccount_message_endpoint_top_header_text_bold"><?php echo esc_html($message_type); ?></span></div>
				<div class="marketking_myaccount_message_endpoint_container_top_header_item"><?php esc_html_e('Date Started:','marketking'); ?> <span class="marketking_myaccount_message_endpoint_top_header_text_bold"><?php echo esc_html($message_started_time); ?></span></div>
			</div>
		<?php
		
		// Check user permission against message user meta
		$user = get_post_meta ($message_id, 'marketking_message_user', true);
		$user2 = get_post_meta ($message_id, 'marketking_message_message_1_author', true);

		// build array of current login + subaccount logins
		$current_user = wp_get_current_user();
		$subaccounts_list = get_user_meta($current_user->ID, 'marketking_subaccounts_list', true);
		$subaccounts_list = explode (',',$subaccounts_list);
		$subaccounts_list = array_filter($subaccounts_list);
		$logins_array = array($current_user->user_login);
		foreach($subaccounts_list as $subaccount_id){
			$username = get_user_by('id', $subaccount_id)->user_login;
			$logins_array[$subaccount_id] = $username;
		}

		// if message user is part of the logins array (user + subaccounts), give permission
		if (in_array($user, $logins_array) || in_array($user2, $logins_array)){
			// Display message

			// get number of messages
			$nr_messages = get_post_meta ($message_id, 'marketking_message_messages_number', true);
			?>
			<div id="marketking_message_messages_container">
				<?php	
				// loop through and display messages
				for ($i = 1; $i <= $nr_messages; $i++) {
				    // get message details
				    $message = get_post_meta ($message_id, 'marketking_message_message_'.$i, true);
				    $author = get_post_meta ($message_id, 'marketking_message_message_'.$i.'_author', true);
				    $time = get_post_meta ($message_id, 'marketking_message_message_'.$i.'_time', true);
				    // check if message author is self, parent, or subaccounts
				    $current_user_id = get_current_user_id();
				    $subaccounts_list = get_user_meta($current_user_id,'marketking_subaccounts_list', true);
				    $subaccounts_list = explode(',', $subaccounts_list);
				    $subaccounts_list = array_filter($subaccounts_list);
				    array_push($subaccounts_list, $current_user_id);

					// add parent account+all subaccounts lists
				    $account_type = get_user_meta($current_user_id, 'marketking_account_type', true);
				    if ($account_type === 'subaccount'){
						$parent_account = get_user_meta($current_user_id, 'marketking_account_parent', true);
			    		$parent_subaccounts_list = explode(',', get_user_meta($parent_account, 'marketking_subaccounts_list', true));
			    		$parent_subaccounts_list = array_filter($parent_subaccounts_list); // filter blank, null, etc.
			    		array_push($parent_subaccounts_list, $parent_account); // add parent itself to form complete parent accounts list

			    		$subaccounts_list = array_merge($subaccounts_list, $parent_subaccounts_list);
				    }



				    foreach ($subaccounts_list as $user){
				    	$subaccounts_list[$user] = get_user_by('id', $user)->user_login;
				    }
				    if (in_array($author, $subaccounts_list)){
				    	$self = ' marketking_message_message_self';
				    } else {
				    	$self = '';
				    }
				    // build time string
					    // if today
					    if((time()-$time) < 86400){
					    	// show time
					    	$timestring = date_i18n( 'h:i A', $time+(get_option('gmt_offset')*3600) );
					    } else if ((time()-$time) < 172800){
					    // if yesterday
					    	$timestring = 'Yesterday at '.date_i18n( 'h:i A', $time+(get_option('gmt_offset')*3600) );
					    } else {
					    // date
					    	$timestring = date_i18n( get_option('date_format'), $time+(get_option('gmt_offset')*3600) ); 
					    }
				    ?>
				    <div class="marketking_message_message <?php echo esc_attr($self); ?>">
				    	<?php echo nl2br($message); ?>
				    	<div class="marketking_message_message_time">
				    		<?php
				    		 $userobj = get_user_by('login', $author);
						        if (marketking()->is_vendor($userobj->ID)){
						        	$author = marketking()->get_store_name_display($userobj->ID);
						        }

				    		?>
				    		<?php echo esc_html($author).' - '; ?>
				    		<?php echo esc_html($timestring); ?>
				    	</div>
				    </div>
				    <?php
				}
				?>
			</div>
			<textarea name="marketking_message_user_new_message" id="marketking_message_user_new_message"></textarea><br />
			<input type="hidden" id="marketking_message_id" value="<?php echo esc_attr($message_id); ?>">
			<div class="marketking_myaccount_message_endpoint_bottom">
		    	<button id="marketking_message_message_submit" class="marketking_myaccount_message_endpoint_button" type="button">
		    		<svg class="marketking_myaccount_message_endpoint_button_icon" xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 21 21">
				  	<path fill="#fff" d="M5.243 12.454h9.21v4.612c0 .359-.122.66-.368.906-.246.245-.567.377-.964.396H5.243L1.955 21v-2.632h-.651a1.19 1.19 0 01-.907-.396A1.414 1.414 0 010 17.066V8.52c0-.358.132-.67.397-.934.264-.264.567-.387.907-.368h3.939v5.236zM19.696.002c.378 0 .69.123.936.368.245.245.368.566.368.962V9.85c0 .359-.123.66-.368.906a1.37 1.37 0 01-.936.396h-.652v2.632l-3.287-2.632H6.575v-9.82c0-.377.123-.698.368-.962.246-.264.558-.387.936-.368h11.817z"/>
					</svg>
		    		<?php esc_html_e('Send Message','marketking'); ?>
		    	</button>
			</div>
			<?php
		} else {
			esc_html_e('message does not exist!','marketking'); // or user does not have permission
		}
		echo '</div>';

	}

	function add_vendor_offers_public($b2bking_offer_id){
		// get author / vendor ID
		$offer_author = get_post_field ('post_author', $b2bking_offer_id);

		if (marketking()->is_vendor($offer_author)){
			?>
			<div class="b2bking_myaccount_individual_offer_custom_text_vendor"><strong><?php esc_html_e('Vendor: ','marketking'); echo esc_html(marketking()->get_store_name_display($offer_author)); ?></strong></div>
			<?php
		}
	}

	function remove_offer_product($order_id){
	    $order = new WC_Order( $order_id );
        $order_items = $order->get_items();

        $offer_products = get_option('b2bking_marketking_hidden_offer_product_ids', 'string');
        if ($offer_products !== 'string' && !empty($offer_products)){
        	$offer_products = explode(',', $offer_products);
        	$clean_offer_products = array_unique(array_filter($offer_products));
        } else {
        	$clean_offer_products = array();
        }
        $deleted_items = array();
        $hiddenstring = '';
        foreach ($order_items as $order_item){
        	if (in_array($order_item->get_product_id(), $clean_offer_products)){
        		// delete temporary offer
        		wp_delete_post($order_item->get_product_id());
        		array_push($deleted_items,$order_item->get_product_id());
        	}
        }
        foreach ($clean_offer_products as $product_id){
        	if (!in_array($product_id, $deleted_items)){
        		$hiddenstring.=','.$product_id;
        	}
        }
        update_option('b2bking_marketking_hidden_offer_product_ids', $hiddenstring);

	}

	function exclude_offers_from_rules($offer_id, $product_id){
		$offer_products = get_option('b2bking_marketking_hidden_offer_product_ids', 'string');
		$clean_offer_products = array();
		if ($offer_products !== 'string' && !empty($offer_products)){
			$offer_products = explode(',', $offer_products);
			$clean_offer_products = array_unique(array_filter($offer_products));
		}
		if (in_array($product_id, $clean_offer_products)){
			// if current product is offer, return product id
			return $product_id;
		} else {
			return $offer_id;
		}
	}

	function b2bking_add_query_vars_filter( $vars ) {
	  $vars[] = "vendor";
	  return $vars;
	}

	function add_vendor_to_new_message(){
		?>
		<div class="b2bking_myaccount_new_conversation_content_element">
			<div class="b2bking_myaccount_new_conversation_content_element_text"><?php esc_html_e('Vendor','marketking'); ?></div>
			<select id="b2bking_myaccount_conversation_vendor">
				<option value="store"><?php esc_html_e('Store','marketking'); ?></option>
				<?php
				$vendors = marketking()->get_all_vendors();	

				$vendorurl = get_query_var('vendor');
				foreach($vendors as $vendor){
					$vendor = $vendor->ID;
					$store_name = marketking()->get_store_name_display($vendor);
					echo '<option value="'.esc_attr($vendor).'" '.selected($vendor,$vendorurl,false).'>'.esc_html($store_name).'</option>';
				}
				?>
			</select>
		</div>
		<?php
	}

	function add_vendor_to_conversations_title($conversation_id){
		?>
		<div class="b2bking_myaccount_individual_conversation_top_item"><?php esc_html_e('Vendor','marketking'); ?></div>
		<?php
	}

	function add_vendor_to_conversations_content($conversation_id){
		// get vendor
		$vendor = get_post_meta($conversation_id,'b2bking_conversation_vendor', true);
		if ($vendor === null || empty($vendor) || $vendor === 'store'){
			$vendor = esc_html__('store','marketking');
		}
		?>
		<div class="b2bking_myaccount_individual_conversation_content_item"><?php echo esc_html($vendor); ?></div>
		<?php
	}

	function enqueue_public_resources(){

		// scripts and styles already registered by default
		wp_enqueue_script('jquery'); 

		wp_enqueue_style('marketkingpro_main_style', plugins_url('../includes/assets/css/style.css', __FILE__), $deps = array(), filemtime( plugin_dir_path( __FILE__ ) . '../includes/assets/css/style.css' ));

    }

	
    	
}

