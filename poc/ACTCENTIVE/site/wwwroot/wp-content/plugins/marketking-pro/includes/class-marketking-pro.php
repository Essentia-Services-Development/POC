<?php

class Marketkingpro {

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

			add_action( 'plugins_loaded', function(){
				if ( class_exists( 'woocommerce' ) && defined('MARKETKINGCORE_DIR') ) {

					require_once ( MARKETKINGPRO_DIR . 'includes/class-marketking-vendor-shipping.php' );

					// Advertising
					if (intval(get_option('marketking_enable_advertising_setting', 0)) === 1){

						// add credit to balance when order with credit is completed
						add_action( 'woocommerce_order_status_changed', array( $this, 'payment_complete'), 10, 3);

						// display advertised products on top
						add_action( 'posts_results', array( $this, 'display_advertised_products_on_top' ), 10, 2 );
						add_filter( 'woocommerce_shortcode_products_query_results', array($this, 'display_advertised_products_on_top_vendor'), 10, 2);
					}

					// Coupons
					// Add custom checkbox for automatically including new published products
					add_action('woocommerce_coupon_options_usage_restriction', array($this, 'marketking_coupon_options'));
					add_action('woocommerce_coupon_options_save', array($this, 'marketking_coupon_options_save'));
					add_action('marketking_add_product_first', array($this, 'marketking_auto_include_product'), 10, 2);
					
					// Stripe
					if (intval(get_option( 'marketking_enable_stripe_setting', 1 )) === 1){

						add_filter( 'woocommerce_payment_gateways',  array( $this, 'marketking_add_stripe_gateway' ) );
					}

					/* Shipping */
					if (intval(get_option( 'marketking_enable_shipping_setting', 1 )) === 1){
						add_filter( 'woocommerce_shipping_methods', array($this, 'register_vendor_shipping_method' ));
						add_filter( 'woocommerce_cart_shipping_packages', array($this ,'split_cart_shipping_packages'));
						// split cart shipping package names
						add_filter( 'woocommerce_shipping_package_name', array($this, 'split_cart_shipping_packages_names'), 10, 3 );
						add_action( 'woocommerce_checkout_create_order_shipping_item', array($this, 'add_shipping_pack_meta'), 10, 4 );		

						// allow admin to set a method that's not enabled for all vendors
						add_filter( 'woocommerce_package_rates', array($this, 'marketking_disable_shipping_methods'), 10, 2 );

					}

					/* Badges */
					if (intval(get_option( 'marketking_enable_badges_setting', 1 )) === 1){
						add_action( 'init', array($this, 'marketking_register_post_type_badge'), 0 );
						add_action( 'add_meta_boxes', array($this, 'marketking_badge_metaboxes') );
						add_action( 'save_post', array($this, 'marketking_save_badge_metaboxes'), 10, 1);
						add_filter( 'manage_marketking_badge_posts_columns', array($this, 'marketking_add_columns_group_menu_badge') );
						add_action( 'manage_marketking_badge_posts_custom_column' , array($this, 'marketking_columns_group_data_badge'), 10, 2 );
					}

					/* Memberships */
					if (intval(get_option( 'marketking_enable_memberships_setting', 1 )) === 1){
						add_action( 'init', array($this, 'marketking_register_post_type_mpack'), 0 );
						add_action( 'add_meta_boxes', array($this, 'marketking_mpack_metaboxes') );
						add_action( 'save_post', array($this, 'marketking_save_mpack_metaboxes'), 10, 1);
						add_filter( 'manage_marketking_mpack_posts_columns', array($this, 'marketking_add_columns_group_menu_mpack') );
						add_action( 'manage_marketking_mpack_posts_custom_column' , array($this, 'marketking_columns_group_data_mpack'), 10, 2 );
						// move vendor to group on order status completed
						add_action( 'woocommerce_order_status_completed', array($this, 'process_membership_completed'), 10, 1);
					}


					/* Import Export */
					// only if current user is vendor, prevent issues on admin side
					// only if not admin
					if (!current_user_can('activate_plugins') && !current_user_can( 'manage_woocommerce')){
						if (intval(get_option( 'marketking_enable_importexport_setting', 1 )) === 1){
							add_action( 'wp_ajax_woocommerce_do_ajax_product_export', array( $this, 'do_ajax_product_export' ) );
							add_action( 'template_redirect', array( $this, 'download_export_file' ) );

							// only the vendor's products
							add_filter( 'woocommerce_product_export_product_query_args', array($this, 'vendor_products_export'), 10, 1);
							
							add_action( 'wp_ajax_woocommerce_do_ajax_product_import', array( $this, 'do_ajax_product_import' ) );
							// Protect other vendor products
							add_filter( 'woocommerce_product_import_process_item_data', [ $this, 'protect_other_vendor_product_on_csv' ] );
							// Do not allow featured items
							add_filter( 'woocommerce_product_import_process_item_data', [ $this, 'feature_column_to_false' ] );
							// Prevent import if max product nr reached
							add_action( 'woocommerce_product_import_before_process_item', [$this, 'prevent_import_max_products']);
							// Other protections: mapping options
							add_filter ('woocommerce_csv_product_import_mapping_options', [$this, 'protect_mapping_options'], 10, 2);
						}
					}


					/* Seller Verification */
					if (intval(get_option( 'marketking_enable_verification_setting', 1 )) === 1){
						add_action( 'init', array($this, 'marketking_register_post_type_verification_item'), 0 );
						add_action( 'add_meta_boxes', array($this, 'marketking_vitem_metaboxes') );
						add_action( 'save_post', array($this, 'marketking_save_vitem_metaboxes'), 10, 1);

						add_action( 'init', array($this, 'marketking_register_post_type_verification_request'), 0 );

						add_filter( 'manage_marketking_vitem_posts_columns', array($this, 'marketking_add_columns_group_menu_vitem') );
						add_action( 'manage_marketking_vitem_posts_custom_column' , array($this, 'marketking_columns_group_data_vitem'), 10, 2 );

						add_filter( 'manage_marketking_vreq_posts_columns', array($this, 'marketking_add_columns_group_menu_vreq') );
						add_action( 'manage_marketking_vreq_posts_custom_column' , array($this, 'marketking_columns_group_data_vreq'), 10, 2 );

						add_filter('post_row_actions',array($this, 'marketking_vreq_row_action'), 10, 2);

					}

					/* Refunds */
					if (intval(get_option( 'marketking_enable_refunds_setting', 1 )) === 1){
						add_action( 'init', array($this, 'marketking_register_post_type_refund'), 0 );
						add_filter( 'manage_marketking_refund_posts_columns', array($this, 'marketking_add_columns_group_menu_refund') );
						add_action( 'manage_marketking_refund_posts_custom_column' , array($this, 'marketking_columns_group_data_refund'), 10, 2 );
						add_filter('post_row_actions',array($this, 'marketking_refund_row_action'), 10, 2);
					}

					/* Abuse Reports */
					if (intval(get_option( 'marketking_enable_abusereports_setting', 1 )) === 1){
						add_action( 'init', array($this, 'marketking_register_post_type_abuse'), 0 );
						add_filter( 'manage_marketking_abuse_posts_columns', array($this, 'marketking_add_columns_group_menu_abuse') );
						add_action( 'manage_marketking_abuse_posts_custom_column' , array($this, 'marketking_columns_group_data_abuse'), 10, 2 );
					}

					/* Announcements */
					// Disable Guternberg Editor on Post Type
					add_filter('use_block_editor_for_post_type', array($this, 'disable_gutenberg'), 10, 2);

					if (intval(get_option( 'marketking_enable_announcements_setting', 1 )) === 1){
						add_action( 'init', array($this, 'marketking_register_post_type_announcement'), 0 );
						add_action( 'add_meta_boxes', array($this, 'marketking_announcement_metaboxes') );
						// Save post and send emails
						add_action( 'save_post', array($this, 'marketking_save_announcement_metaboxes'), 10, 1);

						add_filter( 'manage_marketking_announce_posts_columns', array($this, 'marketking_add_columns_group_menu_announcement') );
						add_action( 'manage_marketking_announce_posts_custom_column' , array($this, 'marketking_columns_group_data_announcement'), 10, 2 );
					}

					/* Seller Docs */
					if (intval(get_option( 'marketking_enable_vendordocs_setting', 1 )) === 1){
						add_action( 'init', array($this, 'marketking_register_post_type_docs'), 0 );
						add_action( 'add_meta_boxes', array($this, 'marketking_docs_metaboxes') );
						// Save post and send emails
						add_action( 'save_post', array($this, 'marketking_save_docs_metaboxes'), 10, 1);

						add_filter( 'manage_marketking_docs_posts_columns', array($this, 'marketking_add_columns_group_menu_docs') );
						add_action( 'manage_marketking_docs_posts_custom_column' , array($this, 'marketking_columns_group_data_docs'), 10, 2 );
					}

					/* Messages */
					if (intval(get_option( 'marketking_enable_messages_setting', 1 )) === 1){
						// Messages Count
						add_action( 'admin_head', array( $this, 'marketking_messages_menu_order_count' ) );
						add_action( 'init', array($this, 'marketking_register_post_type_message'), 0 );
						add_action( 'add_meta_boxes', array($this, 'marketking_message_metaboxes') );
						add_action( 'save_post', array($this, 'marketking_save_message_metaboxes'), 10, 1);
						
						add_filter( 'manage_marketking_message_posts_columns', array($this, 'marketking_add_columns_group_menu_message') );
						add_action( 'manage_marketking_message_posts_custom_column' , array($this, 'marketking_columns_group_data_message'), 10, 2 );
					}

					/* Vendor Groups */
					add_action( 'init', array($this, 'marketking_register_post_type_vendor_groups'), 0 );
					add_action( 'add_meta_boxes', array($this, 'marketking_groups_metaboxes') );
					// save groups + save order / order assigned
					add_action( 'save_post', array($this, 'marketking_save_groups_metaboxes'), 10, 1);
					add_filter( 'manage_marketking_group_posts_columns', array($this, 'marketking_add_columns_group_menu') );
					add_action( 'manage_marketking_group_posts_custom_column' , array($this, 'marketking_columns_group_data'), 10, 2 );
					// enable wc_help_tip and others in group post
					add_filter('woocommerce_screen_ids', [ $this, 'set_wc_screen_ids' ] );

					// taxable products
					//add_filter('woocommerce_product_is_taxable' [$this, 'group_products_non_taxable'], 10, 2);
					add_filter( 'woocommerce_product_is_taxable', array($this, 'group_products_non_taxable'), 10, 2);


					/* Group Rules */
					// Register new post type
					add_action( 'init', array($this, 'marketking_register_post_type_group_rules'), 0 );
					// Add metaboxes to rules
					add_action( 'add_meta_boxes', array($this, 'marketking_group_rules_metaboxes') );
					// Save metaboxes
					add_action('save_post', array($this, 'marketking_save_group_rules_metaboxes'), 10, 1);
					add_filter( 'manage_marketking_grule_posts_columns', array($this, 'marketking_add_columns_grule_menu') );
					add_action( 'manage_marketking_grule_posts_custom_column' , array($this, 'marketking_columns_grule_data'), 10, 2 );					

					/* Commission Rules */
					if (intval(get_option( 'marketking_enable_complexcommissions_setting', 1 )) === 1){

						// Register new post type
						add_action( 'init', array($this, 'marketking_register_post_type_commission_rules'), 0 );
						// Add metaboxes to rules
						add_action( 'add_meta_boxes', array($this, 'marketking_rules_metaboxes') );
						// Save metaboxes
						add_action('save_post', array($this, 'marketking_save_rules_metaboxes'), 10, 1);
						add_filter( 'manage_marketking_rule_posts_columns', array($this, 'marketking_add_columns_group_menu_rules') );
						add_action( 'manage_marketking_rule_posts_custom_column' , array($this, 'marketking_columns_group_data_rules'), 10, 2 );
					}

					/* Invoices */
					// pdf invoices & packings slips
					add_filter('wpo_wcpdf_shop_name', array($this,'invoice_shop_name_filter'), 10, 2);
					add_filter('wpo_wcpdf_shop_address', array($this,'invoice_shop_address_filter'), 10, 2);
					add_filter('wpo_wcpdf_header_logo_img_element', array($this,'invoice_shop_logo_filter'), 10, 3);
					// webtoffee invoices
					add_filter('wf_pklist_alter_shipping_from_address', array($this,'webtoffe_invoice_from'), 10, 3);
					// change logo
					add_filter('wf_pklist_alter_settings', array($this,'webtoffe_logo_settings'), 10, 2);
					add_filter('wf_module_generate_template_html', array($this,'webtoffe_logo_settings2'), 100, 6);

					// Stripe Integration
					// Handle non-connected vendors
					add_action('woocommerce_after_checkout_validation', array($this,'handle_non_connected_vendors'), 10, 2);
					// Show transaction ID and link in backend
					add_filter('woocommerce_get_transaction_url', array($this, 'filter_stripe_transaction_url_backend'), 1000, 3);
					add_filter('woocommerce_gateway_title', array($this,'charge_id_backend_order'), 10, 2);

					// Subscription created attribute to vendor
					add_action('woocommerce_checkout_subscription_created', array($this, 'attribute_subscription_to_vendor'), 10, 3);



				}
			});

			// Handle Ajax Requests
			if ( wp_doing_ajax() ){

				/* Shipping */		
				add_action( 'wp_ajax_marketking_add_shipping_method_vendor', array($this, 'marketking_add_shipping_method_vendor') );
				add_action( 'wp_ajax_nopriv_marketking_add_shipping_method_vendor', array($this, 'marketking_add_shipping_method_vendor') );

				add_action( 'wp_ajax_marketking_delete_shipping_method_vendor', array($this, 'marketking_delete_shipping_method_vendor') );
				add_action( 'wp_ajax_nopriv_marketking_delete_shipping_method_vendor', array($this, 'marketking_delete_shipping_method_vendor') );

				add_action( 'wp_ajax_marketking_configure_shipping_method_retrieve', array($this, 'marketking_configure_shipping_method_retrieve') );
				add_action( 'wp_ajax_nopriv_marketking_configure_shipping_method_retrieve', array($this, 'marketking_configure_shipping_method_retrieve') );

				add_action( 'wp_ajax_marketking_configure_shipping_method_save', array($this, 'marketking_configure_shipping_method_save') );
				add_action( 'wp_ajax_nopriv_marketking_configure_shipping_method_save', array($this, 'marketking_configure_shipping_method_save') );

				add_action( 'wp_ajax_marketking_enable_disable_shipping_method', array($this, 'marketking_enable_disable_shipping_method') );
				add_action( 'wp_ajax_nopriv_marketking_enable_disable_shipping_method', array($this, 'marketking_enable_disable_shipping_method') );

				// Advertising credit
				add_action( 'wp_ajax_marketkingaddcredit', array($this, 'marketkingaddcredit') );
				add_action( 'wp_ajax_nopriv_marketkingaddcredit', array($this, 'marketkingaddcredit') );

				add_action( 'wp_ajax_marketking_purchase_ad', array($this, 'marketking_purchase_ad') );
				add_action( 'wp_ajax_nopriv_marketking_purchase_ad', array($this, 'marketking_purchase_ad') );

				//remove ad
				add_action( 'wp_ajax_marketking_remove_advertise_admin', array($this, 'marketking_remove_advertise_admin') );
				add_action( 'wp_ajax_nopriv_marketking_remove_advertise_admin', array($this, 'marketking_remove_advertise_admin') );
				// add ad
				add_action( 'wp_ajax_marketking_add_advertise_admin', array($this, 'marketking_add_advertise_admin') );
				add_action( 'wp_ajax_nopriv_marketking_add_advertise_admin', array($this, 'marketking_add_advertise_admin') );
				

				/* Membership */
				// Select Plan
				add_action( 'wp_ajax_marketking_member_select_plan', array($this, 'marketking_member_select_plan') );
				add_action( 'wp_ajax_nopriv_marketking_member_select_plan', array($this, 'marketking_member_select_plan') );

				/* Verification */
				add_action( 'wp_ajax_marketkingsendverification', array($this, 'marketkingsendverification') );
				add_action( 'wp_ajax_nopriv_marketkingsendverification', array($this, 'marketkingsendverification') );

				/* Reports */
				add_action( 'wp_ajax_marketking_reports_get_data', array($this, 'marketking_reports_get_data') );
				add_action( 'wp_ajax_nopriv_marketking_reports_get_data', array($this, 'marketking_reports_get_data') );
				
				/* Single Product Multiple Vendors */
				add_action( 'wp_ajax_marketkingaddproductstore', array($this, 'marketkingaddproductstore') );
				add_action( 'wp_ajax_nopriv_marketkingaddproductstore', array($this, 'marketkingaddproductstore') );

				// Inquiries
				add_action( 'wp_ajax_marketkingmessagemessage', array($this, 'marketkingmessagemessage') );
				add_action( 'wp_ajax_nopriv_marketkingmessagemessage', array($this, 'marketkingmessagemessage') );

				// Abuse Reports
				add_action( 'wp_ajax_marketkingabusereport', array($this, 'marketkingabusereport') );
				add_action( 'wp_ajax_nopriv_marketkingabusereport', array($this, 'marketkingabusereport') );

				// Favorite Stores
				add_action( 'wp_ajax_marketking_change_follow_status', array($this, 'marketking_change_follow_status') );
				add_action( 'wp_ajax_nopriv_marketking_change_follow_status', array($this, 'marketking_change_follow_status') );

				// Save Store Notice Settings
				add_action( 'wp_ajax_marketking_save_notice_settings', array($this, 'marketking_save_notice_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_notice_settings', array($this, 'marketking_save_notice_settings') );

				// Save Store Policy Settings
				add_action( 'wp_ajax_marketking_save_policy_settings', array($this, 'marketking_save_policy_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_policy_settings', array($this, 'marketking_save_policy_settings') );

				// Save Store Categories Settings
				add_action( 'wp_ajax_marketking_save_storecategories_settings', array($this, 'marketking_save_storecategories_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_storecategories_settings', array($this, 'marketking_save_storecategories_settings') );

				// Save Store SEO Settings
				add_action( 'wp_ajax_marketking_save_seo_settings', array($this, 'marketking_save_seo_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_seo_settings', array($this, 'marketking_save_seo_settings') );

				// Save Social Settings
				add_action( 'wp_ajax_marketking_save_social_settings', array($this, 'marketking_save_social_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_social_settings', array($this, 'marketking_save_social_settings') );

				// Save OtherRules (B2B) Settings
				add_action( 'wp_ajax_marketking_save_otherrules_settings', array($this, 'marketking_save_otherrules_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_otherrules_settings', array($this, 'marketking_save_otherrules_settings') );

				// Save Invoice Settings
				add_action( 'wp_ajax_marketking_save_invoice_settings', array($this, 'marketking_save_invoice_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_invoice_settings', array($this, 'marketking_save_invoice_settings') );

				// Save Vacation Settings
				add_action( 'wp_ajax_marketking_save_vacation_settings', array($this, 'marketking_save_vacation_settings') );
				add_action( 'wp_ajax_nopriv_marketking_save_vacation_settings', array($this, 'marketking_save_vacation_settings') );

				// Save Support Settings
				add_action( 'wp_ajax_marketking_save_support_settings', array($this, 'marketking_save_support_settings') );
				add_action( 'wp_ajax_nopriv_arketking_save_support_settings', array($this, 'marketking_save_support_settings') );

				// Mark announcement read
				add_action( 'wp_ajax_marketkingmarkread', array($this, 'marketkingmarkread') );
	    		add_action( 'wp_ajax_nopriv_marketkingmarkread', array($this, 'marketkingmarkread') );

				// Mark all announcement read
				add_action( 'wp_ajax_marketkingmarkallread', array($this, 'marketkingmarkallread') );
	    		add_action( 'wp_ajax_nopriv_marketkingmarkallread', array($this, 'marketkingmarkallread') );

	    		// Mark message read
				add_action( 'wp_ajax_marketkingmarkreadmessage', array($this, 'marketkingmarkreadmessage') );
	    		add_action( 'wp_ajax_nopriv_marketkingmarkreadmessage', array($this, 'marketkingmarkreadmessage') );
	    		// Mark message closed
				add_action( 'wp_ajax_marketkingmarkclosedmessage', array($this, 'marketkingmarkclosedmessage') );
	    		add_action( 'wp_ajax_nopriv_marketkingmarkclosedmessage', array($this, 'marketkingmarkclosedmessage') );
	    		
	    		// Reply message
	    		add_action( 'wp_ajax_marketkingreplymessage', array($this, 'marketkingreplymessage') );
	    		add_action( 'wp_ajax_nopriv_marketkingreplymessage', array($this, 'marketkingreplymessage') );

	    		// Compose message
	    		add_action( 'wp_ajax_marketkingcomposemessage', array($this, 'marketkingcomposemessage') );
	    		add_action( 'wp_ajax_nopriv_marketkingcomposemessage', array($this, 'marketkingcomposemessage') );

	    		// Report Review
	    		add_action( 'wp_ajax_marketkingreportreview', array($this, 'marketkingreportreview') );
	    		add_action( 'wp_ajax_nopriv_marketkingreportreview', array($this, 'marketkingreportreview') );

	    		// Add Team Member (Staff)
	    		add_action( 'wp_ajax_marketkingaddmember', array($this, 'marketkingaddmember') );
	    		add_action( 'wp_ajax_nopriv_marketkingaddmember', array($this, 'marketkingaddmember') );

	    		// Reply Review
	    		add_action( 'wp_ajax_marketkingreplyreview', array($this, 'marketkingreplyreview') );
	    		add_action( 'wp_ajax_nopriv_marketkingreplyreview', array($this, 'marketkingreplyreview') );

	    		// Refunds
	    		add_action( 'wp_ajax_marketking_approve_refund', array($this, 'marketking_approve_refund') );
	    		add_action( 'wp_ajax_nopriv_marketking_approve_refund', array($this, 'marketking_approve_refund') );
	    		add_action( 'wp_ajax_marketking_reject_refund', array($this, 'marketking_reject_refund') );
	    		add_action( 'wp_ajax_nopriv_marketking_reject_refund', array($this, 'marketking_reject_refund') );
	    		add_action( 'wp_ajax_b2bkingconversationmessagerefunds', array($this, 'b2bkingconversationmessagerefunds') );
	    		add_action( 'wp_ajax_nopriv_b2bkingconversationmessagerefunds', array($this, 'b2bkingconversationmessagerefunds') );

				// Dismiss "activate woocommerce" admin notice permanently
				add_action( 'wp_ajax_marketkingpro_dismiss_activate_woocommerce_admin_notice', array($this, 'marketkingpro_dismiss_activate_woocommerce_admin_notice') );

				
				// Load Earnings Table AJAX Vendor Dashboard
				add_action( 'wp_ajax_marketking_earnings_table_ajax', array($this, 'marketking_earnings_table_ajax') );
				add_action( 'wp_ajax_nopriv_marketking_earnings_table_ajax', array($this, 'marketking_earnings_table_ajax') );		


				// Load Coupons Table AJAX Vendor Dashboard
				add_action( 'wp_ajax_marketking_coupons_table_ajax', array($this, 'marketking_coupons_table_ajax') );
				add_action( 'wp_ajax_nopriv_marketking_coupons_table_ajax', array($this, 'marketking_coupons_table_ajax') );


				// Load Subscriptions Table AJAX Vendor Dashboard
				add_action( 'wp_ajax_marketking_subscriptions_table_ajax', array($this, 'marketking_subscriptions_table_ajax') );
				add_action( 'wp_ajax_nopriv_marketking_subscriptions_table_ajax', array($this, 'marketking_subscriptions_table_ajax') );


				// Load Reviews Table AJAX Vendor Dashboard
				add_action( 'wp_ajax_marketking_reviews_table_ajax', array($this, 'marketking_reviews_table_ajax') );
				add_action( 'wp_ajax_nopriv_marketking_reviews_table_ajax', array($this, 'marketking_reviews_table_ajax') );


				// Load Refunds Table AJAX Vendor Dashboard
				add_action( 'wp_ajax_marketking_refunds_table_ajax', array($this, 'marketking_refunds_table_ajax') );
				add_action( 'wp_ajax_nopriv_marketking_refunds_table_ajax', array($this, 'marketking_refunds_table_ajax') );

				// B2BKING INTEGRATION START 

				// new offer
				add_action( 'wp_ajax_nopriv_b2bking_save_new_ajax_offer', array($this, 'b2bking_save_new_ajax_offer') );
				add_action( 'wp_ajax_b2bking_save_new_ajax_offer', array($this, 'b2bking_save_new_ajax_offer') );
				// edit offer
				add_action( 'wp_ajax_nopriv_b2bking_get_offer_data', array($this, 'b2bking_get_offer_data') );
				add_action( 'wp_ajax_b2bking_get_offer_data', array($this, 'b2bking_get_offer_data') );
				// delete offer
				add_action( 'wp_ajax_nopriv_b2bking_delete_ajax_offer', array($this, 'b2bking_delete_ajax_offer') );
				add_action( 'wp_ajax_b2bking_delete_ajax_offer', array($this, 'b2bking_delete_ajax_offer') );
				// save rules
				add_action( 'wp_ajax_nopriv_b2bking_save_new_ajax_rule', array($this, 'b2bking_save_new_ajax_rule') );
				add_action( 'wp_ajax_b2bking_save_new_ajax_rule', array($this, 'b2bking_save_new_ajax_rule') );
				// delete rules
				add_action( 'wp_ajax_nopriv_b2bking_delete_ajax_rule', array($this, 'b2bking_delete_ajax_rule') );
				add_action( 'wp_ajax_b2bking_delete_ajax_rule', array($this, 'b2bking_delete_ajax_rule') );
				// edit rule
				add_action( 'wp_ajax_nopriv_b2bking_get_rule_data', array($this, 'b2bking_get_rule_data') );
				add_action( 'wp_ajax_b2bking_get_rule_data', array($this, 'b2bking_get_rule_data') );
				// edit rule
				add_action( 'wp_ajax_nopriv_b2bking_get_conversation_data', array($this, 'b2bking_get_conversation_data') );
				add_action( 'wp_ajax_b2bking_get_conversation_data', array($this, 'b2bking_get_conversation_data') );

				// email offer
				add_action( 'wp_ajax_nopriv_b2bking_email_offer_marketking', array($this, 'b2bking_email_offer_marketking') );
				add_action( 'wp_ajax_b2bking_email_offer_marketking', array($this, 'b2bking_email_offer_marketking') );

				// subscription actions
				add_action( 'wp_ajax_nopriv_marketkingsubscriptionaction', array($this, 'marketkingsubscriptionaction') );
				add_action( 'wp_ajax_marketkingsubscriptionaction', array($this, 'marketkingsubscriptionaction') );



				// HOW TO NOTICES
				add_action( 'wp_ajax_marketking_dismiss_announcements_howto_admin_notice', array($this, 'marketking_dismiss_announcements_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_groups_howto_admin_notice', array($this, 'marketking_dismiss_groups_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_grules_howto_admin_notice', array($this, 'marketking_dismiss_grules_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_messages_howto_admin_notice', array($this, 'marketking_dismiss_messages_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_commissionrules_howto_admin_notice', array($this, 'marketking_dismiss_commissionrules_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_abusereports_howto_admin_notice', array($this, 'marketking_dismiss_abusereports_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_memberships_howto_admin_notice', array($this, 'marketking_dismiss_memberships_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_verifications_howto_admin_notice', array($this, 'marketking_dismiss_verifications_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_vitems_howto_admin_notice', array($this, 'marketking_dismiss_vitems_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_badges_howto_admin_notice', array($this, 'marketking_dismiss_badges_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_refunds_howto_admin_notice', array($this, 'marketking_dismiss_refunds_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_roptions_howto_admin_notice', array($this, 'marketking_dismiss_roptions_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_rfields_howto_admin_notice', array($this, 'marketking_dismiss_rfields_howto_admin_notice') );
				add_action( 'wp_ajax_marketking_dismiss_sellerdocs_howto_admin_notice', array($this, 'marketking_dismiss_sellerdocs_howto_admin_notice') );

				// Core installer
				add_action( 'wp_ajax_marketking_core_install', array( $this, 'install_marketking_core' ) );


			}

			add_action( 'plugins_loaded', function(){
				if ( defined('MARKETKINGCORE_DIR') && class_exists( 'woocommerce' )) {

					// Variation Edit Pricing (not just public due to ajax component)
					// Allocate Offers to Vendors
					add_filter( 'b2bking_before_add_offer_to_cart', array($this, 'allocate_offers_vendors'), 10, 1);
					// Filter offer ID before adding it to cart
					add_filter( 'b2bking_offer_id_before_add_offer_to_cart', array($this, 'filter_offer_product_id'), 10, 2);
					// Add vendor to conversations permission list
					add_filter('b2bking_conversation_permission_list', array($this,'filter_conversation_permission_list'), 10, 4);
					// Modify email recipient
					add_filter('b2bking_recipient_new_message', array($this, 'filter_message_recipient'), 10, 2);
					add_filter('b2bking_recipient_new_message_quote', array($this, 'filter_message_recipient_quote'), 10, 2);

					// Hide Offer Products
					add_filter('parse_query', array($this, 'b2bking_hide_offer_products'));

					// B2BKING INTEGRATION END


					// Configure product class structures
					add_filter('product_type_selector', function($arr){
						return array(
							'simple'   => esc_html__( 'Simple product', 'woocommerce' ),
							'grouped'  => esc_html__( 'Grouped product', 'woocommerce' ),
							'external' => esc_html__( 'External/Affiliate product', 'woocommerce' ),
							'variable' => esc_html__( 'Variable product', 'woocommerce' ),
						);
					}, 9, 1);
					add_filter('product_type_options', function($arr){
						return array(
							'virtual'      => array(
								'id'            => '_virtual',
								'wrapper_class' => 'show_if_simple',
								'label'         => esc_html__( 'Virtual', 'woocommerce' ),
								'description'   => esc_html__( 'Virtual products are intangible and are not shipped.', 'woocommerce' ),
								'default'       => 'no',
							),
							'downloadable' => array(
								'id'            => '_downloadable',
								'wrapper_class' => 'show_if_simple',
								'label'         => esc_html__( 'Downloadable', 'woocommerce' ),
								'description'   => esc_html__( 'Downloadable products give access to a file upon purchase.', 'woocommerce' ),
								'default'       => 'no',
							),
						);
					}, 9, 1);

					// On vacation mode, filter product visibility
					// This is only a backup method in cases where the vendor enables vacation and then keeps modifying products.
					// Otherwise, the catalog visibility method used during the ajax enable vacation would be working
					add_filter('woocommerce_product_is_visible', array($this,'filter_product_visibility_vacation'), 10, 2);

					/* Filter products visibility in shop/ category / archive pages for SPMV START */

					if (intval(get_option('marketking_enable_spmv_setting', 1)) === 1){

						// Hide products
						add_action( 'woocommerce_product_query', array($this, 'marketking_product_categories_visibility_rules'), 9999, 1 );

						add_filter( 'woocommerce_product_related_posts_query', array($this, 'marketking_product_categories_visibility_rules_related'), 9999, 3 );
						add_filter('woocommerce_related_products', array($this, 'marketking_product_categories_visibility_rules_related2'), 9999, 3);
						
						add_action( 'woocommerce_shortcode_products_query', array($this, 'marketking_product_categories_visibility_rules_shortcode'), 9999, 1 );
						add_filter( 'woocommerce_products_widget_query_args', array($this, 'marketking_product_categories_visibility_rules_shortcode'), 99999, 1);
						add_filter( 'woocommerce_top_rated_products_widget_args', array($this, 'marketking_product_categories_visibility_rules_shortcode'), 99999, 1);
						add_filter( 'woocommerce_recently_viewed_products_widget_query_args', array($this, 'marketking_product_categories_visibility_rules_shortcode'), 99999, 1);
						

						// general product is visible filter ( works for upsells, crosssells and more )
						add_action('wp', function(){
							// if page is not vendor store page
							// also cart or checkout...problem is with links to products in cart not working otherwise
							if (!marketking()->is_vendor_store_page() && !is_cart() && !is_checkout()){
								add_filter('woocommerce_product_is_visible', array($this, 'marketking_product_categories_visibility_rules_productfilter'), 100, 2);
							}
						});

						// add compatibility with AJAX SEARCH LITE
						add_filter('asl_query_args', array($this, 'asl_query_args_postin'), 10, 1);

						// previous functionality 1.6.22 changes
						add_action('plugins_loaded', function(){
							if (apply_filters('marketking_apply_visibility_in_ajax', true)){
				   				if ( ! current_user_can( 'manage_woocommerce' ) ) { 
				   					add_action( 'pre_get_posts', array($this, 'marketking_product_categories_visibility_rules') );
				   				}
				   			}
						});
						
						
						if (apply_filters('marketking_apply_visibility_in_ajax_direct', false)){
			   				if ( ! current_user_can( 'manage_woocommerce' ) ) { 
			   					add_action( 'pre_get_posts', array($this, 'marketking_product_categories_visibility_rules') );
			   				}
			   			}
			   			


			   			// Uncode theme compatibility
			   			add_action( 'uncode_get_uncode_index_args', array($this, 'marketking_product_categories_visibility_rules_shortcode'), 9999, 1 );

			   			// Update cache on stock change
			   			add_action('woocommerce_product_set_stock',  array($this, 'update_visibility_cache_when_stock_changes'));
			   			add_action('woocommerce_variation_set_stock',  array($this, 'update_visibility_cache_when_stock_changes'));
			   			// Update cache on product change
			   			add_action( 'save_post', array($this, 'update_product_set_cache'), 10, 1);
			   			// Update cache of vendor products on vendor change rating
			   			add_action( 'comment_post', array($this, 'update_visibility_cache_when_new_rating'), 100, 3 );

			   			// Update cache on setting change
			   			add_filter( 'update_option_marketking_stock_priority_setting', array($this, 'update_visibility_cache_when_settings_change'), 10, 2 );
			   			add_filter( 'update_option_marketking_vendor_priority_setting', array($this, 'update_visibility_cache_when_settings_change'), 10, 2 );

			   			// Rebuild visibility cache is needed
			   			add_action('wp', array($this,'rebuild_visibility_cache_needed'));
			  			
					}
					/* Filter visibility SPMV END */

					/* Memberships & Subscriptions */
					// Subscriptio
					add_action('subscriptio_subscription_status_changed', array($this,'subscriptio_cancelled_checks'), 10, 3 );

					// Sumo
					if (defined('SUMO_SUBSCRIPTIONS_PLUGIN_FILE')){
						add_action( 'save_post', array($this, 'sumo_cancelled_checks'), 10, 1);
						add_action( 'sumosubscriptions_subscription_resumed', array($this, 'sumo_cancelled_checks'), 10, 1);
						add_action( 'sumosubscriptions_subscription_paused', array($this, 'sumo_cancelled_checks'), 10, 1);
						add_action( 'sumosubscriptions_subscription_cancelled', array($this, 'sumo_cancelled_checks'), 10, 1);
						add_action( 'sumosubscriptions_subscription_expired', array($this, 'sumo_cancelled_checks'), 10, 1);

						add_action( 'init', array($this,'check_sumo_still_active'));
					}
					

					// YITH
					add_action( 'ywsbs_subscription_status_changed', array($this,'yith_cancelled_checks'), 10, 3 );

					// WooCommerce Subscriptions
					add_action( 'woocommerce_subscription_status_changed', array($this,'woo_cancelled_checks'), 10, 4 );

					// Store Categories, Taxonomy
					if (intval(get_option('marketking_enable_storecategories_setting', 1)) === 1){
						add_action( 'init', array($this, 'user_status_taxonomy') );
						add_filter('parent_file', array($this, 'parent_menu'));
					}

				}
			});
				
		}
		/* yith bundles ajax, show vendor only their own product */
		/* Product Bundles */
        add_filter('yith_wcpb_select_product_box_args', function($args){

        	if (intval(get_option('marketking_enable_bundles_setting', 1)) === 1){
        	    if(defined('MARKETKINGPRO_DIR')){
        	        // yith bundle as well
        	        if (defined('YITH_WCPB_VERSION')){
			            $user_id = get_current_user_id();
			            if (marketking()->is_vendor_team_member()){
			                $user_id = marketking()->get_team_member_parent();
			            }
			            // get all products of current vendor
			            $args['author'] = $user_id;
			        }
			    }
			}
            return $args;
        }, 10, 1);
   
		add_action( 'wc_ajax_wc_stripe_verify_intent', array($this, 'stripe_verify_intent'));

		// Run Admin/Public code 
		if ( is_admin() ) { 
			require_once MARKETKINGPRO_DIR . '/admin/class-marketking-pro-admin.php';
			$admin = new Marketkingpro_Admin();
		} else if ( !$this->marketkingpro_is_login_page() ) {
			require_once MARKETKINGPRO_DIR . '/public/class-marketking-pro-public.php';
			global $marketkingpro_public;
			$marketkingpro_public = new Marketkingpro_Public();

		}
	}

	function check_sumo_still_active(){
		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
			$currentuser = new WP_User($current_id);
		}
		$subscription_vendor = $current_id;
		$active_sub = get_user_meta($current_id,'marketking_vendor_active_subscription', true);

		if (!empty($active_sub)){

    		$vendor_sub_details = explode(':', $active_sub);

    		if ($vendor_sub_details[0] === 'sumo'){
    			$vendor_sub_id = intval($vendor_sub_details[1]);

    			// if this subscription product is indeed the vendor's subscription
    			$post_id = $vendor_sub_id;
    			
				$new = get_post_meta($post_id,'sumo_get_status', true);

				if ($new !== 'Active'){
					// move user to default group
					$default_group = get_option('marketking_memberships_default_group_setting', '');
					if (!empty($default_group)){
						update_user_meta($subscription_vendor,'marketking_group', $default_group);
					}
				}

				if ($new === 'Active'){

					// subscription was re-enabled, move user to pack group
					update_user_meta($subscription_vendor,'marketking_group', $vendor_sub_details[2]);
				}      		
				
			
    		}
    		
    	}   	
	}

	function install_marketking_core(){
		// Check security nonce.
		if ( ! check_ajax_referer( 'marketking-core-install-nonce', 'security' ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$plugin = 'marketking-multivendor-marketplace-for-woocommerce';
		$api    = plugins_api(
		    'plugin_information', [
		        'slug'   => $plugin,
		        'fields' => [ 'sections' => false ],
		    ]
		);

		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$result   = $upgrader->install( $api->download_link );
		activate_plugin( 'marketking-multivendor-marketplace-for-woocommerce/marketking-core.php' );

		wp_send_json_success();
	}

	function parent_menu($parent = '') {
	    global $pagenow;
 		    
	    if(!empty($_GET['taxonomy']) && in_array($pagenow,array( 'edit-tags.php','term.php')) && $_GET['taxonomy'] == 'storecat') {
	        $parent = 'marketking';
	    }

	    return $parent;
	}

	function attribute_subscription_to_vendor($subscription, $order, $cart){
		
		$vendors = array();
		// check all products in subscription, and if only 1 vendor, set it as post_author
		$items = $subscription->get_items();
		foreach ($items as $item){
			$product_id = $item->get_product_id();
			array_push($vendors, marketking()->get_product_vendor($product_id));
		}

		$vendors = array_unique(array_filter($vendors));

		if (count($vendors) === 1){

			// set that vendor as post author
			wp_update_post(
			   array(
					'ID'          => $subscription->get_id(),
					'post_author' => reset($vendors),
			   )
			);
		}

	}

	function user_status_taxonomy() {
		register_taxonomy(
		'storecat', 'user',
		 array(
		    'public' => true,
		    'hierarchical'          => false,
		    'public'                => true,
		    'show_ui'               => true,
		    'show_in_nav_menus'          => true,
		    'labels' => array(
		        'name' => esc_html__( 'Store Categories','marketking' ),
		        'singular_name' => esc_html__( 'Store Category','marketking' ),
		        'menu_name' => esc_html__( 'Store Categories','marketking' ),
		        'search_items' => esc_html__( 'Search Categories','marketking' ),
		        'popular_items' => esc_html__( 'Popular Categories','marketking' ),
		        'all_items' => esc_html__( 'All Categories','marketking' ),
		        'edit_item' => esc_html__( 'Edit Category','marketking' ),
		        'update_item' => esc_html__( 'Update Category','marketking' ),
		        'add_new_item' => esc_html__( 'Add New Category','marketking' ),
		        'new_item_name' => esc_html__( 'New Category Name','marketking' ),
		        'separate_items_with_commas' => esc_html__( 'Separate categories with commas','marketking' ),
		        'add_or_remove_items' => esc_html__( 'Add or remove categories','marketking' ),
		        'choose_from_most_used' => esc_html__( 'Choose from the most popular categories','marketking' ),
		    )
		)
		);
	}

	function stripe_verify_intent(){

		if ( ! class_exists( 'Marketking_Stripe_Gateway' ) ) {
			include_once('stripe/class-marketking-stripe-connect-gateway.php');
		}
		$gateway = new Marketking_Stripe_Gateway();

		global $woocommerce;


		try {
		  $order = $gateway->get_order_from_request();
		} catch ( Throwable $ex ) {
		  /* translators: Error message text */
		  $message = sprintf( __( 'Payment verification error: %s', 'marketking' ), $ex->getMessage() );

		  marketking()->logdata( "Stripe Split Pay Error: " . esc_html( $message ) );
		  wc_add_notice( __("Stripe Split Pay Error: ", 'marketking') . esc_html( $message ), 'error' );

		  $redirect_url = $woocommerce->cart->is_empty()
		    ? get_permalink( wc_get_page_id( 'shop' ) )
		    : wc_get_checkout_url();

		  if ( isset( $_GET['is_ajax'] ) ) {
		    exit;
		  }
		
		  wp_safe_redirect( $redirect_url );
		}

		try {
		  $gateway->verify_intent_after_checkout( $order );

		  if ( ! isset( $_GET['is_ajax'] ) ) {
		    $redirect_url = isset( $_GET['redirect_to'] ) // wpcs: csrf ok.
		      ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) // wpcs: csrf ok.
		      : $gateway->get_return_url( $order );

		    wp_safe_redirect( $redirect_url );
		  }

		  exit;
		} catch ( Throwable $ex ) {

		  marketking()->logdata( "Stripe Split Pay Error: " . esc_html( $ex->getMessage() ) );

		  wc_add_notice( __("Stripe Split Pay Error: ", 'marketking') . esc_html( $ex->getMessage() ), 'error' );
		  
		  wp_safe_redirect( $gateway->get_return_url( $order ) );
		}
	}

	function handle_non_connected_vendors ($fields, $errors) {

		// get if non connected vendors are allowed
		if ($fields['payment_method'] === 'marketking_stripe_gateway'){
			$settings = get_option('woocommerce_marketking_stripe_gateway_settings');
			if ($settings['non_connected'] === 'no'){

				$error = 'no';
				$non_connected_vendors = array();
				// get vendors in cart
				$vendorscart = marketking()->get_vendors_in_cart();
				foreach ($vendorscart as $vendor_id){
					if (!marketking()->is_connected_stripe($vendor_id)){
						$error = 'yes';
						array_push($non_connected_vendors, marketking()->get_store_name_display($vendor_id));
					}
				}

				if ($error === 'yes'){
					$errors->add( 'validation', esc_html__('The following vendors are not connected with Stripe:', 'marketking').' '.implode(',', $non_connected_vendors) );
				}

			}
		}
		
	}

	// Disable shipping methods based on user settings (group)
	function marketking_disable_shipping_methods( $rates, $package ){

		$package_vendor_id = $package['vendor_id'];
		$admin_user_id = apply_filters('marketking_admin_user_id', 1);

		$available = array();

		// first make all available
		foreach ( $rates as $rate_id => $rate ) {
			$available[ $rate_id ] = $rate;
		}

		// now remove admin only methods from other vendors
		foreach ( $rates as $rate_id => $rate ) {

			// If not admin vendor
			if ($package_vendor_id !== $admin_user_id){


				// if method is admin only
				$admin_only = get_option('marketking_admin_only_shipping_methods_setting',array());
				if (!is_array($admin_only)){
					$admin_only = array();
				}

				if (in_array($rate->method_id.$rate->instance_id, $admin_only)){
					// remove method
					unset($available[ $rate_id ]);
				}

				if (in_array($rate->method_id, $admin_only)){
					// remove method
					unset($available[ $rate_id ]);
				}
			}
			
		}

		return $available;
		
	}

	function marketking_coupon_options() {
	    global $post;
	    echo '<div class="options_group">';
	    woocommerce_wp_checkbox(array(
	        'id'            => 'marketking_auto_include',
	        'label'         => esc_html__('Auto-add new products', 'marketking'),
	        'value'         => get_post_meta($post->ID, 'marketking_auto_include', true),
	        'desc_tip'      => true,
	        'description'   => esc_html__('If selected, every new published product will be automatically included in this coupon.', 'marketking'),
	    ));
	    echo '</div>';
	}

	function marketking_coupon_options_save($post_id) {
	    $auto_include = isset($_POST['marketking_auto_include']) ? 'yes' : 'no';
	    update_post_meta($post_id, 'marketking_auto_include', $auto_include);
	}

	function marketking_auto_include_product($product_id, $vendor_id){

		$coupons = get_posts(array(
		    'post_type' => 'shop_coupon',
		    'meta_key' => 'marketking_auto_include',
		    'meta_value' => 'yes',
		    'post_author' => $vendor_id
		));
		
		foreach ($coupons as $coupon) {
		    $product_ids = get_post_meta($coupon->ID, 'product_ids', true);
		    $product_ids_array = explode(',', $product_ids);
		    $product_ids_array = array_filter(array_unique($product_ids_array));

		    if (!in_array($product_id, $product_ids_array)) {
		        $product_ids_array[] = $product_id;
		        update_post_meta($coupon->ID, 'product_ids', implode(',', $product_ids_array));
		    }
		}
	}



	function marketking_dismiss_announcements_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_announcements_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_groups_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_groups_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_grules_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_grules_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_messages_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_messages_howto_notice', 1);

		echo 'success';
		exit();
	}


	function marketking_dismiss_rfields_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_rfields_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_sellerdocs_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_sellerdocs_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_roptions_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_roptions_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_refunds_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_refunds_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_badges_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_badges_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_vitems_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_vitems_howto_notice', 1);

		echo 'success';
		exit();
	}


	function marketking_dismiss_verifications_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_verifications_howto_notice', 1);

		echo 'success';
		exit();
	}

	// Add marketking payment gateway
	function marketking_add_stripe_gateway ( $methods ){
		if ( ! class_exists( 'Marketking_Stripe_Gateway' ) ) {
			include_once('stripe/class-marketking-stripe-connect-gateway.php');
			// enable when ready
			$methods[] = 'Marketking_Stripe_Gateway';
		}
    	return $methods;
	}

	function marketking_dismiss_memberships_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_memberships_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_abusereports_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_abusereports_howto_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_dismiss_commissionrules_howto_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketking_dismiss_commissionrules_howto_notice', 1);

		echo 'success';
		exit();
	}


	function register_vendor_shipping_method( $methods ) {

		// $method contains available shipping methods
		$methods[ 'marketking_shipping' ] = 'WC_Shipping_MarketKing';

		return $methods;
	}


	function add_shipping_pack_meta( $item, $package_key, $package, $order ) {
	    $item->add_meta_data( 'vendor_id', $package['vendor_id'], true );
	}

	public function split_cart_shipping_packages_names( $title, $i, $package  ) {
    	$vendor_id = $package['vendor_id'];

    	$vendor_name = marketking()->get_store_name_display($vendor_id);

    	return apply_filters('marketking_shipping_name_display', esc_html__('Shipping : ','marketking').esc_html($vendor_name), $vendor_name);
    }

	function split_cart_shipping_packages( $packages ) {

		$vendor_items_map = array();
		$packages = array();

		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id = $item['product_id'];
			$vendor_id = marketking()->get_product_vendor( $product_id );
			if ( $item['data']->needs_shipping() ) {
				$vendor_items_map[$vendor_id][] = $item;
			} else {
				// No product vendor associated with item.
				$vendor_items_map['0'][] = $item;
			}
			
		}

		foreach($vendor_items_map as $key => $vendor_items) {

			if (intval($key) === 0){
				continue;
			}
			$packages[] = array(
				'contents' => $vendor_items,
				'contents_cost' => array_sum( wp_list_pluck( $vendor_items, 'line_total' ) ),
				'applied_coupons' => WC()->cart->applied_coupons,
				'destination' => array(
					'country' => WC()->customer->get_shipping_country(),
					'state' => WC()->customer->get_shipping_state(),
					'postcode' => WC()->customer->get_shipping_postcode(),
					'city' => WC()->customer->get_shipping_city(),
					'address' => WC()->customer->get_shipping_address(),
					'address_2' => WC()->customer->get_shipping_address_2()
				),
				'user'            => [
				    'ID' => get_current_user_id(),
				],
				'vendor_id' => $key,
			); 
		}

		return $packages;

	  
	}

	function woo_cancelled_checks($post_id, $old, $new, $obj ) {

		if ( $old === $new ){
			return;
		}

		// Woo integration
	    if (get_post_type($post_id) === 'shop_subscription'){

	    	$subscription_vendor = get_post_meta($post_id,'_customer_user', true);
	    	// check if vendor has a subscriptio active sub
	    	$active_sub = get_user_meta($subscription_vendor,'marketking_vendor_active_subscription', true);

	    	if (!empty($active_sub)){

	    		$vendor_sub_details = explode(':', $active_sub);

	    		if ($vendor_sub_details[0] === 'woo'){
	    			$vendor_sub_id = intval($vendor_sub_details[1]);

	    			// if this subscription product is indeed the vendor's subscription
	    			if ($vendor_sub_id === intval($post_id)){

	    				$active_statuses = array('active','wc-active','pending-cancel','wc-pending-cancel');
	    				$inactive_statuses = array('on-hold','wc-on-hold','wc-cancelled','cancelled', 'expired', 'wc-expired');


	    				if (in_array($old, $active_statuses) && in_array($new, $inactive_statuses)){
	    					// move user to default group
	    					$default_group = get_option('marketking_memberships_default_group_setting', '');
	    					if (!empty($default_group)){
	    						update_user_meta($subscription_vendor,'marketking_group', $default_group);
	    					}
	    				}

	    				if (in_array($new, $active_statuses) && in_array($old, $inactive_statuses)){

	    					// subscription was re-enabled, move user to pack group
	    					update_user_meta($subscription_vendor,'marketking_group', $vendor_sub_details[2]);
	    				}      		
	    				
	    			}
	    		}
	    		
	    	}   	
	    	
	    }
	}

	function marketking_purchase_ad(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}
		$user_id = $current_id;

		$product_id = intval(sanitize_text_field($_POST['productid']));

		// If nonce verification didn't fail, run further
		$days = intval(sanitize_text_field($_POST['days']));

		// check credit cost
		$credit_cost = intval(get_option('marketking_credit_cost_per_day_setting',1));
		$total_cost = $credit_cost * $days;

		// check available credits
		$advertising_credits = intval(marketking()->get_advertising_credits($user_id));

		if ($advertising_credits < $total_cost){
			echo 'insufficient_funds';
			exit();
		} else {
			// proceed

			// mark product as advertised
			update_post_meta($product_id, 'marketking_is_advertised', 'yes');

			// add to advertised ids list
			$marketking_advertised_product_ids = get_option('marketking_advertised_product_ids');
			if (!is_array($marketking_advertised_product_ids)){
				$marketking_advertised_product_ids = array();
			}
			array_push($marketking_advertised_product_ids, $product_id);
			$marketking_advertised_product_ids = array_filter(array_unique($marketking_advertised_product_ids));
			update_option('marketking_advertised_product_ids', $marketking_advertised_product_ids);


			// add featured
			if (intval(get_option( 'marketking_advertising_featured_setting', 1 )) === 1){
				$wc_product = wc_get_product($product_id);
			    $wc_product->set_featured(1);
			    $wc_product->save();
			}

			// if already advertised, add on top of existing time
			$expiry_date = intval(get_post_meta($product_id, 'marketking_advertisement_expires', true));
			if ($expiry_date > time()){
				$time = $expiry_date;
			} else {
				$time = time();
			}
			update_post_meta($product_id, 'marketking_advertisement_expires', $time + (86400*$days));

			// take out credits
			$credits = intval(get_user_meta($user_id, 'marketking_advertising_credits_available', true));
			$new_credits = $credits - $total_cost;
			$amount = $new_credits - $credits;
			// update and add to history
			update_user_meta( $user_id, 'marketking_advertising_credits_available', $new_credits);	

			// get user history
			$user_credit_history = sanitize_text_field(get_user_meta($user_id,'marketking_user_credit_history', true));
			// create reimbursed transaction
			$date = date_i18n( 'Y/m/d', time()+(get_option('gmt_offset')*3600) ); 

			$operation = 'consumed';
			$product = wc_get_product($product_id);
			$title = $product->get_title();
			$note = esc_html__('Purchased advertisement for', 'marketking-multivendor-marketplace-for-woocommerce').' '.$title;
			$transaction_new = $date.':'.$operation.':'.$amount.':'.$new_credits.':'.$note;

			// update credit history
			update_user_meta($user_id,'marketking_user_credit_history',$user_credit_history.';'.$transaction_new);

			// additional things, e.g mark as featured



			echo 'success';
			exit();	
		}
		
	}

	function marketking_remove_advertise_admin(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$product_id = intval(sanitize_text_field($_POST['productid']));

		// has expired
		update_post_meta($product_id, 'marketking_is_advertised', 'no');

		// remove featured
		if (intval(get_option( 'marketking_advertising_featured_setting', 1 )) === 1){
			$wc_product = wc_get_product($product_id);
		    $wc_product->set_featured(0);
		    $wc_product->save();
		}

		// removed from advertised ids list
		$marketking_advertised_product_ids = get_option('marketking_advertised_product_ids');
		if (is_array($marketking_advertised_product_ids)){
			if (($key = array_search($product_id, $marketking_advertised_product_ids)) !== false) {
			    unset($marketking_advertised_product_ids[$key]);
			}
			update_option('marketking_advertised_product_ids', $marketking_advertised_product_ids);
		}

		// set time to 0
		update_post_meta($product_id, 'marketking_advertisement_expires', time());


		echo 'success';
		exit();	

	}

	function marketking_add_advertise_admin(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$product_id = intval(sanitize_text_field($_POST['productid']));
		$days = intval(sanitize_text_field($_POST['days']));

		update_post_meta($product_id, 'marketking_is_advertised', 'yes');

		// add featured
		if (intval(get_option( 'marketking_advertising_featured_setting', 1 )) === 1){
			$wc_product = wc_get_product($product_id);
		    $wc_product->set_featured(1);
		    $wc_product->save();
		}

		// add to advertised ids list
		$marketking_advertised_product_ids = get_option('marketking_advertised_product_ids');
		if (!is_array($marketking_advertised_product_ids)){
			$marketking_advertised_product_ids = array();
		}
		array_push($marketking_advertised_product_ids, $product_id);
		$marketking_advertised_product_ids = array_filter(array_unique($marketking_advertised_product_ids));
		update_option('marketking_advertised_product_ids', $marketking_advertised_product_ids);

		
		// if already advertised, add on top of existing time
		$expiry_date = intval(get_post_meta($product_id, 'marketking_advertisement_expires', true));
		if ($expiry_date > time()){
			$time = $expiry_date;
		} else {
			$time = time();
		}
		update_post_meta($product_id, 'marketking_advertisement_expires', $time + (86400*$days));



		echo 'success';
		exit();	

	}

	function marketkingaddcredit(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		// If nonce verification didn't fail, run further
		$amount = sanitize_text_field($_POST['amount']);
		$cart_item_data = array();
		$cart_item_data['marketking_credit_amount'] = $amount;
		// Create B2B offer product if it doesn't exist
		$credit_id = intval(get_option('marketking_credit_product_id_setting', 0));
		if ( !get_post_status ( $credit_id ) ) {
			$credit = array(
			    'post_title' => 'Credit',
			    'post_status' => 'publish',
			    'post_type' => 'product',
			    'post_author' => 1,
			);
			$product_id = wp_insert_post($credit);
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
			update_option( 'marketking_credit_product_id_setting', $product_id );
			$credit_id = intval(get_option('marketking_credit_product_id_setting', 0));
		}

		// set credit product price based on setting
		$credit_cost = get_option('marketking_credit_price_setting', 1);
		update_post_meta( $credit_id, '_regular_price', $credit_cost );
		update_post_meta( $credit_id, '_price', $credit_cost );

		
		WC()->cart->add_to_cart( $credit_id, $amount, 0, array(), $cart_item_data);


		echo 'success';
		exit();	
	}

	function sumo_cancelled_checks($subscription_id) {
		$post_id = $subscription_id;

		// Sumo integration
	    if (get_post_type($post_id) === 'sumosubscriptions'){

	    	$subscription_vendor = get_post_meta($post_id,'sumo_get_user_id', true);
	    	// check if vendor has an active sub
	    	$active_sub = get_user_meta($subscription_vendor,'marketking_vendor_active_subscription', true);

	    	if (!empty($active_sub)){

	    		$vendor_sub_details = explode(':', $active_sub);

	    		if ($vendor_sub_details[0] === 'sumo'){
	    			$vendor_sub_id = intval($vendor_sub_details[1]);

	    			// if this subscription product is indeed the vendor's subscription
	    			if ($vendor_sub_id === intval($post_id)){

	    				$new = get_post_meta($post_id,'sumo_get_status', true);

	    				if ($new !== 'Active'){
	    					// move user to default group
	    					$default_group = get_option('marketking_memberships_default_group_setting', '');
	    					if (!empty($default_group)){
	    						update_user_meta($subscription_vendor,'marketking_group', $default_group);
	    					}
	    				}

	    				if ($new === 'Active'){

	    					// subscription was re-enabled, move user to pack group
	    					update_user_meta($subscription_vendor,'marketking_group', $vendor_sub_details[2]);
	    				}      		
	    				
	    			}
	    		}
	    		
	    	}   	
	    	
	    }
	}

	function subscriptio_cancelled_checks($subscription, $old, $new ) {
		$post_id = $subscription->get_id();

		if ( $old === $new ){
			return;
		}

		// Subscriptio integration
	    if (get_post_type($post_id) === 'rp_sub_subscription'){

	    	$subscription_vendor = get_post_meta($post_id,'_customer_user', true);
	    	// check if vendor has a subscriptio active sub
	    	$active_sub = get_user_meta($subscription_vendor,'marketking_vendor_active_subscription', true);

	    	if (!empty($active_sub)){

	    		$vendor_sub_details = explode(':', $active_sub);

	    		if ($vendor_sub_details[0] === 'subscriptio'){
	    			$vendor_sub_id = intval($vendor_sub_details[1]);

	    			// if this subscription product is indeed the vendor's subscription
	    			if ($vendor_sub_id === intval($post_id)){


	    				if ($new !== 'active' && $new !== 'wc-active' && ($old === 'active' || $old === 'wc-active')){
	    					// move user to default group
	    					$default_group = get_option('marketking_memberships_default_group_setting', '');
	    					if (!empty($default_group)){
	    						update_user_meta($subscription_vendor,'marketking_group', $default_group);
	    					}
	    				}

	    				if ($old !== 'active' && $old !== 'wc-active' && ($new === 'active' || $new === 'wc-active')){

	    					// subscription was re-enabled, move user to pack group
	    					update_user_meta($subscription_vendor,'marketking_group', $vendor_sub_details[2]);
	    				}      		
	    				
	    			}
	    		}
	    		
	    	}   	
	    	
	    }
	}

	function yith_cancelled_checks($post_id, $old, $new ) {

		if ( $old === $new ){
			return;
		}

		// YITH integration
	    if (get_post_type($post_id) === 'ywsbs_subscription'){

	    	$subscription_vendor = get_post_meta($post_id,'user_id', true);
	    	// check if vendor has a subscriptio active sub
	    	$active_sub = get_user_meta($subscription_vendor,'marketking_vendor_active_subscription', true);

	    	if (!empty($active_sub)){

	    		$vendor_sub_details = explode(':', $active_sub);

	    		if ($vendor_sub_details[0] === 'yith'){
	    			$vendor_sub_id = intval($vendor_sub_details[1]);

	    			// if this subscription product is indeed the vendor's subscription
	    			if ($vendor_sub_id === intval($post_id)){


	    				if ($new !== 'active' && $new !== 'wc-active' && ($old === 'active' || $old === 'wc-active')){
	    					// move user to default group
	    					$default_group = get_option('marketking_memberships_default_group_setting', '');
	    					if (!empty($default_group)){
	    						update_user_meta($subscription_vendor,'marketking_group', $default_group);
	    					}
	    				}

	    				if ($old !== 'active' && $old !== 'wc-active' && ($new === 'active' || $new === 'wc-active')){

	    					// subscription was re-enabled, move user to pack group
	    					update_user_meta($subscription_vendor,'marketking_group', $vendor_sub_details[2]);
	    				}      		
	    				
	    			}
	    		}
	    		
	    	}   	
	    	
	    }
	}

	// changes logo on customer side my account
	function webtoffe_logo_settings2($find_replace,$html,$template_type,$order,$box_packing,$order_package){

		$current_id = 0;
		if (!empty($order)){
			$current_id = marketking()->get_order_vendor($order->get_id());
		}

		$logo = marketking()->get_vendor_invoice_data($current_id,'logo');
		$store_name = marketking()->get_store_name_display($current_id);

		$find_replace['woocommerce_wf_packinglist_logo'] = $logo;
		$find_replace['[wfte_company_logo_url]'] = $logo;

		return $find_replace;
	}


	// changes logo on vendor side
	function webtoffe_logo_settings($settings, $base_id){

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
			$currentuser = new WP_User($current_id);
		}

		$logo = marketking()->get_vendor_invoice_data($current_id,'logo');
		$store_name = marketking()->get_store_name_display($current_id);

		$settings['woocommerce_wf_packinglist_logo'] = $logo;
		return $settings;
	}

	function webtoffe_invoice_from($fromaddress,$template_type,$order){

		$order_id = $order->get_id();

		if (!marketking()->is_multivendor_order($order_id)){
			$vendor_id = marketking()->get_order_vendor($order_id);

			$shopname = marketking()->get_vendor_invoice_data($vendor_id,'store');
			$shopaddress = marketking()->get_vendor_invoice_data($vendor_id,'address');
			$custom = marketking()->get_vendor_invoice_data($vendor_id,'custom');
			if (intval($vendor_id) !== 1){

				$fromaddress['name'] = $shopname;
				$fromaddress['address_line1'] = $shopaddress;
				$fromaddress['contact_number'] = $fromaddress['address_line2'] = $fromaddress['city'] = $fromaddress['state'] = $fromaddress['country'] = $fromaddress['postcode'] = '';
				$fromaddress['vat'] = nl2br($custom);
			} else {
				// admin user, set the invoice data only if the vendor side data is not empty
				if (!empty($shopname)){
					$fromaddress['name'] = $shopname;
				}
				if (!empty($shopaddress)){
					$fromaddress['address_line1'] = $shopaddress;
					$fromaddress['contact_number'] = $fromaddress['address_line2'] = $fromaddress['city'] = $fromaddress['state'] = $fromaddress['country'] = $fromaddress['postcode'] = '';
				}
				if (!empty($custom)){
					$fromaddress['vat'] = nl2br($custom);
				}
			}
		}
		return $fromaddress;
	}

	function invoice_shop_name_filter($text, $orderdoc){
		$order_id = $orderdoc->order_id;

		if (!marketking()->is_multivendor_order($order_id)){
			$vendor_id = marketking()->get_order_vendor($order_id);

			$shopname = marketking()->get_vendor_invoice_data($vendor_id,'store');

			if (intval($vendor_id) !== 1){
				$text = $shopname;
			} else {
				if (!empty($shopname)){
					$text = $shopname;
				}
			}
		}

		return $text;
	}

	function invoice_shop_address_filter($text, $orderdoc){
		$order_id = $orderdoc->order_id;

		if (!marketking()->is_multivendor_order($order_id)){

			$vendor_id = marketking()->get_order_vendor($order_id);

			$shopaddress = marketking()->get_vendor_invoice_data($vendor_id,'address');
			$custominfo = marketking()->get_vendor_invoice_data($vendor_id,'custom');

			if (intval($vendor_id) !== 1){

				$text = $shopaddress;

				// if there is custom info, also add custom info here
				if (!empty($custominfo)){
					$text.='<br>'.nl2br($custominfo);
				}
			} else {
				if (!empty($shopaddress)){
					$text = $shopaddress;
				}
				if (!empty($custominfo)){
					$text.='<br>'.nl2br($custominfo);
				}
			}

		}

		return $text;
	}

	function invoice_shop_logo_filter($img_element, $attachment, $orderdoc){
		$order_id = $orderdoc->order_id;

		if (!marketking()->is_multivendor_order($order_id)){

			$vendor_id = marketking()->get_order_vendor($order_id);

			$logo = marketking()->get_vendor_invoice_data($vendor_id,'logo');
			$store_name = marketking()->get_store_name_display($vendor_id);

			if (intval($vendor_id) !== 1){
				$img_element = sprintf('<img src="%1$s" alt="%2$s" />', esc_attr( $logo ), esc_attr( $store_name ) );
			} else {
				if (!empty($logo) && !empty($store_name)){
					$img_element = sprintf('<img src="%1$s" alt="%2$s" />', esc_attr( $logo ), esc_attr( $store_name ) );
				}
			}
		}

		return $img_element;
	}

	



	// Register new post type: Commission Rules
	public static function marketking_register_post_type_commission_rules() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Commission Rules', 'marketking' ),
	        'singular_name'         => esc_html__( 'Rule', 'marketking' ),
	        'all_items'             => esc_html__( 'Commission Rules', 'marketking' ),
	        'menu_name'             => esc_html__( 'Commission Rules', 'marketking' ),
	        'add_new'               => esc_html__( 'Create new rule', 'marketking' ),
	        'add_new_item'          => esc_html__( 'Create new rule', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit rule', 'marketking' ),
	        'new_item'              => esc_html__( 'New rule', 'marketking' ),
	        'view_item'             => esc_html__( 'View rule', 'marketking' ),
	        'view_items'            => esc_html__( 'View rules', 'marketking' ),
	        'search_items'          => esc_html__( 'Search rules', 'marketking' ),
	        'not_found'             => esc_html__( 'No rules found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No rules found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent rule', 'marketking' ),
	        'featured_image'        => esc_html__( 'Rule image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set rule image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove rule image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as rule image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into rule', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this rule', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter rules', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Rules navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Commission rules list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Commission Rules', 'marketking' ),
	        'description'           => esc_html__( 'This is where you can create commission rules', 'marketking' ),
	        'labels'                => $labels,
	        'supports'              => array( 'title','custom-fields' ),
	        'hierarchical'          => false,
	        'public'                => false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 123,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => true,
	        'has_archive'           => false,
	        'exclude_from_search'   => true,
	        'publicly_queryable'    => false,
	        'capability_type'       => 'product',
	        'map_meta_cap'          => true,
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_rule',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_rule', $args );
	}



	// Add Rule Details Metabox to Rules
	function marketking_rules_metaboxes($post_type) {
	    $post_types = array('marketking_rule');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
	           add_meta_box(
	               'marketking_rule_details_metabox'
	               ,esc_html__( 'Rule Details', 'marketking' )
	               ,array( $this, 'marketking_rule_details_metabox_content' )
	               ,$post_type
	               ,'advanced'
	               ,'high'
	           );
	       }
	}

	function b2bkingconversationmessagerefunds(){

		do_action('b2bking_conversation_message_start');

		// If nonce verification didn't fail, run further
		$message = sanitize_textarea_field($_POST['message']);
		$conversationid = sanitize_text_field($_POST['conversationid']);

		$currentuser = wp_get_current_user()->user_login;
		$conversationuser = get_post_meta ($conversationid, 'b2bking_conversation_user', true);

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
			$currentuser = new WP_User($current_id);
		}

		// Check message not empty
		if ($message !== NULL && trim($message) !== ''){

			// Check user permission against Conversation user meta. Check subaccounts as well
			$current_user_id = $current_id;
		    $subaccounts_list = get_user_meta($current_user_id,'b2bking_subaccounts_list', true);
		    $subaccounts_list = explode(',', $subaccounts_list);
		    $subaccounts_list = array_filter($subaccounts_list);
		    array_push($subaccounts_list, $current_user_id);

		    $subaccounts_list = apply_filters('b2bking_conversation_permission_list', $subaccounts_list, $conversationid, $current_user_id, $conversationuser);

		    // if current account is subaccount AND has permission to view all account conversations, add parent account+all subaccounts lists
		    $account_type = get_user_meta($current_user_id, 'b2bking_account_type', true);
		    if ($account_type === 'subaccount'){
		    	$permission_view_all_conversations = filter_var(get_user_meta($current_user_id, 'b2bking_account_permission_view_conversations', true),FILTER_VALIDATE_BOOLEAN);
		    	if ($permission_view_all_conversations === true){
		    		// has permission
		    		$parent_account = get_user_meta($current_user_id, 'b2bking_account_parent', true);
		    		$parent_subaccounts_list = explode(',', get_user_meta($parent_account, 'b2bking_subaccounts_list', true));
		    		$parent_subaccounts_list = array_filter($parent_subaccounts_list); // filter blank, null, etc.
		    		array_push($parent_subaccounts_list, $parent_account); // add parent itself to form complete parent accounts list

		    		$subaccounts_list = array_merge($subaccounts_list, $parent_subaccounts_list);
		    	}
		    }

		    foreach ($subaccounts_list as $user){
		    	$subaccounts_list[$user] = get_user_by('id', $user)->user_login;
		    }

		    if (in_array($conversationuser, $subaccounts_list)){

				$nr_messages = intval(get_post_meta($conversationid, 'b2bking_conversation_messages_number', true));
				$current_message_nr = $nr_messages+1;
				update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr, $message);
				update_post_meta( $conversationid, 'b2bking_conversation_messages_number', $current_message_nr);
				update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr.'_author', $currentuser );
				update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr.'_time', time() );

				do_action('b2bking_conversation_after_message_inserted', $conversationid, $current_message_nr, $message);

				// if status is new, change to open
				$status = get_post_meta ($conversationid, 'b2bking_conversation_status', true);
				if ($status === 'new'){
					update_post_meta( $conversationid, 'b2bking_conversation_status', 'open');
				}


				
				$recipient = get_option( 'admin_email' );

				$recipient = apply_filters('b2bking_recipient_new_message', $recipient, $conversationid);

				do_action( 'b2bking_new_message', $recipient, $message, $current_user_id, $conversationid );
			}
		}
	}

	function marketkingsubscriptionaction(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$id = sanitize_text_field($_POST['id']);
		// check that current user is author of the product
		$author_id = get_post_field( 'post_author', $id );

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		if (intval($author_id) === $current_id || intval($author_id) === intval(get_current_user_id())){
			if (apply_filters('marketking_allow_vendor_subscription_edit', true)){

				$action = sanitize_text_field($_POST['value']); // cancel, pause, etc, what the vendor wanted to do

				$subscription = new WC_Subscription($id);

				if ($action === 'reactivate'){
					if ( $subscription->can_be_updated_to( 'active' ) ) {
						$subscription->update_status( 'active' );
					}
				}

				if ($action === 'pause'){
					if ( $subscription->can_be_updated_to( 'on-hold' ) ) {
						$subscription->update_status( 'on-hold' );
					}
				}

				if ($action === 'cancel'){
					if ( $subscription->can_be_updated_to( 'cancelled' ) ) {
						$subscription->update_status( 'cancelled' );
					}
				}

			}
		}
	}

	public function marketkingaddmember(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$firstname = sanitize_text_field($_POST['firstname']);
		$lastname = sanitize_text_field($_POST['lastname']);
		$description = sanitize_text_field($_POST['description']);
		$phoneno = sanitize_text_field($_POST['phoneno']);
		$username = sanitize_text_field($_POST['username']);
		$emailaddress = sanitize_text_field($_POST['emailaddress']);
		$password = sanitize_text_field($_POST['password']);
		$agent_id = get_current_user_id();

		$user_id = wp_create_user( $username, $password, $emailaddress);


		if ( ! (is_wp_error($user_id))){
			// no errors, proceed
			// set user meta
			update_user_meta($user_id, 'billing_first_name', $firstname);
			update_user_meta($user_id, 'shipping_first_name', $firstname);
			update_user_meta($user_id, 'first_name', $firstname);

			update_user_meta($user_id, 'billing_last_name', $lastname);
			update_user_meta($user_id, 'shipping_last_name', $lastname);
			update_user_meta($user_id, 'last_name', $lastname);

			update_user_meta($user_id, 'billing_phone', $phoneno);
			update_user_meta($user_id, 'shipping_phone', $phoneno);

			update_user_meta($user_id, 'marketking_member_description', $description);

			// set assigned agent
			update_user_meta($user_id, 'marketking_parent_vendor', $agent_id);

			$userobj = new WP_User($user_id);
			$userobj->set_role('customer');

			$parent_agent_id = $agent_id;
			do_action('marketking_after_member_created', $user_id, $parent_agent_id, $parentaggroup);

			add_filter( 'wp_new_user_notification_email' , 'edit_user_notification_email', 10, 3 );

			function edit_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {

			    $message = sprintf(esc_html__( "Your vendor team member account for %s has been created! Here are your login details:",'marketking' ), $blogname ) . "\r\n\r\n";
			    $message .= trailingslashit(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))) . "\r\n";
			    $message .= sprintf(esc_html__( 'Username: %s','marketking' ), $user->user_login ) . "\r\n";
			    $message .= sprintf(esc_html__( 'Password: %s','marketking' ), sanitize_text_field($_POST['password']) ) . "\r\n\r\n";
			   

			    $key = get_password_reset_key( $user );
		        if ( is_wp_error( $key ) ) {
		            return $wp_new_user_notification_email;
		        }
		     
		        $switched_locale = switch_to_locale( get_user_locale( $user ) );
		     
		        /* translators: %s: User login. */
		        if (apply_filters('marketking_allow_team_new_password', true)){
		        	$message .= esc_html__( 'To set a new password, visit the following address:','marketking' ) . "\r\n\r\n";
		        	$message .= network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n";
		        }


			    $wp_new_user_notification_email['message'] = $message;

			    $wp_new_user_notification_email = array(
			            'to'      => $user->user_email,
			            /* translators: Login details notification email subject. %s: Site title. */
			            'subject' => esc_html__( 'Your %s team member account details' ),
			            'message' => $message,
			            'headers' => '',
			        );

			    return $wp_new_user_notification_email;

			}

			// Sent email
			wp_new_user_notification( $user_id, null, 'user');


			echo esc_html($user_id);
			echo 'success';

		} else {
			echo 'error'.$user_id->get_error_message();
		}


		exit();
	}

	public function vendor_products_export($args){

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}


		if ( marketking()->is_vendor($current_id)){
			$args['author'] = $current_id;
		}

		return $args;
	}

	function group_products_non_taxable($taxable, $product){

		$vendor_id = marketking()->get_product_vendor($product->get_id());

		if (!marketking()->vendor_can_taxable($vendor_id)){
			return false;
		}
		
		return $taxable;
	}

	public function marketking_save_groups_metaboxes($post_id){
		$panels = marketkingpro()->get_all_dashboard_panels();

		if (isset($_POST['_inline_edit'])){
			return;
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		$postobj = get_post($post_id);
		if (isset($postobj->post_status)){
			if ( $postobj->post_status === 'trash' ) {
		        return;
		    }
		}
		
	    if (isset($_GET['action'])) {
	    	if ($_GET['action'] === 'untrash'){
	    		return;
	    	}
	    }

		$p = get_post($post_id);
		if (isset($p->post_status)){
			if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || ($p->post_status === 'auto-draft')) { 
				return;
			}
		}
		

		if (get_post_type($post_id) === 'marketking_group'){

			if (apply_filters('marketking_use_wp_roles', false)){
				/** 
				* WP Roles Support
				* add_role adds role if it does not exist
				* if it does exist, change its display name to title
				*/

				// clean auto unpublished roles
				$roles = get_option( 'wp_user_roles' );
				if (is_array($roles)){
					foreach ($roles as $index=>$role){
						$rolepostid = explode('_', $index)[2];
						if (get_post_status($rolepostid) !== 'publish'){
							// delete role
							remove_role('marketking_role_'.$rolepostid);
						}
					}
				}
				

				if (add_role('marketking_role_'.$post_id, sanitize_text_field(get_the_title($post_id))) === null){
					global $wpdb;
					$prefix = $wpdb->prefix;
					
					$val = get_option( 'wp_user_roles' );
					$val['marketking_role_'.$post_id]['name'] = sanitize_text_field(get_the_title($post_id));
					update_option( 'wp_user_roles', $val );

					if (get_option($prefix.'user_roles', 0) !== 0){
						$val = get_option( $prefix.'user_roles' );
						$val['marketking_role_'.$post_id]['name'] = sanitize_text_field(get_the_title($post_id));
						update_option( $prefix.'user_roles', $val );
					}
					
				};
			}

			$panels = marketkingpro()->get_all_dashboard_panels();

			foreach ($panels as $panel_slug => $panel_name){
				$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_available_panel_'.$panel_slug));
				if ($method !== NULL ){
					update_post_meta( $post_id, 'marketking_group_available_panel_'.$panel_slug, $method);
				}
			}

			// save other settings
			$group_max_products = sanitize_text_field($_POST['marketking_group_max_products']);
			update_post_meta($post_id,'marketking_group_allowed_products_number', $group_max_products);

			$rule_applies_multiple_options = $_POST['marketking_group_allowed_products_type'];
			if (empty($rule_applies_multiple_options)){
				$rule_applies_multiple_options = array();
			}

			$options_string = '';
			foreach ($rule_applies_multiple_options as $option){
				$options_string .= sanitize_text_field ($option).',';
			}
			// remove last comma
			$options_string = substr($options_string, 0, -1);
			update_post_meta( $post_id, 'marketking_group_allowed_products_type_settings', $options_string);


			$rule_applies_multiple_options = $_POST['marketking_group_allowed_categories'];
			if (empty($rule_applies_multiple_options)){
				$rule_applies_multiple_options = array();
			}

			$options_string = '';
			foreach ($rule_applies_multiple_options as $option){
				$options_string .= sanitize_text_field ($option).',';
			}
			// remove last comma
			$options_string = substr($options_string, 0, -1);
			update_post_meta( $post_id, 'marketking_group_allowed_categories_settings', $options_string);


			$rule_applies_multiple_options = $_POST['marketking_group_allowed_tags'];
			if (empty($rule_applies_multiple_options)){
				$rule_applies_multiple_options = array();
			}

			$options_string = '';
			foreach ($rule_applies_multiple_options as $option){
				$options_string .= sanitize_text_field ($option).',';
			}
			// remove last comma
			$options_string = substr($options_string, 0, -1);
			update_post_meta( $post_id, 'marketking_group_allowed_tags_settings', $options_string);


			$rule_applies_multiple_options = $_POST['marketking_group_allowed_tabs'];
			if (empty($rule_applies_multiple_options)){
				$rule_applies_multiple_options = array();
			}

			$options_string = '';
			foreach ($rule_applies_multiple_options as $option){
				$options_string .= sanitize_text_field ($option).',';
			}
			// remove last comma
			$options_string = substr($options_string, 0, -1);
			update_post_meta( $post_id, 'marketking_group_allowed_tabs_settings', $options_string);
			

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendors_can_linked_products_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendors_can_linked_products_setting', $method);
			}	

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendors_non_taxable_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendors_non_taxable_setting', $method);
			}	

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendors_new_attributes_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendors_new_attributes_setting', $method);
			}


			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendors_all_virtual_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendors_all_virtual_setting', $method);
			}

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendors_all_downloadable_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendors_all_downloadable_setting', $method);
			}

			

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendors_multiple_categories_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendors_multiple_categories_setting', $method);
			}

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendors_allow_backorders_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendors_allow_backorders_setting', $method);
			}	


			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_products_sold_individually_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_products_sold_individually_setting', $method);
			}	
			

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendor_status_direct_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendor_status_direct_setting', $method);
			}	

			$method = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_vendor_publish_direct_setting'));
			if ($method !== NULL ){
				update_post_meta( $post_id, 'marketking_group_vendor_publish_direct_setting', $method);
			}			

			
		}
	}


	public function download_export_file() {

		$nonce = marketking()->get_pagenr_query_var();
		if ( wp_verify_nonce( $nonce, 'product-csv' )) { 
			include_once WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php';
			
			$exporter = new WC_Product_CSV_Exporter();
			$exporter->set_filename( 'wc-product-'.get_current_user_id().'-download' ); // WPCS: input var ok, sanitization ok.
			$exporter->export();
		}
	}

	public function do_ajax_product_import() {
		global $wpdb;

		include_once( MARKETKINGPRO_DIR . 'public/dashboard/importer/importer-controller.php' );
		include_once WC_ABSPATH . 'includes/admin/importers/class-wc-product-csv-importer-controller.php';
		include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';

		$file   = wc_clean( wp_unslash( $_POST['file'] ) ); // PHPCS: input var ok.
		$params = array(
			'delimiter'       => ! empty( $_POST['delimiter'] ) ? wc_clean( wp_unslash( $_POST['delimiter'] ) ) : ',', // PHPCS: input var ok.
			'start_pos'       => isset( $_POST['position'] ) ? absint( $_POST['position'] ) : 0, // PHPCS: input var ok.
			'mapping'         => isset( $_POST['mapping'] ) ? (array) wc_clean( wp_unslash( $_POST['mapping'] ) ) : array(), // PHPCS: input var ok.
			'update_existing' => isset( $_POST['update_existing'] ) ? (bool) $_POST['update_existing'] : false, // PHPCS: input var ok.
			'character_encoding' => isset( $_POST['character_encoding'] ) ? wc_clean( wp_unslash( $_POST['character_encoding'] ) ) : '',
			'lines'           => apply_filters( 'woocommerce_product_import_batch_size', 30 ),
			'parse'           => true,
		);

		// Log failures.
		if ( 0 !== $params['start_pos'] ) {
			$error_log = array_filter( (array) get_user_option( 'product_import_error_log' ) );
		} else {
			$error_log = array();
		}

		$importer         = Marketking_Product_CSV_Importer_Controller::get_importer( $file, $params );
		$results          = $importer->import();
		$percent_complete = $importer->get_percent_complete();
		$error_log        = array_merge( $error_log, $results['failed'], $results['skipped'] );

		update_user_option( get_current_user_id(), 'product_import_error_log', $error_log );

		if ( 100 === $percent_complete ) {

			$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_original_id' ) );
			$wpdb->delete( $wpdb->posts, array(
				'post_type'   => 'product',
				'post_status' => 'importing',
			) );
			$wpdb->delete( $wpdb->posts, array(
				'post_type'   => 'product_variation',
				'post_status' => 'importing',
			) );

			// Clean up orphaned data.
			$wpdb->query(
				"
				DELETE {$wpdb->posts}.* FROM {$wpdb->posts}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->posts}.post_parent
				WHERE wp.ID IS NULL AND {$wpdb->posts}.post_type = 'product_variation'
			"
			);
			$wpdb->query(
				"
				DELETE {$wpdb->postmeta}.* FROM {$wpdb->postmeta}
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = {$wpdb->postmeta}.post_id
				WHERE wp.ID IS NULL
			"
			);
			$wpdb->query( "
				DELETE tr.* FROM {$wpdb->term_relationships} tr
				LEFT JOIN {$wpdb->posts} wp ON wp.ID = tr.object_id
				LEFT JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE wp.ID IS NULL
				AND tt.taxonomy IN ( '" . implode( "','", array_map( 'esc_sql', get_object_taxonomies( 'product' ) ) ) . "' )
			" );


			// Send success.
			wp_send_json_success(
				array(
					'position'   => 'done',
					'percentage' => 100,
					'url'        => trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'import-products/?step=done&_wpnonce='.wp_create_nonce( 'woocommerce-csv-importer' ),
					'imported'   => count( $results['imported'] ),
					'failed'     => count( $results['failed'] ),
					'updated'    => count( $results['updated'] ),
					'skipped'    => count( $results['skipped'] ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'position'   => $importer->get_file_position(),
					'percentage' => $percent_complete,
					'imported'   => count( $results['imported'] ),
					'failed'     => count( $results['failed'] ),
					'updated'    => count( $results['updated'] ),
					'skipped'    => count( $results['skipped'] ),
				)
			);
		}
	}

	public function prevent_import_max_products($data){
		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		if(!marketking()->vendor_can_add_more_products($current_id)){
			throw new Exception(esc_html__('You have reached you maximum products number limit. Please remove a product or increase your limit, to be able to use the import tool.','marketking'));

		}

	}

	// if ID or SKU already exist, we set these to 0, to create a new product instead
	public function protect_other_vendor_product_on_csv( $data ) {

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$removed_skus = array();
		$removed_ids = array();

	    $current_user = $current_id;
	    $product_id   = $data['id'] ? $data['id'] : $data['sku'];

	    // if SKU, but not ID, check SKU does not already belong to another
	    if ( empty( $data['id'] ) && ! empty( $data['sku'] ) ) {
	        $product_id = wc_get_product_id_by_sku( $data['sku'] );

	        if (intval($product_id) != 0){
	        	$post_author = absint( get_post_field( 'post_author', $product_id ) );

	        	if ( (int) $post_author !== (int) $current_user ) {
	        		array_push($removed_skus, $data['sku']);
	        	    $data['sku'] = 0;
	        	}
	        }	

	        $allowed_product = apply_filters('marketking_allowed_vendor_edit_product', true, $product_id);
	        if (!$allowed_product){
	        	array_push($removed_skus, $data['sku']);
	            $data['sku'] = 0;
	        }

	    } else {
	    	if (isset($data['id'])){
	    		if ($data['id']){
	    			if (intval($data['id']) != 0){
	    				$post_author = absint( get_post_field( 'post_author', $product_id ) );

	    				if ( (int) $post_author !== (int) $current_user ) {
	    					array_push($removed_ids, $data['id']);
	    				    $data['id'] = 0;
	    				}

	    			}
	    		}

	    		$allowed_product = apply_filters('marketking_allowed_vendor_edit_product', true, $product_id);
	    		if (!$allowed_product){
	    			array_push($removed_ids, $data['id']);
	    		    $data['id'] = 0;
	    		}
	    	}
	    }

	    $data = apply_filters('marketking_import_process_vendor', $data);
	    $removed_ids = apply_filters('marketking_import_removed_ids', $removed_ids);
	    $removed_skus = apply_filters('marketking_import_removed_skus', $removed_skus);

	    update_user_meta($current_id, 'marketking_import_skipped_skus', $removed_skus);
	    update_user_meta($current_id, 'marketking_import_skipped_ids', $removed_ids);

	    return $data;
	}

	function protect_mapping_options($options, $item){

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		// If admin cannot set status as published, remove published:
		if (!marketking()->vendor_can_publish_products($current_id)){
			unset($options['published']);
		}

		if( intval(get_option( 'marketking_vendors_can_tags_setting',1 )) !== 1){
			unset($options['tag_ids']);
			unset($options['tag_ids_spaces']);
		}

		if(!marketking()->vendor_can_linked_products($current_id)){
			unset($options['upsell_ids']);
			unset($options['cross_sell_ids']);
		}

		if(intval(get_option( 'marketking_vendors_can_purchase_notes_setting', 1 )) !== 1){
			unset($options['purchase_note']);
		}

		if(intval(get_option( 'marketking_vendors_can_reviews_setting', 0 )) !== 1){
			unset($options['reviews_allowed']);
		}

		unset($options['featured']);
		unset($options['menu_order']);


		// if vendor has any restrictons on categories, then do not allow categories to be set through the importer
		$groupid = get_user_meta($current_id,'marketking_group', true);
		if (!empty($groupid)){
			$selected_options_string = get_post_meta($groupid, 'marketking_group_allowed_categories_settings', true);
			if (!empty($selected_options_string)){
				unset($options['category_ids']);
			}
		}

		return apply_filters('marketking_available_options_import', $options, $current_id);

	}

	public function feature_column_to_false( $data ) {

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

	    if ( ! wc_current_user_has_role( 'administrator' ) ) {
	        $data['featured'] = false;
	    }

	    if (!marketking()->vendor_can_publish_products($current_id)){
	    	if ($data['type'] === 'simple'){
	    		$data['status'] = 'pending';
	    	}
	    	if ($data['type'] === 'variable'){
	    		$data['status'] = 'pending';
	    	}
	    }

	    return $data;
	}

	/**
	 * AJAX callback for doing the actual export to the CSV file.
	 */
	public function do_ajax_product_export() {

		include_once WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php';

		$step     = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1; 

		$exporter = new WC_Product_CSV_Exporter();


		if ( ! empty( $_POST['columns'] ) ) { // WPCS: input var ok.
			$exporter->set_column_names( wp_unslash( $_POST['columns'] ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( ! empty( $_POST['selected_columns'] ) ) { // WPCS: input var ok.
			$exporter->set_columns_to_export( wp_unslash( $_POST['selected_columns'] ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( ! empty( $_POST['export_meta'] ) ) { // WPCS: input var ok.
			$exporter->enable_meta_export( true );
		}

		if ( ! empty( $_POST['export_types'] ) ) { // WPCS: input var ok.
			$exporter->set_product_types_to_export( wp_unslash( $_POST['export_types'] ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( ! empty( $_POST['export_category'] ) && is_array( $_POST['export_category'] ) ) {// WPCS: input var ok.
			$exporter->set_product_category_to_export( wp_unslash( array_values( $_POST['export_category'] ) ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( ! empty( $_POST['filename'] ) ) { // WPCS: input var ok.
			$exporter->set_filename( wp_unslash( $_POST['filename'] ) ); // WPCS: input var ok, sanitization ok.
		}

		$exporter->set_page( $step );

		$exporter->generate_file();

		$nonce = wp_create_nonce( 'product-csv' );

		$query_args = apply_filters(
			'woocommerce_export_get_ajax_query_args',
			array(
				'nonce'    => $nonce,
				'action'   => 'download_product_csv',
				'filename' => $exporter->get_filename(),
			)
		);

		if ( 100 === $exporter->get_percent_complete() ) {
			wp_send_json_success(
				array(
					'step'       => 'done',
					'percentage' => 100,
					'url'        => trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'export-products/'.$nonce,
				)
			);
		} else {
			wp_send_json_success(
				array(
					'step'       => ++$step,
					'percentage' => $exporter->get_percent_complete(),
					'columns'    => $exporter->get_column_names(),
				)
			);
		}
	}

	// Rule Details Metabox Content
	function marketking_rule_details_metabox_content(){
		global $post;
		?>
		<div class="marketking_commission_rule_metabox_content_container">
			<div class="marketking_rule_select_container">
				<div class="marketking_rule_label"><?php esc_html_e('Commission type:','marketking'); ?></div>
				<select id="marketking_rule_select_what" name="marketking_rule_select_what">
					<?php
					// if page not "Add new", get selected
					$selected = '';
					if( get_current_screen()->action !== 'add'){
			        	$selected = esc_html(get_post_meta($post->ID, 'marketking_rule_what', true));
			        }
					?>
					<optgroup label="<?php esc_attr_e('Commission Rules', 'marketking'); ?>"> 
						<option value="fixed" <?php selected('fixed',$selected,true); ?>><?php esc_html_e('Flat (fixed amount)','marketking'); ?></option>
						<option value="percentage" <?php selected('percentage',$selected,true); ?>><?php esc_html_e('Percentage','marketking'); ?></option>
					</optgroup>
				</select>
			</div>
			<div id="marketking_container_howmuch" class="marketking_rule_select_container">
				<div class="marketking_rule_label"><?php esc_html_e('Value:','marketking'); ?></div>
				<input type="number" step="0.001" name="marketking_rule_select_howmuch" id="marketking_rule_select_howmuch" value="<?php echo esc_attr(get_post_meta($post->ID, 'marketking_rule_howmuch', true)); ?>">
			</div>
			<div class="marketking_rule_select_container" id="marketking_container_applies">
				<div class="marketking_rule_label"><?php esc_html_e('Applies for products:','marketking'); ?></div>
				
				<select id="marketking_rule_select_applies" name="marketking_rule_select_applies">
					<?php
					// if page not "Add new", get selected
					$selected = '';
					if( get_current_screen()->action !== 'add'){
			        	$selected = esc_html(get_post_meta($post->ID, 'marketking_rule_applies', true));
			        	$rule_replaced = esc_html(get_post_meta($post->ID, 'marketking_rule_replaced', true));
			        	if ($rule_replaced === 'yes' && $selected === 'multiple_options'){
			        		$selected = 'replace_ids';
			        	}
			        }
					?>
					<optgroup label="<?php esc_attr_e('Multiple', 'marketking'); ?>" id="marketking_cart_total_optgroup" >
						<option value="cart_total" <?php selected('cart_total',$selected,true); ?>><?php esc_html_e('All products','marketking'); ?></option>
						<option value="multiple_options" <?php selected('multiple_options',$selected,true); ?>><?php esc_html_e('Select categories & tags','marketking'); ?></option>
						<option value="replace_ids" <?php selected('replace_ids',$selected,true); ?>><?php esc_html_e('Add product or variation IDs','marketking'); ?></option>

						<option value="once_per_order" <?php selected('once_per_order',$selected,true); ?>><?php esc_html_e('Once per order','marketking'); ?></option>


						<?php
						if (intval(get_option( 'marketking_replace_product_selector_setting', 0 )) === 1){
							?>
							<option value="replace_ids" <?php selected('replace_ids',$selected,true); ?>><?php esc_html_e('Product or Variation ID(s)','marketking'); ?></option>
							<?php
						}
						?>
					</optgroup>
					<optgroup label="<?php esc_attr_e('Product Categories', 'marketking'); ?>">
						<?php
						// Get all categories
						$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
						foreach ($categories as $category){
							echo '<option value="category_'.esc_attr($category->term_id).'" '.selected('category_'.$category->term_id, $selected,false).'>'.esc_html($category->name).'</option>';
						}
						?>
					</optgroup>

					<optgroup label="<?php esc_attr_e('Product Tags', 'marketking'); ?>">
						<?php
						// Get all categories
						$tags = get_terms( array( 'taxonomy' => 'product_tag', 'hide_empty' => false ) );
						foreach ($tags as $tag){
							echo '<option value="tag_'.esc_attr($tag->term_id).'" '.selected('tag_'.$tag->term_id, $selected,false).'>'.esc_html($tag->name).'</option>';
						}
						?>
					</optgroup>
					
				</select>
			</div>
			<div class="marketking_rule_select_container">
				<div class="marketking_rule_label"><?php esc_html_e('For vendors:','marketking'); ?></div>
				<select id="marketking_rule_select_vendors_who" name="marketking_rule_select_vendors_who">
					<?php
					// if page not "Add new", get selected
					$selected = '';
					if( get_current_screen()->action !== 'add'){
			        	$selected = esc_html(get_post_meta($post->ID, 'marketking_rule_vendors_who', true));
			        }
					?>
					<optgroup label="<?php esc_attr_e('Multiple', 'marketking'); ?>">

						<option value="all_vendors" <?php selected('all_vendors',$selected,true); ?>><?php esc_html_e('All vendors','marketking'); ?></option>
						<option value="multiple_options" <?php selected('multiple_options',$selected,true); ?>><?php esc_html_e('Select multiple options','marketking'); ?></option>
					</optgroup>
					<optgroup label="<?php esc_attr_e('Vendor Groups', 'marketking'); ?>">
						<?php
						// Get all groups
						$groups = get_posts( array( 'post_type' => 'marketking_group','post_status'=>'publish','numberposts' => -1) );
						foreach ($groups as $group){
							echo '<option value="group_'.esc_attr($group->ID).'" '.selected('group_'.$group->ID,$selected,false).'>'.esc_html($group->post_title).'</option>';
						}
						?>
					</optgroup>
					<optgroup label="<?php esc_attr_e('Vendors (individual)', 'marketking'); ?>">
						<?php 
							// if B2B/B2C Hybrid, show only B2B users
						 	$vendors = get_users(array(
									    'meta_key'     => 'marketking_group',
									    'meta_value'   => 'none',
									    'meta_compare' => '!=',
									));

							foreach ($vendors as $vendor){
								echo '<option value="vendor_'.esc_attr($vendor->ID).'" '.selected('vendor_'.$vendor->ID,$selected,false).'>'.esc_html(marketking()->get_store_name_display($vendor->ID)).'</option>';
							}
						?>
					</optgroup>
				</select>
			</div>

			<br /><br />

			<div id="marketking_rule_select_applies_replaced_container" >
				<div class="marketking_rule_label marketking_product_variation_ids_title">
					<div><?php esc_html_e('Product or Variation ID(s) (comma-separated):','marketking'); ?></div>
				</div>
				<?php
				$replaced_content_string = get_post_meta($post->ID,'marketking_rule_product_ids', true);
				?>
				<input type="text" id="marketking_rule_select_applies_replaced" name="marketking_rule_select_applies_replaced" value="<?php echo esc_attr($replaced_content_string);?>">
			</div>
			
			<div id="marketking_select_multiple_product_categories_selector" >
				<div class="marketking_select_multiple_products_categories_title">
					<?php esc_html_e('Select multiple categories & tags','marketking'); ?>
				</div>
				<select class="marketking_select_multiple_product_categories_selector_select" name="marketking_select_multiple_product_categories_selector_select[]" multiple>
					<?php
					// if page not "Add new", get selected options
					$selected_options = array();
					if( get_current_screen()->action !== 'add'){
			        	$selected_options_string = get_post_meta($post->ID, 'marketking_rule_applies_multiple_options', true);
			        	$selected_options = explode(',', $selected_options_string);
			        }
			        ?>
			        <optgroup label="<?php esc_attr_e('Product Categories', 'marketking'); ?>">
			        	<?php
			        	// Get all categories
			        	$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false) );
			        	foreach ($categories as $category){
    		            	$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if ($selected_option === ('category_'.$category->term_id )){
									$is_selected = 'yes';
								}
							}
			        		echo '<option value="category_'.esc_attr($category->term_id).'" '.selected('yes',$is_selected, true).'>'.esc_html($category->name).'</option>';
			        	}
			        	?>
			        </optgroup>
			        <optgroup label="<?php esc_attr_e('Product Tags', 'marketking'); ?>">
			        	<?php
			        	// Get all categories
			        	$tags = get_terms( array( 'taxonomy' => 'product_tag', 'hide_empty' => false) );
			        	foreach ($tags as $tag){
    		            	$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if ($selected_option === ('tag_'.$tag->term_id )){
									$is_selected = 'yes';
								}
							}
			        		echo '<option value="tag_'.esc_attr($tag->term_id).'" '.selected('yes',$is_selected, true).'>'.esc_html($tag->name).'</option>';
			        	}
			        	?>
			        </optgroup>
				</select>

			</div>
		
			<div id="marketking_select_multiple_vendors_selector" >
				<div class="marketking_select_multiple_products_categories_title">
					<?php esc_html_e('Select multiple vendor options','marketking'); ?>
				</div>
				<select class="marketking_select_multiple_product_categories_selector_select" name="marketking_select_multiple_vendors_selector_select[]" multiple>
					<?php
					// if page not "Add new", get selected options
					$selected_options = array();
					if( get_current_screen()->action !== 'add'){
			        	$selected_options_string = get_post_meta($post->ID, 'marketking_rule_vendors_who_multiple_options', true);
			        	$selected_options = explode(',', $selected_options_string);
			        }
					?>
					<optgroup label="<?php esc_attr_e('Vendor Groups', 'marketking'); ?>">
						<?php
						// Get all groups
						$groups = get_posts( array( 'post_type' => 'marketking_group','post_status'=>'publish','numberposts' => -1) );
						foreach ($groups as $group){
    		            	$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if ($selected_option === ('group_'.$group->ID )){
									$is_selected = 'yes';
								}
							}
							echo '<option value="group_'.esc_attr($group->ID).'" '.selected('yes',$is_selected,false).'>'.esc_html($group->post_title).'</option>';
						}
						?>
					</optgroup>
					<optgroup label="<?php esc_attr_e('Vendors (individual)', 'marketking'); ?>">
						<?php 
							// if B2B/B2C Hybrid, show only B2B users
						 	$vendors = get_users(array(
									    'meta_key'     => 'marketking_group',
									    'meta_value'   => 'none',
									    'meta_compare' => '!=',
									));

							foreach ($vendors as $vendor){
	    		            	$is_selected = 'no';
	    		            	foreach ($selected_options as $selected_option){
									if ($selected_option === ('vendor_'.$vendor->ID )){
										$is_selected = 'yes';
									}
								}
								echo '<option value="vendor_'.esc_attr($vendor->ID).'" '.selected('yes',$is_selected,false).'>'.esc_html(marketking()->get_store_name_display($vendor->ID)).'</option>';
							}
						?>
					</optgroup>
				</select>

			</div>



			<br /><br />
			
		</div>
		<?php
	}

		// Save Rules Metabox Content
	function marketking_save_rules_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		// clear cache when saving products
		if (get_post_type($post_id) === 'product'){
			// set that rules have changed so that pricing cache can be updated
			update_option('marketking_commission_rules_have_changed', 'yes');

			// delete all marketking transients
			global $wpdb;
			$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%transient_marketking%'" );
			foreach( $plugin_options as $option ) {
			    delete_option( $option->option_name );
			}
			wp_cache_flush();
		}
		if (get_post_type($post_id) === 'marketking_rule'){

			// set that rules have changed so that pricing cache can be updated
			update_option('marketking_commission_rules_have_changed', 'yes');

			// delete all marketking transients
			global $wpdb;
			$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%transient_marketking%'" );
			foreach( $plugin_options as $option ) {
			    delete_option( $option->option_name );
			}
			wp_cache_flush();

			$rule_what = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_what'));
			$rule_applies = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_applies'));
			$rule_orders = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_orders'));

			$rule_who = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_who'));
			$rule_vendors_who = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_vendors_who'));

			$rule_quantity_value = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_quantity_value'));
			$rule_tax_shipping = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_tax_shipping'));
			$rule_tax_shipping_rate = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_tax_shipping_rate'));
			$rule_howmuch = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_howmuch'));
			$rule_x = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_x'));

			$rule_currency = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_currency'));
			$rule_paymentmethod = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_paymentmethod'));
			$rule_paymentmethod_minmax = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_paymentmethod_minmax'));
			$rule_paymentmethod_percentamount = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_paymentmethod_percentamount'));

			$rule_taxname = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_taxname'));
			$rule_discountname = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_discountname'));
			$rule_conditions = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_conditions'));
			$rule_tags = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_tags'));
			$rule_discount_show_everywhere = sanitize_text_field(filter_input(INPUT_POST, 'marketking_commission_rule_discount_show_everywhere_checkbox_input'));
			
			if (isset($_POST['marketking_rule_select_countries'])){
				$rule_countries = $_POST['marketking_rule_select_countries'];
			} else {
				$rule_countries = NULL;
			}

			if (isset($_POST['marketking_select_multiple_product_categories_selector_select'])){
				$rule_applies_multiple_options = $_POST['marketking_select_multiple_product_categories_selector_select'];
			} else {
				$rule_applies_multiple_options = NULL;
			}

			if (isset($_POST['marketking_select_multiple_users_selector_select'])){
				$rule_who_multiple_options = $_POST['marketking_select_multiple_users_selector_select'];
			} else {
				$rule_who_multiple_options = NULL;
			}

			if (isset($_POST['marketking_select_multiple_vendors_selector_select'])){
				$rule_vendors_who_multiple_options = $_POST['marketking_select_multiple_vendors_selector_select'];
			} else {
				$rule_vendors_who_multiple_options = NULL;
			}

			$rule_requires = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_requires'));
			$rule_showtax = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_showtax'));

			if ($rule_what !== NULL){
				update_post_meta( $post_id, 'marketking_rule_what', $rule_what);
			}
			if ($rule_currency !== NULL){
				update_post_meta( $post_id, 'marketking_rule_currency', $rule_currency);
			}
			if ($rule_paymentmethod !== NULL){
				update_post_meta( $post_id, 'marketking_rule_paymentmethod', $rule_paymentmethod);
			}
			if ($rule_paymentmethod_minmax !== NULL){
				update_post_meta( $post_id, 'marketking_rule_paymentmethod_minmax', $rule_paymentmethod_minmax);
			}
			if ($rule_paymentmethod_percentamount !== NULL){
				update_post_meta( $post_id, 'marketking_rule_paymentmethod_percentamount', $rule_paymentmethod_percentamount);
			}
			if ($rule_applies !== NULL){
				update_post_meta( $post_id, 'marketking_rule_applies', $rule_applies);
			}
			if ($rule_who !== NULL){
				update_post_meta( $post_id, 'marketking_rule_who', $rule_who);
			}
			if ($rule_orders !== NULL){
				update_post_meta( $post_id, 'marketking_rule_orders', $rule_orders);
			}
			if ($rule_vendors_who !== NULL){
				update_post_meta( $post_id, 'marketking_rule_vendors_who', $rule_vendors_who);
			}
			if ($rule_quantity_value !== NULL){
				update_post_meta( $post_id, 'marketking_rule_quantity_value', $rule_quantity_value);
			}
			if ($rule_howmuch !== NULL){
				update_post_meta( $post_id, 'marketking_rule_howmuch', $rule_howmuch);
			}
			if ($rule_x !== NULL){
				update_post_meta( $post_id, 'marketking_rule_x', $rule_x);
			}
			if ($rule_taxname !== NULL){
				update_post_meta( $post_id, 'marketking_rule_taxname', $rule_taxname);
			}
			if ($rule_tax_shipping !== NULL){
				update_post_meta( $post_id, 'marketking_rule_tax_shipping', $rule_tax_shipping);
			}
			if ($rule_tax_shipping_rate !== NULL){
				update_post_meta( $post_id, 'marketking_rule_tax_shipping_rate', $rule_tax_shipping_rate);
			}
			if ($rule_discountname !== NULL){
				update_post_meta( $post_id, 'marketking_rule_discountname', $rule_discountname);
			}
			if ($rule_conditions !== NULL){
				update_post_meta( $post_id, 'marketking_rule_conditions', $rule_conditions);
			}
			if ($rule_tags !== NULL){
				update_post_meta( $post_id, 'marketking_rule_tags', $rule_tags);
			}
			if ($rule_discount_show_everywhere !== NULL){
				update_post_meta( $post_id, 'marketking_rule_discount_show_everywhere', $rule_discount_show_everywhere);
			}

			
			if ($rule_countries !== NULL){
				$countries_string = '';
				foreach ($rule_countries as $country){
					$countries_string .= sanitize_text_field ($country).',';
				}
				// remove last comma
				$countries_string = substr($countries_string, 0, -1);
				update_post_meta( $post_id, 'marketking_rule_countries', $countries_string);
			}
			if ($rule_requires !== NULL){
				update_post_meta( $post_id, 'marketking_rule_requires', $rule_requires);
			}
			if ($rule_showtax !== NULL){
				update_post_meta( $post_id, 'marketking_rule_showtax', $rule_showtax);
			}

			if ($rule_applies_multiple_options !== NULL){
				$options_string = '';
				foreach ($rule_applies_multiple_options as $option){
					$options_string .= sanitize_text_field ($option).',';
				}
				// remove last comma
				$options_string = substr($options_string, 0, -1);
				update_post_meta( $post_id, 'marketking_rule_applies_multiple_options', $options_string);
			}

			if ($rule_who_multiple_options !== NULL){
				$options_string = '';
				foreach ($rule_who_multiple_options as $option){
					$options_string .= sanitize_text_field ($option).',';
				}
				// remove last comma
				$options_string = substr($options_string, 0, -1);
				update_post_meta( $post_id, 'marketking_rule_who_multiple_options', $options_string);
			}

			if ($rule_vendors_who_multiple_options !== NULL){
				$options_string = '';
				foreach ($rule_vendors_who_multiple_options as $option){
					$options_string .= sanitize_text_field ($option).',';
				}
				// remove last comma
				$options_string = substr($options_string, 0, -1);
				update_post_meta( $post_id, 'marketking_rule_vendors_who_multiple_options', $options_string);
			}

			$rule_replaced =  sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_applies_replaced')); 
			update_post_meta( $post_id, 'marketking_rule_product_ids', $rule_replaced);


		}
	}
	// Add custom columns to RULES menu
	function marketking_add_columns_group_menu_rules($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'title' => esc_html__( 'Rule name', 'marketking' ),
			'marketking_commission' => esc_html__( 'Commission', 'marketking' ),

		);

		$columns = array_slice($columns_initial, 0, 1, true) + $columns;

	    return $columns;
	}

	// Add groups custom columns data
	function marketking_columns_group_data_rules( $column, $post_id ) {
	    switch ( $column ) {

	        case 'marketking_commission' :
	        	$rule_type = get_post_meta($post_id,'marketking_rule_what', true);
	        	$howmuch = get_post_meta($post_id,'marketking_rule_howmuch', true);
	        	$text = '';
	        	if ($rule_type === 'percentage'){
	        		$text = $howmuch.'%';
	        	} else if ($rule_type === 'fixed'){
	        		$text = wc_price($howmuch);
	        	}

	            echo '<strong>'.wp_kses( $text, array( 'span' => true, 'bdi' => true ) ).'</strong>';
	            break;


	    }
	}


		// Register new post type: Group Rules
		public static function marketking_register_post_type_group_rules() {
			// Build labels and arguments
		    $labels = array(
		        'name'                  => esc_html__( 'Group Rules', 'marketking' ),
		        'singular_name'         => esc_html__( 'Rule', 'marketking' ),
		        'all_items'             => esc_html__( 'Group Rules', 'marketking' ),
		        'menu_name'             => esc_html__( 'Group Rules', 'marketking' ),
		        'add_new'               => esc_html__( 'Create new rule', 'marketking' ),
		        'add_new_item'          => esc_html__( 'Create new rule', 'marketking' ),
		        'edit'                  => esc_html__( 'Edit', 'marketking' ),
		        'edit_item'             => esc_html__( 'Edit rule', 'marketking' ),
		        'new_item'              => esc_html__( 'New rule', 'marketking' ),
		        'view_item'             => esc_html__( 'View rule', 'marketking' ),
		        'view_items'            => esc_html__( 'View rules', 'marketking' ),
		        'search_items'          => esc_html__( 'Search rules', 'marketking' ),
		        'not_found'             => esc_html__( 'No rules found', 'marketking' ),
		        'not_found_in_trash'    => esc_html__( 'No rules found in trash', 'marketking' ),
		        'parent'                => esc_html__( 'Parent rule', 'marketking' ),
		        'featured_image'        => esc_html__( 'Rule image', 'marketking' ),
		        'set_featured_image'    => esc_html__( 'Set rule image', 'marketking' ),
		        'remove_featured_image' => esc_html__( 'Remove rule image', 'marketking' ),
		        'use_featured_image'    => esc_html__( 'Use as rule image', 'marketking' ),
		        'insert_into_item'      => esc_html__( 'Insert into rule', 'marketking' ),
		        'uploaded_to_this_item' => esc_html__( 'Uploaded to this rule', 'marketking' ),
		        'filter_items_list'     => esc_html__( 'Filter rules', 'marketking' ),
		        'items_list_navigation' => esc_html__( 'Rules navigation', 'marketking' ),
		        'items_list'            => esc_html__( 'Commission rules list', 'marketking' )
		    );
		    $args = array(
		        'label'                 => esc_html__( 'Group Rules', 'marketking' ),
		        'description'           => esc_html__( 'This is where you can create group rules', 'marketking' ),
		        'labels'                => $labels,
		        'supports'              => array( 'title'),
		        'hierarchical'          => false,
		        'public'                => false,
		        'show_ui'               => true,
		        'show_in_menu'          => false,
		        'menu_position'         => false,
		        'show_in_admin_bar'     => true,
		        'show_in_nav_menus'     => false,
		        'can_export'            => true,
		        'has_archive'           => false,
		        'exclude_from_search'   => true,
		        'publicly_queryable'    => false,
		        'capability_type'       => 'post',
		        'map_meta_cap'          => true,
		        'show_in_rest'          => true,
		        'rest_base'             => 'marketking_grule',
		        'rest_controller_class' => 'WP_REST_Posts_Controller',
		    );

			// Actually register the post type
			register_post_type( 'marketking_grule', $args );
		}

		// Add Rule Details Metabox to Rules
		function marketking_group_rules_metaboxes($post_type) {
		    $post_types = array('marketking_grule');     //limit meta box to certain post types
	       	if ( in_array( $post_type, $post_types ) ) {
		           add_meta_box(
		               'marketking_rule_details_metabox'
		               ,esc_html__( 'Rule Details', 'marketking' )
		               ,array( $this, 'marketking_grule_details_metabox_content' )
		               ,$post_type
		               ,'advanced'
		               ,'high'
		           );
		       }
		}

		// Rule Details Metabox Content
			function marketking_grule_details_metabox_content(){
				global $post;
				?>
				<div class="marketking_commission_rule_metabox_content_container">
					<div class="marketking_rule_select_container">
						<div class="marketking_rule_label"><?php esc_html_e('Rule type:','marketking'); ?></div>
						<select id="marketking_rule_select_what" name="marketking_rule_select_what">
							<?php
							// if page not "Add new", get selected
							$selected = '';
							if( get_current_screen()->action !== 'add'){
					        	$selected = esc_html(get_post_meta($post->ID, 'marketking_rule_what', true));
					        }
							?>
							<option value="change_group" <?php selected('change_group',$selected,true); ?>><?php esc_html_e('Change group','marketking'); ?></option>
						</select>
					</div>

					<div class="marketking_rule_select_container" id="marketking_container_applies">
						<div class="marketking_rule_label"><?php esc_html_e('Condition:','marketking'); ?></div>
						
						<select id="marketking_rule_select_applies" name="marketking_rule_select_applies">
							<?php
							// if page not "Add new", get selected
							$selected = '';
							if( get_current_screen()->action !== 'add'){
					        	$selected = esc_html(get_post_meta($post->ID, 'marketking_rule_applies', true));
					        	$rule_replaced = esc_html(get_post_meta($post->ID, 'marketking_rule_replaced', true));
					        	if ($rule_replaced === 'yes' && $selected === 'multiple_options'){
					        		$selected = 'replace_ids';
					        	}
					        }
							?>
							<option value="order_value_total" <?php selected('order_value_total',$selected,true); ?>><?php esc_html_e('Total orders value (completed orders)','marketking'); ?></option>
							
						</select>
					</div>
					<div id="marketking_container_howmuch" class="marketking_rule_select_container">
						<div class="marketking_rule_label"><?php esc_html_e('How much:','marketking'); ?></div>
						<input type="number" step="0.001" name="marketking_rule_select_howmuch" id="marketking_rule_select_howmuch" value="<?php echo esc_attr(get_post_meta($post->ID, 'marketking_rule_howmuch', true)); ?>">
					</div>
					<div class="marketking_rule_select_container">
						<div class="marketking_rule_label"><?php esc_html_e('For who:','marketking'); ?></div>
						<select id="marketking_rule_select_agents_who" name="marketking_rule_select_agents_who">
							<?php
							// if page not "Add new", get selected
							$selected = '';
							if( get_current_screen()->action !== 'add'){
					        	$selected = esc_html(get_post_meta($post->ID, 'marketking_rule_agents_who', true));
					        }
							?>
							<optgroup label="<?php esc_attr_e('Multiple', 'marketking'); ?>">
								<option value="multiple_options" <?php selected('multiple_options',$selected,true); ?>><?php esc_html_e('Select multiple options','marketking'); ?></option>
							</optgroup>
							<optgroup label="<?php esc_attr_e('Vendor Groups', 'marketking'); ?>">
								<?php
								// Get all groups
								$groups = get_posts( array( 'post_type' => 'marketking_group','post_status'=>'publish','numberposts' => -1) );
								foreach ($groups as $group){
									echo '<option value="group_'.esc_attr($group->ID).'" '.selected('group_'.$group->ID,$selected,false).'>'.esc_html($group->post_title).'</option>';
								}
								?>
							</optgroup>
						</select>
					</div>
					<div class="marketking_rule_select_container" id="marketking_container_forcustomers">
						<div class="marketking_rule_label"><?php esc_html_e('New group:','marketking'); ?></div>
						<select id="marketking_rule_select_who" name="marketking_rule_select_who">
							<?php
							// if page not "Add new", get selected
							$selected = '';
							if( get_current_screen()->action !== 'add'){
					        	$selected = esc_html(get_post_meta($post->ID, 'marketking_rule_who', true));
					        }
							?>
							<optgroup label="<?php esc_attr_e('Vendor Groups', 'marketking'); ?>">
								<?php
								// Get all groups
								$groups = get_posts( array( 'post_type' => 'marketking_group','post_status'=>'publish','numberposts' => -1) );
								foreach ($groups as $group){
									echo '<option value="group_'.esc_attr($group->ID).'" '.selected('group_'.$group->ID,$selected,false).'>'.esc_html($group->post_title).'</option>';
								}
								?>
							</optgroup>
						</select>
					</div>
					<br><br>
					<div id="marketking_select_multiple_agents_selector" >
						<div class="marketking_select_multiple_products_categories_title">
							<?php esc_html_e('Select multiple vendor options','marketking'); ?>
						</div>
						<select class="marketking_select_multiple_product_categories_selector_select" name="marketking_select_multiple_agents_selector_select[]" multiple>
							<?php
							// if page not "Add new", get selected options
							$selected_options = array();
							if( get_current_screen()->action !== 'add'){
					        	$selected_options_string = get_post_meta($post->ID, 'marketking_rule_agents_who_multiple_options', true);
					        	$selected_options = explode(',', $selected_options_string);
					        }
							?>
							<optgroup label="<?php esc_attr_e('Vendor Groups', 'marketking'); ?>">
								<?php
								// Get all groups
								$groups = get_posts( array( 'post_type' => 'marketking_group','post_status'=>'publish','numberposts' => -1) );
								foreach ($groups as $group){
		    		            	$is_selected = 'no';
		    		            	foreach ($selected_options as $selected_option){
										if ($selected_option === ('group_'.$group->ID )){
											$is_selected = 'yes';
										}
									}
									echo '<option value="group_'.esc_attr($group->ID).'" '.selected('yes',$is_selected,false).'>'.esc_html($group->post_title).'</option>';
								}
								?>
							</optgroup>
							
						</select>

					</div>



					<br /><br />
					
				</div>
				<?php
			}

		// Save Rules Metabox Content
		function marketking_save_group_rules_metaboxes($post_id){
			if (isset($_POST['_inline_edit'])){
				if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
				    return;
				}
			}
			if (isset($_REQUEST['bulk_edit'])){
			    return;
			}

			if (get_post_type($post_id) === 'marketking_grule'){

				// set that rules have changed so that pricing cache can be updated
				update_option('marketking_commission_rules_have_changed', 'yes');

				// delete all marketking transients
				global $wpdb;
				$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%transient_marketking%'" );
				foreach( $plugin_options as $option ) {
				    delete_option( $option->option_name );
				}
				wp_cache_flush();

				$rule_what = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_what'));
				$rule_applies = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_applies'));
				$rule_orders = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_orders'));

				$rule_who = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_who'));
				$rule_agents_who = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_agents_who'));

				$rule_quantity_value = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_quantity_value'));
				$rule_tax_shipping = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_tax_shipping'));
				$rule_tax_shipping_rate = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_tax_shipping_rate'));
				$rule_howmuch = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_howmuch'));
				$rule_x = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_x'));

				$rule_currency = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_currency'));
				$rule_paymentmethod = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_paymentmethod'));
				$rule_paymentmethod_minmax = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_paymentmethod_minmax'));
				$rule_paymentmethod_percentamount = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_paymentmethod_percentamount'));

				$rule_taxname = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_taxname'));
				$rule_discountname = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_discountname'));
				$rule_conditions = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_conditions'));
				$rule_tags = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_tags'));
				$rule_discount_show_everywhere = sanitize_text_field(filter_input(INPUT_POST, 'marketking_commission_rule_discount_show_everywhere_checkbox_input'));
				
				if (isset($_POST['marketking_rule_select_countries'])){
					$rule_countries = $_POST['marketking_rule_select_countries'];
				} else {
					$rule_countries = NULL;
				}

				if (isset($_POST['marketking_select_multiple_product_categories_selector_select'])){
					$rule_applies_multiple_options = $_POST['marketking_select_multiple_product_categories_selector_select'];
				} else {
					$rule_applies_multiple_options = NULL;
				}

				if (isset($_POST['marketking_select_multiple_users_selector_select'])){
					$rule_who_multiple_options = $_POST['marketking_select_multiple_users_selector_select'];
				} else {
					$rule_who_multiple_options = NULL;
				}

				if (isset($_POST['marketking_select_multiple_agents_selector_select'])){
					$rule_agents_who_multiple_options = $_POST['marketking_select_multiple_agents_selector_select'];
				} else {
					$rule_agents_who_multiple_options = NULL;
				}

				$rule_requires = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_requires'));
				$rule_showtax = sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_showtax'));

				if ($rule_what !== NULL){
					update_post_meta( $post_id, 'marketking_rule_what', $rule_what);
				}
				if ($rule_currency !== NULL){
					update_post_meta( $post_id, 'marketking_rule_currency', $rule_currency);
				}
				if ($rule_paymentmethod !== NULL){
					update_post_meta( $post_id, 'marketking_rule_paymentmethod', $rule_paymentmethod);
				}
				if ($rule_paymentmethod_minmax !== NULL){
					update_post_meta( $post_id, 'marketking_rule_paymentmethod_minmax', $rule_paymentmethod_minmax);
				}
				if ($rule_paymentmethod_percentamount !== NULL){
					update_post_meta( $post_id, 'marketking_rule_paymentmethod_percentamount', $rule_paymentmethod_percentamount);
				}
				if ($rule_applies !== NULL){
					update_post_meta( $post_id, 'marketking_rule_applies', $rule_applies);
				}
				if ($rule_who !== NULL){
					update_post_meta( $post_id, 'marketking_rule_who', $rule_who);
				}
				if ($rule_orders !== NULL){
					update_post_meta( $post_id, 'marketking_rule_orders', $rule_orders);
				}
				if ($rule_agents_who !== NULL){
					update_post_meta( $post_id, 'marketking_rule_agents_who', $rule_agents_who);
				}
				if ($rule_quantity_value !== NULL){
					update_post_meta( $post_id, 'marketking_rule_quantity_value', $rule_quantity_value);
				}
				if ($rule_howmuch !== NULL){
					update_post_meta( $post_id, 'marketking_rule_howmuch', $rule_howmuch);
				}
				if ($rule_x !== NULL){
					update_post_meta( $post_id, 'marketking_rule_x', $rule_x);
				}
				if ($rule_taxname !== NULL){
					update_post_meta( $post_id, 'marketking_rule_taxname', $rule_taxname);
				}
				if ($rule_tax_shipping !== NULL){
					update_post_meta( $post_id, 'marketking_rule_tax_shipping', $rule_tax_shipping);
				}
				if ($rule_tax_shipping_rate !== NULL){
					update_post_meta( $post_id, 'marketking_rule_tax_shipping_rate', $rule_tax_shipping_rate);
				}
				if ($rule_discountname !== NULL){
					update_post_meta( $post_id, 'marketking_rule_discountname', $rule_discountname);
				}
				if ($rule_conditions !== NULL){
					update_post_meta( $post_id, 'marketking_rule_conditions', $rule_conditions);
				}
				if ($rule_tags !== NULL){
					update_post_meta( $post_id, 'marketking_rule_tags', $rule_tags);
				}
				if ($rule_discount_show_everywhere !== NULL){
					update_post_meta( $post_id, 'marketking_rule_discount_show_everywhere', $rule_discount_show_everywhere);
				}

				
				if ($rule_countries !== NULL){
					$countries_string = '';
					foreach ($rule_countries as $country){
						$countries_string .= sanitize_text_field ($country).',';
					}
					// remove last comma
					$countries_string = substr($countries_string, 0, -1);
					update_post_meta( $post_id, 'marketking_rule_countries', $countries_string);
				}
				if ($rule_requires !== NULL){
					update_post_meta( $post_id, 'marketking_rule_requires', $rule_requires);
				}
				if ($rule_showtax !== NULL){
					update_post_meta( $post_id, 'marketking_rule_showtax', $rule_showtax);
				}

				if ($rule_applies_multiple_options !== NULL){
					$options_string = '';
					foreach ($rule_applies_multiple_options as $option){
						$options_string .= sanitize_text_field ($option).',';
					}
					// remove last comma
					$options_string = substr($options_string, 0, -1);
					update_post_meta( $post_id, 'marketking_rule_applies_multiple_options', $options_string);
				}

				if ($rule_who_multiple_options !== NULL){
					$options_string = '';
					foreach ($rule_who_multiple_options as $option){
						$options_string .= sanitize_text_field ($option).',';
					}
					// remove last comma
					$options_string = substr($options_string, 0, -1);
					update_post_meta( $post_id, 'marketking_rule_who_multiple_options', $options_string);
				}

				if ($rule_agents_who_multiple_options !== NULL){
					$options_string = '';
					foreach ($rule_agents_who_multiple_options as $option){
						$options_string .= sanitize_text_field ($option).',';
					}
					// remove last comma
					$options_string = substr($options_string, 0, -1);
					update_post_meta( $post_id, 'marketking_rule_agents_who_multiple_options', $options_string);
				}

				$rule_replaced =  sanitize_text_field(filter_input(INPUT_POST, 'marketking_rule_select_applies_replaced')); 
				$rule_replaced_array = explode(',',$rule_replaced);
				$rule_replaced_string = '';
				foreach ($rule_replaced_array as $element){
					$rule_replaced_string.= 'product_'.trim($element).',';
				}
				// remove last comma
				$rule_replaced_string = substr($rule_replaced_string, 0, -1);

				// if rule applies is product & variation IDS, set applies as marketking_rule_select_applies_replaced
				if ($rule_applies === 'replace_ids'){
					if ($rule_replaced !== NULL){
						update_post_meta( $post_id, 'marketking_rule_applies', 'multiple_options');
						update_post_meta( $post_id, 'marketking_rule_applies_multiple_options', $rule_replaced_string);
						update_post_meta( $post_id, 'marketking_rule_replaced', 'yes');
					}
				} else {
					update_post_meta( $post_id, 'marketking_rule_replaced', 'no');
				}

			}
		}

		// Add custom columns to Group Rules menu
		function marketking_add_columns_grule_menu($columns) {

			$columns_initial = $columns;
			
			// rename title
			$columns = array(
				'title' => esc_html__( 'Rule name', 'marketking' ),
				'type' => esc_html__( 'Rule type', 'marketking' ),
				'condition' => esc_html__( 'Condition', 'marketking' ),
				'value' => esc_html__( 'Value', 'marketking' ),
				'newgroup' => esc_html__( 'New Group', 'marketking' ),
			);

			$columns = array_slice($columns_initial, 0, 1, true) + $columns;

		    return $columns;
		}

		// Add groups custom columns data
		function marketking_columns_grule_data( $column, $post_id ) {

			$rule_type = get_post_meta($post_id,'marketking_rule_what', true);
			if ($rule_type === 'change_group'){
				$rule_type = esc_html__('Change group','marketking');
			}

			$condition = get_post_meta($post_id,'marketking_rule_applies', true);
			if ($condition === 'earnings_total'){
				$condition = esc_html__('Total earnings reached','marketking');
			} else if ($condition === 'order_value_total'){
				$condition = esc_html__('Total order value (completed orders)','marketking');
			}

			$howmuch = get_post_meta($post_id,'marketking_rule_howmuch', true);
			$howmuch = strip_tags(wc_price($howmuch));
			$newgroup = get_post_meta($post_id,'marketking_rule_who', true);
			$newgroup = get_the_title(explode('_',$newgroup)[1]);
		    switch ( $column ) {

		        case 'type' :

		            echo '<strong>'.esc_html($rule_type).'</strong>';
		            break;

		        case 'condition' :

		            echo '<strong>'.esc_html($condition).'</strong>';
		            break;

		        case 'value' :

		            echo '<strong>'.esc_html($howmuch).'</strong>';
		            break;


		        case 'newgroup' :

		            echo '<strong>'.esc_html($newgroup).'</strong>';
		            break;

		    }
		}

	function update_visibility_cache_when_new_rating( $comment_id, $is_approved, $commentdata ) {

		if (get_option('marketking_vendor_priority_setting', 'lowerprice') === 'higherrated'){
		    if ( ! is_admin() && ( 'product' === get_post_type( absint( $commentdata['comment_post_ID'] ) ) ) && ( 'review' === $commentdata['comment_type'] ) ) {

		    	$product_id = absint( $commentdata['comment_post_ID'] );
		    	$vendor_id = marketking()->get_product_vendor($product_id);
		    	// get all vendor products
		    	$products = get_posts( array( 
		    	    'post_type' => 'product',
		    	    'numberposts' => -1,
		    	    'post_status'    => 'any',
		    	    'fields'    => 'ids',
		    	    'author'	=> $vendor_id
		    	));
		    	foreach ($products as $vendor_product_id){
		    		marketking()->update_visibility_cache($vendor_product_id);
		    	}
		    }
		}
	}

	public static function rebuild_visibility_cache_needed(){
		if (get_option('marketking_rebuild_visibility_cache','no')==='yes'){
			// rebuild visibility cache
			marketking()->rebuild_visibility_cache();
		}
	}

	function update_visibility_cache_when_settings_change( $old_value, $new_value ){
	    marketking()->rebuild_visibility_cache();
	}

	function update_visibility_cache_when_stock_changes( $product ) {
		marketking()->update_visibility_cache($product->get_id());
	}

	function update_product_set_cache($post_id){
		if (get_post_type($post_id) === 'product'){
			if(get_post_meta($post_id,'marketking_is_product_standby', true) !== 'yes'){
				marketking()->update_visibility_cache($post_id);
			}
		}
	}

	function marketking_product_categories_visibility_rules( $q ){

		if (!marketking()->is_vendor_store_page()){

			if (isset($q->query_vars['wc_query'])){
				if ( 'product' !== $q->get( 'post_type' ) && array('product') !== $q->get( 'post_type' ) && $q->query_vars['wc_query'] !== 'product_query') { 
					return;
				}
			}		
			
			$not_visible_ids = marketking()->get_not_visible_ids_cache(); 		

			if (isset($q->query_vars['post__not_in'])){
				$currentval = $q->query_vars['post__not_in'];
				if (!empty($currentval) && $not_visible_ids !== false){
					$not_visible_ids = array_merge($not_visible_ids, $currentval);
				}
			}
				
			if ($not_visible_ids){
			    if(!empty($not_visible_ids)){
			    	$q->set('post__not_in',$not_visible_ids);
				}
			}
		}
	}


	function marketking_product_categories_visibility_rules_related( $q, $product_id, $args ){

		if (!marketking()->is_vendor_store_page()){
		
			$not_visible_ids = marketking()->get_not_visible_ids_cache(); 		

			$currentval = '';
			if (isset($q->query_vars['post__not_in'])){
				$currentval = $q->query_vars['post__not_in'];
			}

			if (!empty($currentval) && $not_visible_ids !== false){
				$not_visible_ids = array_merge($not_visible_ids, $currentval);
			}
				
			if ($not_visible_ids){
			    if(!empty($not_visible_ids)){
			    	$datastore = new WC_Product_Data_Store_CPT;

			    	$querynew = $datastore->get_related_products_query( $args['categories'], $args['tags'], array_merge($args['exclude_ids'],$not_visible_ids), $args['limit'] );

			    	return $querynew;
				}
			}
		}

		return $q;
	}

	function marketking_product_categories_visibility_rules_related2( $related_posts, $product_id, $args ){

		// we have an array of product ids (strings), and we need to remove the ones in cache

		if (!marketking()->is_vendor_store_page()){
		
			$not_visible_ids = marketking()->get_not_visible_ids_cache(); 		

			$currentval = $args['excluded_ids'];
			if (!empty($currentval) && $not_visible_ids !== false){
				$not_visible_ids = array_merge($not_visible_ids, $currentval);
			}

			if ($not_visible_ids){
			    if(!empty($not_visible_ids)){
			    	foreach ($related_posts as $key => $relatedid){
			    		if(in_array(intval($relatedid), $not_visible_ids)){
			    			unset($related_posts[$key]);
			    		}
			    	}
				}
			}

			// if we are in the product page of a product, we want to exclude the priority winner too
			$linkedproducts = marketking()->get_linkedproducts($product_id,'array');
			foreach ($related_posts as $key => $relatedid){
				if(in_array($relatedid, $linkedproducts)){
					unset($related_posts[$key]);
				}
			}
		}


		return $related_posts;
	}


	function marketking_product_categories_visibility_rules_shortcode( $query_args ){

		// if page is not vendor store page
		if (!marketking()->is_vendor_store_page()){

			if (isset($query_args['post_type'])){
				// if not product, then return 
				if ($query_args['post_type'] !== 'product' && $query_args['post_type'] !== array('product')){
					return $query_args;
				}
			}

			$not_visible_ids = marketking()->get_not_visible_ids_cache(); 	

			if (isset($query_args['post__not_in'])){
				$currentval = $query_args['post__not_in'];
				if (!empty($currentval) && $not_visible_ids !== false){
					$not_visible_ids = array_merge($not_visible_ids, $currentval);
				}
			}

			if ($not_visible_ids){
			    if(!empty($not_visible_ids)){
			    	$query_args['post__not_in'] = $not_visible_ids;

				}
			}

		}
		return $query_args;
	}

	function marketking_product_categories_visibility_rules_productfilter($visible, $product_id){
		
		if (!marketking()->is_vendor_store_page()){

			$not_visible_ids = marketking()->get_not_visible_ids_cache(); 	

			if(!empty($not_visible_ids) && $visible !== false){
		    	$post_parent_id = wp_get_post_parent_id($product_id);
		    	if (in_array($product_id, $not_visible_ids) || in_array($post_parent_id, $not_visible_ids)){
		    		$visible = false;
		    	}
			}	
		}
		

		return $visible;
	}


	function asl_query_args_postin($args) {

		if (!marketking()->is_vendor_store_page()){

			$args['post_not_in'] = is_array($args['post_not_in']) ? $args['post_not_in'] : array();

			$not_visible_ids = marketking()->get_not_visible_ids_cache(); 

			$currentval = $args['post_not_in'];
			if (!empty($currentval) && $not_visible_ids !== false){
				$not_visible_ids = array_merge($not_visible_ids, $currentval);
			}
				
			if ($not_visible_ids){
			    if(!empty($not_visible_ids)){
			    	$args['post_not_in'] = $not_visible_ids;
			    }
			}		
		}		   				    
		
		return $args;
	}

	function filter_product_visibility_vacation($visible, $product_id){

		// check if vendor is on vacation
		$vendor_id = marketking()->get_product_vendor($product_id);
		if (marketking()->is_on_vacation($vendor_id)){
			$visible = false;
		}

		return $visible;
	}

	function marketking_reports_get_data(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$vendor = sanitize_text_field($_POST['vendor']);
		$firstday = sanitize_text_field($_POST['firstday']);
		$lastday = sanitize_text_field($_POST['lastday']);


		global $wpdb;

		if (apply_filters('marketking_dashboard_set_timezone', true)){
			$timezone = get_option('timezone_string');
			if (empty($timezone) || $timezone === null){
				$timezone = 'UTC';
			}
		//	date_default_timezone_set($timezone);
		}

		$date_to = $lastday.' 23:59:59';
		$date_from = $firstday;

		$post_status = implode("','", array('wc-processing', 'wc-completed') );

		if ($vendor === 'all'){
			// all orders = general marketplace report
			$orders = $wpdb->get_results( "SELECT ID FROM $wpdb->posts 
	            WHERE post_type = 'shop_order'
	            AND post_status IN ('{$post_status}')
	            AND post_date BETWEEN '{$date_from}  00:00:00' AND '{$date_to}'
	        ");
		} else {
			// report for specific vendor $vendor is vendor_id
			$orders = $wpdb->get_results( "SELECT ID FROM $wpdb->posts 
	            WHERE post_type = 'shop_order'
	            AND post_status IN ('{$post_status}')
	            AND post_author = $vendor
	            AND post_date BETWEEN '{$date_from}  00:00:00' AND '{$date_to}'
	        ");
		}
		

        //calculate sales total and order numbers
        $sales_total = 0;
        $order_number = 0;
        $timestamps_sales = array();
        $timestamps_orders = array();

        foreach ($orders as $order){

        	$sales_total += get_post_meta($order->ID,'_order_total', true);
        	$order_number++;

        	$orderobj = wc_get_order($order->ID);
        	$date = $orderobj->get_date_created()->getTimestamp()+(get_option('gmt_offset')*3600);

        	// if this microsecond slot is occupied, use next one, for accurate charts
        	while(isset($timestamps_sales[$date])){
        		$date++;
        	}
       		$timestamps_sales[$date] = get_post_meta($order->ID,'_order_total', true);
        	$timestamps_orders[$date] = 1;

        }

        $sales_total_wc = wc_price($sales_total);

        // calculate new vendors if "all option"
        if ($vendor === 'all'){
			$vendors = get_users(array(
			    'meta_query'=> array(
	    	  		'relation' => 'AND',
	                array(
	                    'key' => 'marketking_account_approved',
	                    'value' => 'no',
	                    'compare' => '!=',
	                ),
	                array(
	                    'key' => 'marketking_group',
	                    'value' => 'none',
	                    'compare' => '!=',
	                ),
	        	),
			    'date_query'    => array(
		            array(
		            	'before'     => $date_to,
		                'after'     => $date_from,
		                'inclusive' => true,
		            ),
		         )
			));
			$new_vendors = count($vendors);

			// get admin commission
			$commission_data = marketking()->get_earnings('allvendors', 'fromto', false, false, false, true, $date_from, $date_to, true);
			$commission = explode('***',$commission_data)[0];
			$timestamps_commissions = unserialize(explode('***',$commission_data)[1]);

        } else {
        	$new_vendors = '-';

        	// get admin commission
        	$commission_data = marketking()->get_earnings($vendor, 'fromto', false, false, false, true, $date_from, $date_to, true);
        	$commission = explode('***',$commission_data)[0];
        	$timestamps_commissions = unserialize(explode('***',$commission_data)[1]);
        }

        $commission_wc = wc_price($commission);

        // 1. Establish draw labels in chart
        /*
		if user chooses < 32 days, show by day ; if they choose > 31 < 366 show by month; > 366 show by year
	    */
		$timedifference = strtotime($lastday) - strtotime($firstday);
		$nrdays = intval(ceil($timedifference/86400));
		if ($nrdays < 32) { // 32 days
			// show days
			$firstdaynumber = date('d',strtotime($firstday));

			$days_array = array();
			$sales_array = array();
			$ordernr_array = array();
			$commissions_array = array();

			$i = 0;
			while ($i <= $nrdays){
				// build label
				array_push($days_array, date('d',(strtotime($firstday)+86400*$i)));

				// for each day, get sales, ordernr, commission
				$sales_of_the_day = 0;
				$ordernr_of_the_day = 0;
				$sales_of_the_day = 0;
				$commissions_of_the_day = 0;

				foreach ($timestamps_sales as $timestamp => $sales){
					if (date("m.d.y", $timestamp) === date("m.d.y",strtotime($firstday)+86400*$i)){
						$sales_of_the_day += $sales;
						$ordernr_of_the_day++;
					}
				}

				foreach ($timestamps_commissions as $timestamp => $commissions){
					if (date("m.d.y", $timestamp) === date("m.d.y",strtotime($firstday)+86400*$i)){
						$commissions_of_the_day += $commissions;
					}
				}
				array_push($sales_array, $sales_of_the_day);
				array_push($ordernr_array, $ordernr_of_the_day);
				array_push($commissions_array, $commissions_of_the_day);


				$i++;

			}

			$labels = json_encode($days_array);

		} else if ($nrdays >= 32){

			// show months
			$firstmonthnumber = date('m.y',strtotime($firstday));
			$lastmonthnumber = date('m.y',strtotime($lastday));

			$months_array = array();
			$sales_array = array();
			$ordernr_array = array();
			$commissions_array = array();
			$i = 1;
			while ($i !== 'stop'){
				
				// for each month, get sales, ordernr, commission
				$sales_of_the_month = 0;
				$ordernr_of_the_month = 0;
				$sales_of_the_month = 0;
				$commissions_of_the_month = 0;

				foreach ($timestamps_sales as $timestamp => $sales){
					if (date("m.y", $timestamp) === $firstmonthnumber){
						$sales_of_the_month += $sales;
						$ordernr_of_the_month++;
					}
				}

				foreach ($timestamps_commissions as $timestamp => $commissions){
					if (date("m.y", $timestamp) === $firstmonthnumber){
						$commissions_of_the_month += $commissions;
					}
				}
				array_push($sales_array, $sales_of_the_month);
				array_push($ordernr_array, $ordernr_of_the_month);
				array_push($commissions_array, $commissions_of_the_month);


				// build label
				array_push($months_array, date("M y", strtotime("+".($i-1)." month", strtotime($firstday))));

				if($firstmonthnumber === $lastmonthnumber){
					$i = 'stop';
				} else {
					$firstmonthnumber = date("m.y", strtotime("+".$i." month", strtotime($firstday)));
					$i++;
				}

				
			}

			$labels = json_encode($months_array);

		} 

		// round values to 2 decimals
		foreach ($sales_array as $index => $value){
			$sales_array[$index] = round($value, 2);
		}
		foreach ($commissions_array as $index => $value){
			$commissions_array[$index] = round($value, 2);
		}


		$salestotal = json_encode($sales_array);
		$ordernumbers = json_encode($ordernr_array);
		$commissiontotal = json_encode($commissions_array);

		
		echo $sales_total.'*'.$sales_total_wc.'*'.$order_number.'*'.$new_vendors.'*'.$commission.'*'.$commission_wc.'*'.$labels.'*'.$salestotal.'*'.$ordernumbers.'*'.$commissiontotal;
	
		exit();
	}

	function marketkingaddproductstore(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$product_id = sanitize_text_field($_POST['productid']);

		$product = wc_get_product($product_id);

		if ($product!==false){
			$admin = new WC_Admin_Duplicate_Product;
			$duplicate = $admin->product_duplicate( $product );
			$duplicate->set_name( $product->get_name() );
			$duplicate->set_status( apply_filters('marketking_add_product_store_status', 'publish'));
			$duplicate->save();

			marketking()->set_new_linkedproduct($product_id, $duplicate->get_id());

			do_action('marketking_after_add_product_my_store', $duplicate);

		}

		echo 'success';

		exit();
	}

	function marketking_member_select_plan(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$product_id = sanitize_text_field($_POST['prodid']);

		if (apply_filters('marketking_select_plan_custom', false, $product_id)){
			$go_to_url = apply_filters('marketking_select_plan_custom_url', '#', $product_id);
		} else {
			WC()->cart->add_to_cart( $product_id, 1, 0, array());

			// go directly to cart
			$go_to_url = wc_get_cart_url();
			if (apply_filters('marketking_membership_go_to_product', 0) === 1){
				// go to product page
				$product = wc_get_product( $product_id );
				$go_to_url = $product->get_permalink();
			}

		}
		
		echo esc_url($go_to_url);


		exit();
	}

	function marketkingabusereport(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$user_id = get_current_user_id();

		$product_id = sanitize_text_field($_POST['productid']);
		$message = sanitize_textarea_field($_POST['message']);

		// submit abuse report
		$report = array(
			'post_title'  => sanitize_text_field( esc_html__( 'Abuse Report', 'marketking' ) ),
			'post_status' => 'publish',
			'post_type'   => 'marketking_abuse',
			'post_author' => get_current_user_id(),
		);
		$report_id = wp_insert_post( $report );

		update_post_meta($report_id,'message', $message);
		update_post_meta($report_id,'product', $product_id);
		update_post_meta($report_id,'vendor', marketking()->get_product_vendor($product_id));

		echo 'success';

		exit();
	}

	function marketking_change_follow_status(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$vendor_id = sanitize_text_field($_POST['vendorid']);

		// get current status and change it
		$follows = get_user_meta($user_id,'marketking_follows_vendor_'.$vendor_id, true);

		if ($follows !== 'yes'){
			update_user_meta($user_id,'marketking_follows_vendor_'.$vendor_id, 'yes');
			echo 'followed';
		} else {
			update_user_meta($user_id,'marketking_follows_vendor_'.$vendor_id, 'no');
			echo 'unfollowed';
		}


		exit();
	}

	function marketking_save_notice_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$noticeenabled = sanitize_text_field($_POST['noticeenabled']);
		$noticemessage = sanitize_textarea_field($_POST['noticemessage']);

		$noticeenabled = filter_var($noticeenabled,FILTER_VALIDATE_BOOLEAN);

		if ($noticeenabled === true){
			update_user_meta($user_id,'marketking_notice_enabled', 'yes');	
			
		} else {
			update_user_meta($user_id,'marketking_notice_enabled', 'no');
		}

		update_user_meta($user_id,'marketking_notice_message', $noticemessage);

		echo 'success';
		exit();
	}

	function marketking_save_storecategories_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$selectedcategories = $_POST['storecategories'];

		if (is_array($selectedcategories)){
			$arraycats = array_map('sanitize_text_field',$selectedcategories);
		} else {
			$arraycats = array(sanitize_text_field($selectedcategories));
		}

		update_user_meta($current_id,'marketking_store_categories', $arraycats);

		echo 'success';
		exit();
	}

	function marketking_save_policy_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}
		$user_id = $current_id;

		$policyenabled = sanitize_text_field($_POST['policyenabled']);
		$policyenabled = filter_var($policyenabled,FILTER_VALIDATE_BOOLEAN);

		$rawmessage = $_POST['policymessage'];
		$allowed = array('<h3>','<h4>','<i>','<strong>','</h3>','</h4>','</i>','</strong>');
		$replaced = array('***h3***','***h4***','***i***','***strong***','***/h3***','***/h4***','***/i***','***/strong***');

		$rawmessage = str_replace($allowed, $replaced, $rawmessage);
		$policymessage = sanitize_textarea_field($rawmessage);

		if ($policyenabled === true){
			update_user_meta($user_id,'marketking_policy_enabled', 'yes');	
			
		} else {
			update_user_meta($user_id,'marketking_policy_enabled', 'no');
		}

		update_user_meta($user_id,'marketking_policy_message', $policymessage);

		echo 'success';
		exit();
	}

	function marketking_save_invoice_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$invoicestorename = sanitize_text_field($_POST['invoicestorename']);
		$invoicestoreaddress = sanitize_text_field($_POST['invoicestoreaddress']);
		$invoicecustominfo = sanitize_textarea_field($_POST['invoicecustominfo']);
		$invoicestorelogo = sanitize_text_field($_POST['invoicestorelogo']);

		update_user_meta($user_id,'marketking_invoicestore', $invoicestorename);
		update_user_meta($user_id,'marketking_invoiceaddress', $invoicestoreaddress);
		update_user_meta($user_id,'marketking_invoicecustom', $invoicecustominfo);
		update_user_meta($user_id,'marketking_invoicelogo', $invoicestorelogo);

		echo 'success';
		exit();
	}

	function marketking_save_seo_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$seotitle = sanitize_text_field($_POST['seotitle']);
		$metadescription = sanitize_text_field($_POST['metadescription']);
		$metakeywords = sanitize_text_field($_POST['metakeywords']);

		update_user_meta($user_id,'marketking_seotitle', $seotitle);
		update_user_meta($user_id,'marketking_metadescription', $metadescription);
		update_user_meta($user_id,'marketking_metakeywords', $metakeywords);

		echo 'success';
		exit();
	}

	function marketking_save_social_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$facebook = sanitize_text_field($_POST['facebook']);
		$twitter = sanitize_text_field($_POST['twitter']);
		$instagram = sanitize_text_field($_POST['instagram']);
		$youtube = sanitize_text_field($_POST['youtube']);
		$pinterest = sanitize_text_field($_POST['pinterest']);
		$linkedin = sanitize_text_field($_POST['linkedin']);


		update_user_meta($user_id,'marketking_facebook', $facebook);
		update_user_meta($user_id,'marketking_twitter', $twitter);
		update_user_meta($user_id,'marketking_instagram', $instagram);
		update_user_meta($user_id,'marketking_youtube', $youtube);
		update_user_meta($user_id,'marketking_pinterest', $pinterest);
		update_user_meta($user_id,'marketking_linkedin', $linkedin);

		echo 'success';
		exit();
	}

	function marketking_save_otherrules_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$minordervalb2b = sanitize_text_field($_POST['minordervalb2b']);
		$minorderqtyb2b = sanitize_text_field($_POST['minorderqtyb2b']);

		$minordervalb2c = sanitize_text_field($_POST['minordervalb2c']);
		$minorderqtyb2c = sanitize_text_field($_POST['minorderqtyb2c']);

		update_user_meta($user_id,'marketking_minordervalb2b', $minordervalb2b);
		update_user_meta($user_id,'marketking_minorderqtyb2b', $minorderqtyb2b);
		update_user_meta($user_id,'marketking_minordervalb2c', $minordervalb2c);
		update_user_meta($user_id,'marketking_minorderqtyb2c', $minorderqtyb2c);

		$maxordervalb2b = sanitize_text_field($_POST['maxordervalb2b']);
		$maxorderqtyb2b = sanitize_text_field($_POST['maxorderqtyb2b']);

		$maxordervalb2c = sanitize_text_field($_POST['maxordervalb2c']);
		$maxorderqtyb2c = sanitize_text_field($_POST['maxorderqtyb2c']);

		update_user_meta($user_id,'marketking_maxordervalb2b', $maxordervalb2b);
		update_user_meta($user_id,'marketking_maxorderqtyb2b', $maxorderqtyb2b);
		update_user_meta($user_id,'marketking_maxordervalb2c', $maxordervalb2c);
		update_user_meta($user_id,'marketking_maxorderqtyb2c', $maxorderqtyb2c);

		echo 'success';
		exit();
	}

	function marketking_save_vacation_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$vacationenabled = sanitize_text_field($_POST['vacationenabled']);
		$vacationmessage = sanitize_textarea_field($_POST['vacationmessage']);

		$vacationenabled = sanitize_text_field($_POST['vacationenabled']);
		$vacationenabled = filter_var($vacationenabled,FILTER_VALIDATE_BOOLEAN);

		$closingtime = sanitize_text_field($_POST['closingtime']);
		$closestart = sanitize_text_field($_POST['closestart']);
		$closeend = sanitize_text_field($_POST['closeend']);

		update_user_meta($user_id,'marketking_vacation_closingtime', $closingtime);
		update_user_meta($user_id,'marketking_vacation_closingstart', $closestart);
		update_user_meta($user_id,'marketking_vacation_closingend', $closeend);

		if ($vacationenabled === true){
			update_user_meta($user_id,'marketking_vacation_enabled', 'yes');

			do_action('marketking_vacation_close_shop', $user_id);

			// if close is now
			if ($closingtime === 'now'){
				// set catalog visibility to hidden
				if (apply_filters('marketking_vacation_sets_visibility', true)){
					marketking()->set_vendor_products_visibility($user_id,'hidden');
				}
			} 

			// if close is dates and current date fits
			if ($closingtime === 'dates'){
				$closingstart = strtotime($closestart);
				$closingend = strtotime($closeend);
				// check that current time is between start and end
				$currenttime = time();
				if ($currenttime > $closingstart && $currenttime < $closingend){
					// set catalog visibility to hidden
					if (apply_filters('marketking_vacation_sets_visibility', true)){
						marketking()->set_vendor_products_visibility($user_id,'hidden');
					}
				}
			} 
			
		} else {
			update_user_meta($user_id,'marketking_vacation_enabled', 'no');

			// set catalog visibility to visible
			if (apply_filters('marketking_vacation_sets_visibility', true)){
				marketking()->set_vendor_products_visibility($user_id,'visible');
			}
			do_action('marketking_vacation_open_shop', $user_id);

		}

		update_user_meta($user_id,'marketking_vacation_message', $vacationmessage);

		echo 'success';
		exit();
	}

	function marketking_configure_shipping_method_save(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}
		$user_id = $current_id;

		$methodid = sanitize_text_field($_POST['method_id']);
		$methodvalue = sanitize_text_field($_POST['method_value']);

		$option_key = 'woocommerce_'.$methodvalue.'_'.$methodid.'_settings';



		$shipping_class_names = WC()->shipping->get_shipping_method_class_names();
		$method_instance = new $shipping_class_names[$methodvalue](intval($methodid));

		$method_instance->init_instance_settings();

		$post_data = $method_instance->get_post_data();

		foreach ( $method_instance->get_instance_form_fields() as $key => $field ) {
			if ( 'title' !== $method_instance->get_field_type( $field ) ) {
				try {
					$method_instance->instance_settings[ $key ] = $method_instance->get_field_value( $key, $field, $post_data );
				} catch ( Exception $e ) {
					$method_instance->add_error( $e->getMessage() );
				}
			}
		}

		update_option( $method_instance->get_instance_option_key(), apply_filters( 'woocommerce_shipping_' . $method_instance->id . '_instance_settings_values', $method_instance->instance_settings, $method_instance ), 'yes' );

		echo esc_html($methodid);

		exit();

	}

	function marketking_configure_shipping_method_retrieve(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}
		$user_id = $current_id;

		$methodid = sanitize_text_field($_POST['methodid']);

		$details = '';

		$vendor_shipping_methods = get_user_meta($user_id,'marketking_vendor_shipping_methods', true);
		if (empty($vendor_shipping_methods)){
		    $vendor_shipping_methods = array();
		}
		foreach ($vendor_shipping_methods as $index => $method){
			if (intval($method['instanceid']) === intval($methodid)){
				// get details here
				$shipping_class_names = WC()->shipping->get_shipping_method_class_names();

				$method_instance = new $shipping_class_names[$method['value']](intval($method['instanceid']));

				$options = $method_instance->get_admin_options_html();

				$tip = esc_html__('How to use:','marketking').'<br><br>'.esc_html__( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'woocommerce' ) .' '.esc_html__( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'woocommerce' );
		

				// remove links if any
				$options = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $options);

				$options = str_replace('product shipping class.', 'product shipping class. '.$tip, $options);


				$details .= $options;

				$details .= '<input type="hidden" id="marketking_configure_method_value" value="'.esc_attr($method['value']).'">';

				$details .= '<input type="hidden" id="marketking_configure_method_instance" value="'.esc_attr($method['instanceid']).'">';

			}	
		}

		echo $details;
		exit();

	}

	function marketking_delete_shipping_method_vendor(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}
		$user_id = $current_id;

		$deleted_id = sanitize_text_field($_POST['deletedid']);

		$vendor_shipping_methods = get_user_meta($user_id,'marketking_vendor_shipping_methods', true);
		if (empty($vendor_shipping_methods)){
		    $vendor_shipping_methods = array();
		}
		foreach ($vendor_shipping_methods as $index => $method){
			if (intval($method['instanceid']) === intval($deleted_id)){
				unset($vendor_shipping_methods[$index]);
			}
		}
		update_user_meta($user_id,'marketking_vendor_shipping_methods', $vendor_shipping_methods);

		echo 'success';
		exit();


	}

	function marketking_enable_disable_shipping_method(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;
		$methodid = sanitize_text_field($_POST['methodid']);
		$value = sanitize_text_field($_POST['value']);

		$vendor_shipping_methods = get_user_meta($user_id,'marketking_vendor_shipping_methods', true);
		if (empty($vendor_shipping_methods)){
		    $vendor_shipping_methods = array();
		}
		foreach ($vendor_shipping_methods as $index => $method){
			if (intval($method['instanceid']) === intval($methodid)){
				$vendor_shipping_methods[$index]['enabled'] = $value;
			}
		}

		update_user_meta($user_id,'marketking_vendor_shipping_methods', $vendor_shipping_methods);
		echo 'success';
		exit();


	}

	function marketking_add_shipping_method_vendor(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;


		// build method
		$method = array();
		$method['value'] = sanitize_text_field($_POST['method_value']);
		$method['name'] = sanitize_text_field($_POST['method_name']);
		$method['instanceid'] = hexdec(uniqid());
		$method['zoneid'] = sanitize_text_field($_POST['zone_id']);
		$method['sellerid'] = $user_id;
		$method['enabled'] = 1; // 1 is enabled, 0 is disabled

		$vendor_shipping_methods = get_user_meta($user_id,'marketking_vendor_shipping_methods', true);
		if (empty($vendor_shipping_methods)){
			$vendor_shipping_methods = array();
		}
		array_push($vendor_shipping_methods, $method);

		update_user_meta($user_id,'marketking_vendor_shipping_methods', $vendor_shipping_methods);
		

		echo 'success';
		exit();
	}

	function marketking_save_support_settings(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$supportchoice = sanitize_text_field($_POST['supportchoice']);
		$supporturl = sanitize_textarea_field($_POST['supporturl']);
		$supportemail = sanitize_textarea_field($_POST['supportemail']);

		update_user_meta($user_id,'marketking_support_option', $supportchoice);
		update_user_meta($user_id,'marketking_support_url', $supporturl);
		update_user_meta($user_id,'marketking_support_email', $supportemail);
		

		echo 'success';
		exit();
	}

	// Conversation Details Metabox Content
	function marketking_message_details_metabox_content(){

		// If current page is ADD New Conversation
		if(get_current_screen()->action === 'add'){
			?>
			<div id="marketking_message_details_wrapper">
				<div id="marketking_message_user_container">
					<?php esc_html_e('Vendor: ','marketking'); ?>
					<?php 
					$included_ids = get_users(array(
							    'meta_key'     => 'marketking_group',
							    'meta_value'   => 'none',
							    'meta_compare' => '!=',
							    'fields' => 'ids',
							));

					wp_dropdown_users($args = array('id' => 'marketking_message_user_input', 'name'=>'marketking_message_user_input', 'show' => 'user_login', 'include' => $included_ids)); 

					?>
				</div>
			</div>
			<?php
		} else {
			// just display user
			global $post;
			$user = get_post_meta( $post->ID, 'marketking_message_user', true );
			if ($user === 'shop'){
				$user = get_post_meta ($post->ID, 'marketking_message_message_1_author', true);
			}

			// display status after check
			$status = get_post_meta( $post->ID, 'marketking_conversation_status', true );
			?>
			<div id="marketking_message_details_wrapper">
				<div id="marketking_message_user_container">
					<?php echo esc_html__('Vendor: ', 'marketking').'&nbsp;'; ?>
					<strong> <?php echo esc_html($user); ?></strong>
				</div>
				<div id="marketking_conversation_user_status_container">
					<?php esc_html_e('Status: ','marketking'); ?>
					<select id="marketking_conversation_status_select" name="marketking_conversation_status_select">
						<option value="open" <?php selected('open', $status, true); ?>><?php esc_html_e('Open', 'marketking');?></option>
						<option value="resolved" <?php selected('resolved', $status, true); ?>><?php esc_html_e('Closed', 'marketking');?></option>
					</select>
				</div>
			</div>


			<?php
		}
	}

	// Update message with user message meta
	function marketkingmessagemessage(){

    	// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		do_action('marketking_message_message_start');

		// If nonce verification didn't fail, run further
		$message = apply_filters('marketking_filter_message_general', sanitize_textarea_field($_POST['message']));
		$messageid = sanitize_text_field($_POST['messageid']);

		$currentuser = wp_get_current_user()->user_login;

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
			$currentuser = new WP_User($current_id);
			$currentuser = $currentuser->user_login;
		}

		$messageuser = get_post_meta ($messageid, 'marketking_message_user', true);
		$messageuser2 = get_post_meta ($messageid, 'marketking_message_message_1_author', true);

		if (get_post_type($messageid) === 'marketking_refund' && intval(get_post_field ('post_author', $messageid)) === $current_id){
			$refund_ok = true;
		} else {
			$refund_ok = false;
		}

		// Check message not empty
		if ($message !== NULL && trim($message) !== ''){
			// Check user permission against message user meta. Check subaccounts as well
			$current_user_id = $current_id;
			$currentuser = new WP_User($current_id);
			$currentuser = $currentuser->user_login;

		    if ($currentuser === $messageuser || $currentuser === $messageuser2 || $refund_ok){

				$nr_messages = intval(get_post_meta ($messageid, 'marketking_message_messages_number', true));
				$current_message_nr = $nr_messages+1;
				update_post_meta( $messageid, 'marketking_message_message_'.$current_message_nr, $message);
				update_post_meta( $messageid, 'marketking_message_messages_number', $current_message_nr);
				update_post_meta( $messageid, 'marketking_message_message_'.$current_message_nr.'_author', $currentuser );
				update_post_meta( $messageid, 'marketking_message_message_'.$current_message_nr.'_time', time() );

				update_post_meta( $messageid, 'marketking_conversation_status', 'open');


				do_action('marketking_message_after_message_inserted', $messageid, $current_message_nr, $message);

				// not for refunds
				if (!$refund_ok){
					$vendor_id = marketkingpro()->get_conversation_party($messageid, 'vendor');
					$vendor = new WP_User($vendor_id);
					$recipient = $vendor->user_email;
					do_action( 'marketking_new_message', $recipient, $message, $current_user_id, $messageid );


				}
				
			}
		}
	}

	// Conversation Details Metabox Content
	function marketking_message_messaging_metabox_content(){

		// If current page is ADD New Conversation
		if(get_current_screen()->action === 'add'){
			?>
			<textarea name="marketking_message_start_message" id="marketking_message_start_message" placeholder="<?php esc_html_e('Enter your message here...','marketking');?>" required></textarea>
			<?php
		} else {
			// Display Conversation
			// get number of messages
			global $post;
			$nr_messages = get_post_meta ($post->ID, 'marketking_message_messages_number', true);

			$currentuser = wp_get_current_user();
			$current_id = get_current_user_id();
			if (marketking()->is_vendor_team_member()){
				$current_id = marketking()->get_team_member_parent();
				$currentuser = new WP_User($current_id);
			}
			
			?>
			<div id="marketking_message_messages_container">
				<?php	
				// loop through and display messages
				for ($i = 1; $i <= $nr_messages; $i++) {
				    // get message details
				    $message = get_post_meta ($post->ID, 'marketking_message_message_'.$i, true);
				    $author = get_post_meta ($post->ID, 'marketking_message_message_'.$i.'_author', true);
				    $time = get_post_meta ($post->ID, 'marketking_message_message_'.$i.'_time', true);
				    // check if message author is self
				    if ($currentuser->user_login === $author){
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
				    <div class="marketking_message_message <?php echo esc_attr($self); 

				    // check system message
				    if ($author === esc_html__('System Message','marketking')){
				    	echo 'marketking_message_system_message';
				    }

				    ?>">
				    	<?php echo wp_kses( nl2br($message), array( 'br' => true ) ); ?>
				    	<div class="marketking_message_message_time">
				    		<?php echo esc_html($author).' - '; ?>
				    		<?php echo esc_html($timestring); ?>
				    	</div>
				    </div>
				    <?php
				}
				?>
			</div>
			<?php
			if ($author !== esc_html__('System Message','marketking')){
				?>
				<textarea name="marketking_message_admin_new_message" id="marketking_message_admin_new_message" placeholder="<?php esc_html_e('Enter your message here...','marketking');?>" ></textarea><br /><br />
				<button type="submit" class="button button-primary button-large"><?php esc_html_e('Send message'); ?></button>

			<?php
			}
		}
		
	}


	public function marketking_messages_menu_order_count() {
		global $submenu;

		// New messages are: How many conversations are not "resolved" AND do not have a response from admin.

		// first get all conversations that are new or open
		$new_open_conversations = get_posts( array( 
			'post_type' => 'marketking_message',
			'post_status' => 'publish',
			'numberposts' => -1,
			'fields' => 'ids',
		));

		// go through all of them to find which ones have the latest response from someone who is a vendor
		$message_nr = 0;
		foreach ($new_open_conversations as $conversation){
			// check latest response and role
			$conversation_msg_nr = get_post_meta($conversation, 'marketking_message_messages_number', true);
			$latest_message_author = get_post_meta($conversation, 'marketking_message_message_'.$conversation_msg_nr.'_author', true);
			// Get the user object.
			if (get_post_meta($conversation,'marketking_conversation_status', true) !== 'resolved'){
	            $user = get_user_by('login', $latest_message_author);
	            if (is_object($user)){
	            	if (!$user->has_cap('manage_woocommerce') || $user->has_cap('demo_user')){
	            		$message_nr++;
	            	}
	            } else {
	            	$message_nr++;
	            }
	        }
		}

		if ( $message_nr ) {
			if (isset($submenu['marketking'])){
				if (is_array($submenu['marketking'])){
					foreach ( $submenu['marketking'] as $key => $menu_item ) {
						if ( 0 === strpos( $menu_item[0], esc_html_x( 'Messages', 'Admin menu name', 'marketking' ) ) ) {
							$submenu['marketking'][ $key ][0] .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $message_nr ) . '"><span class="processing-count">' . number_format_i18n( $message_nr ) . '</span></span>'; 
							break;
						}
					}
				}
			}
		}
	}

	// Save message Metabox Content
	function marketking_save_message_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		if (get_post_type($post_id) === 'marketking_message'){
			$meta_user = sanitize_text_field(filter_input(INPUT_POST, 'marketking_message_user_input'));
			if ($meta_user !== NULL && trim($meta_user) !== ''){
				// meta user is user ID . Get user login
				$user_login = get_user_by('id', $meta_user)->user_login;
				update_post_meta( $post_id, 'marketking_message_user', sanitize_text_field($user_login));
			}

			$meta_status = sanitize_text_field(filter_input(INPUT_POST, 'marketking_conversation_status_select'));
			if ($meta_status !== NULL ){
				update_post_meta( $post_id, 'marketking_conversation_status', sanitize_text_field($meta_status));
			}

			$current_id = get_current_user_id();
			$currentuser = wp_get_current_user();
			if (marketking()->is_vendor_team_member()){
				$current_id = marketking()->get_team_member_parent();
				$currentuser = new WP_User($current_id);
			}

			$meta_conversation_start_message = sanitize_textarea_field(filter_input(INPUT_POST, 'marketking_message_start_message'));
			if ($meta_conversation_start_message !== NULL && trim($meta_conversation_start_message) !== ''){
				update_post_meta( $post_id, 'marketking_message_message_1', sanitize_textarea_field($meta_conversation_start_message));
				update_post_meta( $post_id, 'marketking_message_message_1_author', $currentuser->user_login );
				update_post_meta( $post_id, 'marketking_message_message_1_time', time() );
				update_post_meta( $post_id, 'marketking_message_messages_number', 1);
				update_post_meta( $post_id, 'marketking_message_type', 'message');

				// send email notification
				do_action( 'marketking_new_message', get_user_by('id', $meta_user)->user_email, $meta_conversation_start_message, $current_id, $post_id );
			}

			$meta_admin_new_message = sanitize_textarea_field(filter_input(INPUT_POST, 'marketking_message_admin_new_message'));
			if ($meta_admin_new_message !== NULL && trim($meta_admin_new_message) !== ''){
				$nr_messages = intval(get_post_meta ($post_id, 'marketking_message_messages_number', true));
				$current_message_nr = $nr_messages+1;

				update_post_meta( $post_id, 'marketking_message_message_'.$current_message_nr, sanitize_textarea_field($meta_admin_new_message));
				update_post_meta( $post_id, 'marketking_message_messages_number', $current_message_nr);
				update_post_meta( $post_id, 'marketking_message_message_'.$current_message_nr.'_author', $currentuser->user_login );
				update_post_meta( $post_id, 'marketking_message_message_'.$current_message_nr.'_time', time() );

				$other_party = get_post_meta($post_id, 'marketking_message_user', true);
				if ($other_party === $currentuser->user_login){
					$other_party = get_post_meta($post_id, 'marketking_message_message_1_author', true);
				}
				if ($other_party === 'shop'){
					$other_party = get_post_meta($post_id, 'marketking_message_message_1_author', true);
				}

				do_action( 'marketking_new_message', get_user_by('login', $other_party)->user_email, $meta_admin_new_message , $current_id, $post_id );
				
			}
		}
	}

	// Register messages
	public static function marketking_register_post_type_message() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Messages', 'marketking' ),
	        'singular_name'         => esc_html__( 'Message', 'marketking' ),
	        'all_items'             => esc_html__( 'Messages', 'marketking' ),
	        'menu_name'             => esc_html__( 'Messages', 'marketking' ),
	        'add_new'               => esc_html__( 'New message', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New message', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit message', 'marketking' ),
	        'new_item'              => esc_html__( 'New message', 'marketking' ),
	        'view_item'             => esc_html__( 'View message', 'marketking' ),
	        'view_items'            => esc_html__( 'View messages', 'marketking' ),
	        'search_items'          => esc_html__( 'Search messages', 'marketking' ),
	        'not_found'             => esc_html__( 'No messages found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No messages found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent message', 'marketking' ),
	        'featured_image'        => esc_html__( 'Message image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set message image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove message image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as message image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into message', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this message', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter messages', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Message navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Messages list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Message', 'marketking' ),
	        'description'           => esc_html__( 'This is where you can send new messages', 'marketking' ),
	        'labels'                => $labels,
	        'supports'              => array('title'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_message',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_message', $args );
	}

	// Add Metaboxes to message
	function marketking_message_metaboxes($post_type) {
	    $post_types = array('marketking_message');     //limit meta box to certain post types
	   	if ( in_array( $post_type, $post_types ) ) {
	       add_meta_box(
	           'marketking_message_details_metabox'
	           ,esc_html__( 'Thread Details', 'marketking' )
	           ,array( $this, 'marketking_message_details_metabox_content' )
	           ,$post_type
	           ,'advanced'
	           ,'high'
	       );
	       add_meta_box(
	           'marketking_message_messaging_metabox'
	           ,esc_html__( 'Messages', 'marketking' )
	           ,array( $this, 'marketking_message_messaging_metabox_content' )
	           ,$post_type
	           ,'advanced'
	           ,'high'
	       );
	   }
	}

	// Add custom columns to message menu
	function marketking_add_columns_group_menu_message($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'marketking_agent' => esc_html__( 'Vendor', 'marketking' ),
			'marketking_lastreplydate' => esc_html__( 'Date of last reply', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 2, true) + $columns;

	    return $columns;
	}

	// Add message custom columns data
	function marketking_columns_group_data_message( $column, $post_id ) {
	    switch ( $column ) {

	        case 'marketking_agent' :

	        	$user = get_post_meta($post_id, 'marketking_message_user', true);
	        	if ($user === 'shop'){
	        		$user = get_post_meta ($post_id, 'marketking_message_message_1_author', true);
	        	}
	            echo '<strong>'.esc_html($user).'</strong>';
	            // check if have new message, and add
	            // check latest response and role
	            $conversation_msg_nr = get_post_meta($post_id, 'marketking_message_messages_number', true);
	            $latest_message_author = get_post_meta($post_id, 'marketking_message_message_'.$conversation_msg_nr.'_author', true);
	            // Get the user object.
				if (get_post_meta($post_id,'marketking_conversation_status', true) !== 'resolved'){
		            $user = get_user_by('login', $latest_message_author);
		            if (is_object($user)){
		            	if (!$user->has_cap('manage_woocommerce') || $user->has_cap('demo_user')){
		            		esc_html_e(' (New message!)','marketking');
		            	}
		            } else {
		            	esc_html_e(' (New message!)','marketking');
		            }
		        }
	            break;

	        case 'marketking_lastreplydate' :
	        	$lastmessagenumber = get_post_meta ($post_id, 'marketking_message_messages_number', true);
	            $time_last_message = get_post_meta( $post_id , 'marketking_message_message_'.$lastmessagenumber.'_time' , true );

	            // In case of empty start message, prevent error
	            if ($time_last_message === '' || $time_last_message === null){
	            	$time_last_message = 1;
	            }

	            // if today
	            if((time()-$time_last_message) < 86400){
	            	// show time
	            	echo date_i18n( 'h:i A', $time_last_message+(get_option('gmt_offset')*3600) );
	            } else if ((time()-$time_last_message) < 172800){
	            // if yesterday
	            	echo esc_html__('Yesterday at ','marketking').date_i18n( 'h:i A', $time_last_message+(get_option('gmt_offset')*3600) );
	            } else {
	            // date
	            	echo date_i18n( get_option('date_format'), $time_last_message+(get_option('gmt_offset')*3600) ); 
	            }

	            break;

	    }
	}


	// Register vendor Groups
	public static function marketking_register_post_type_vendor_groups() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Vendor Groups', 'marketking' ),
	        'singular_name'         => esc_html__( 'Group', 'marketking' ),
	        'all_items'             => esc_html__( 'Vendor Groups', 'marketking' ),
	        'menu_name'             => esc_html__( 'Vendor Groups', 'marketking' ),
	        'add_new'               => esc_html__( 'Create new group', 'marketking' ),
	        'add_new_item'          => esc_html__( 'Create new customer group', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit group', 'marketking' ),
	        'new_item'              => esc_html__( 'New group', 'marketking' ),
	        'view_item'             => esc_html__( 'View group', 'marketking' ),
	        'view_items'            => esc_html__( 'View groups', 'marketking' ),
	        'search_items'          => esc_html__( 'Search groups', 'marketking' ),
	        'not_found'             => esc_html__( 'No groups found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No groups found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent group', 'marketking' ),
	        'featured_image'        => esc_html__( 'Group image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set group image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove group image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as group image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into group', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this group', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter groups', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Groups navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Groups list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Vendor Group', 'marketking' ),
	        'description'           => esc_html__( 'This is where you can create new vendor groups', 'marketking' ),
	        'labels'                => $labels,
	        'supports'              => array( 'title' ),
	        'hierarchical'          => false,
	        'public'                => false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 105,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => true,
	        'has_archive'           => false,
	        'exclude_from_search'   => true,
	        'publicly_queryable'    => false,
	        'capability_type'       => 'post',
	        'map_meta_cap'          => true,
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_group',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_group', $args );


	}

	// Add Groups Metaboxes
	function marketking_groups_metaboxes($post_type) {
	    $post_types = array('marketking_group');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
       		if( get_current_screen()->action !== 'add'){
	           add_meta_box(
	               'marketking_group_users_metabox'
	               ,esc_html__( 'Vendors in this group', 'marketking' )
	               ,array( $this, 'marketking_group_users_metabox_content' )
	               ,$post_type
	               ,'advanced'
	               ,'low'
	           );
	       }

	       add_meta_box(
	           'marketking_group_permissions_metabox'
	           ,esc_html__( 'Vendor Group Settings', 'marketking' )
	           ,array( $this, 'marketking_group_permissions_metabox_content' )
	           ,$post_type
	           ,'advanced'
	           ,'low'
	       );

	       add_meta_box(
	           'marketking_group_panels_metabox'
	           ,esc_html__( 'Vendor Group Features & Panels', 'marketking' )
	           ,array( $this, 'marketking_group_panels_metabox_content' )
	           ,$post_type
	           ,'advanced'
	           ,'low'
	       );

	       add_meta_box(
	           'marketking_group_permissions_advanced_metabox'
	           ,esc_html__( 'Advanced Settings', 'marketking' )
	           ,array( $this, 'marketking_group_permissions_advanced_metabox_content' )
	           ,$post_type
	           ,'advanced'
	           ,'low'
	       );
	    }
	}


	// Group Users Metabox Content
	function marketking_group_users_metabox_content(){
		?>
		<div id="marketking_metabox_product_categories_wrapper">
			<div id="marketking_metabox_product_categories_wrapper_content">
				<div class="marketking_metabox_product_categories_wrapper_content_line">
					<?php
					global $post;
					// get all users in the group
					$users = get_users(array(
							    'meta_key'     => 'marketking_group',
							    'meta_value'   => $post->ID,
							    'fields' => array('ID', 'user_login'),

							));
					foreach ($users as $user){
						echo '
						<a href="'.esc_attr(get_edit_user_link($user->ID)).'" class="marketking_metabox_product_categories_wrapper_content_category_user_link"><div class="marketking_metabox_product_categories_wrapper_content_category_user">
							'.esc_html($user->user_login).'
						</div></a>
						';
					}
					if (empty($users)){
						esc_html_e('There are no vendors in this group','marketking');
					}
					?>
				</div>
			</div>
		</div>

		<?php
	}

	// Group Permissions Metabox Content
	function marketking_group_permissions_metabox_content(){
		global $post;
		$group_id = $post->ID;
		?>
		<div class="marketking_group_payment_shipping_methods_container">
			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_group_settings" xmlns="http://www.w3.org/2000/svg" width="41" height="41" fill="none" viewBox="0 0 41 41">
					  <path fill="#A5A5A5" d="M34.85 14.35v5.33a11.275 11.275 0 00-15.17 15.17h-8.405a5.125 5.125 0 01-5.125-5.125V14.35h28.7zm-5.125-8.2a5.125 5.125 0 015.125 5.125V12.3H6.15v-1.025a5.125 5.125 0 015.125-5.125h18.45zm-4.99 17.306a4.102 4.102 0 01-2.931 5.08l-.947.242a9.643 9.643 0 00.02 2.083l.718.17a4.102 4.102 0 012.985 5.164l-.26.865a8.77 8.77 0 001.71 1.062l.667-.705a4.1 4.1 0 015.966.004l.69.734a8.813 8.813 0 001.686-1.021l-.32-1.14a4.101 4.101 0 012.931-5.082l.943-.24a9.701 9.701 0 00-.02-2.085l-.714-.168a4.099 4.099 0 01-2.984-5.166l.258-.863a8.852 8.852 0 00-1.712-1.064l-.666.705a4.1 4.1 0 01-5.966-.002l-.69-.734a8.85 8.85 0 00-1.686 1.02l.322 1.141zm4.99 8.319a2.05 2.05 0 110-4.1 2.05 2.05 0 010 4.1z"/>
					</svg>
					
					<?php esc_html_e('Group Settings', 'marketking'); ?>
				</div>
				<div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Maximum Products Number ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(leave empty for no limit)', 'marketking');?></span></div>
			        <input type="number" id="marketking_group_max_products" step="1" min="0" name="marketking_group_max_products" class="marketking_user_registration_user_data_container_element_text" placeholder="Enter the maximum number of products vendors in this group can add ..." <?php 

			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_group_allowed_products_number', true);

			        if (!empty($group_allowed_products_number) || intval($group_allowed_products_number) === 0){
			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
			        }
			    ?>>
			    </div>

			    <!-- ALLOWED PRODUCTS TYPE -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_multiple_vendor_selectors_container">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Allowed Products Type ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(leave empty to allow all)', 'marketking');?></span></div>
			        <?php

    					$selected_options = array();
    					if( get_current_screen()->action !== 'add'){
    			        	$selected_options_string = get_post_meta($post->ID, 'marketking_group_allowed_products_type_settings', true);

    			        	$selected_options = explode(',', $selected_options_string);
    			        }
			        			        

			        ?>
			        <select id="marketking_group_allowed_products_type" name="marketking_group_allowed_products_type[]" class="marketking_select_group_multiple_vendor_selectors" multiple>
			        	<?php 

			        	$selectable_items = wc_get_product_types();
			        	$selectable_items['virtual'] = 'Virtual products';
			        	$selectable_items['downloadable'] = 'Downloadable products';

			        	foreach ( $selectable_items as $value => $label ) { 
			        		$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if ($selected_option === ($value )){
									$is_selected = 'yes';
								}
							}
			        		?>
			        		<option value="<?php echo esc_attr( $value ); ?>" <?php selected('yes',$is_selected, true);?>><?php echo esc_html( $label ); ?></option>
			        		<?php 
			        	}


			        	?>
			        </select>
			    </div>

			    <!-- ALLOWED CATEGORIES -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_multiple_vendor_selectors_container">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Allowed Categories ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(leave empty to allow all)', 'marketking');?></span></div>
			        <?php

    					$selected_options = array();
    					if( get_current_screen()->action !== 'add'){
    			        	$selected_options_string = get_post_meta($post->ID, 'marketking_group_allowed_categories_settings', true);

    			        	$selected_options = explode(',', $selected_options_string);
    			        }
			        			        

			        ?>
			        <select id="marketking_group_allowed_categories" name="marketking_group_allowed_categories[]" class="marketking_select_group_multiple_vendor_selectors" multiple>
			        	<?php 

			        	// Get all categories
			        	$categories = get_categories( array( 'taxonomy' => 'product_cat', 'hide_empty' => false) );
			        	foreach ($categories as $category){
    		            	$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if ($selected_option === ('category_'.$category->term_id )){
									$is_selected = 'yes';
								}
							}
			        		echo '<option value="category_'.esc_attr($category->term_id).'" '.selected('yes',$is_selected, false).'>';

			        		// show category hierarchy or not
			        		if (apply_filters('marketkingking_bulkorder_category_hierarchical', true)){

			        			$parents = 0;
			        			$parentcat = $category->category_parent;
			        			while ($parentcat != 0){
			        				$parents++;
			        				$newparent = get_term($parentcat);
			        				$parentcat = $newparent->parent;
			        			}

			        			while ($parents > 0 ){
			        				echo '— ';
			        				$parents--;
			        			}
			        		}

			        		echo esc_html($category->name).'</option>';
			        	} 
			        	?>
			        </select>
			    </div>

			    <!-- ALLOWED TAGS -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_multiple_vendor_selectors_container">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Allowed Tags ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(leave empty to allow all)', 'marketking');?></span></div>
			        <?php

    					$selected_options = array();
    					if( get_current_screen()->action !== 'add'){
    			        	$selected_options_string = get_post_meta($post->ID, 'marketking_group_allowed_tags_settings', true);

    			        	$selected_options = explode(',', $selected_options_string);
    			        }
		        			        

			        ?>
			        <select id="marketking_group_allowed_tags" name="marketking_group_allowed_tags[]" class="marketking_select_group_multiple_vendor_selectors" multiple>
			        	<?php 
			        	// Get all categories
			        	$categories = get_terms( array( 'taxonomy' => 'product_tag', 'hide_empty' => false) );
			        	foreach ($categories as $category){
    		            	$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if ($selected_option === ('category_'.$category->term_id )){
									$is_selected = 'yes';
								}
							}
			        		echo '<option value="category_'.esc_attr($category->term_id).'" '.selected('yes',$is_selected, false).'>'.esc_html($category->name).'</option>';
			        	} 
			        	?>
			        </select>
			    </div>

			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_multiple_vendor_selectors_container">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Remove Product Tabs ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(leave empty to allow all)', 'marketking');?></span></div>
			        <?php

    					$selected_options = array();
    					if( get_current_screen()->action !== 'add'){
    			        	$selected_options_string = get_post_meta($post->ID, 'marketking_group_allowed_tabs_settings', true);

    			        	$selected_options = explode(',', $selected_options_string);
    			        }
		        			        

			        ?>
			        <select id="marketking_group_allowed_tabs" name="marketking_group_allowed_tabs[]" class="marketking_select_group_multiple_vendor_selectors" multiple>
			        	<?php 

			        	try {
			        		$tabs = array();
			        		$tabs['inventory']['class'] = array();

			        	    $metatabs = apply_filters('woocommerce_product_data_tabs', $tabs);

    			        	// Get all 
    			        	foreach ($metatabs as $tab => $arr){
        		            	$is_selected = 'no';
        		            	foreach ($selected_options as $selected_option){
    								if ($selected_option === $tab){
    									$is_selected = 'yes';
    								}
    							}
    							if (isset($arr['label'])){
    								echo '<option value="'.esc_attr($tab).'" '.selected('yes',$is_selected, false).'>'.esc_html($arr['label']).'</option>';
    							}
    			        	} 

			        	} catch (Exception $e) {

			        	}
			        	
			        	
			        	?>
			        </select>
			    </div>


			</div>

			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title marketking_group_payment_shipping_methods_container_element_title_empty">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_shipping" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
					  <path fill="#A5A5A5" d="M35 26.667v-20H5v20h30zm0-23.334a3.333 3.333 0 013.333 3.334v20A3.333 3.333 0 0135 30H23.333v3.333h3.334v3.334H13.333v-3.334h3.334V30H5a3.333 3.333 0 01-3.333-3.333v-20A3.322 3.322 0 015 3.333h30zM8.333 10h15v8.333h-15V10zM25 10h6.667v3.333H25V10zm6.667 5v8.333H25V15h6.667zM8.333 20H15v3.333H8.333V20zm8.334 0h6.666v3.333h-6.666V20z"/>
					</svg>
					<?php esc_html_e('Group Settings', 'marketking'); ?>
				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">
					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Vendors can publish products directly','marketking'); ?>
					</div>
					<?php

					$groupval = get_post_meta($group_id, 'marketking_group_vendor_publish_direct_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					// if global setting is enabled, show these as CHECKED and disabled checkbox.
					$globalval = '';
					$global = get_option( 'marketking_vendor_publish_direct_setting', 0 );
					if (intval($global) === 1){
						$globalval = 'disabled="disabled"';
						$groupval = 1;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendor_publish_direct_setting" name="marketking_group_vendor_publish_direct_setting" <?php checked(1, intval($groupval), true); echo ' '.$globalval; ?>>

					<?php

					if ($globalval !== ''){
						$tip = esc_html__('This setting cannot be disabled, because it is enabled globally in MarketKing -> Settings.','marketking');
						echo ' '.wc_help_tip($tip, false);
					}

					?>
				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">
					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Vendors can change order status','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_vendor_status_direct_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					// if global setting is enabled, show these as CHECKED and disabled checkbox.
					$globalval = '';
					$global = get_option( 'marketking_vendor_status_direct_setting', 1 );
					if (intval($global) === 1){
						$globalval = 'disabled="disabled"';
						$groupval = 1;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendor_status_direct_setting" name="marketking_group_vendor_status_direct_setting" <?php checked(1, intval($groupval), true); echo ' '.$globalval; ?>>

					<?php

					if ($globalval !== ''){
						$tip = esc_html__('This setting cannot be disabled, because it is enabled globally in MarketKing -> Settings.','marketking');
						echo ' '.wc_help_tip($tip, false);
					}

					?>
				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">
					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Vendors can add linked products (cross-sell / upsell)','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_vendors_can_linked_products_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					// if global setting is enabled, show these as CHECKED and disabled checkbox.
					$globalval = '';
					$global = get_option( 'marketking_vendors_can_linked_products_setting', 1 );
					if (intval($global) === 1){
						$globalval = 'disabled="disabled"';
						$groupval = 1;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendors_can_linked_products_setting" name="marketking_group_vendors_can_linked_products_setting" <?php checked(1, intval($groupval), true); echo ' '.$globalval; ?>>

					<?php

					if ($globalval !== ''){
						$tip = esc_html__('This setting cannot be disabled, because it is enabled globally in MarketKing -> Settings.','marketking');
						echo ' '.wc_help_tip($tip, false);
					}

					?>

				</div>


			</div>



		</div>


		<?php
	}

	// Group Permissions Metabox Content
	function marketking_group_permissions_advanced_metabox_content(){
		global $post;
		$group_id = $post->ID;
		?>
		<div class="marketking_group_payment_shipping_methods_container">
			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_group_settings" xmlns="http://www.w3.org/2000/svg" width="41" height="41" fill="none" viewBox="0 0 41 41">
					  <path fill="#A5A5A5" d="M34.85 14.35v5.33a11.275 11.275 0 00-15.17 15.17h-8.405a5.125 5.125 0 01-5.125-5.125V14.35h28.7zm-5.125-8.2a5.125 5.125 0 015.125 5.125V12.3H6.15v-1.025a5.125 5.125 0 015.125-5.125h18.45zm-4.99 17.306a4.102 4.102 0 01-2.931 5.08l-.947.242a9.643 9.643 0 00.02 2.083l.718.17a4.102 4.102 0 012.985 5.164l-.26.865a8.77 8.77 0 001.71 1.062l.667-.705a4.1 4.1 0 015.966.004l.69.734a8.813 8.813 0 001.686-1.021l-.32-1.14a4.101 4.101 0 012.931-5.082l.943-.24a9.701 9.701 0 00-.02-2.085l-.714-.168a4.099 4.099 0 01-2.984-5.166l.258-.863a8.852 8.852 0 00-1.712-1.064l-.666.705a4.1 4.1 0 01-5.966-.002l-.69-.734a8.85 8.85 0 00-1.686 1.02l.322 1.141zm4.99 8.319a2.05 2.05 0 110-4.1 2.05 2.05 0 010 4.1z"/>
					</svg>
					
					<?php esc_html_e('Advanced Group Settings', 'marketking'); ?>
				</div>
				
				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">

					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Vendor products are non-taxable','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_vendors_non_taxable_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendors_non_taxable_setting" name="marketking_group_vendors_non_taxable_setting" <?php checked(1, intval($groupval), true); ?>>


				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">

					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Vendors can select multiple categories per product','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_vendors_multiple_categories_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					// enabled by default
					if (!metadata_exists('post', $group_id, 'marketking_group_vendors_multiple_categories_setting')){
						$groupval = 1;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendors_multiple_categories_setting" name="marketking_group_vendors_multiple_categories_setting" <?php checked(1, intval($groupval), true); ?>>

				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">

					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Vendors can sell products on backorder','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_vendors_allow_backorders_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					// enabled by default
					if (!metadata_exists('post', $group_id, 'marketking_group_vendors_allow_backorders_setting')){
						$groupval = 1;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendors_allow_backorders_setting" name="marketking_group_vendors_allow_backorders_setting" <?php checked(1, intval($groupval), true); ?>>

				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">

					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('All products are sold individually (max 1 per order)','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_products_sold_individually_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					// disabled by default
					if (!metadata_exists('post', $group_id, 'marketking_group_products_sold_individually_setting')){
						$groupval = 0;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_products_sold_individually_setting" name="marketking_group_products_sold_individually_setting" <?php checked(1, intval($groupval), true); ?>>

				</div>



			</div>

			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title marketking_group_payment_shipping_methods_container_element_title_empty">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_shipping" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
					  <path fill="#A5A5A5" d="M35 26.667v-20H5v20h30zm0-23.334a3.333 3.333 0 013.333 3.334v20A3.333 3.333 0 0135 30H23.333v3.333h3.334v3.334H13.333v-3.334h3.334V30H5a3.333 3.333 0 01-3.333-3.333v-20A3.322 3.322 0 015 3.333h30zM8.333 10h15v8.333h-15V10zM25 10h6.667v3.333H25V10zm6.667 5v8.333H25V15h6.667zM8.333 20H15v3.333H8.333V20zm8.334 0h6.666v3.333h-6.666V20z"/>
					</svg>
					<?php esc_html_e('Group Settings', 'marketking'); ?>
				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">

					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Vendors can add new product attributes','marketking'); ?>
					</div>
					<?php


					$groupval = get_post_meta($group_id, 'marketking_group_vendors_new_attributes_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					// enabled by default
					if (!metadata_exists('post', $group_id, 'marketking_group_vendors_new_attributes_setting')){
						$groupval = 1;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendors_new_attributes_setting" name="marketking_group_vendors_new_attributes_setting" <?php checked(1, intval($groupval), true); ?>>

				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">

					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('All products are virtual (no shipping)','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_vendors_all_virtual_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendors_all_virtual_setting" name="marketking_group_vendors_all_virtual_setting" <?php checked(1, intval($groupval), true); ?>>

				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">

					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('All products are downloadable (digital)','marketking'); ?>
					</div>
					<?php
					$groupval = get_post_meta($group_id, 'marketking_group_vendors_all_downloadable_setting', true );
					if (empty($groupval)){
						$groupval = 0;
					}

					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_vendors_all_downloadable_setting" name="marketking_group_vendors_all_downloadable_setting" <?php checked(1, intval($groupval), true); ?>>

				</div>

				

			</div>



		</div>


		<?php
	}

	// Group Panels Metabox Content
	function marketking_group_panels_metabox_content(){
		?>
		<div class="marketking_group_payment_shipping_methods_container">
			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_shipping" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
					  <path fill="#A5A5A5" d="M35 26.667v-20H5v20h30zm0-23.334a3.333 3.333 0 013.333 3.334v20A3.333 3.333 0 0135 30H23.333v3.333h3.334v3.334H13.333v-3.334h3.334V30H5a3.333 3.333 0 01-3.333-3.333v-20A3.322 3.322 0 015 3.333h30zM8.333 10h15v8.333h-15V10zM25 10h6.667v3.333H25V10zm6.667 5v8.333H25V15h6.667zM8.333 20H15v3.333H8.333V20zm8.334 0h6.666v3.333h-6.666V20z"/>
					</svg>
					<?php esc_html_e('Available Dashboard Panels', 'marketking'); ?>
				</div>

				
				<?php
				global $post;
				$group_id = $post->ID;
				$add_group_check = '';
				// if current screen is Add / Create new customer group, check all methods by default
				if( get_current_screen()->action === 'add'){
		        	$add_group_check = 'checked="checked"';
		        }
		        ?>


		        <?php
		        // get all available modules
				$panels = marketkingpro()->get_all_dashboard_panels();

				$panelnumber = count($panels);
				$panelhalf = ceil($panelnumber/2);
				$currentpanel = 0;

				foreach ($panels as $panel_slug => $panel_name){

					if ($currentpanel < $panelhalf){
						if (!metadata_exists('post', $post->ID, 'marketking_group_available_panel_'.esc_attr($panel_slug))){
							$checkedval = 1;
						} else {
							$checkedval = intval(get_post_meta($post->ID, 'marketking_group_available_panel_'.esc_attr($panel_slug), true));
						}

						?>
						<div class="marketking_group_payment_shipping_methods_container_element_method">
							<div class="marketking_group_payment_shipping_methods_container_element_method_name">
								<?php echo esc_html($panel_name); ?>
							</div>
							<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>" name="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>" <?php checked(1, $checkedval, true); echo esc_attr($add_group_check); ?>>
						</div>
						<?php

						$currentpanel++;
					}
					
				}

				?>

				
			</div>


			<div class="marketking_group_payment_shipping_methods_container_element">
				<div class="marketking_group_payment_shipping_methods_container_element_title marketking_group_payment_shipping_methods_container_element_title_empty">
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_payment" xmlns="http://www.w3.org/2000/svg" width="37" height="30" fill="none" viewBox="0 0 37 30">
					  <path fill="#C4C4C4" d="M33.3 0H3.7A3.672 3.672 0 00.018 3.7L0 25.9c0 2.053 1.647 3.7 3.7 3.7h29.6c2.053 0 3.7-1.647 3.7-3.7V3.7C37 1.646 35.353 0 33.3 0zm0 25.9H3.7V14.8h29.6v11.1zm0-18.5H3.7V3.7h29.6v3.7z"/>
					</svg>
					<?php esc_html_e('Panel', 'marketking'); ?>
				</div>

				<?php

				$currentpanel = 0;
				foreach ($panels as $panel_slug => $panel_name){

					if ($currentpanel >= $panelhalf){
						if (!metadata_exists('post', $post->ID, 'marketking_group_available_panel_'.esc_attr($panel_slug))){
							$checkedval = 1;
						} else {
							$checkedval = intval(get_post_meta($post->ID, 'marketking_group_available_panel_'.esc_attr($panel_slug), true));
						}

						?>
						<div class="marketking_group_payment_shipping_methods_container_element_method">
							<div class="marketking_group_payment_shipping_methods_container_element_method_name">
								<?php echo esc_html($panel_name); ?>
							</div>
							<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>" name="marketking_group_available_panel_<?php echo esc_attr($panel_slug); ?>" <?php checked(1, $checkedval, true); echo esc_attr($add_group_check); ?>>
						</div>
						<?php

					}
					$currentpanel++;

					
				}

				?>

			</div>
		</div>

		<br /><br />

		<!-- Information panel -->
		<div class="marketking_group_payment_shipping_information_box">
			<svg class="marketking_group_payment_shipping_information_box_icon" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 36 36">
			  <path fill="#358BBB" d="M18 0C8.06 0 0 8.06 0 18s8.06 18 18 18 18-8.06 18-18S27.94 0 18 0zm0 28.446a1.607 1.607 0 110-3.213 1.607 1.607 0 010 3.213zm2.527-8.819a1.941 1.941 0 00-1.241 1.8v.912a.322.322 0 01-.322.322h-1.928a.322.322 0 01-.322-.322v-.864c0-.928.27-1.844.8-2.607a4.49 4.49 0 012.093-1.643c1.366-.527 2.25-1.672 2.25-2.921 0-1.772-1.732-3.215-3.857-3.215s-3.857 1.443-3.857 3.215v.305a.322.322 0 01-.322.321h-1.928a.322.322 0 01-.322-.321v-.305c0-1.58.691-3.054 1.945-4.15C14.721 9.095 16.312 8.517 18 8.517c1.688 0 3.279.582 4.484 1.635 1.253 1.097 1.945 2.572 1.945 4.15 0 2.323-1.531 4.412-3.902 5.324z"/>
			</svg>
			<?php esc_html_e('Here you can control which features and dashboard panels are available / visible to vendors in this group.','marketking'); ?>
		</div>

		<?php
	}

	// Add custom columns to Groups menu
	function marketking_add_columns_group_menu($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'title' => esc_html__( 'Group name', 'marketking' ),
			'marketking_user_number' => esc_html__( 'Number of vendors', 'marketking' ),

		);

		$columns = array_slice($columns_initial, 0, 1, true) + $columns;

	    return $columns;
	}

	public function set_wc_screen_ids( $screen ){
	      $screen[] = 'marketking_group';
	      return $screen;
	}

	// Add groups custom columns data
	function marketking_columns_group_data( $column, $post_id ) {
	    switch ( $column ) {

	        case 'marketking_user_number' :
	        	$users = get_users(array(
				    'meta_key'     => 'marketking_group',
				    'meta_value'   => $post_id,
				    'fields' => 'ids',
				));	

	            echo '<strong>'.esc_html(count($users)).'</strong>';
	            break;


	    }
	}

	function disable_gutenberg ($current_status, $post_type){
	    if ($post_type === 'marketking_announce') {
	    	return false;
	    }
	    if ($post_type === 'marketking_docs') {
	    	return false;
	    }
	    if ($post_type === 'marketking_mpack') {
	    	return false;
	    }
	    return $current_status;
	}

	// Register vreq
	public static function marketking_register_post_type_verification_request() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Verification Requests', 'marketking' ),
	        'singular_name'         => esc_html__( 'Request', 'marketking' ),
	        'all_items'             => esc_html__( 'Verification Requests', 'marketking' ),
	        'menu_name'             => esc_html__( 'Verification Requests', 'marketking' ),
	        'add_new'               => esc_html__( 'New request', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New request', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit request', 'marketking' ),
	        'new_item'              => esc_html__( 'New request', 'marketking' ),
	        'view_item'             => esc_html__( 'View request', 'marketking' ),
	        'view_items'            => esc_html__( 'View requests', 'marketking' ),
	        'search_items'          => esc_html__( 'Search requests', 'marketking' ),
	        'not_found'             => esc_html__( 'No requests found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No requests found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent request', 'marketking' ),
	        'featured_image'        => esc_html__( 'Request image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set request image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove request image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as request image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into request', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this request', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter requests', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Request navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Requests list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Verification Requests', 'marketking' ),
	        'description'           => '',
	        'labels'                => $labels,
	        'supports'              => array('title'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'capabilities' => array(
	            'create_posts' => false, // Removes support for the "Add New" function
	          ),
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_vreq',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_vreq', $args );

	}

	function charge_id_backend_order($title, $id){

		$apply = false;
		if (is_admin() && isset($_GET['post'])){
			if (get_post_type($_GET['post']) === 'shop_order'){
				$apply = true;
			}
		} else {
			if (is_admin() && isset($_GET['page'])){
				if ($_GET['page'] === 'wc-orders'){
					$apply = true;
				}
			}
		}

		if ($apply){
			if ($title === 'Stripe Card Payment'){

				$order_id = sanitize_text_field($_GET['post']);
				$order = wc_get_order($order_id);
				if ($order){
					$transaction_id = $order->get_transaction_id();

					if (empty($transaction_id)){
						$charge = get_post_meta($_GET['post'], 'marketking_stripe_split_pay_charge_id_admin', true);
						if (!empty($charge)){
							$order->set_transaction_id($charge);
							$order->save();
						}
					}
				}
			}
		}

		return $title;
	}
	function filter_stripe_transaction_url_backend ($url, $order, $gateway){

		$charge = $order->get_meta('marketking_stripe_split_pay_charge_id_admin');
		if (!empty($charge)){
			$url = 'https://dashboard.stripe.com/payments/'.$charge;
		}

		return $url;
	}


	// Register vitem
	public static function marketking_register_post_type_verification_item() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Verification Items', 'marketking' ),
	        'singular_name'         => esc_html__( 'Item', 'marketking' ),
	        'all_items'             => esc_html__( 'Verification Items', 'marketking' ),
	        'menu_name'             => esc_html__( 'Verification Items', 'marketking' ),
	        'add_new'               => esc_html__( 'New item', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New item', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit item', 'marketking' ),
	        'new_item'              => esc_html__( 'New item', 'marketking' ),
	        'view_item'             => esc_html__( 'View item', 'marketking' ),
	        'view_items'            => esc_html__( 'View items', 'marketking' ),
	        'search_items'          => esc_html__( 'Search items', 'marketking' ),
	        'not_found'             => esc_html__( 'No items found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No items found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent item', 'marketking' ),
	        'featured_image'        => esc_html__( 'Item image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set item image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove item image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as item image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into item', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this item', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter items', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Item navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Items list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Verification Items', 'marketking' ),
	        'description'           => '',
	        'labels'                => $labels,
	        'supports'              => array('title'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_vitem',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_vitem', $args );

	}

	// Add Metaboxes to Verification items
	function marketking_vitem_metaboxes($post_type) {
	    $post_types = array('marketking_vitem');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
       			add_meta_box(
	               'marketking_vitem_description_metabox'
	               ,esc_html__( 'Item Description', 'marketking' )
	               ,array( $this, 'marketking_vitem_description_metabox_content' )
	               ,$post_type
	               ,'advanced'
	               ,'high'
	           );
	           add_meta_box(
	               'marketking_vitem_visibility_metabox'
	               ,esc_html__( 'Item Visibility', 'marketking' )
	               ,array( $this, 'marketking_vitem_visibility_metabox_content' )
	               ,$post_type
	               ,'advanced'
	               ,'high'
	           );
	           
	       }
	}

	function marketking_vitem_description_metabox_content(){
		global $post;
		?>
		<div class="marketking_offers_metabox_padding">
			<div class="marketking_group_visibility_container_content_title">
				<svg class="marketking_offers_metabox_icon" xmlns="http://www.w3.org/2000/svg" width="39" height="39" fill="none" viewBox="0 0 39 39">
				  <path fill="#C4C4C4" fill-rule="evenodd" d="M29.25 2.438H9.75a4.875 4.875 0 00-4.875 4.874v24.375a4.875 4.875 0 004.875 4.875h19.5a4.875 4.875 0 004.875-4.874V7.313a4.875 4.875 0 00-4.875-4.875zM12.187 9.75a1.219 1.219 0 000 2.438h14.626a1.219 1.219 0 000-2.438H12.188zm-1.218 6.094a1.219 1.219 0 011.219-1.219h14.624a1.219 1.219 0 010 2.438H12.188a1.219 1.219 0 01-1.218-1.22zm1.219 3.656a1.219 1.219 0 000 2.438h14.624a1.219 1.219 0 000-2.438H12.188zm0 4.875a1.219 1.219 0 000 2.438H19.5a1.219 1.219 0 000-2.438h-7.313z" clip-rule="evenodd"/>
				</svg>
				<?php esc_html_e('Description / explanation that is shown to vendors.','marketking');?>
			</div>
			<textarea name="marketking_vitem_description" id="marketking_vitem_description_textarea"><?php 
					if (get_current_screen()->action !== 'add'){
			            echo get_post_meta($post->ID, 'marketking_vitem_description_textarea', true);
			        } 
		        ?></textarea>
		</div>
		<?php
	}

	function marketking_vitem_visibility_metabox_content(){
		if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
	    ?>
	    <div class="marketking_group_visibility_container">
	    	<div class="marketking_group_visibility_container_top">
	    		<?php esc_html_e( 'Group Visibility', 'marketking' ); ?>
	    	</div>
	    	<div class="marketking_group_visibility_container_content">
	    		<div class="marketking_group_visibility_container_content_title">
					<svg class="marketking_group_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="none" viewBox="0 0 45 45">
					  <path fill="#C4C4C4" d="M22.382 7.068c-3.876 0-7.017 3.668-7.017 8.193 0 3.138 1.51 5.863 3.73 7.239l-2.573 1.192-6.848 3.176c-.661.331-.991.892-.991 1.686v7.541c.054.943.62 1.822 1.537 1.837h24.36c1.048-.091 1.578-.935 1.588-1.837v-7.541c0-.794-.33-1.355-.992-1.686l-6.6-3.175-2.742-1.3c2.128-1.407 3.565-4.073 3.565-7.132 0-4.525-3.142-8.193-7.017-8.193zM11.063 9.95c-1.667.063-2.99.785-3.993 1.935a7.498 7.498 0 00-1.663 4.663c.068 2.418 1.15 4.707 3.076 5.905l-7.69 3.573c-.529.198-.793.661-.793 1.389v6.053c.041.802.458 1.477 1.24 1.488h5.11v-6.401c.085-1.712.888-3.095 2.333-3.77l5.109-2.43a4.943 4.943 0 001.141-.944c-2.107-3.25-2.4-7.143-1.041-10.567-.883-.54-1.876-.888-2.829-.894zm22.822 0c-1.09.023-2.098.425-2.926.992 1.32 3.455.956 7.35-.993 10.37.43.495.877.876 1.34 1.14l4.912 2.333c1.496.82 2.267 2.216 2.282 3.77v6.401h5.259c.865-.074 1.233-.764 1.241-1.488v-6.053c0-.662-.264-1.124-.794-1.39l-7.59-3.622c1.968-1.452 2.956-3.627 2.976-5.855-.053-1.763-.591-3.4-1.663-4.663-1.12-1.215-2.51-1.922-4.044-1.935z"/>
					</svg>
					<?php esc_html_e( 'Groups who can see this verification item', 'marketking' ); ?>
	    		</div>
            	<?php
	            	$groups = get_posts([
	            	  'post_type' => 'marketking_group',
	            	  'post_status' => 'publish',
	            	  'numberposts' => -1
	            	]);
	            	foreach ($groups as $group){
	            		$checked = '';
		            		// If current page is not Add New 
		            		if( get_current_screen()->action !== 'add'){
			            		global $post;
			            		$check = intval(get_post_meta($post->ID, 'marketking_group_'.$group->ID, true));
			            		if ($check === 1){
			            			$checked = 'checked="checked"';
			            		}	
			            	}  
	            		?>
	            		<div class="marketking_group_visibility_container_content_checkbox">
	            			<div class="marketking_group_visibility_container_content_checkbox_name">
	            				<?php echo esc_html($group->post_title); ?>
	            			</div>
	            			<input type="hidden" name="marketking_group_<?php echo esc_attr($group->ID);?>" value="0">
	            			<input type="checkbox" value="1" class="marketking_group_visibility_container_content_checkbox_input" name="marketking_group_<?php echo esc_attr($group->ID);?>" id="marketking_group_<?php echo esc_attr($group->ID);?>" value="1" <?php echo $checked;?> />
	            		</div>
	            		<?php
	            	}
	            ?>
	    	</div>
	    </div>
	    <?php
	}


	// Save Verification Item Metabox Content
	function marketking_save_vitem_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		if (get_post_type($post_id) === 'marketking_vitem'){

			// Save description
			$marketking_vitem_description = sanitize_textarea_field(filter_input(INPUT_POST, 'marketking_vitem_description'));
			if ($marketking_vitem_description !== NULL){
				update_post_meta($post_id, 'marketking_vitem_description_textarea', sanitize_textarea_field($marketking_vitem_description));
			}

			// Get all groups
			$groups = get_posts([
			  'post_type' => 'marketking_group',
			  'post_status' => 'publish',
			  'numberposts' => -1
			]);

			// For each group option, save user's choice as post meta
			foreach ($groups as $group){
				$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_'.$group->ID));
				if($meta_input !== NULL){
					update_post_meta($post_id, 'marketking_group_'.$group->ID, sanitize_text_field($meta_input));
				}
			}
		}
	}

	// Register refund
	public static function marketking_register_post_type_refund() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Refund Requests', 'marketking' ),
	        'singular_name'         => esc_html__( 'Refund', 'marketking' ),
	        'all_items'             => esc_html__( 'Refunds', 'marketking' ),
	        'menu_name'             => esc_html__( 'Refunds', 'marketking' ),
	        'add_new'               => esc_html__( 'New refund', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New refund', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit refund', 'marketking' ),
	        'new_item'              => esc_html__( 'New refund', 'marketking' ),
	        'view_item'             => esc_html__( 'View refund', 'marketking' ),
	        'view_items'            => esc_html__( 'View refunds', 'marketking' ),
	        'search_items'          => esc_html__( 'Search refunds', 'marketking' ),
	        'not_found'             => esc_html__( 'No refunds found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No refunds found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent refund', 'marketking' ),
	        'featured_image'        => esc_html__( 'Refund image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set refund image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove refund image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as refund image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into refund', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this refund', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter refunds', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Refund navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Refunds list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Refund Requests', 'marketking' ),
	        'description'           => '',
	        'labels'                => $labels,
	        'supports'              => array('title', 'editor'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'capabilities' => array(
	            'create_posts' => false, // Removes support for the "Add New" function
	          ),
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_refund',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_refund', $args );

	}

	/* Badges */	
	public static function marketking_register_post_type_badge() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Vendor Badges', 'marketking' ),
	        'singular_name'         => esc_html__( 'Badge', 'marketking' ),
	        'all_items'             => esc_html__( 'Vendor Badges', 'marketking' ),
	        'menu_name'             => esc_html__( 'Vendor Badges', 'marketking' ),
	        'add_new'               => esc_html__( 'New badge', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New badge', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit badge', 'marketking' ),
	        'new_item'              => esc_html__( 'New badge', 'marketking' ),
	        'view_item'             => esc_html__( 'View badge', 'marketking' ),
	        'view_items'            => esc_html__( 'View badges', 'marketking' ),
	        'search_items'          => esc_html__( 'Search badges', 'marketking' ),
	        'not_found'             => esc_html__( 'No badges found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No badges found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent badge', 'marketking' ),
	        'featured_image'        => esc_html__( 'Badge image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set badge image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove badge image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as badge image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into badge', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this badge', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter badges', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Badge navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Badges list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Vendor Badge', 'marketking' ),
	        'description'           => '',
	        'labels'                => $labels,
	        'supports'              => array('title','thumbnail'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_badge',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_badge', $args );

	}

	function marketking_add_columns_group_menu_badge($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'sort' => esc_html__( 'Sort Order', 'marketking' ),
			'applies' => esc_html__( 'Applies To', 'marketking' ),
			'badge' => esc_html__( 'Badge Image', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 2, true) + $columns;
	    return $columns;
	}

	function marketking_columns_group_data_badge( $column, $post_id ) {
	    switch ( $column ) {

	    	case 'sort' :

            	$sort = get_post_meta($post_id,'marketking_badge_sort_order', true);
            	echo esc_html($sort);
	            break;

	        case 'applies' :

            	$groups = get_post_meta($post_id,'marketking_group_visible_groups_settings', true);

            	if (!empty($groups)){
            		$groups = explode(',', $groups);

            		$groups_message = '';
            		foreach ($groups as $group){
            			$group = get_post($group);
            			if ($group){
            				$groups_message .= esc_html($group->post_title).', ';     	
            			}	
            		}
            		if ( ! empty($groups_message)){
            			echo '<strong>'.esc_html__('Groups: ','marketking').'</strong>'.esc_html(substr($groups_message, 0, -2));
            			echo '<br />';
            		}
            	}
            	


            	$users = get_post_meta($post_id,'marketking_group_visible_vendors_settings', true);
            	if (!empty($users)){
            		$users = explode(',', $users);

            		$users_message = '';
            		foreach ($users as $user_id){
            			$users_message .= esc_html(marketking()->get_store_name_display($user_id)).', ';     		
            		}
            		if ( ! empty($users_message)){
            			echo '<strong>'.esc_html__('Users: ','marketking').'</strong>'.esc_html(substr($users_message, 0, -2));
            			echo '<br />';
            		}
            	}
            	

	            break;


			case 'badge' :

				$imageurl = get_the_post_thumbnail_url($post_id, array(150, 150));
				if (!empty($imageurl)){
					echo '<img class="marketking_thumbnail_backend" src="'.esc_attr($imageurl).'">';
				}
				break;

	    }
	}
	

	function marketking_save_badge_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		if (get_post_type($post_id) === 'marketking_badge'){

			// Save description
			$marketking_vitem_description = sanitize_textarea_field(filter_input(INPUT_POST, 'marketking_badge_description'));
			if ($marketking_vitem_description !== NULL){
				update_post_meta($post_id, 'marketking_badge_description', sanitize_textarea_field($marketking_vitem_description));
			}

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_badge_condition_value'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_badge_condition_value', sanitize_text_field($meta_input));
			}			

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_badge_sort_order'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_badge_sort_order', sanitize_text_field($meta_input));
			}			

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_badge_condition'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_badge_condition', sanitize_text_field($meta_input));
			}


			// save other settings
			$rule_applies_multiple_options = array();
			if (isset($_POST['marketking_group_visible_vendors_settings'])){
				$rule_applies_multiple_options = $_POST['marketking_group_visible_vendors_settings'];
			}
			$options_string = '';
			if (empty($rule_applies_multiple_options)){
				$rule_applies_multiple_options = array();
			}

			foreach ($rule_applies_multiple_options as $option){
				$options_string .= sanitize_text_field ($option).',';
			}
			// remove last comma
			$options_string = substr($options_string, 0, -1);
			update_post_meta( $post_id, 'marketking_group_visible_vendors_settings', $options_string);


			$rule_applies_multiple_options = array();
			if (isset($_POST['marketking_group_visible_groups_settings'])){
				$rule_applies_multiple_options = $_POST['marketking_group_visible_groups_settings'];
			}
			if (empty($rule_applies_multiple_options)){
				$rule_applies_multiple_options = array();
			}

			$options_string = '';
			foreach ($rule_applies_multiple_options as $option){
				$options_string .= sanitize_text_field ($option).',';
			}
			// remove last comma
			$options_string = substr($options_string, 0, -1);
			update_post_meta( $post_id, 'marketking_group_visible_groups_settings', $options_string);

			// clear vendor badges cache
			marketkingpro()->clear_all_vendor_badges_cache();
			
		}
	}

	// Add Metaboxes to Badge items
	function marketking_badge_metaboxes($post_type) {
	    $post_types = array('marketking_badge');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
       			add_meta_box(
	               'marketking_badge_description_metabox'
	               ,esc_html__( 'Badge Settings', 'marketking' )
	               ,array( $this, 'marketking_badge_description_metabox_content' )
	               ,$post_type
	               ,'advanced'
	               ,'high'
	           );
	         
	       }
	}

	function marketking_badge_description_metabox_content(){
		global $post;
		$group_id = $post->ID;
		?>
		<div class="marketking_group_payment_shipping_methods_container">
			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_group_settings" xmlns="http://www.w3.org/2000/svg" width="41" height="41" fill="none" viewBox="0 0 41 41">
					  <path fill="#A5A5A5" d="M34.85 14.35v5.33a11.275 11.275 0 00-15.17 15.17h-8.405a5.125 5.125 0 01-5.125-5.125V14.35h28.7zm-5.125-8.2a5.125 5.125 0 015.125 5.125V12.3H6.15v-1.025a5.125 5.125 0 015.125-5.125h18.45zm-4.99 17.306a4.102 4.102 0 01-2.931 5.08l-.947.242a9.643 9.643 0 00.02 2.083l.718.17a4.102 4.102 0 012.985 5.164l-.26.865a8.77 8.77 0 001.71 1.062l.667-.705a4.1 4.1 0 015.966.004l.69.734a8.813 8.813 0 001.686-1.021l-.32-1.14a4.101 4.101 0 012.931-5.082l.943-.24a9.701 9.701 0 00-.02-2.085l-.714-.168a4.099 4.099 0 01-2.984-5.166l.258-.863a8.852 8.852 0 00-1.712-1.064l-.666.705a4.1 4.1 0 01-5.966-.002l-.69-.734a8.85 8.85 0 00-1.686 1.02l.322 1.141zm4.99 8.319a2.05 2.05 0 110-4.1 2.05 2.05 0 010 4.1z"/>
					</svg>
					
					<?php esc_html_e('Badge Settings', 'marketking'); ?>
				</div>

				<!-- VISIBLE GROUPS -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_multiple_vendor_selectors_container">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Applies to Groups','marketking');?></div>
			        <?php

    					$selected_options = array();
    					if( get_current_screen()->action !== 'add'){
    			        	$selected_options_string = get_post_meta($post->ID, 'marketking_group_visible_groups_settings', true);

    			        	$selected_options = explode(',', $selected_options_string);
    			        }
			        			        

			        ?>
			        <select id="marketking_group_visible_groups_settings" name="marketking_group_visible_groups_settings[]" class="marketking_select_group_multiple_vendor_selectors" multiple>
			        	<?php 

			        	$groups = get_posts([
		            	  'post_type' => 'marketking_group',
		            	  'post_status' => 'publish',
		            	  'numberposts' => -1
		            	]);
		            	foreach ($groups as $group){
		            		$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if (intval($selected_option) === intval($group->ID)){
									$is_selected = 'yes';
								}
							}
		            		?>
			        		<option value="<?php echo esc_attr( $group->ID ); ?>" <?php selected('yes',$is_selected, true);?>><?php echo esc_html( $group->post_title ); ?></option>
			        		<?php 
			        	}


			        	?>
			        </select>
			    </div>

			    <!-- CONDITION -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_single_vendor_selectors">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Condition ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(badge is displayed only if condition is met)', 'marketking');?></span></div>
			        <?php

    				$condition = get_post_meta($post->ID, 'marketking_badge_condition', true);

    				?>
        	        <select id="marketking_badge_condition" name="marketking_badge_condition">
        	        	<option value="none" <?php selected('none', $condition, true); ?>><?php esc_html_e('No condition','marketking');?></option>
        	        	<option value="salesvalue" <?php selected('salesvalue', $condition, true); ?>><?php esc_html_e('Total value sold (e.g. 1000 USD)','marketking');?></option>
        	        	<option value="ordernumber" <?php selected('ordernumber', $condition, true); ?>><?php esc_html_e('Number of orders (e.g. 500 orders)','marketking');?></option>
        	        	<option value="registrationtime" <?php selected('registrationtime', $condition, true); ?>><?php esc_html_e('Days since registration (e.g. 365 days)','marketking');?></option>
        	        	
        	        </select>
			    </div>

			    <!-- BADGE SORT ORDER --> 
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Badge Sort Order ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(controls the order in which badges are displayed)', 'marketking');?></span></div>
			        <input type="number" id="marketking_badge_sort_order" step="1" min="1" name="marketking_badge_sort_order" class="marketking_user_registration_user_data_container_element_text" placeholder="Enter the sort order of this badge (e.g: 1, 2, 5) ..." <?php 

			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_badge_sort_order', true);

			        if (!empty($group_allowed_products_number)){
			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
			        }
			    ?>>
			    </div>


			</div>

			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title marketking_group_payment_shipping_methods_container_element_title_empty">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_shipping" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
					  <path fill="#A5A5A5" d="M35 26.667v-20H5v20h30zm0-23.334a3.333 3.333 0 013.333 3.334v20A3.333 3.333 0 0135 30H23.333v3.333h3.334v3.334H13.333v-3.334h3.334V30H5a3.333 3.333 0 01-3.333-3.333v-20A3.322 3.322 0 015 3.333h30zM8.333 10h15v8.333h-15V10zM25 10h6.667v3.333H25V10zm6.667 5v8.333H25V15h6.667zM8.333 20H15v3.333H8.333V20zm8.334 0h6.666v3.333h-6.666V20z"/>
					</svg>
					<?php esc_html_e('Group Settings', 'marketking'); ?>
				</div>

				<!-- VISIBLE VENDORS -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_multiple_vendor_selectors_container">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Applies to Vendors','marketking');?></div>
			        <?php

    					$selected_options = array();
    					if( get_current_screen()->action !== 'add'){
    			        	$selected_options_string = get_post_meta($post->ID, 'marketking_group_visible_vendors_settings', true);

    			        	$selected_options = explode(',', $selected_options_string);
    			        }
			        			        

			        ?>
			        <select id="marketking_group_visible_vendors_settings" name="marketking_group_visible_vendors_settings[]" class="marketking_select_group_multiple_vendor_selectors" multiple>
			        	<?php 

			        	$vendors = marketking()->get_all_vendors();
			        	foreach ($vendors as $vendor){
		            		$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if (intval($selected_option) === intval($vendor->ID)){
									$is_selected = 'yes';
								}
							}
		            		?>
			        		<option value="<?php echo esc_attr( $vendor->ID ); ?>" <?php selected('yes',$is_selected, true);?>><?php echo esc_html( marketking()->get_store_name_display($vendor->ID) ); ?></option>
			        		<?php 
			        	}


			        	?>
			        </select>
			    </div>


			     <!-- CONDITION VALUE -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group ">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Condition Value ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(enter a value for the selected condition if any)', 'marketking');?></span></div>
    			        <input type="text" id="marketking_badge_condition_value" name="marketking_badge_condition_value" class="marketking_user_registration_user_data_container_element_text" <?php 

    			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_badge_condition_value', true);

    			        if (!empty($group_allowed_products_number)){
    			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
    			        }
    			    ?>>
			    </div>

			</div>



		</div>
		<div class="marketking_pack_description_padding">
			<div class="marketking_group_visibility_container_content_title">
				<svg class="marketking_offers_metabox_icon" xmlns="http://www.w3.org/2000/svg" width="39" height="39" fill="none" viewBox="0 0 39 39">
				  <path fill="#C4C4C4" fill-rule="evenodd" d="M29.25 2.438H9.75a4.875 4.875 0 00-4.875 4.874v24.375a4.875 4.875 0 004.875 4.875h19.5a4.875 4.875 0 004.875-4.874V7.313a4.875 4.875 0 00-4.875-4.875zM12.187 9.75a1.219 1.219 0 000 2.438h14.626a1.219 1.219 0 000-2.438H12.188zm-1.218 6.094a1.219 1.219 0 011.219-1.219h14.624a1.219 1.219 0 010 2.438H12.188a1.219 1.219 0 01-1.218-1.22zm1.219 3.656a1.219 1.219 0 000 2.438h14.624a1.219 1.219 0 000-2.438H12.188zm0 4.875a1.219 1.219 0 000 2.438H19.5a1.219 1.219 0 000-2.438h-7.313z" clip-rule="evenodd"/>
				</svg>
				<?php esc_html_e('Description shown when hovering over the badge.','marketking');?>
			</div>
				<textarea name="marketking_badge_description" id="marketking_badge_description"><?php 
					if (get_current_screen()->action !== 'add'){
			            echo get_post_meta($post->ID, 'marketking_badge_description', true);
			        } 
		        ?></textarea>
		</div>
		<?php
	}

	/* Memberships */	
	public static function marketking_register_post_type_mpack() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Membership Packages', 'marketking' ),
	        'singular_name'         => esc_html__( 'Package', 'marketking' ),
	        'all_items'             => esc_html__( 'Membership Packages', 'marketking' ),
	        'menu_name'             => esc_html__( 'Membership Packages', 'marketking' ),
	        'add_new'               => esc_html__( 'New package', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New package', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit package', 'marketking' ),
	        'new_item'              => esc_html__( 'New package', 'marketking' ),
	        'view_item'             => esc_html__( 'View package', 'marketking' ),
	        'view_items'            => esc_html__( 'View packages', 'marketking' ),
	        'search_items'          => esc_html__( 'Search packages', 'marketking' ),
	        'not_found'             => esc_html__( 'No packages found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No packages found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent package', 'marketking' ),
	        'featured_image'        => esc_html__( 'Package image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set package image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove package image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as package image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into package', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this package', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter packages', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Package navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Packages list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Membership Package', 'marketking' ),
	        'description'           => '',
	        'labels'                => $labels,
	        'supports'              => array('title'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_mpack',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_mpack', $args );

	}

	// Add Metaboxes to Mpack items
	function marketking_mpack_metaboxes($post_type) {
	    $post_types = array('marketking_mpack');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
       			add_meta_box(
	               'marketking_mpack_description_metabox'
	               ,esc_html__( 'Package Settings', 'marketking' )
	               ,array( $this, 'marketking_mpack_description_metabox_content' )
	               ,$post_type
	               ,'advanced'
	               ,'high'
	           );
	         
	       }
	}

	function marketking_mpack_description_metabox_content(){
		global $post;
		$group_id = $post->ID;
		?>
		<div class="marketking_group_payment_shipping_methods_container">
			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_group_settings" xmlns="http://www.w3.org/2000/svg" width="41" height="41" fill="none" viewBox="0 0 41 41">
					  <path fill="#A5A5A5" d="M34.85 14.35v5.33a11.275 11.275 0 00-15.17 15.17h-8.405a5.125 5.125 0 01-5.125-5.125V14.35h28.7zm-5.125-8.2a5.125 5.125 0 015.125 5.125V12.3H6.15v-1.025a5.125 5.125 0 015.125-5.125h18.45zm-4.99 17.306a4.102 4.102 0 01-2.931 5.08l-.947.242a9.643 9.643 0 00.02 2.083l.718.17a4.102 4.102 0 012.985 5.164l-.26.865a8.77 8.77 0 001.71 1.062l.667-.705a4.1 4.1 0 015.966.004l.69.734a8.813 8.813 0 001.686-1.021l-.32-1.14a4.101 4.101 0 012.931-5.082l.943-.24a9.701 9.701 0 00-.02-2.085l-.714-.168a4.099 4.099 0 01-2.984-5.166l.258-.863a8.852 8.852 0 00-1.712-1.064l-.666.705a4.1 4.1 0 01-5.966-.002l-.69-.734a8.85 8.85 0 00-1.686 1.02l.322 1.141zm4.99 8.319a2.05 2.05 0 110-4.1 2.05 2.05 0 010 4.1z"/>
					</svg>
					
					<?php esc_html_e('Package Settings', 'marketking'); ?>
				</div>
				<div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Package Sort Order ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(controls the order in which packages are displayed)', 'marketking');?></span></div>
			        <input type="number" id="marketking_pack_sort_order" step="1" min="1" name="marketking_pack_sort_order" class="marketking_user_registration_user_data_container_element_text" placeholder="Enter the sort order of this package (e.g: 1, 2, 5) ..." <?php 

			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_pack_sort_order', true);

			        if (!empty($group_allowed_products_number)){
			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
			        }
			    ?>>
			    </div>

			    <!-- PACKAGE PRICE -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group ">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Package Price ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(text field)', 'marketking');?></span></div>
    			        <input type="text" id="marketking_pack_price" name="marketking_pack_price" class="marketking_user_registration_user_data_container_element_text" placeholder="Enter the price of the package as text (e.g: '$135 /yr') ..." <?php 

    			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_pack_price', true);

    			        if (!empty($group_allowed_products_number)){
    			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
    			        }
    			    ?>>
			    </div>

			    <!-- ASSOCIATED VENDOR GROUP -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_single_vendor_selectors">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Vendor Group ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(vendors that purchase this package will be assigned to this group)', 'marketking');?></span></div>
			        <?php

			        $checkedgroup = get_post_meta($post->ID, 'marketking_pack_vendor_group', true);

			        ?>
			        <select id="marketking_pack_vendor_group" name="marketking_pack_vendor_group">
			        	<?php 
			        	$groups = get_posts([
						  'post_type' => 'marketking_group',
						  'post_status' => 'publish',
						  'numberposts' => -1
						]);

						foreach ($groups as $group){
							echo '<option value="'.esc_attr($group->ID).'" '.selected($group->ID, $checkedgroup, false).'>'.esc_html($group->post_title).'</option>';
						}
			        	?>
			        </select>
			    </div>

			    <!-- PACKAGE IMAGE -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group ">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Package Image ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(optional)', 'marketking');?></span></div>
    			        <input type="text" id="marketking_pack_image" name="marketking_pack_image" class="marketking_user_registration_user_data_container_element_text" placeholder="Choose an image / enter image URL..." <?php 

    			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_pack_image', true);

    			        if (!empty($group_allowed_products_number)){
    			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
    			        }
    			    ?>>

			    </div>

			    <!-- PACKAGE IMAGE -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group ">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Advertising Credits ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(optional: number of credits given to vendor when they purchase this package)', 'marketking');?></span></div>
    			        <input type="number" id="marketking_pack_credits" name="marketking_pack_credits" min="0" step="1" placeholder="<?php echo esc_attr('Enter a number of credits (min 0) ...','marketking');?>" class="marketking_user_registration_user_data_container_element_text" <?php 

    			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_pack_credits', true);

    			        if (!empty($group_allowed_products_number)){
    			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
    			        }
    			    ?>>

			    </div>


			</div>

			<div class="marketking_group_payment_shipping_methods_container_element">

				<div class="marketking_group_payment_shipping_methods_container_element_title marketking_group_payment_shipping_methods_container_element_title_empty">
					
					<svg class="marketking_group_payment_shipping_methods_container_element_title_icon_shipping" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
					  <path fill="#A5A5A5" d="M35 26.667v-20H5v20h30zm0-23.334a3.333 3.333 0 013.333 3.334v20A3.333 3.333 0 0135 30H23.333v3.333h3.334v3.334H13.333v-3.334h3.334V30H5a3.333 3.333 0 01-3.333-3.333v-20A3.322 3.322 0 015 3.333h30zM8.333 10h15v8.333h-15V10zM25 10h6.667v3.333H25V10zm6.667 5v8.333H25V15h6.667zM8.333 20H15v3.333H8.333V20zm8.334 0h6.666v3.333h-6.666V20z"/>
					</svg>
					<?php esc_html_e('Group Settings', 'marketking'); ?>
				</div>

				<div class="marketking_group_payment_shipping_methods_container_element_method marketking_group_settings_checkbox">
					<div class="marketking_group_payment_shipping_methods_container_element_method_name">
						<?php esc_html_e('Featured package ("Most Popular")','marketking'); ?>
					</div>
					<?php

					$featured = get_post_meta($group_id, 'marketking_mpack_featured_pack_setting', true );
					
					?>
					<input type="checkbox" value="1" class="marketking_group_payment_shipping_methods_container_element_method_checkbox" id="marketking_mpack_featured_pack_setting" name="marketking_mpack_featured_pack_setting" <?php checked(1, intval($featured), true); ?>>

				</div>

				 <!-- PACKAGE PRICE DESCRIPTION -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_user_registration_user_data_container_element_group_after_checkbox ">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Package Price Description','marketking');?></div>
    			        <input type="text" id="marketking_pack_price_description" name="marketking_pack_price_description" class="marketking_user_registration_user_data_container_element_text" placeholder="Enter a description below the price (e.g. 5 Sites, One-Time Purchase)..." <?php 

    			        $group_allowed_products_number = get_post_meta($group_id, 'marketking_pack_price_description', true);

    			        if (!empty($group_allowed_products_number)){
    			        	echo 'value="'.esc_attr($group_allowed_products_number).'"';
    			        }
    			    ?>>
			    </div>

				<!-- ASSOCIATED PRODUCT -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_single_vendor_selectors">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Associated Product ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(this package will be connected with an existing site product)', 'marketking');?></span></div>
			        <?php

			        // if there are a relatively small nr of products e.g <1000, show all products in a select, else 
			        // enter product id

			        $products_over_number = get_posts( array(
    					'post_type' => array( 'product'),
    					'post_status'=>'publish', 
    					'numberposts' => 1000,
    					'fields' => 'ids',
    				));
    				$checkedproduct = get_post_meta($post->ID, 'marketking_pack_product', true);

    				$maxnr = apply_filters('marketking_pack_product_max_nr', 999);

			        if (count($products_over_number) > $maxnr || apply_filters('marketking_pack_show_product_id', false)){
			        	// show input
			        	?>
			        	<input type="number" id="marketking_pack_product" step="1" min="1" name="marketking_pack_product" class="marketking_user_registration_user_data_container_element_text" placeholder="Enter a product ID (e.g: 1234, 8050, etc.) ..." <?php 

			        	$group_allowed_products_number = get_post_meta($group_id, 'marketking_pack_product', true);

			        	if (!empty($group_allowed_products_number)){
			        		echo 'value="'.esc_attr($group_allowed_products_number).'"';
			        	}
			        	echo '>';
			        } else {
			        	// show select

	        	        ?>
	        	        <select id="marketking_pack_product" name="marketking_pack_product">
	        	        	<optgroup label="<?php esc_html_e('Products', 'marketking'); ?>">
		        	        	<?php 
		        	        	$products = get_posts([
		        				  'post_type' => 'product',
		        				  'post_status' => 'publish',
		        				  'numberposts' => -1
		        				]);

		        				foreach ($products as $product){
		        					echo '<option value="'.esc_attr($product->ID).'" '.selected($product->ID, $checkedproduct, false).'>'.esc_html($product->post_title).'</option>';
		        				}
		        	        	?>
		        	        </optgroup>
            	        	<optgroup label="<?php esc_html_e('Product Variations', 'marketking'); ?>">
    	        	        	<?php 
    	        	        	$products = get_posts([
    	        				  'post_type' => 'product_variation',
    	        				  'post_status' => 'publish',
    	        				  'numberposts' => -1
    	        				]);

    	        				foreach ($products as $product){
    	        					echo '<option value="'.esc_attr($product->ID).'" '.selected($product->ID, $checkedproduct, false).'>'.esc_html($product->post_title).'</option>';
    	        				}
    	        	        	?>
    	        	        </optgroup>
	        	        </select>
	        	       <?php
			        }
			    	?>
			        
			    </div>

			    <!-- VISIBLE GROUPS -->
			    <div class="marketking_user_registration_user_data_container_element marketking_user_registration_user_data_container_element_group marketking_select_group_multiple_vendor_selectors_container">
			        <div class="marketking_user_registration_user_data_container_element_label"><?php esc_html_e('Visible to Groups ','marketking');?><span class="marketking_group_setting_description"><?php esc_html_e('(leave empty to choose all)', 'marketking');?></span></div>
			        <?php

    					$selected_options = array();
    					if( get_current_screen()->action !== 'add'){
    			        	$selected_options_string = get_post_meta($post->ID, 'marketking_group_visible_groups_settings', true);

    			        	$selected_options = explode(',', $selected_options_string);
    			        }
			        			        

			        ?>
			        <select id="marketking_group_visible_groups_settings" name="marketking_group_visible_groups_settings[]" class="marketking_select_group_multiple_vendor_selectors" multiple>
			        	<?php 

			        	$groups = get_posts([
		            	  'post_type' => 'marketking_group',
		            	  'post_status' => 'publish',
		            	  'numberposts' => -1
		            	]);
		            	foreach ($groups as $group){
		            		$is_selected = 'no';
    		            	foreach ($selected_options as $selected_option){
								if (intval($selected_option) === intval($group->ID)){
									$is_selected = 'yes';
								}
							}
		            		?>
			        		<option value="<?php echo esc_attr( $group->ID ); ?>" <?php selected('yes',$is_selected, true);?>><?php echo esc_html( $group->post_title ); ?></option>
			        		<?php 
			        	}


			        	?>
			        </select>
			    </div>


			</div>



		</div>
		<div class="marketking_pack_description_padding">
			<div class="marketking_group_visibility_container_content_title">
				<svg class="marketking_offers_metabox_icon" xmlns="http://www.w3.org/2000/svg" width="39" height="39" fill="none" viewBox="0 0 39 39">
				  <path fill="#C4C4C4" fill-rule="evenodd" d="M29.25 2.438H9.75a4.875 4.875 0 00-4.875 4.874v24.375a4.875 4.875 0 004.875 4.875h19.5a4.875 4.875 0 004.875-4.874V7.313a4.875 4.875 0 00-4.875-4.875zM12.187 9.75a1.219 1.219 0 000 2.438h14.626a1.219 1.219 0 000-2.438H12.188zm-1.218 6.094a1.219 1.219 0 011.219-1.219h14.624a1.219 1.219 0 010 2.438H12.188a1.219 1.219 0 01-1.218-1.22zm1.219 3.656a1.219 1.219 0 000 2.438h14.624a1.219 1.219 0 000-2.438H12.188zm0 4.875a1.219 1.219 0 000 2.438H19.5a1.219 1.219 0 000-2.438h-7.313z" clip-rule="evenodd"/>
				</svg>
				<?php esc_html_e('Description that is shown to vendors.','marketking');?>
			</div>
				<textarea name="marketking_pack_description" id="marketking_pack_description"><?php 
					if (get_current_screen()->action !== 'add'){
			            echo get_post_meta($post->ID, 'marketking_pack_description', true);
			        } 
		        ?></textarea>
		</div>
		<?php
	}

	// Save Membership Package Metabox Content
	function marketking_save_mpack_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		if (get_post_type($post_id) === 'marketking_mpack'){

			// Save description
			$marketking_vitem_description = sanitize_textarea_field(filter_input(INPUT_POST, 'marketking_pack_description'));
			if ($marketking_vitem_description !== NULL){
				update_post_meta($post_id, 'marketking_pack_description', sanitize_textarea_field($marketking_vitem_description));
			}

			
			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_pack_sort_order'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_pack_sort_order', sanitize_text_field($meta_input));
			}

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_pack_price'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_pack_price', sanitize_text_field($meta_input));
			}

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_pack_image'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_pack_image', sanitize_text_field($meta_input));
			}

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_pack_credits'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_pack_credits', sanitize_text_field($meta_input));
			}

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_pack_product'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_pack_product', sanitize_text_field($meta_input));
			}			

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_pack_vendor_group'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_pack_vendor_group', sanitize_text_field($meta_input));
			}	

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_pack_price_description'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_pack_price_description', sanitize_text_field($meta_input));
			}			

			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_mpack_featured_pack_setting'));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_mpack_featured_pack_setting', sanitize_text_field($meta_input));
			}


			// save other settings
			if (isset($_POST['marketking_group_visible_groups_settings'])){
				$rule_applies_multiple_options = $_POST['marketking_group_visible_groups_settings'];
			} else {
				$rule_applies_multiple_options = array();
			}
			if (empty($rule_applies_multiple_options)){
				$rule_applies_multiple_options = array();
			}

			$options_string = '';
			foreach ($rule_applies_multiple_options as $option){
				$options_string .= sanitize_text_field ($option).',';
			}
			// remove last comma
			$options_string = substr($options_string, 0, -1);
			update_post_meta( $post_id, 'marketking_group_visible_groups_settings', $options_string);
		
			
			
		}
	}

	// Register abuse reports
	public static function marketking_register_post_type_abuse() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Abuse Reports', 'marketking' ),
	        'singular_name'         => esc_html__( 'Report', 'marketking' ),
	        'all_items'             => esc_html__( 'Abuse Reports', 'marketking' ),
	        'menu_name'             => esc_html__( 'Abuse Reports', 'marketking' ),
	        'add_new'               => esc_html__( 'New report', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New report', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit report', 'marketking' ),
	        'new_item'              => esc_html__( 'New report', 'marketking' ),
	        'view_item'             => esc_html__( 'View report', 'marketking' ),
	        'view_items'            => esc_html__( 'View reports', 'marketking' ),
	        'search_items'          => esc_html__( 'Search reports', 'marketking' ),
	        'not_found'             => esc_html__( 'No reports found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No reports found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent report', 'marketking' ),
	        'featured_image'        => esc_html__( 'Report image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set report image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove report image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as report image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into report', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this report', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter reports', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Report navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Reports list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Abuse Report', 'marketking' ),
	        'description'           => '',
	        'labels'                => $labels,
	        'supports'              => array('title', 'editor'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'capabilities' => array(
	            'create_posts' => false, // Removes support for the "Add New" function
	          ),
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_abuse',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_abuse', $args );

	}

	// Register announcements
	public static function marketking_register_post_type_announcement() {
		// Build labels and arguments
	    $labels = array(
	        'name'                  => esc_html__( 'Announcements', 'marketking' ),
	        'singular_name'         => esc_html__( 'Announcement', 'marketking' ),
	        'all_items'             => esc_html__( 'Announcements', 'marketking' ),
	        'menu_name'             => esc_html__( 'Announcements', 'marketking' ),
	        'add_new'               => esc_html__( 'New announcement', 'marketking' ),
	        'add_new_item'          => esc_html__( 'New announcement', 'marketking' ),
	        'edit'                  => esc_html__( 'Edit', 'marketking' ),
	        'edit_item'             => esc_html__( 'Edit announcement', 'marketking' ),
	        'new_item'              => esc_html__( 'New announcement', 'marketking' ),
	        'view_item'             => esc_html__( 'View announcement', 'marketking' ),
	        'view_items'            => esc_html__( 'View announcements', 'marketking' ),
	        'search_items'          => esc_html__( 'Search announcements', 'marketking' ),
	        'not_found'             => esc_html__( 'No announcements found', 'marketking' ),
	        'not_found_in_trash'    => esc_html__( 'No announcements found in trash', 'marketking' ),
	        'parent'                => esc_html__( 'Parent announcement', 'marketking' ),
	        'featured_image'        => esc_html__( 'Announcement image', 'marketking' ),
	        'set_featured_image'    => esc_html__( 'Set announcement image', 'marketking' ),
	        'remove_featured_image' => esc_html__( 'Remove announcement image', 'marketking' ),
	        'use_featured_image'    => esc_html__( 'Use as announcement image', 'marketking' ),
	        'insert_into_item'      => esc_html__( 'Insert into announcement', 'marketking' ),
	        'uploaded_to_this_item' => esc_html__( 'Uploaded to this announcement', 'marketking' ),
	        'filter_items_list'     => esc_html__( 'Filter announcements', 'marketking' ),
	        'items_list_navigation' => esc_html__( 'Announcement navigation', 'marketking' ),
	        'items_list'            => esc_html__( 'Announcements list', 'marketking' )
	    );
	    $args = array(
	        'label'                 => esc_html__( 'Announcement', 'marketking' ),
	        'description'           => esc_html__( 'This is where you can create new announcements', 'marketking' ),
	        'labels'                => $labels,
	        'supports'              => array('title', 'editor'),
	        'hierarchical'          => false,
	        'public'                => false,
	        'publicly_queryable' 	=> false,
	        'show_ui'               => true,
	        'show_in_menu'          => false,
	        'menu_position'         => 100,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => false,
	        'can_export'            => false,
	        'has_archive'           => false,
	        'exclude_from_search'   =>  true,
	        'rewrite'               => false,
	        'capability_type'       => 'product',
	        'show_in_rest'          => true,
	        'rest_base'             => 'marketking_announce',
	        'rest_controller_class' => 'WP_REST_Posts_Controller',
	    );

		// Actually register the post type
		register_post_type( 'marketking_announce', $args );

	}

	// Add Metaboxes to Announcements
	function marketking_announcement_metaboxes($post_type) {
	    $post_types = array('marketking_announce');     //limit meta box to certain post types
       	if ( in_array( $post_type, $post_types ) ) {
	           add_meta_box(
	               'marketking_announcement_visibility_metabox'
	               ,esc_html__( 'Announcement Visibility', 'marketking' )
	               ,array( $this, 'marketking_announcement_visibility_metabox_content' )
	               ,$post_type
	               ,'advanced'
	               ,'high'
	           );
	       }
	}

	function marketking_announcement_visibility_metabox_content(){
		if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
	    ?>
	    <div class="marketking_group_visibility_container">
	    	<div class="marketking_group_visibility_container_top">
	    		<?php esc_html_e( 'Group Visibility', 'marketking' ); ?>
	    	</div>
	    	<div class="marketking_group_visibility_container_content">
	    		<div class="marketking_group_visibility_container_content_title">
					<svg class="marketking_group_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="none" viewBox="0 0 45 45">
					  <path fill="#C4C4C4" d="M22.382 7.068c-3.876 0-7.017 3.668-7.017 8.193 0 3.138 1.51 5.863 3.73 7.239l-2.573 1.192-6.848 3.176c-.661.331-.991.892-.991 1.686v7.541c.054.943.62 1.822 1.537 1.837h24.36c1.048-.091 1.578-.935 1.588-1.837v-7.541c0-.794-.33-1.355-.992-1.686l-6.6-3.175-2.742-1.3c2.128-1.407 3.565-4.073 3.565-7.132 0-4.525-3.142-8.193-7.017-8.193zM11.063 9.95c-1.667.063-2.99.785-3.993 1.935a7.498 7.498 0 00-1.663 4.663c.068 2.418 1.15 4.707 3.076 5.905l-7.69 3.573c-.529.198-.793.661-.793 1.389v6.053c.041.802.458 1.477 1.24 1.488h5.11v-6.401c.085-1.712.888-3.095 2.333-3.77l5.109-2.43a4.943 4.943 0 001.141-.944c-2.107-3.25-2.4-7.143-1.041-10.567-.883-.54-1.876-.888-2.829-.894zm22.822 0c-1.09.023-2.098.425-2.926.992 1.32 3.455.956 7.35-.993 10.37.43.495.877.876 1.34 1.14l4.912 2.333c1.496.82 2.267 2.216 2.282 3.77v6.401h5.259c.865-.074 1.233-.764 1.241-1.488v-6.053c0-.662-.264-1.124-.794-1.39l-7.59-3.622c1.968-1.452 2.956-3.627 2.976-5.855-.053-1.763-.591-3.4-1.663-4.663-1.12-1.215-2.51-1.922-4.044-1.935z"/>
					</svg>
					<?php esc_html_e( 'Groups who can see this announcement', 'marketking' ); ?>
	    		</div>
            	<?php
	            	$groups = get_posts([
	            	  'post_type' => 'marketking_group',
	            	  'post_status' => 'publish',
	            	  'numberposts' => -1
	            	]);
	            	foreach ($groups as $group){
	            		$checked = '';
		            		// If current page is not Add New 
		            		if( get_current_screen()->action !== 'add'){
			            		global $post;
			            		$check = intval(get_post_meta($post->ID, 'marketking_group_'.$group->ID, true));
			            		if ($check === 1){
			            			$checked = 'checked="checked"';
			            		}	
			            	}  
	            		?>
	            		<div class="marketking_group_visibility_container_content_checkbox">
	            			<div class="marketking_group_visibility_container_content_checkbox_name">
	            				<?php echo esc_html($group->post_title); ?>
	            			</div>
	            			<input type="hidden" name="marketking_group_<?php echo esc_attr($group->ID);?>" value="0">
	            			<input type="checkbox" value="1" class="marketking_group_visibility_container_content_checkbox_input" name="marketking_group_<?php echo esc_attr($group->ID);?>" id="marketking_group_<?php echo esc_attr($group->ID);?>" value="1" <?php echo $checked;?> />
	            		</div>
	            		<?php
	            	}
	            ?>
	    	</div>
	    </div>

	    <div class="marketking_group_visibility_container">
	    	<div class="marketking_group_visibility_container_top">
	    		<?php esc_html_e( 'Vendor Visibility', 'marketking' ); ?>
	    	</div>
	    	<div class="marketking_group_visibility_container_content">
	    		<div class="marketking_group_visibility_container_content_title">
					<svg class="marketking_user_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="31" height="23" fill="none" viewBox="0 0 31 23">
					  <path fill="#C4C4C4" d="M9.333 11.58c3.076 0 5.396-2.32 5.396-5.396C14.73 3.11 12.41.79 9.333.79c-3.075 0-5.396 2.32-5.396 5.395 0 3.076 2.32 5.396 5.396 5.396zm1.542 1.462H7.792c-4.25 0-7.709 3.458-7.709 7.708v1.542h18.5V20.75c0-4.25-3.458-7.708-7.708-7.708zm17.412-7.258l-6.63 6.616-1.991-1.992-2.18 2.18 4.171 4.17 8.806-8.791-2.176-2.183z"/>
					</svg>
					<?php esc_html_e( 'Vendors who can see this announcement (comma-separated)', 'marketking' ); ?>
	    		</div>
	    		<textarea name="marketking_category_users_textarea" id="marketking_category_users_textarea"><?php 
		            		// If current page is not Add New 
		            		if( get_current_screen()->action !== 'add'){
			            		global $post;
			            		echo get_post_meta($post->ID, 'marketking_category_users_textarea', true);
			            	}  
	            			?></textarea>
            	<div class="marketking_category_users_textarea_buttons_container"><?php 
            		// get all vendors
            		$vendors = marketking()->get_all_vendors();

            		echo '<select id="marketking_all_users_dropdown">';
            		foreach ($vendors as $vendor){
            			$name = marketking()->get_store_name_display($vendor->ID);
            			// get user login
            			echo '<option value="'.esc_attr($vendor->user_login).'">'.esc_html($name).' ('.$vendor->user_login.')</option>';
            		}
            		echo '</select>';
					?>

					<button type="button" class="button" id="marketking_category_add_user"><?php esc_html_e('Add vendor','marketking'); ?></button>
            	</div>

	    	</div>
	    </div>
	    <?php
	}


	// Save Announcements Metabox Content
	function marketking_save_announcement_metaboxes($post_id){
		if (isset($_POST['_inline_edit'])){
			if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
			    return;
			}
		}
		if (isset($_REQUEST['bulk_edit'])){
		    return;
		}
		if (get_post_type($post_id) === 'marketking_announce'){

			// Get all groups
			$groups = get_posts([
			  'post_type' => 'marketking_group',
			  'post_status' => 'publish',
			  'numberposts' => -1
			]);

			// For each group option, save user's choice as post meta
			foreach ($groups as $group){
				$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_'.$group->ID));
				if($meta_input !== NULL){
					update_post_meta($post_id, 'marketking_group_'.$group->ID, sanitize_text_field($meta_input));
				}
			}

			// Save user visibility
			$meta_user_visibility = sanitize_text_field(filter_input(INPUT_POST, 'marketking_category_users_textarea'));
			if ($meta_user_visibility !== NULL){
				// get current users list
				$currentuserstextarea = esc_html(get_post_meta($post_id, 'marketking_category_users_textarea', true));
				$currentusersarray = explode(',', $currentuserstextarea);
				// delete all individual user meta
				foreach ($currentusersarray as $user){
					delete_post_meta( $post_id, 'marketking_user_'.trim($user));
				}
				// get new users list
				$newusertextarea = $meta_user_visibility;
				$newusersarray = explode(',', $newusertextarea);
				// set new user meta
				foreach ($newusersarray as $newuser){
					update_post_meta( $post_id, 'marketking_user_'.sanitize_text_field(trim($newuser)), 1);
				}
				// Update users textarea
				update_post_meta($post_id, 'marketking_category_users_textarea', sanitize_text_field($meta_user_visibility));
			}


		    if ( 'publish' !== get_post_status($post_id) ){
		        return;
		    }
		    $post = get_post($post_id);

		    $content = $post->post_content;
		    // get all vendors
		    $agents = get_users(array(
			    'meta_key'     => 'marketking_group',
			    'meta_value'   => 'none',
			    'meta_compare' => '!=',
			    'fields' => 'ids',
			));
			
			foreach ($agents as $agent){
				// check if announcement visible, and if so, send it.
				$agent_group = get_user_meta($agent, 'marketking_group', true);
				$group_visible = intval(get_post_meta($post->ID, 'marketking_group_'.$agent_group, true));
				$user_info = get_userdata($agent);

				$login = $user_info->user_login;
				$user_visible = intval(get_post_meta($post->ID, 'marketking_user_'.$login, true));
				
				if (($group_visible === 1) || ($user_visible === 1)){

					$current_id = get_current_user_id();
					if (marketking()->is_vendor_team_member()){
						$current_id = marketking()->get_team_member_parent();
					}

					// send it
					$mailadress = $user_info->user_email;
					do_action( 'marketking_new_announcement', $mailadress, $content, $current_id, $post->ID );
				}
			}
	
		}
	}


	function marketking_add_columns_group_menu_mpack($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'sort' => esc_html__( 'Sort Order', 'marketking' ),
			'price' => esc_html__( 'Package Price', 'marketking' ),
			'group' => esc_html__( 'Vendor Group', 'marketking' ),
			'image' => esc_html__( 'Package Image', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 2, true) + $columns;
	    return $columns;
	}

	function marketking_columns_group_data_mpack( $column, $post_id ) {
	    switch ( $column ) {

	        case 'sort' :

            	$sort = get_post_meta($post_id,'marketking_pack_sort_order', true);
            	echo esc_html($sort);
	            break;


			case 'price' :

				$price = get_post_meta($post_id,'marketking_pack_price', true);
				echo esc_html($price);
				break;


			case 'group' :

				$groupid = get_post_meta($post_id,'marketking_pack_vendor_group', true);
				echo esc_html(get_the_title($groupid));
				break;

			case 'image' :

				$imageurl = get_post_meta($post_id,'marketking_pack_image', true);

				if (!empty($imageurl)){
					echo '<img class="marketking_thumbnail_backend" src="'.esc_attr($imageurl).'">';
				}
				break;

	    }
	}
	
	function process_membership_completed($order_id) {

		$order = wc_get_order( $order_id );
		$current_id = $order->get_customer_id();

		foreach ($order->get_items() as $item_id => $item ) {

			// Get the WC_Order_Item_Product object properties in an array
		    $item_data = $item->get_data();

		    if ($item['quantity'] > 0) {
		        // get the WC_Product object
		        $product_id = $item['product_id'];

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

		        		// check setting to see if vendor should be added to group now, or on order completed
		        		if (get_option('marketking_memberships_assign_group_time_setting', 'order_placed')  === 'order_completed'){
		        			// this product is indeed a pack product, we must now move the user to group
		        			$pack_group = get_post_meta($pack->ID,'marketking_pack_vendor_group', true);
		        			// check that current user is indeed a vendor

		        			if (marketking()->is_vendor($current_id)){
		        				update_user_meta($current_id, 'marketking_group', $pack_group);

		        				$note= esc_html__('The vendor has been moved to the following group:','marketking').' '.get_the_title($pack_group);

		        				$order->add_order_note( $note );

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


		        				// SUBSCRIPTIO
		        				$subscriptio_product = get_post_meta($product_id, '_rp_sub:subscription_product', true);
		        				if ($subscriptio_product === 'yes'){
		        					$subscription_id = intval($order_id)+2;
		        					$subscription_item = 'subscriptio:'.$subscription_id.':'.$pack_group;

		        					update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        				
		        				}

		        				// SUMO
		        				$sumo_product = get_post_meta($product_id, 'sumo_susbcription_status', true);
		        				if (intval($sumo_product) === 1){
		        					$subscription_id = intval($order_id)+1;
		        					$subscription_item = 'sumo:'.$subscription_id.':'.$pack_group;

		        					update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        				}

		        				// YITH
		        				$yith_product = get_post_meta($product_id, '_ywsbs_subscription', true);
		        				if ($yith_product === 'yes'){
		        					$subscription_id = intval($order_id)+1;
		        					$subscription_item = 'yith:'.$subscription_id.':'.$pack_group;

		        					update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        				}

		        				// WooCommerce Subscriptions
		        				$product = wc_get_product($product_id);
		        				$type = $product->get_type();
		        				if ($type === 'subscription' || $type === 'variable-subscription'){
		        					$subscription_id = intval($order_id)+1;
		        					$subscription_item = 'woo:'.$subscription_id.':'.$pack_group;

		        					update_user_meta($current_id,'marketking_vendor_active_subscription', $subscription_item);
		        				}

		        			}
		        		}

		        		
		        	}
		        }

		    }
		}
	}


	// Add custom columns to vitem menu
	function marketking_add_columns_group_menu_vitem($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'description' => esc_html__( 'Description', 'marketking' ),
			'visibility' => esc_html__( 'Visibility', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 2, true) + $columns;
	    return $columns;
	}

	// Add vitem custom columns data
	function marketking_columns_group_data_vitem( $column, $post_id ) {

		$description = get_post_meta($post_id, 'marketking_vitem_description_textarea', true);


	    switch ( $column ) {

	        case 'description' :

	        	echo esc_html($description);

	            break;

	        case 'visibility' :

            	$groups = get_posts([
            	  'post_type' => 'marketking_group',
            	  'post_status' => 'publish',
            	  'numberposts' => -1
            	]);

            	$groups_message = '';
            	foreach ($groups as $group){
            		$check = intval(get_post_meta($post_id, 'marketking_group_'.$group->ID, true));
            		if ($check === 1){
            			$groups_message .= esc_html($group->post_title).', ';
            		}        		
            	}
            	if ( ! empty($groups_message)){
            		echo '<strong>'.esc_html__('Groups: ','marketking').'</strong>'.esc_html(substr($groups_message, 0, -2));
            	}

	            break;

	    }
	}

	// Add custom columns to vreq menu
	function marketking_add_columns_group_menu_vreq($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'vendor' => esc_html__( 'Vendor', 'marketking' ),
			'vitem' => esc_html__( 'Item Submitted', 'marketking' ),
			'download' => esc_html__( 'Uploaded File', 'marketking' ),
			'status' => esc_html__( 'Status', 'marketking' ),
			'approval' => esc_html__( 'Approval', 'marketking' ),
			'dated' => esc_html__( 'Date', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 1, true) + $columns;
	    return $columns;
	}

	// Add vreq custom columns data
	function marketking_columns_group_data_vreq( $column, $post_id ) {

		$description = get_post_meta($post_id, 'marketking_vitem_description_textarea', true);

		$status = get_post_meta($post_id,'status', true);

	    switch ( $column ) {

	        case 'vendor' :

	        	$author_id = get_post_field('post_author', $post_id);
	        	$link = get_edit_user_link($author_id);
	        	$name = marketking()->get_store_name_display($author_id);
	        	echo '<a href="'.esc_attr($link).'">'.esc_html($name).'</a>';

	            break;

	        case 'vitem' :

            	$vitem = get_post_meta($post_id,'vitem', true);
            	$title = get_the_title($vitem);
            	echo esc_html($title);

	            break;

            case 'status' :
          	
            	echo ucfirst(esc_html($status));

	            break;

	        case 'download' :

            	$fileurl = get_post_meta($post_id,'fileurl', true);
            	echo '<a href="'.esc_url($fileurl).'">'.esc_html__('View File','marketking').'</a>';

	            break;

            case 'dated' :

            	echo get_the_date('',$post_id);

	            break;

            case 'approval' :


	        	if ($status === 'rejected'){
	        		?>
	        		<button type="button" class="button button-secondary marketking_mark_button marketking_mark_button_verification_approve" value="<?php echo esc_attr($post_id);?>"><?php esc_html_e('Change status: Approve','marketking'); ?></button>

	        		<?php
	        	} else if ($status === 'approved'){
	        		?>
	        		<button type="button" class="button button-secondary marketking_mark_button marketking_mark_button_verification_reject" value="<?php echo esc_attr($post_id);?>"><?php esc_html_e('Change status: Reject','marketking'); ?></button>

	        		<?php
	        	} else if ($status === 'pending'){
	        		?>
	        		<button type="button" class="button button-primary marketking_mark_button marketking_mark_button_verification_approve" value="<?php echo esc_attr($post_id);?>"><?php esc_html_e('Approve','marketking'); ?></button>

	        		<button type="button" class="button button-secondary marketking_mark_button marketking_mark_button_verification_reject" value="<?php echo esc_attr($post_id);?>"><?php esc_html_e('Reject','marketking'); ?></button>

	        		<?php
	        	}

	            break;

	    }
	}

	// Add custom columns to refunds menu
	function marketking_add_columns_group_menu_refund($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'orderid' => esc_html__( 'Order', 'marketking' ),
			'vendor' => esc_html__( 'Vendor', 'marketking' ),
			'refundamount' => esc_html__( 'Refund Amount', 'marketking' ),
			'reason' => esc_html__( 'Reason', 'marketking' ),
			'paymentmethod'	=> esc_html__( 'Payment Method', 'marketking' ),
			'status'	=> esc_html__( 'Status', 'marketking' ),
			'dated'	=> esc_html__( 'Date', 'marketking' ),
			'actions'	=> esc_html__( 'Actions', 'marketking' ),

		);
		$columns = array_slice($columns_initial, 0, 1, true) + $columns;
	    return $columns;
	}

	function marketking_refund_row_action($actions, $post){
	    //check for your post type
	    if ($post->post_type === "marketking_refund"){

	        unset($actions['edit']);
	 	    unset( $actions['inline'] );
	 	    unset( $actions['trash'] );
	 	    unset($actions['inline hide-if-no-js']);

	    }
	    return $actions;
	}

	function marketking_vreq_row_action($actions, $post){
	    //check for your post type
	    if ($post->post_type === "marketking_vreq"){

	        unset($actions['edit']);
	 	    unset( $actions['inline'] );
	 	    unset($actions['inline hide-if-no-js']);

	    }
	    return $actions;
	}
	

	// Add refund custom columns data
	function marketking_columns_group_data_refund( $column, $post_id ) {

		$orderid = get_post_meta($post_id,'order_id', true);
		$order = wc_get_order($orderid);
		if ($order){
				$vendorid = get_post_meta($post_id,'vendor_id', true);
				$reason = get_post_meta($post_id,'reason', true);
				$value = get_post_meta($post_id,'value', true);
				$method = $order->get_payment_method_title();

				$completion_status = get_post_meta($post_id,'completion_status', true);

				
				$order_link = get_edit_post_link($orderid);

			    switch ( $column ) {

			        case 'orderid' :

			        	echo '<a href="'.esc_attr($order_link).'">'.esc_html__('Order ','marketking').'#'.esc_html($orderid).'</a>';

			            break;

			        case 'vendor' :

			        	$store = marketking()->get_store_name_display($vendorid);
			        	echo esc_html($store);
			        	

			            break;

			        case 'refundamount' :

			        	if ($value === 'full'){
			        		esc_html_e('Full Refund','marketking');
			        	} else if ($value === 'partial'){
			        		esc_html_e('Partial: ','marketking');
			        		$partialamount = get_post_meta($post_id, 'partialamount', true);
			        		echo wc_price($partialamount);
			        		if ($order){
			        			echo ' / '.wc_price($order->get_total());
			        		}
			        	}
			            break;

			        case 'reason' :


			        	if (intval($vendorid ) !== 1){
			        		echo esc_html(substr($reason, 0, 250));
			        		if (substr($reason, 0, 250) !== $reason){
			        			echo '...';
			        		}
			        	} else {
			        		// if vendor order, show full reason here
			        		echo esc_html($reason);
			        	}
			        	

			            break;

			        case 'status' :

			        	if (intval($vendorid) !== 1){
			        		if (apply_filters('hide_nonvendor_refunds_backend', true)){
				        		esc_html_e('Approved by Vendor - ','marketking');
				        	}
			        	}
			        	if ($completion_status === 'completed'){
			        		esc_html_e('Completed','marketking');
			        	}
			        	if ($completion_status === 'pending' || empty($completion_status)){
			        		esc_html_e('Pending','marketking');

			        	}


			            break;

			        case 'paymentmethod' :

			        	echo esc_html($method);
			            break;

			        case 'actions' :


			        	?>

			        	<?php
			        	if ($completion_status!=='completed'){
			        		?>
			        		<button type="button" class="button button-primary marketking_mark_button marketking_mark_button_completed" value="<?php echo esc_attr($post_id);?>"><?php esc_html_e('Mark as Completed','marketking'); ?></button>

			        		<?php
			        	} else {
			        		?>
			        		<button type="button" class="button button-secondary marketking_mark_button marketking_mark_button_pending" value="<?php echo esc_attr($post_id);?>"><?php esc_html_e('Mark as Pending','marketking'); ?></button>

			        		<?php
			        	}

			            break;

			        case 'dated':
			        	echo get_the_date('',$post_id);
			        	break;

			    }
		}
		
	}

	// Add custom columns to abuse menu
	function marketking_add_columns_group_menu_abuse($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'message' => esc_html__( 'Report Message', 'marketking' ),
			'product' => esc_html__( 'Post Reported', 'marketking' ),
			'vendor' => esc_html__( 'Vendor', 'marketking' ),
			'reportedby' => esc_html__( 'Reported by', 'marketking' ),
			'datetime'	=> esc_html__( 'Date', 'marketking' ),

		);
		$columns = array_slice($columns_initial, 0, 1, true) + $columns;
	    return $columns;
	}

	// Register docs
public static function marketking_register_post_type_docs() {
	// Build labels and arguments
    $labels = array(
        'name'                  => esc_html__( 'Docs', 'marketking' ),
        'singular_name'         => esc_html__( 'Docs', 'marketking' ),
        'all_items'             => esc_html__( 'Docs', 'marketking' ),
        'menu_name'             => esc_html__( 'Docs', 'marketking' ),
        'add_new'               => esc_html__( 'New docs', 'marketking' ),
        'add_new_item'          => esc_html__( 'New docs', 'marketking' ),
        'edit'                  => esc_html__( 'Edit', 'marketking' ),
        'edit_item'             => esc_html__( 'Edit docs', 'marketking' ),
        'new_item'              => esc_html__( 'New docs', 'marketking' ),
        'view_item'             => esc_html__( 'View docs', 'marketking' ),
        'view_items'            => esc_html__( 'View docs', 'marketking' ),
        'search_items'          => esc_html__( 'Search docs', 'marketking' ),
        'not_found'             => esc_html__( 'No docs found', 'marketking' ),
        'not_found_in_trash'    => esc_html__( 'No docs found in trash', 'marketking' ),
        'parent'                => esc_html__( 'Parent docs', 'marketking' ),
        'featured_image'        => esc_html__( 'Docs image', 'marketking' ),
        'set_featured_image'    => esc_html__( 'Set docs image', 'marketking' ),
        'remove_featured_image' => esc_html__( 'Remove docs image', 'marketking' ),
        'use_featured_image'    => esc_html__( 'Use as docs image', 'marketking' ),
        'insert_into_item'      => esc_html__( 'Insert into docs', 'marketking' ),
        'uploaded_to_this_item' => esc_html__( 'Uploaded to this docs', 'marketking' ),
        'filter_items_list'     => esc_html__( 'Filter docs', 'marketking' ),
        'items_list_navigation' => esc_html__( 'Docs navigation', 'marketking' ),
        'items_list'            => esc_html__( 'Docs list', 'marketking' )
    );
    $args = array(
        'label'                 => esc_html__( 'Docs', 'marketking' ),
        'description'           => esc_html__( 'This is where you can create new seller docs', 'marketking' ),
        'labels'                => $labels,
        'supports'              => array('title', 'editor'),
        'hierarchical'          => false,
        'public'                => false,
        'publicly_queryable' 	=> false,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_position'         => 100,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => false,
        'has_archive'           => false,
        'exclude_from_search'   =>  true,
        'rewrite'               => false,
        'capability_type'       => 'product',
        'show_in_rest'          => true,
        'rest_base'             => 'marketking_docs',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

	// Actually register the post type
	register_post_type( 'marketking_docs', $args );

}

// Add Metaboxes to Docs
function marketking_docs_metaboxes($post_type) {
    $post_types = array('marketking_docs');     //limit meta box to certain post types
   	if ( in_array( $post_type, $post_types ) ) {
           add_meta_box(
               'marketking_docs_visibility_metabox'
               ,esc_html__( 'Docs Visibility', 'marketking' )
               ,array( $this, 'marketking_docs_visibility_metabox_content' )
               ,$post_type
               ,'advanced'
               ,'high'
           );
       }
}

function marketking_docs_visibility_metabox_content(){
	if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
    ?>
    <div class="marketking_group_visibility_container">
    	<div class="marketking_group_visibility_container_top">
    		<?php esc_html_e( 'Group Visibility', 'marketking' ); ?>
    	</div>
    	<div class="marketking_group_visibility_container_content">
    		<div class="marketking_group_visibility_container_content_title">
				<svg class="marketking_group_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="none" viewBox="0 0 45 45">
				  <path fill="#C4C4C4" d="M22.382 7.068c-3.876 0-7.017 3.668-7.017 8.193 0 3.138 1.51 5.863 3.73 7.239l-2.573 1.192-6.848 3.176c-.661.331-.991.892-.991 1.686v7.541c.054.943.62 1.822 1.537 1.837h24.36c1.048-.091 1.578-.935 1.588-1.837v-7.541c0-.794-.33-1.355-.992-1.686l-6.6-3.175-2.742-1.3c2.128-1.407 3.565-4.073 3.565-7.132 0-4.525-3.142-8.193-7.017-8.193zM11.063 9.95c-1.667.063-2.99.785-3.993 1.935a7.498 7.498 0 00-1.663 4.663c.068 2.418 1.15 4.707 3.076 5.905l-7.69 3.573c-.529.198-.793.661-.793 1.389v6.053c.041.802.458 1.477 1.24 1.488h5.11v-6.401c.085-1.712.888-3.095 2.333-3.77l5.109-2.43a4.943 4.943 0 001.141-.944c-2.107-3.25-2.4-7.143-1.041-10.567-.883-.54-1.876-.888-2.829-.894zm22.822 0c-1.09.023-2.098.425-2.926.992 1.32 3.455.956 7.35-.993 10.37.43.495.877.876 1.34 1.14l4.912 2.333c1.496.82 2.267 2.216 2.282 3.77v6.401h5.259c.865-.074 1.233-.764 1.241-1.488v-6.053c0-.662-.264-1.124-.794-1.39l-7.59-3.622c1.968-1.452 2.956-3.627 2.976-5.855-.053-1.763-.591-3.4-1.663-4.663-1.12-1.215-2.51-1.922-4.044-1.935z"/>
				</svg>
				<?php esc_html_e( 'Groups who can see this article', 'marketking' ); ?>
    		</div>
        	<?php
            	$groups = get_posts([
            	  'post_type' => 'marketking_group',
            	  'post_status' => 'publish',
            	  'numberposts' => -1
            	]);
            	foreach ($groups as $group){
            		$checked = '';
	            		// If current page is not Add New 
	            		if( get_current_screen()->action !== 'add'){
		            		global $post;
		            		$check = intval(get_post_meta($post->ID, 'marketking_group_'.$group->ID, true));
		            		if ($check === 1){
		            			$checked = 'checked="checked"';
		            		}	
		            	}  
            		?>
            		<div class="marketking_group_visibility_container_content_checkbox">
            			<div class="marketking_group_visibility_container_content_checkbox_name">
            				<?php echo esc_html($group->post_title); ?>
            			</div>
            			<input type="hidden" name="marketking_group_<?php echo esc_attr($group->ID);?>" value="0">
            			<input type="checkbox" value="1" class="marketking_group_visibility_container_content_checkbox_input" name="marketking_group_<?php echo esc_attr($group->ID);?>" id="marketking_group_<?php echo esc_attr($group->ID);?>" value="1" <?php echo $checked;?> />
            		</div>
            		<?php
            	}
            ?>
    	</div>
    </div>

    <div class="marketking_group_visibility_container">
    	<div class="marketking_group_visibility_container_top">
    		<?php esc_html_e( 'Vendor Visibility', 'marketking' ); ?>
    	</div>
    	<div class="marketking_group_visibility_container_content">
    		<div class="marketking_group_visibility_container_content_title">
				<svg class="marketking_user_visibility_container_content_title_icon" xmlns="http://www.w3.org/2000/svg" width="31" height="23" fill="none" viewBox="0 0 31 23">
				  <path fill="#C4C4C4" d="M9.333 11.58c3.076 0 5.396-2.32 5.396-5.396C14.73 3.11 12.41.79 9.333.79c-3.075 0-5.396 2.32-5.396 5.395 0 3.076 2.32 5.396 5.396 5.396zm1.542 1.462H7.792c-4.25 0-7.709 3.458-7.709 7.708v1.542h18.5V20.75c0-4.25-3.458-7.708-7.708-7.708zm17.412-7.258l-6.63 6.616-1.991-1.992-2.18 2.18 4.171 4.17 8.806-8.791-2.176-2.183z"/>
				</svg>
				<?php esc_html_e( 'Vendors who can see this article (comma-separated)', 'marketking' ); ?>
    		</div>
    		<textarea name="marketking_category_users_textarea" id="marketking_category_users_textarea"><?php 
	            		// If current page is not Add New 
	            		if( get_current_screen()->action !== 'add'){
		            		global $post;
		            		echo get_post_meta($post->ID, 'marketking_category_users_textarea', true);
		            	}  
            			?></textarea>
        	<div class="marketking_category_users_textarea_buttons_container"><?php 
        		// get all vendors
        		$vendors = marketking()->get_all_vendors();

        		echo '<select id="marketking_all_users_dropdown">';
        		foreach ($vendors as $vendor){
        			$name = marketking()->get_store_name_display($vendor->ID);
        			// get user login
        			echo '<option value="'.esc_attr($vendor->user_login).'">'.esc_html($name).' ('.$vendor->user_login.')</option>';
        		}
        		echo '</select>';
				?>

				<button type="button" class="button" id="marketking_category_add_user"><?php esc_html_e('Add vendor','marketking'); ?></button>
        	</div>

    	</div>
    </div>
    <?php
}


// Save Docs Metabox Content
function marketking_save_docs_metaboxes($post_id){
	if (isset($_POST['_inline_edit'])){
		if (wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')){
		    return;
		}
	}
	if (isset($_REQUEST['bulk_edit'])){
	    return;
	}
	if (get_post_type($post_id) === 'marketking_docs'){

		// Get all groups
		$groups = get_posts([
		  'post_type' => 'marketking_group',
		  'post_status' => 'publish',
		  'numberposts' => -1
		]);

		// For each group option, save user's choice as post meta
		foreach ($groups as $group){
			$meta_input = sanitize_text_field(filter_input(INPUT_POST, 'marketking_group_'.$group->ID));
			if($meta_input !== NULL){
				update_post_meta($post_id, 'marketking_group_'.$group->ID, sanitize_text_field($meta_input));
			}
		}

		// Save user visibility
		$meta_user_visibility = sanitize_text_field(filter_input(INPUT_POST, 'marketking_category_users_textarea'));
		if ($meta_user_visibility !== NULL){
			// get current users list
			$currentuserstextarea = esc_html(get_post_meta($post_id, 'marketking_category_users_textarea', true));
			$currentusersarray = explode(',', $currentuserstextarea);
			// delete all individual user meta
			foreach ($currentusersarray as $user){
				delete_post_meta( $post_id, 'marketking_user_'.trim($user));
			}
			// get new users list
			$newusertextarea = $meta_user_visibility;
			$newusersarray = explode(',', $newusertextarea);
			// set new user meta
			foreach ($newusersarray as $newuser){
				update_post_meta( $post_id, 'marketking_user_'.sanitize_text_field(trim($newuser)), 1);
			}
			// Update users textarea
			update_post_meta($post_id, 'marketking_category_users_textarea', sanitize_text_field($meta_user_visibility));
		}


	    if ( 'publish' !== get_post_status($post_id) ){
	        return;
	    }
	    $post = get_post($post_id);

	    $content = $post->post_content;
	    // get all vendors
	    $agents = get_users(array(
		    'meta_key'     => 'marketking_group',
		    'meta_value'   => 'none',
		    'meta_compare' => '!=',
		    'fields' => 'ids',
		));
		
		foreach ($agents as $agent){
			// check if docs visible, and if so, send it.
			$agent_group = get_user_meta($agent, 'marketking_group', true);
			$group_visible = intval(get_post_meta($post->ID, 'marketking_group_'.$agent_group, true));
			$user_info = get_userdata($agent);

			$login = $user_info->user_login;
			$user_visible = intval(get_post_meta($post->ID, 'marketking_user_'.$login, true));
			
		}

	}
}

	// Add custom columns to docs menu
	function marketking_add_columns_group_menu_docs($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'marketking_visible' => esc_html__( 'Visible to:', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 2, true) + $columns + array_slice($columns_initial, 2, 1, true);

	    return $columns;
	}

	// Add docs custom columns data
	function marketking_columns_group_data_docs( $column, $post_id ) {
	    switch ( $column ) {

	        case 'marketking_visible' :

            	$groups = get_posts([
            	  'post_type' => 'marketking_group',
            	  'post_status' => 'publish',
            	  'numberposts' => -1
            	]);

            	$groups_message = '';
            	foreach ($groups as $group){
            		$check = intval(get_post_meta($post_id, 'marketking_group_'.$group->ID, true));
            		if ($check === 1){
            			$groups_message .= esc_html($group->post_title).', ';
            		}        		
            	}
            	if ( ! empty($groups_message)){
            		echo '<strong>'.esc_html__('Groups: ','marketking').'</strong>'.esc_html(substr($groups_message, 0, -2));
            		echo '<br />';
            	}

            	$users = get_post_meta($post_id, 'marketking_category_users_textarea', true);
            	if (!empty($users)){
            		echo '<strong>'.esc_html__('Users: ','marketking').'</strong>'.esc_html($users);
            	}
	            break;

	    }
	}


	// Add abuse custom columns data
	function marketking_columns_group_data_abuse( $column, $post_id ) {
	    switch ( $column ) {

	        case 'message' :

	        	$message = get_post_meta($post_id,'message', true);
	        	echo esc_html($message);

	            break;

	        case 'product' :

	        	$productid = get_post_meta($post_id,'product', true);
	        	$product = wc_get_product($productid);
	        	if ($product){
	        		$link = $product->get_permalink();
	        		echo esc_html__('Product:','marketking').' '.'<a href="'.esc_url($link).'">'.esc_html($product->get_title()).'</a>';
	        	} else if (!empty(get_comment_meta($productid,'rating', true))){
	        		$review = get_comment($productid);
	        		$product = wc_get_product($review->comment_post_ID);
	        		$link = $product->get_permalink();
	        		echo esc_html__('Review for:','marketking').' '.'<a href="'.esc_url($link).'">'.esc_html($product->get_title()).'</a>';
	        		echo ' -> '.'<a href="'.esc_url(admin_url('comment.php?action=editcomment&c='.$productid)).'">'.esc_html__('View Review','marketking').'</a>';
	        	} else {
	        		esc_html_e('Product no longer exists.','marketking');
	        	}

	            break;

	        case 'vendor' :

	        	$vendorid = get_post_meta($post_id,'vendor', true);
	        	$storelink = marketking()->get_store_link($vendorid);
	        	$storename = marketking()->get_store_name_display($vendorid);
	        	echo '<a href="'.esc_url($storelink).'">'.esc_html($storename).'</a>';
	            break;

	        case 'reportedby' :

	        	$reportedby = get_post_field('post_author', $post_id);
	        	$user_link = get_edit_user_link($reportedby);

	        	$userobj = new WP_User($reportedby);
	        	$userlogin = $userobj->user_login;
	        	echo '<a href="'.esc_url($user_link).'">'.esc_html($userlogin).'</a>';
	            break;

	        case 'datetime':
	        	echo get_the_date('',$post_id);
	        	break;

	    }
	}

	public function display_advertised_products_on_top_vendor($results, $shortcode){

		if (intval(get_option( 'marketking_advertised_products_top_setting', 1 )) === 1){

			$ids = $results->ids;

			$non_advertised = [];
			$advertised    = [];
			// get all advertised products
			$advertised_products = marketking()->get_advertised_product_ids();

			foreach ( $ids as $id ) {
			    if ( in_array( (int) $id, $advertised_products, true ) ) {
			        $advertised[] = $id;
			    } else {
			        $non_advertised[] = $id;
			    }
			}

			shuffle($advertised);

			$results->ids = array_merge( $advertised, $non_advertised );
		}

		return $results;
	}

	public function display_advertised_products_on_top( $posts, $query ) {

	    global $wp_query;

	    if (intval(get_option( 'marketking_advertised_products_top_setting', 1 )) === 1){
		    if ( ! is_admin() &&
		        $query->is_main_query() &&
		        (
		            is_search() ||
		            ( is_a( $wp_query, 'WP_Query' ) && ! empty( $wp_query->get_queried_object() ) && is_shop() ) ||
		            is_product_category() ||
		            ( is_a( $wp_query, 'WP_Query' ) && marketking()->is_vendor_store_page() )
		        )
		    ) {
		        $non_advertised = [];
		        $advertised    = [];
		        // get all advertised products
		        $advertised_products = marketking()->get_advertised_product_ids();

		        foreach ( $posts as $post ) {
		            if ( in_array( (int) $post->ID, $advertised_products, true ) ) {
		                $advertised[] = $post;
		            } else {
		                $non_advertised[] = $post;
		            }
		        }

		        shuffle($advertised);

		        $posts = array_merge( $advertised, $non_advertised );

		    }
		}

	    return $posts;
	}

	function advertised_products_top($order_by, $query){

		if (intval(get_option( 'marketking_advertised_products_top_setting', 1 )) === 1){
			global  $wpdb ;
			if ( ( $query->get('post_type') == 'product' ) && ( !is_admin() ) ){
				$orderby_value = ( isset( $_GET['orderby'] ) ? wc_clean( (string) $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) ) );
				$orderby_value_array = explode( '-', $orderby_value );
				$orderby = esc_attr( $orderby_value_array[0] );
				$order = ( !empty($orderby_value_array[1]) ? $orderby_value_array[1] : 'ASC' );

				$feture_product_id = marketking()->get_advertised_product_ids();
				if ( is_array( $feture_product_id ) && !empty($feture_product_id) ) {

				  if ( empty($order_by) ) {
				    $order_by = "FIELD(" . $wpdb->posts . ".ID,'" . implode( "','", $feture_product_id ) . "') DESC ";
				  } else {
				    $order_by = "FIELD(" . $wpdb->posts . ".ID,'" . implode( "','", $feture_product_id ) . "') DESC, " . $order_by;
				  }
				}  
			}
		}
		
		return $order_by;
	}

	function payment_complete( $order_id, $old_status, $new_status ){

		$order = wc_get_order($order_id);
		$user_id = $order->get_customer_id();
		$modified_already = $order->get_meta('b2bking_modified_already');		

        if( $new_status === "completed" ) {
        	// check how many credit points order contains, if any
        	$credit_points = 0;
        	// Get and Loop Over Order Items
        	foreach ( $order->get_items() as $item_id => $item ) {
        	   $product_id = $item->get_product_id();
        	   if ($product_id === intval(get_option('marketking_credit_product_id_setting', 0))){
        	   		$total = $item->get_quantity();
        	   		$credit_points+= $total;	
        	   }
        	}
        	if ($credit_points > 0){
        		// if this is a completed order with credit inside, set its status to "Credit purchase"
        		$changed = $order->get_meta('marketking_credit_changed_status');

        		if ($changed !== 'yes'){

        			$order->update_meta_data( 'marketking_credit_changed_status', 'yes' );

        			$amount = $credit_points;
        			$user_credits = intval(get_user_meta($user_id, 'marketking_advertising_credits_available', true));
        			$user_credits += intval($amount);
        			update_user_meta($user_id, 'marketking_advertising_credits_available', $user_credits);


        			$note = esc_html__('Purchased via order ','marketking').'#'.$order_id;
        			// get user history
        			$user_credit_history = sanitize_text_field(get_user_meta($user_id,'marketking_user_credit_history', true));
        			// create reimbursed transaction
        			$date = date_i18n( 'Y/m/d', time()+(get_option('gmt_offset')*3600) ); 
        			$operation = 'purchase';
        			$transaction_new = $date.':'.$operation.':'.$amount.':'.$user_credits.':'.$note;

        			// update credit history
        			update_user_meta($user_id,'marketking_user_credit_history',$user_credit_history.';'.$transaction_new);
        		}
        	}
        }

        $order->save();

	} 

	// Add custom columns to announcements menu
	function marketking_add_columns_group_menu_announcement($columns) {

		$columns_initial = $columns;
		
		// rename title
		$columns = array(
			'marketking_visible' => esc_html__( 'Visible to:', 'marketking' ),
		);
		$columns = array_slice($columns_initial, 0, 2, true) + $columns + array_slice($columns_initial, 2, 1, true);

	    return $columns;
	}

	// Add announcements custom columns data
	function marketking_columns_group_data_announcement( $column, $post_id ) {
	    switch ( $column ) {

	        case 'marketking_visible' :

            	$groups = get_posts([
            	  'post_type' => 'marketking_group',
            	  'post_status' => 'publish',
            	  'numberposts' => -1
            	]);

            	$groups_message = '';
            	foreach ($groups as $group){
            		$check = intval(get_post_meta($post_id, 'marketking_group_'.$group->ID, true));
            		if ($check === 1){
            			$groups_message .= esc_html($group->post_title).', ';
            		}        		
            	}
            	if ( ! empty($groups_message)){
            		echo '<strong>'.esc_html__('Groups: ','marketking').'</strong>'.esc_html(substr($groups_message, 0, -2));
            		echo '<br />';
            	}

            	$users = get_post_meta($post_id, 'marketking_category_users_textarea', true);
            	if (!empty($users)){
            		echo '<strong>'.esc_html__('Users: ','marketking').'</strong>'.esc_html($users);
            	}
	            break;

	    }
	}


	// Helps prevent public code from running on login / register pages, where is_admin() returns false
	function marketkingpro_is_login_page() {
		if(isset($GLOBALS['pagenow'])){
	    	return in_array( $GLOBALS['pagenow'],array( 'wp-login.php', 'wp-register.php', 'admin.php' ),  true  );
	    }
	}

	
	function marketkingpro_dismiss_activate_woocommerce_admin_notice(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketkingpro_notice_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		update_user_meta(get_current_user_id(), 'marketkingpro_dismiss_activate_woocommerce_notice', 1);

		echo 'success';
		exit();
	}

	function marketking_subscriptions_table_ajax(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}


		$start = sanitize_text_field($_POST['start']);
		$length = sanitize_text_field($_POST['length']);
		$search = sanitize_text_field($_POST['search']['value']);
		$pagenr = ($start/$length)+1;

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$args = array( 
		    'posts_per_page' => -1,
		    'post_status'    => 'any',
		    'post_type'		=> 'shop_subscription',
		    'author'   => $current_id,
		    'fields' => 'ids',
		    's' => $search,
		);

		$total_subscriptions = get_posts( $args );
		$itemnr = count($total_subscriptions);

		$data = array(
			'length'=> $length,
			'data' => array(),
			'recordsTotal' => $itemnr,
			'recordsFiltered' => $itemnr
		);
		
		$args = array( 
		    'posts_per_page' => $length,
		    'post_status'    => 'any',
		    'post_type'		=> 'shop_subscription',
		    'author'   => $current_id,
		    'paged'   => floatval($pagenr),
		    'fields' => 'ids',
		    's' => $search,
		);

		$vendor_subscriptions = get_posts( $args );


		foreach ($vendor_subscriptions as $subscriptionid){
			$subscription = new WC_Subscription($subscriptionid);

			if ($subscription !== false){
			    ?>	
		    	<?php ob_start(); ?>
		        <td class="nk-tb-col" data-order="<?php
                    echo esc_attr($subscriptionid);
                ?>">

                    <div>
                        <span class="tb-lead">#<?php 

                        // sequential
                        $order_nr_sequential = get_post_meta($subscriptionid,'_order_number', true);
                        if (!empty($order_nr_sequential)){
                            echo $order_nr_sequential;
                        } else {
                            echo esc_html($subscriptionid);
                        }
                        echo ' ';

                        $name = $subscription->get_formatted_billing_full_name();

                        $name = apply_filters('marketking_customers_page_name_display', $name, $subscription);
                        
                        echo esc_html($name);


                    ?></span>
                    </div>
                </td>
                <?php $col1 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md" data-order="<?php
                    $date = $subscription->get_date_created();
                    echo $date->getTimestamp();
                ?>">
                    <div>
                        <span class="tb-sub"><?php 
                        
                        echo $date->date_i18n( get_option('date_format'), $date->getTimestamp()+(get_option('gmt_offset')*3600) );

                        
                        ?></span>
                    </div>
                </td>
                <?php $col2 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md"> 
                    <div >
                        <span class="dot bg-warning d-mb-none"></span>
                        <?php
                        $status = $subscription->get_status();
                        $badge = '';
                        if ($status === 'active'){
                            $badge = 'badge-success';
                        } else if ($status === 'on-hold'){
                            $badge = 'badge-warning';
                        } else if (in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                            $badge = 'badge-info';
                        } else if ($status === 'refunded'){
                            $badge = 'badge-gray';
                        } else if ($status === 'cancelled' or $status === 'pending-cancel' or $status === 'suspended' or $status === 'expired'){
                            $badge = 'badge-gray';
                        } else if ($status === 'pending'){
                            $badge = 'badge-dark';
                        } else if ($status === 'failed'){
                            $badge = 'badge-danger';
                        } else {
                            $badge = 'badge-gray';
                        }

                        ?>
                        <span class="badge badge-sm badge-dot has-bg <?php echo esc_attr($badge);?> d-none d-mb-inline-flex"><?php
                        echo wcs_get_subscription_status_name( $status );

                        ?></span>
                    </div>
                </td>
                <?php $col3 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-sm">
                    <div>
                         <span class="tb-sub"><?php
                         $customer_id = $subscription -> get_customer_id();

                         $name = $subscription -> get_formatted_billing_full_name();

                         $name = apply_filters('marketking_customers_page_name_display', $name, $customer_id);

                         echo esc_html($name);
                         ?></span>
                    </div>
                </td>
                <?php $col4 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md"> 
                    <div>
                        <span class="tb-sub text-primary"><?php
                        $items = $subscription->get_items();
                        $items_count = count( $items );
                        if ($items_count > apply_filters('marketking_dashboard_item_count_limit', 4)){
                            echo esc_html($items_count).' '.esc_html__('Items', 'marketking');
                        } else {
                            // show the items
                            foreach ($items as $item){
                                echo apply_filters('marketking_item_display_dashboard', $item->get_name().' x '.$item->get_quantity().'<br>', $item);
                            }
                        }
                        ?></span>
                    </div>
                </td>
                <?php $col5 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col" data-order="<?php echo esc_attr($subscription->get_total());?>"> 
                    <div>
                        <span class="tb-lead"><?php 

                        echo $subscription->get_formatted_order_total();


                        $meta_content = ' ';
                        $meta_content .= '<small class="meta">(';
                        // translators: placeholder is the display name of a payment gateway a subscription was paid by
                        $meta_content .= esc_html( sprintf( __( 'Via %s', 'marketking' ), $subscription->get_payment_method_to_display() ) );

                        if ( WCS_Staging::is_duplicate_site() && $subscription->has_payment_gateway() && ! $subscription->get_requires_manual_renewal() ) {
                            $meta_content .= WCS_Staging::get_payment_method_tooltip( $subscription );
                        }

                        $meta_content .= ')</small>';

                        echo $meta_content;




                    ?></span>
                    </div>
                </td>
                <?php $col6 = ob_get_clean();?>
                <?php ob_start();?>
               <td class="nk-tb-col"> 
                   <div>
                       <span class="tb-lead"><?php 

                       $orders = $subscription->get_related_orders();

                       $i = 0;
                       foreach ($orders as $order_id){

                           if ($i !== 0){
                               echo ', ';
                           }
                           ?><a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'manage-order/'.$order_id);?>"><?php echo esc_html($order_id); ?></a><?php
                           $i++;
                       }


                   ?></span>
                   </div>
               </td>
                <?php $col7 = ob_get_clean();?>
                <?php ob_start();?>
               <td class="nk-tb-col tb-col-md marketking-column-mid">
                   <ul class="nk-tb-actions gx-1 my-n1">
                       <li class="mr-n1">
                           <?php
                           if (!marketking()->is_vendor_team_member()){
                               ?>
                                   <div class="dropdown">
                                       <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                       <div class="dropdown-menu dropdown-menu-right">
                                           
                                               <ul class="link-list-opt no-bdr">
                                                   <?php

                                                   if ($status === 'on-hold'){
                                                       ?>
                                                       <li><a href="#" class="toggle marketking_reactivate_button_subscription" value="<?php echo esc_attr($subscription->get_id());?>"><em class="icon ni ni-play-circle"></em><span><?php esc_html_e('Reactivate','marketking'); ?></span></a></li>
                                                       <?php
                                                   }

                                                   if ($status === 'active'){
                                                       ?>
                                                       <li><a href="#" class="toggle marketking_pause_button_subscription" value="<?php echo esc_attr($subscription->get_id());?>"><em class="icon ni ni-pause-circle"></em><span><?php esc_html_e('Pause subscription','marketking'); ?></span></a></li>
                                                       <?php
                                                   }


                                                   if (in_array($status, array('active', 'on-hold'))){
                                                       ?>
                                                       <li><a href="#" class="toggle marketking_cancel_button_subscription" value="<?php echo esc_attr($subscription->get_id());?>"><em class="icon ni ni-cross-circle"></em><span><?php esc_html_e('Cancel subscription','marketking'); ?></span></a></li>
                                                       <?php
                                                   }
                                                   ?>
                                                   
                                               </ul>
                                               
                                       </div>
                                   </div>
                               <?php
                           }
                           ?>
                       </li>
                   </ul>
               </td>
		        <?php
		        $col8 = ob_get_clean();

	        	array_push($data['data'],array($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8));

		    }

		}
		
		echo json_encode($data);

		exit();
	}

	function marketking_coupons_table_ajax(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}


		$start = sanitize_text_field($_POST['start']);
		$length = sanitize_text_field($_POST['length']);
		$search = sanitize_text_field($_POST['search']['value']);
		$pagenr = ($start/$length)+1;

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$args = array( 
		    'posts_per_page' => -1,
		    'post_status'    => 'any',
		    'post_type'		=> 'shop_coupon',
		    'author'   => $current_id,
		    'fields' => 'ids',
		    's' => $search,
		);

		$total_coupons = get_posts( $args );
		$itemnr = count($total_coupons);



		$data = array(
			'length'=> $length,
			'data' => array(),
			'recordsTotal' => $itemnr,
			'recordsFiltered' => $itemnr
		);
		
		$args = array( 
		    'posts_per_page' => $length,
		    'post_status'    => 'any',
		    'post_type'		=> 'shop_coupon',
		    'author'   => $current_id,
		    'paged'   => floatval($pagenr),
		    'fields' => 'ids',
		    's' => $search,
		);

		$vendor_coupons = get_posts( $args );



		foreach ($vendor_coupons as $couponid){
			$coupon = new WC_Coupon($couponid);

			if ($coupon !== false){
			    ?>	
		    	<?php ob_start(); ?>
		        <td class="nk-tb-col tb-col-sm marketking-column-mid">
                    <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'edit-coupon/'.$couponid);?>">
                        <span class="tb-coupon">
                        <?php

                        $code = $coupon -> get_code();
                        $type = $coupon->get_discount_type();
                        $amount = $coupon->get_amount();
                        $description = $coupon->get_description();

                        $expiry_date = $coupon->get_date_expires();
                        $usage_count = $coupon->get_usage_count();
                        $usage_limit = $coupon->get_usage_limit();

                        $time = $coupon->get_date_modified();
                        if ($time === null){
                            $time = $coupon->get_date_created();
                        }


                        ?>
                        <span class="title"><?php echo esc_html($code);?></span>
                        </span>
                    </a>

                </td>
                <?php $col1 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col marketking-column-small">
                    <span class="tb-lead"><?php 

                    $type_name = array(
                        'percent'       => esc_html__( 'Percentage discount', 'woocommerce' ),
                        'fixed_cart'    => esc_html__( 'Fixed cart discount', 'woocommerce' ),
                        'fixed_product' => esc_html__( 'Fixed product discount', 'woocommerce' ),
                    );
                    echo esc_html($type_name[$type]);
                    ?></span>
                </td>
                <?php $col2 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col">
                    <span class="tb-sub">
                    <?php
                    echo esc_html($amount);
                    ?>
                    </span>
                </td>
                <?php $col3 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md marketking-column-mid">
                    <span class="tb-sub"><?php echo esc_html($description);?></span>
                </td>
                <?php $col4 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php echo esc_attr($usage_count);?>">
                    <span class="tb-sub"><?php

                    printf(
                        /* translators: 1: count 2: limit */
                        esc_html__( '%1$s / %2$s', 'woocommerce' ),
                        esc_html( $usage_count ),
                        $usage_limit ? esc_html( $usage_limit ) : '&infin;'
                    );

                    ?></span>
                </td>
                <?php $col5 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php echo esc_attr($expiry_date);?>">
                    <span class="tb-sub"><?php 

                    if ( $expiry_date ) {
                        echo esc_html( $expiry_date->date_i18n( 'F j, Y' ) );
                    } else {
                        echo '&ndash;';
                    }

                    ?></span>
                </td>
                <?php $col6 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md marketking-column-mid" data-order="<?php echo esc_attr($expiry_date);?>">
                   <?php 

                    $status = get_post($couponid)->post_status;
                    $statustext = $badge = '';
                    if ($status === 'publish'){
                        $badge = 'badge-success';
                        $statustext = esc_html__('Published','marketking');
                    } else if ($status === 'draft'){
                        $badge = 'badge-gray';
                        $statustext = esc_html__('Draft','marketking');
                    } else if ($status === 'pending'){
                         $badge = 'badge-info';
                         $statustext = esc_html__('Pending','marketking');
                    } else {
                        $badge = 'badge-gray';
                        $statustext = ucfirst($status);
                    }
                    ?>
                    <span class="badge badge-sm badge-dot has-bg <?php echo esc_attr($badge);?> d-none d-mb-inline-flex"><?php
                    echo esc_html(ucfirst($statustext));
                    ?></span>
                </td>
                <?php $col7 = ob_get_clean();?>
                <?php ob_start();?>
                <td class="nk-tb-col tb-col-md">
                    <ul class="nk-tb-actions gx-1 my-n1">
                        <li class="mr-n1">
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <ul class="link-list-opt no-bdr">
                                        <li><a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'edit-coupon/'.$coupon->get_id());?>"><em class="icon ni ni-edit"></em><span><?php esc_html_e('Edit coupon','marketking'); ?></span></a></li>
                                        <li><a href="#" class="toggle marketking_delete_button_coupon" value="<?php echo esc_attr($coupon->get_id());?>"><em class="icon ni ni-trash"></em><span><?php esc_html_e('Delete coupon','marketking'); ?></span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                </td>
		        <?php
		        $col8 = ob_get_clean();

	        	array_push($data['data'],array($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8));

		    }

		}
		
		echo json_encode($data);

		exit();
	}

	function marketking_refunds_table_ajax(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$start = sanitize_text_field($_POST['start']);
		$length = sanitize_text_field($_POST['length']);
		$search = sanitize_text_field($_POST['search']['value']);
		$pagenr = ($start/$length)+1;


		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}		        


		$args = array ('post_type' => 'marketking_refund', 'numberposts' => -1, 'meta_query'=> array(
				        	'relation' => 'OR',
		                    array(
		                        'key' => 'vendor_id',
		                        'value' => $current_id, 
		                        'compare' => '='
		                    ),
		                ), 'search' => $search);

		$total_refunds = get_posts( $args );
		$itemnr = count($total_refunds);

		$args = array ('numberposts'=>$length, 'post_type' => 'marketking_refund', 'meta_query'=> array(
				        	'relation' => 'OR',
		                    array(
		                        'key' => 'vendor_id',
		                        'value' => $current_id, 
		                        'compare' => '='
		                    ),
		                ), 'paged'   => floatval($pagenr), 'search' => $search);

		$comments = get_posts( $args );

		$data = array(
			'length'=> $length,
			'data' => array(),
			'recordsTotal' => $itemnr,
			'recordsFiltered' => $itemnr
		);
		


		foreach ($comments as $review){
		    // get product
		    $request = $review->ID;
		    $order_id = get_post_meta($request,'order_id', true);
            $order = wc_get_order($order_id);
            $value = get_post_meta($request,'value', true);
            $status = get_post_meta($request,'request_status', true);
            $reason = get_post_meta($request,'reason', true);
            $author_id = get_post_field ('post_author', $request);
            $user = new WP_User($author_id);
            $user = $user->user_login;

		    ob_start();?>
		        <td class="nk-tb-col tb-col-md" data-order="<?php
		            $date = get_the_date('',$request);
		            ?>">
		            <div>
		                <span class="tb-sub"><?php 
		                echo esc_html($date);
		                ?></span>
		            </div>
		        </td>
		        <?php $col1 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col tb-col-sm marketking-column-mid">
                    <?php echo esc_html__('Order','marketking').' '; ?><a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'manage-order/'.$order_id); ?>"><?php 

                    	echo '#'; 
                    	// sequential
                    	$order_nr_sequential = $order->get_meta('_order_number');
                    	if (!empty($order_nr_sequential)){
                    	    echo $order_nr_sequential;
                    	} else {
                    	    echo esc_html($order_id);
                    	}

                    	?></a>

                </td>
		        <?php $col2 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col marketking-column-small">
                    <span class="tb-lead"><?php 
                        echo substr($reason,0, 150);
                        if (substr($reason,0, 150) !== $reason){
                            echo '...';
                        }
                        ?></span>
                </td>
		        <?php $col3 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col">
                    <span class="tb-sub">
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
                    </span>
                </td>
		        <?php $col4 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col">
                    <span class="tb-sub">
                    <?php
                       if ($value === 'full'){
                        esc_html_e('Full Refund','marketking');
                       } else if ($value === 'partial'){
                        esc_html_e('Partial: ','marketking');
                        $partialamount = get_post_meta($request, 'partialamount', true);
                        echo wc_price($partialamount);
                        if ($order){
                        	echo ' / '.wc_price($order->get_total());
                        }
                       }
                        ?>
                    </span>
                </td>
		        <?php $col5 = ob_get_clean(); ?>
		        <?php ob_start();?>
		        <td class="nk-tb-col">
		            <span class="tb-sub">
		            <?php
		                echo esc_html($user);
		                ?>
		            </span>
		        </td>
		        <?php $col6 = ob_get_clean(); ?>
		        <?php ob_start();?>
		        <td class="nk-tb-col tb-col-md">
		           <div class="btn-group">

		               <a href="#b2bking_marketking_conversation_container" rel="modalzz:open"><button type="button" class="btn btn-sm btn-outline-primary marketking_view_refund_button b2bking_conversation_table" type="button" value="<?php echo esc_attr($request);?>"><em class="icon ni ni-eye-fill"></em><span><?php esc_html_e('View','marketking');?></span></button></a>

		                <span class="refunds_hidden_id"><?php echo esc_html($request);?></span>
		           </div>
		           
		        </td>
		        <?php $col7 = ob_get_clean(); ?>

		        <?php
		        array_push($data['data'],array($col1, $col2, $col3, $col4, $col5, $col6, $col7));
				?>

		        
		    <?php
		    
		}

		
		echo json_encode($data);

		exit();
	}

	function marketking_reviews_table_ajax(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$start = sanitize_text_field($_POST['start']);
		$length = sanitize_text_field($_POST['length']);
		$search = sanitize_text_field($_POST['search']['value']);
		$pagenr = ($start/$length)+1;

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}


		$args = array ('post_type' => 'product', 'post_author' => $current_id, 'search' => $search);
		$total_reviews = get_comments( $args );
		$itemnr = count($total_reviews);

		foreach ($total_reviews as $rev){
			$rating = get_comment_meta($rev->comment_ID,'rating', true); 

			if (empty($rating)){
				$itemnr--;
			}
		}

		$args = array ('number'=>$length, 'post_type' => 'product', 'post_author' => $current_id, 'paged'   => floatval($pagenr), 'search' => $search);
		$comments = get_comments( $args );

		$data = array(
			'length'=> $length,
			'data' => array(),
			'recordsTotal' => $itemnr,
			'recordsFiltered' => $itemnr
		);
		

		foreach ($comments as $review){
		    // get product
		    $productid = $review -> comment_post_ID;
		    $product = wc_get_product($productid);
		    $product_name = $product->get_title();
		    $product_link = $product->get_permalink();

		    $product_title = '<a href="'.esc_attr($product_link).'">'.esc_html($product_name).'</a>';

		    $comment = $review -> comment_content;
		    $review_id = $review->comment_ID;
		    $rating = get_comment_meta($review_id,'rating', true); 

		    $review_author = $review->comment_author;

		    if (!empty($rating)){
		    ?> <?php ob_start();?>
		        <td class="nk-tb-col tb-col-sm marketking-column-mid">
		            <a href="<?php echo esc_attr($product_link);?>">
		                <span class="tb-coupon">
		                <span class="title"><?php echo esc_html($product_name);?></span>
		                </span>
		            </a>

		        </td>
		        <?php $col1 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col marketking-column-small">
		            <span class="tb-lead"><?php 

		            echo esc_html($rating);
		            ?></span>
		        </td>
		        <?php $col2 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col">
		            <span class="tb-sub">
		            <?php
		            echo esc_html($comment);
		            ?>
		            </span>
		        </td>
		        <?php $col3 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col tb-col-md marketking-column-mid">
		            <span class="tb-sub"><?php echo esc_html($review_author );?></span>
		        </td>
		        <?php $col4 = ob_get_clean();?>
		        <?php ob_start();?>
		        <td class="nk-tb-col tb-col-md">
		            <div class="btn-group">
		                <button class="btn btn-sm btn-outline-primary marketking_view_review_button" value="<?php echo esc_attr($product_link);?>"><em class="icon ni ni-eye-fill"></em><span><?php esc_html_e('View','marketking');?></span></button>
		               
		              <?php
		              $has_reply = get_comment_meta($review_id,'has_reply', true);
		              if ($has_reply !== 'yes'){
		                ?>
		                <button class="btn btn-sm btn-outline-primary marketking_reply_review_button" value="<?php echo esc_attr($review_id);?>"><em class="icon ni ni-pen-fill"></em><span><?php esc_html_e('Reply','marketking');?></span></button>

		                <?php
		              }             
		              ?>                              
		              <?php
		                if (intval(get_option( 'marketking_enable_abusereports_setting', 1 )) === 1){
		                    $has_report = get_comment_meta($review_id,'has_report', true);
		                    if ($has_report !== 'yes'){
		                        ?>
		                        <button class="btn btn-sm btn-outline-primary marketking_report_review_button" value="<?php echo esc_attr($review_id);?>"><em class="icon ni ni-flag-fill"></em><span><?php esc_html_e('Report','marketking');?></span></button>
		                        <?php
		                    }
		                }
		                ?>
		            </div>
		        </td>
		        <?php $col5 = ob_get_clean();

		        array_push($data['data'],array($col1, $col2, $col3, $col4, $col5));
				?>

		        
		    <?php
		    }
		}

		
		echo json_encode($data);

		exit();
	}


	function marketking_earnings_table_ajax(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}


		$start = sanitize_text_field($_POST['start']);
		$length = sanitize_text_field($_POST['length']);
		$search = sanitize_text_field($_POST['search']['value']);
		$pagenr = ($start/$length)+1;

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}


		$args = array( 
		    'posts_per_page' => -1,
		    'post_status'    => 'any',
		    'post_type'		=> 'marketking_earning',
		    'fields' => 'ids',
		    's' => $search,
		    'meta_key'   => 'vendor_id',
		    'meta_value' => $current_id,
		);

		$total_items = get_posts( $args );
		$itemnr = count($total_items);
		
		$data = array(
			'length'=> $length,
			'data' => array(),
			'recordsTotal' => $itemnr,
			'recordsFiltered' => $itemnr
		);


		
		$args = array( 
		    'posts_per_page' => $length,
		    'post_status'    => 'any',
		    'post_type'		=> 'marketking_earning',
		    'paged'   => floatval($pagenr),
		    'fields' => 'ids',
		    's' => $search,
		    'meta_key'   => 'vendor_id',
		    'meta_value' => $current_id,
		);

		$earnings = get_posts( $args );
		foreach ($earnings as $earning_id){
		    $order_id = get_post_meta($earning_id,'order_id', true);
		    $orderobj = wc_get_order($order_id);
		    if ($orderobj !== false){
		        $earnings_total = get_post_meta($earning_id,'marketking_commission_total', true);
		        if (!empty($earnings_total) && floatval($earnings_total) !== 0){
		            ?>
	            	<?php ob_start(); ?>
	                <td class="nk-tb-col">

	                    <div>
	                        <span class="tb-lead">#<?php 

	                        // sequential
	                        $order_nr_sequential = $orderobj->get_meta('_order_number');
	                        if (!empty($order_nr_sequential)){
	                            echo $order_nr_sequential;
	                        } else {
	                            echo esc_html($order_id);
	                        }

	                        // subscription renewal
	                        $renewal = $orderobj->get_meta('_subscription_renewal');

	                        if (!empty($renewal)){
	                            echo ' ('.esc_html__('susbcription renewal', 'marketking').')';
	                        }

	                    	?></span>
	                    </div>

	                </td>
	                <?php $col1 = ob_get_clean(); ?>
	                <?php ob_start(); ?>
	                <td class="nk-tb-col tb-col-md" data-order="<?php 
	                    $date = explode('T',$orderobj->get_date_created())[0];
	                    echo strtotime($date);
	                ?>">
	                    <div>
	                        <span class="tb-sub"><?php 
	                        echo ucfirst(strftime("%B %e, %G", strtotime($date)));
	                        ?></span>
	                    </div>
	                </td>
	                <?php $col2 = ob_get_clean(); ?>
	                <?php ob_start(); ?>
	                <td class="nk-tb-col"> 
	                    <div>
	                        <span class="dot bg-warning d-mb-none"></span>
	                        <?php
	                        $status = $orderobj->get_status();
	                        $statustext = $badge = '';
	                        if ($status === 'processing'){
	                            $badge = 'badge-warning';
	                            $statustext = esc_html__('Pending Order Completion','marketking');
	                        } else if ($status === 'on-hold'){
	                            $badge = 'badge-warning';
	                            $statustext = esc_html__('Pending Order Completion','marketking');
	                        } else if (in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
	                            $badge = 'badge-success';
	                            $statustext = esc_html__('Completed','marketking');
	                        } else if ($status === 'refunded'){
	                            $badge = 'badge-danger';
	                            $statustext = esc_html__('Order Refunded','marketking');
	                        } else if ($status === 'cancelled'){
	                            $badge = 'badge-danger';
	                            $statustext = esc_html__('Order Cancelled','marketking');
	                        } else if ($status === 'pending'){
	                            $badge = 'badge-warning';
	                            $statustext = esc_html__('Pending Order Payment','marketking');
	                        } else if ($status === 'failed'){
	                            $badge = 'badge-danger';
	                            $statustext = esc_html__('Order Failed','marketking');
	                        }

	                        ?>
	                        <span class="badge badge-sm badge-dot has-bg <?php echo esc_attr($badge);?> d-none d-mb-inline-flex"><?php
	                        echo esc_html($statustext);
	                        ?></span>
	                    </div>
	                </td>
	                <?php $col3 = ob_get_clean(); ?>
	                <?php ob_start(); ?>
	                <td class="nk-tb-col tb-col-sm">
	                    <div>
	                         <span class="tb-sub"><?php
	                         $customer_id = $orderobj -> get_customer_id();
	                         $name = $orderobj -> get_formatted_billing_full_name();
	                         
	                         $name = apply_filters('marketking_customers_page_name_display', $name, $customer_id);
	                         echo esc_html($name);
	                         ?></span>
	                    </div>
	                </td>
	                <?php $col4 = ob_get_clean(); ?>
	                <?php ob_start(); ?>
	                <td class="nk-tb-col tb-col-md"> 
	                    <div>
	                        <span class="tb-sub text-primary"><?php
	                        $items = $orderobj->get_items();
	                        $items_count = count( $items );
	                        if ($items_count > apply_filters('marketking_dashboard_item_count_limit', 4)){
	                            echo esc_html($items_count).' '.esc_html__('Items', 'marketking');
	                        } else {
	                            // show the items
	                            foreach ($items as $item){
	                                echo apply_filters('marketking_item_display_dashboard', $item->get_name().' x '.$item->get_quantity().'<br>', $item);
	                            }
	                        }
	                        ?></span>
	                    </div>
	                </td>
	                <?php $col5 = ob_get_clean(); ?>
	                <?php ob_start(); ?>
	                <td class="nk-tb-col tb-col-sm" data-order="<?php echo esc_attr(apply_filters('marketking_earnings_order_total', $orderobj->get_total(), $orderobj));?>"> 
	                    <div>
	                        <span class="tb-lead"><?php echo wc_price(apply_filters('marketking_earnings_order_total', $orderobj->get_total(), $orderobj));?></span>
	                    </div>
	                </td>
	                <?php $col6 = ob_get_clean(); ?>
	                <?php ob_start(); ?>
	                <td class="nk-tb-col" data-order="<?php echo esc_attr($earnings_total);?>"> 
	                    <div>
	                        <?php
                            if (in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                                $text_color = 'text-success';
                            } else {
                                $text_color = 'text-soft';
                            }

                            // paid via stripe
                            $paidstripe = ($orderobj->get_meta('marketking_paid_via_stripe') === 'yes');

                            ?>
                            <span class="tb-lead <?php echo esc_attr($text_color);?>"><?php 
                            
                            echo wc_price($earnings_total);

                            if (!$paidstripe){
                               if (!in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                                   esc_html_e(' (pending)', 'marketking');
                               } 
                            }

                            if ($paidstripe){
                                ?>
                                <span class="text-info fs-13px"><?php esc_html_e('(Stripe)','marketking');?></span>
                                <?php
                            }
                            
                            ?></span>
	                    </div>
	                </td>
	                <?php $col7 = ob_get_clean(); ?>
	                <?php ob_start(); ?>
	                    <td class="nk-tb-col">
	                        <div class="marketking_manage_order_container"> 
	                            <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'manage-order/'.$order_id);?>"><button class="btn btn-sm btn-primary marketking_manage_order" value="<?php echo esc_attr($order_id);?>"><em class="icon ni ni-bag-fill"></em><span><?php esc_html_e('View Order','marketking');?></span></button></a>
	                        </div>
	                    </td>
	                <?php $col8 = ob_get_clean(); ?>
	                <?php

	                array_push($data['data'],array($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8));
		        }
		    }

		    if ($order_id === 'manual'){
		    			        $earnings_total = get_post_meta($earning_id,'marketking_commission_total', true);
		    			        if (!empty($earnings_total) && floatval($earnings_total) !== 0){
		    			            ?>
		    		            	<?php ob_start(); ?>
		    		                <td class="nk-tb-col">

		    		                    <div>
		    		                        <span class="tb-lead">#<?php 

		    		                        esc_html_e('Manual Adjustment','marketking');

		    		                    	?></span>
		    		                    </div>

		    		                </td>
		    		                <?php $col1 = ob_get_clean(); ?>
		    		                <?php ob_start(); ?>
		    		                <td class="nk-tb-col tb-col-md" data-order="<?php 
		    		                    $date = get_post_meta($earning_id,'time', true);
                                        echo $date;
		    		                ?>">
		    		                    <div>
		    		                        <span class="tb-sub"><?php 
		    		                        echo date_i18n( get_option('date_format'), $date+(get_option('gmt_offset')*3600) );
		    		                        ?></span>
		    		                    </div>
		    		                </td>
		    		                <?php $col2 = ob_get_clean(); ?>
		    		                <?php ob_start(); ?>
		    		                <td class="nk-tb-col"> 
		    		                    <div>
		    		                        <span class="dot bg-warning d-mb-none"></span>
		    		                        <?php
		    		                        $note = get_post_meta($earning_id,'note', true);
		    		                        if (empty($note)){
		    		                            echo '-';
		    		                        } else {
		    		                            echo $note;
		    		                        }
		    		                        ?>
		    		                    </div>
		    		                </td>
		    		                <?php $col3 = ob_get_clean(); ?>
		    		                <?php ob_start(); ?>
		    		                <td class="nk-tb-col tb-col-sm">
		    		                    <div>
		    		                         <span class="tb-sub"><?php
		    		                         echo '-';

		    		                         ?></span>
		    		                    </div>
		    		                </td>
		    		                <?php $col4 = ob_get_clean(); ?>
		    		                <?php ob_start(); ?>
		    		                <td class="nk-tb-col tb-col-md"> 
		    		                    <div>
		    		                        <span class="tb-sub text-primary"><?php
		    		                        echo '-';

		    		                        ?></span>
		    		                    </div>
		    		                </td>
		    		                <?php $col5 = ob_get_clean(); ?>
		    		                <?php ob_start(); ?>
		    		                <td class="nk-tb-col tb-col-sm" > 
		    		                    <div>
		    		                        <span class="tb-lead"><?php echo '-';?></span>
		    		                    </div>
		    		                </td>
		    		                <?php $col6 = ob_get_clean(); ?>
		    		                <?php ob_start(); ?>
		    		                <td class="nk-tb-col" data-order="<?php echo esc_attr($earnings_total);?>"> 
		    		                    <div>
		    		                        <?php
		    	                            echo wc_price($earnings_total);

		    	                            ?>
		    		                    </div>
		    		                </td>
		    		                <?php $col7 = ob_get_clean(); ?>
		    		                <?php ob_start(); ?>
		    		                    <td class="nk-tb-col">
		    		                        <div class="marketking_manage_order_container"> 
		    		                        	-
		    		                        </div>
		    		                    </td>
		    		                <?php $col8 = ob_get_clean(); ?>
		    		                <?php

		    		                array_push($data['data'],array($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8));
		    			        }
		    }
		}
		
		echo json_encode($data);

		exit();
	}

	function marketkingmarkallread(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;
		
		$announcements_ids = sanitize_text_field($_POST['announcementsid']);

		$announcements_ids = explode(':', $announcements_ids);


		foreach ($announcements_ids as $announcement_id){
			update_user_meta($user_id, 'marketking_announce_read_'.$announcement_id, 'read');

		}
		

		echo 'success';
		exit();
	}

	function marketkingmarkread(){

		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$announcement_id = sanitize_text_field($_POST['announcementid']);

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}
		$user_id = $current_id;
	
		update_user_meta($user_id, 'marketking_announce_read_'.$announcement_id, 'read');

		echo 'success';
		exit();

	}

	function marketkingmarkreadmessage(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$messageid = sanitize_text_field($_POST['messageid']);

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;
		
		update_user_meta($user_id, 'marketking_message_last_read_'.$messageid, time());

		echo 'success';
		exit();	
	}

	function marketkingmarkclosedmessage(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$messageid = sanitize_text_field($_POST['messageid']);

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		$is_closed = 'no';
		// get currently selected message
		if (!empty($messageid)){
		    $nr_messages = get_post_meta ($messageid, 'marketking_message_messages_number', true);
		    $last_message = get_post_meta ($messageid, 'marketking_message_message_'.$nr_messages, true);

		    // check if message is closed
		    $last_closed_time = get_user_meta($user_id,'marketking_message_last_closed_'.$messageid, true);
		    if (!empty($last_closed_time)){
		        $last_message_time = get_post_meta ($messageid, 'marketking_message_message_'.$nr_messages.'_time', true);
		        if (floatval($last_closed_time) > floatval($last_message_time)){
		             $is_closed = 'yes';
		        }
		    }
		}

		if ($is_closed === 'yes'){
			update_user_meta($user_id, 'marketking_message_last_closed_'.$messageid, 1);	
		} else {
			update_user_meta($user_id, 'marketking_message_last_closed_'.$messageid, time());
		}

		update_user_meta($user_id, 'marketking_message_last_read_'.$messageid, time());
		
		

		echo 'success';
		exit();	
	}

	function marketkingsendverification(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$vitem = sanitize_text_field($_POST['vitem']);
		$fileurl = sanitize_text_field($_POST['fileurl']);

		// move the media file into the ADMIN's author, so it cannot be deleted by the vendor
		$media_id = attachment_url_to_postid($fileurl);
		$my_post = array(
		    'ID'           => $media_id,
		    'post_author'   => 1,
		);
		wp_update_post( $my_post );
	

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$vendor_id = $current_id;

		$verification = array(
			'post_title'  => sanitize_text_field( esc_html__( 'Verification Request', 'marketking' ) ),
			'post_status' => 'publish',
			'post_type'   => 'marketking_vreq',
			'post_author' => $vendor_id,
		);
		$verification_id = wp_insert_post( $verification );

		update_post_meta($verification_id,'fileurl', $fileurl);
		update_post_meta($verification_id,'vitem', $vitem);
		update_post_meta($verification_id,'status', 'pending');

		echo 'success';
		exit();
	}

	function marketkingreplymessage(){

		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$message_content = apply_filters('marketking_filter_message_general', sanitize_textarea_field($_POST['messagecontent']));
		$conversationid = $message_id = sanitize_text_field($_POST['messageid']);

		$current_id = get_current_user_id();
		$currentuser = wp_get_current_user();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
			$currentuser = new WP_User($current_id);
		}

		$currentuser = $currentuser->user_login;
		$conversationuser = get_post_meta ($conversationid, 'marketking_message_user', true);

		// Check message not empty
		if ($message_content !== NULL && trim($message_content) !== ''){

			$nr_messages = intval(get_post_meta ($conversationid, 'marketking_message_messages_number', true));
			$current_message_nr = $nr_messages+1;
			update_post_meta( $conversationid, 'marketking_message_message_'.$current_message_nr, $message_content);
			update_post_meta( $conversationid, 'marketking_message_messages_number', $current_message_nr);
			update_post_meta( $conversationid, 'marketking_message_message_'.$current_message_nr.'_author', $currentuser );
			update_post_meta( $conversationid, 'marketking_message_message_'.$current_message_nr.'_time', time() );
			// send email notification
			$recipient = get_option( 'admin_email' );

			$conversationparty = marketkingpro()->get_conversation_party($conversationid, 'nonvendor');
			if ($conversationparty !== 'shop'){
				// it's user therefore, get the user email
				$convuser = new WP_User($conversationparty);
				$recipient = $convuser->user_email;
			}

			// if conversation participant is a user, not admin
			$recipient = apply_filters('marketking_recipient_new_message', $recipient, $conversationid);
			do_action( 'marketking_new_message', $recipient, $message_content, $current_id, $conversationid );
		}
		
		echo 'success';
		exit();

	}

	function marketking_approve_refund(){
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$refundid = sanitize_text_field($_POST['refundid']);
		// check the refund belongs to this vendor
		$vendor = get_post_meta($refundid, 'vendor_id', true);
		$orderid = get_post_meta($refundid, 'order_id', true);

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		if (intval($vendor) === $current_id){
			// proceed to approve it
			update_post_meta($refundid,'request_status', 'approved');
		}

		$paidviastripe = get_post_meta($orderid,'marketking_paid_via_stripe', true);

		// process stripe refund automatically
		if ($paidviastripe === 'yes'){

			// IF not 3d secure (for 3d secure, the admin has to process it) 
			// neither this order or its parent has the 3d secure meta
			$metad = get_post_meta($orderid,'_marketking_stripe_split_pay_source_id', true);
			$metadparent = get_post_meta(marketking()->get_parent_order($orderid),'_marketking_stripe_split_pay_source_id', true);
			if (empty($metad) && empty($metadparent)){
				$is_3d_secure = false;
			} else {
				$is_3d_secure = true;
			}

			if (!$is_3d_secure){
				if ( ! class_exists( 'Marketking_Stripe_Gateway' ) ) {
					include_once('stripe/class-marketking-stripe-connect-gateway.php');
				}
				$gateway = new Marketking_Stripe_Gateway();

				do_action( 'marketking_process_refund', $refundid, $orderid, $vendor);

			} else {
				if ( ! class_exists( 'Marketking_Stripe_Gateway' ) ) {
					include_once('stripe/class-marketking-stripe-connect-gateway.php');
				}
				$gateway = new Marketking_Stripe_Gateway();


				// get refund data
				$value = get_post_meta($refundid,'value', true);
				$order = wc_get_order($orderid);

				if ($value === 'full'){
					$refunded_amount = floatval($order->get_total());
					$is_partially_refunded = false;
				} else if ($value === 'partial'){
					$refunded_amount = floatval(get_post_meta($refundid, 'partialamount', true));
					$is_partially_refunded = true;
				}

				$gateway->process_refund($orderid, $refunded_amount, 'requested_by_customer');
				// set refund status to completed
				update_post_meta($refundid,'completion_status','completed');

				// Set order status to refunded. 
				if ($value === 'full'){
				   $order->update_status( 'wc-refunded' );
				}

				if ($value === 'partial'){
				  $refund = wc_create_refund( array(
				    'amount'         => round($refunded_amount,2),
				    'reason'         => '',
				    'order_id'       => $orderid,
				    'refund_payment' => false,
				  ));

				}
			}

		}
		

		echo 'success';
		exit();
	}

	function marketking_reject_refund(){
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$refundid = sanitize_text_field($_POST['refundid']);
		// check the refund belongs to this vendor
		$vendor = get_post_meta($refundid,'vendor_id', true);

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		if (intval($vendor) === $current_id){
			// proceed to reject it
			update_post_meta($refundid,'request_status', 'rejected');

		}


		echo 'success';
		exit();
	}

	function marketkingreplyreview(){

		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$message = sanitize_textarea_field($_POST['messagecontent']);
		$reviewid = sanitize_text_field($_POST['reviewid']);

		$user_id = get_current_user_id();
		$user = wp_get_current_user();

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
			$user_id = $current_id;
			$user = new WP_User($current_id);
		}

		$parent_review = get_comment($reviewid);
		$product_id = $parent_review->comment_post_ID;
		// leave comment on the product, and set comment_parent

		$agent = $_SERVER['HTTP_USER_AGENT'];
		$data = array(
			'comment_post_ID' => $product_id,
		    'comment_parent' => $reviewid,
		    'comment_author' => $user->user_login,
		    'comment_author_email' => $user->user_email,
		    'comment_content' => $message,
		    'comment_agent' => $agent,
		    'comment_type'  => '',
		    'comment_date' => date('Y-m-d H:i:s'),
		    'comment_date_gmt' => date('Y-m-d H:i:s'),
		    'comment_approved' => 1,
		);

		$comment_id = wp_insert_comment($data);

		update_comment_meta($reviewid,'has_reply','yes');

		echo 'success';
		exit();
	}

	function marketkingreportreview(){
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$message = sanitize_textarea_field($_POST['messagecontent']);
		$reviewid = sanitize_text_field($_POST['reviewid']);

		$current_id = get_current_user_id();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
		}

		$user_id = $current_id;

		// submit abuse report
		$report = array(
			'post_title'  => sanitize_text_field( esc_html__( 'Review Report', 'marketking' ) ),
			'post_status' => 'publish',
			'post_type'   => 'marketking_abuse',
			'post_author' => $current_id,
		);
		$report_id = wp_insert_post( $report );

		update_post_meta($report_id,'message', $message);
		update_post_meta($report_id,'product', $reviewid);
		update_post_meta($report_id,'vendor', $current_id);


		update_comment_meta($reviewid,'has_report','yes');


		echo 'success';
		exit();
	}

	function marketkingcomposemessage(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'marketking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$message_content = apply_filters('marketking_filter_message_general',sanitize_textarea_field($_POST['messagecontent']));
		$recipient = sanitize_text_field($_POST['recipient']);
		$title = sanitize_text_field($_POST['title']);

		$current_id = get_current_user_id();
		$currentuser = wp_get_current_user();
		if (marketking()->is_vendor_team_member()){
			$current_id = marketking()->get_team_member_parent();
			$currentuser = new WP_User($current_id);
		}

		$currentuser = $currentuser->user_login;
		$conversationuser = get_post_meta ($conversationid, 'marketking_message_user', true);

		// Check message not empty
		if ($message_content !== NULL && trim($message_content) !== ''){

			// Insert post
			$args = array(
				'post_title' => $title, 
				'post_type' => 'marketking_message',
				'post_status' => 'publish', 
				'post_author' => $current_id
			);
			$conversationid = wp_insert_post( $args);


			update_post_meta( $conversationid, 'marketking_message_user', $currentuser);
			update_post_meta( $conversationid, 'marketking_message_message_1', $message_content);
			update_post_meta( $conversationid, 'marketking_message_messages_number', 1);
			update_post_meta( $conversationid, 'marketking_message_message_1_author', $currentuser );
			update_post_meta( $conversationid, 'marketking_message_message_1_time', time() );
			update_post_meta( $conversationid, 'marketking_message_user', $recipient );

			
			$recipient = get_option( 'admin_email' );
			$recipient = apply_filters('marketking_recipient_new_message', $recipient, $conversationid);

			// send email notification
			do_action( 'marketking_new_message', $recipient, $message_content, $current_id, $conversationid );
			

		}

		
		// return conversation id URL
		echo trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true))).'messages?id='.esc_attr($conversationid);
		exit();

	}

	function b2bking_email_offer_marketking(){
		// Check security nonce.
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
			wp_send_json_error( 'Invalid security token sent.' );
			wp_die();
		}

		// get all recipients of the offer
		$offer_id = sanitize_text_field($_POST['offerid']);
		$offer_link = sanitize_text_field($_POST['offerlink']);
		$emails_send_to = array();
		$emails_send_to_guest = array();
		// for each group, check if visible
		$groups = get_posts( array( 'post_type' => 'b2bking_group','post_status'=>'publish','numberposts' => -1) );
		foreach ($groups as $group){
			$visible = get_post_meta($offer_id, 'b2bking_group_'.$group->ID, true);
			if (intval($visible) === 1){
				// get all users with this group and add them to array
				$users = get_users(array(
				    'meta_key'     => 'b2bking_customergroup',
				    'meta_value'   => $group->ID,
				    'fields' => array('user_email'),
				));
				foreach ($users as $email){
					array_push($emails_send_to, $email->user_email);
				}
				
			}
		}

		// get users
		$userstextarea = get_post_meta($offer_id, 'b2bking_category_users_textarea', true);
		$userarray = explode(',', $userstextarea);
		foreach ($userarray as $user){
			$user = trim($user);
			if (!empty($user)){
				// if email, add directly
				if (strpos($user, '@') !== false) {
					array_push($emails_send_to_guest, $user);
				} else {
					if (username_exists($user)){
						// get email
						$usertemp = get_user_by('login', $user);
						array_push($emails_send_to, $usertemp->user_email);
					}
				}
			}
		}

		foreach ($emails_send_to as $emailad){
			do_action( 'b2bking_new_offer', $emailad, '1', $offer_id, $offer_link );
		}
		foreach ($emails_send_to_guest as $emailad){
			do_action( 'b2bking_new_offer', $emailad, '0', $offer_id, $offer_link );
		}

		echo 'success';
		exit();
	}


	function b2bking_save_new_ajax_offer(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		// get if this is new or edit
		$newedit = sanitize_text_field($_POST['newedit']);
		// get offer details
		$offer_title = sanitize_text_field($_POST['offertitle']);
		$userid = sanitize_text_field($_POST['userid']);
		$uservisibility = sanitize_text_field($_POST['uservisibility']);
		$groupvisibility = sanitize_text_field($_POST['groupvisibility']);
		$customtext = sanitize_textarea_field($_POST['customtext']);
		$offerdetails = sanitize_text_field($_POST['offerdetails']);

		// create new offer if fields not empty
		if (!empty($offerdetails) && $offerdetails!==NULL){

			if ($newedit === 'new'){
				$offer = array(
				    'post_title' => $offer_title,
				    'post_status' => 'publish',
				    'post_type' => 'b2bking_offer',
				    'post_author' => $userid,
				);
				$offer_id = wp_insert_post($offer);
			} else {
				$offer_id = intval($newedit);
				// update title
				$my_post = array(
				    'ID'           => $newedit,
				    'post_title'   => $offer_title,
				);
				// Update the post into the database
				wp_update_post( $my_post );
			}

			update_post_meta($offer_id,'b2bking_post_status_enabled', 1);


			// Save offer details
			if ($offerdetails !== NULL && !empty($offerdetails)){
				update_post_meta( $offer_id, 'b2bking_offer_details', $offerdetails);
			}

			// Save group visibility 
			$group_visibility_items = explode(',', $groupvisibility);

			// First set all groups to invisible
			$groups = get_posts([
			  'post_type' => 'b2bking_group',
			  'post_status' => 'publish',
			  'numberposts' => -1
			]);
			foreach ($groups as $group){
				update_post_meta($offer_id, 'b2bking_group_'.$group->ID, 0);
			}
			// Next set visible groups
			$group_visibility_items = explode(',', $groupvisibility);
			foreach ($group_visibility_items as $item){
				if (!empty($item) && $item !== NULL){
					$item_details = explode('_', $item);
					update_post_meta($offer_id, 'b2bking_group_'.$item_details[2], 1);
				}
			}

			// Save user visibility
			if ($uservisibility !== NULL){
				// get new users list
				$newusersarray = explode(',', $uservisibility);
				// set new user meta
				foreach ($newusersarray as $newuser){
					update_post_meta( $offer_id, 'b2bking_user_'.sanitize_text_field(trim($newuser)), 1);
				}
				// Update users textarea
				update_post_meta($offer_id, 'b2bking_category_users_textarea', sanitize_text_field($uservisibility));
			}

			// Save user visibilitycustom text
			if ($customtext !== NULL){
				update_post_meta($offer_id, 'b2bking_offer_customtext_textarea', $customtext);
			}

			// finally save offer to user list ids
			if ($newedit === 'new'){
				$vendor_offers = get_user_meta($userid,'b2bking_marketking_vendor_offers_list_ids', true);
				$vendor_offers .=','.$offer_id.',';
				update_user_meta($userid,'b2bking_marketking_vendor_offers_list_ids', $vendor_offers );
			}

			// message user if response to quote
			if(isset($_POST['b2bking_quote_response'])){
				if (!empty($_POST['b2bking_quote_response'])){
					$conversationid = sanitize_text_field($_POST['b2bking_quote_response']);
					$requester = get_post_meta($conversationid, 'b2bking_quote_requester', true);
					// verify requester is included in category_Textarea
						// yes, add message
						$nr_messages = intval(get_post_meta ($conversationid, 'b2bking_conversation_messages_number', true));
						$current_message_nr = $nr_messages+1;
						$message = '----- '.esc_html__('You have received a new offer in response to your quote request: ','b2bking').'<a href="'.apply_filters('b2bking_offers_link', get_permalink( get_option('woocommerce_myaccount_page_id') ).get_option('b2bking_offers_endpoint_setting','offers')).'">#'.$offer_id.'</a> -----';
						$cruser = wp_get_current_user();
						$current_id = get_current_user_id();
						if (marketking()->is_vendor_team_member()){
							$current_id = marketking()->get_team_member_parent();
							$cruser = new WP_User($current_id);
						}


						$currentuser = $cruser->user_login;
						update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr, $message);
						update_post_meta( $conversationid, 'b2bking_conversation_messages_number', $current_message_nr);
						update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr.'_author', $currentuser );
						update_post_meta( $conversationid, 'b2bking_conversation_message_'.$current_message_nr.'_time', time() );

						// if status is new, change to open
						$status = get_post_meta ($conversationid, 'b2bking_conversation_status', true);
						if ($status === 'new'){
							update_post_meta( $conversationid, 'b2bking_conversation_status', 'open');
						}

						if (strpos($requester, '@') !== false) {
							// ok we have email
							$recipient = $requester;
						} else {
							// get email
							$userreq = get_user_by('login', $requester);
							$recipient = $userreq->user_email;
						}

						do_action( 'b2bking_new_message', $recipient, $message, $cruser->ID, $conversationid );
				}
			}


		}

		echo esc_html($offer_id);
		exit();
	}			 

	function b2bking_delete_ajax_offer(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		$offerid = sanitize_text_field($_POST['offerid']);
		$userid = sanitize_text_field($_POST['userid']);
		// check that ID belongs to author
		if (intval(get_post_field( 'post_author', $offerid )) === intval($userid)){
			// delete offer
			wp_delete_post($offerid);

			// remove from user meta as author
			$vendor_offers = get_user_meta($userid,'b2bking_marketking_vendor_offers_list_ids', true);
			$vendor_offers = explode(',', $vendor_offers);
			$vendor_offers_string = '';
			foreach ($vendor_offers as $index=> $offer_id){
				if ($offer_id === NULL || empty($offer_id) || $offer_id === $offerid || get_post_type($offer_id) !== 'b2bking_offer'){
					// do nothing
				} else {
					// add to string
					$vendor_offers_string .=$offer_id.',';
				}
			}

			update_user_meta($userid,'b2bking_marketking_vendor_offers_list_ids', $vendor_offers_string );
		}

		echo 'success';
		exit();
	}

	function b2bking_get_offer_data(){
		// Check security nonce. 
		if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
		  	wp_send_json_error( 'Invalid security token sent.' );
		    wp_die();
		}

		// check if this is in response to get quote, and if so, change behaviour
		$quoteid = sanitize_text_field($_POST['quoteid']);

		if (!empty($quoteid)){

			$conversationid = $quoteid;
			if (!empty($conversationid)){
				$requester = get_post_meta($conversationid, 'b2bking_quote_requester', true);
				$uservisibility = esc_html($requester);

				$productsstring = get_post_meta($conversationid, 'b2bking_quote_products', true);
				$offerdetails = esc_html($productsstring);
			}

			$sendstring = $uservisibility.'*'.''.'*'.$offerdetails.'*'.''.'*'.'';
			echo $sendstring;

		} else {
			$offerid = sanitize_text_field($_POST['offerid']);
			$userid = sanitize_text_field($_POST['userid']);
			// check that ID belongs to author
			if (intval(get_post_field( 'post_author', $offerid )) === intval($userid)){
				// get offer data
				$uservisibility = get_post_meta($offerid, 'b2bking_category_users_textarea', true);
				$customtext = get_post_meta($offerid, 'b2bking_offer_customtext_textarea', true);
				$offerdetails = get_post_meta($offerid, 'b2bking_offer_details', true);
				$title = get_the_title($offerid);
				$groupvisibility = '';
				$groups = get_posts([
				  'post_type' => 'b2bking_group',
				  'post_status' => 'publish',
				  'numberposts' => -1,
				  'fields' => 'ids'
				]);
				foreach ($groups as $group){
					$visible = get_post_meta($offerid, 'b2bking_group_'.$group, true);
					if (intval($visible) === 1){
						$groupvisibility .= 'b2bking_group_'.$group.',';
					}
				}

				$sendstring = $uservisibility.'*'.$groupvisibility.'*'.$offerdetails.'*'.$customtext.'*'.$title;
				echo $sendstring;
			} else {
				esc_html_e('no permission','marketking');
			}
		}

		

		exit();
	}

	function allocate_offers_vendors($cart_item_data){
		// add seller ID to cart item
		$offer_id = $cart_item_data['b2bking_offer_id'];
		// get author / vendor ID
		$author_id = get_post_field ('post_author', $offer_id);
		// get if author is a vendor
		$is_vendor = metadata_exists('user',$author_id,'marketking_store_name');

		if ($is_vendor){
			$cart_item_data['b2bkingmarketking_offer_vendor'] = $author_id;
		} else {
			$cart_item_data['b2bkingmarketking_offer_vendor'] = 'store';
		}
		return $cart_item_data;
	}

	function filter_offer_product_id($offer_product_id, $b2bking_offer_id){
		// get author / vendor ID
		$offer_author = get_post_field ('post_author', $b2bking_offer_id);
		// get if author is a vendor
		$is_vendor = metadata_exists('user',$offer_author,'marketking_store_name');

		if (!$is_vendor){
			return $offer_product_id;
		} else {
			$offer = array(
			    'post_title' => 'Offer',
			    'post_status' => 'publish',
			    'post_type' => 'product',
			    'post_author' => $offer_author,
			);
			$product_id = wp_insert_post($offer);

			$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
			wp_set_object_terms( $product_id, $terms, 'product_visibility' );
			wp_set_object_terms( $product_id, 'simple', 'product_type' );
			update_post_meta( $product_id, '_visibility', 'hidden' );
			update_post_meta( $product_id, '_stock_status', 'instock');
			update_post_meta( $product_id, '_regular_price', '' );
			update_post_meta( $product_id, '_sale_price', '' );
			update_post_meta( $product_id, '_purchase_note', '' );
			update_post_meta( $product_id, '_sku', 'SKU11' );
			update_post_meta( $product_id, '_product_attributes', array() );
			update_post_meta( $product_id, '_sale_price_dates_from', '' );
			update_post_meta( $product_id, '_sale_price_dates_to', '' );
			update_post_meta( $product_id, '_price', '1' );
			update_post_meta( $product_id, '_sold_individually', '' );

			$offer_products = get_option('b2bking_marketking_hidden_offer_product_ids', 0);
			if ($offer_products === 0 || empty($offer_products)){
				$offer_products = '';
			}
			$offer_products .=','.$product_id;
			update_option('b2bking_marketking_hidden_offer_product_ids', $offer_products);

			return $product_id;
		}
	}

	function b2bking_hide_offer_products($query) {

		$current_exclude = $query->query_vars['post__not_in'];
		
		$offer_products = get_option('b2bking_marketking_hidden_offer_product_ids', 'string');

		if ($offer_products !== 'string' && !empty($offer_products)){
			$offer_products = explode(',', $offer_products);
			$clean_offer_products = array_unique(array_filter($offer_products));

			if (is_array($current_exclude)){
				$query->query_vars['post__not_in'] = array_merge($clean_offer_products, $current_exclude);
			} else {
	        	$query->query_vars['post__not_in'] = $clean_offer_products;
	    	}
	    }
    }

    // Save Rules
    function b2bking_save_new_ajax_rule(){

    	// Check security nonce. 
    	if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
    	  	wp_send_json_error( 'Invalid security token sent.' );
    	    wp_die();
    	}

    	// get if this is new or edit
    	$newedit = sanitize_text_field($_POST['newedit']);
    	$title = sanitize_text_field($_POST['ruletitle']);
    	$userid = sanitize_text_field($_POST['userid']);
    	$post_id = 0;
    	// create new offer if fields not empty
		if ($newedit === 'new'){
			$rule = array(
			    'post_title' => $title,
			    'post_status' => 'publish',
			    'post_type' => 'b2bking_rule',
			    'post_author' => $userid,
			);
			$rule_id = wp_insert_post($rule);
			$post_id = $rule_id;
		} else {
			$rule_id = intval($newedit);
			// update title
			$my_post = array(
			    'ID'           => $newedit,
			    'post_title'   => $title,
			);
			// Update the post into the database
			wp_update_post( $my_post );
		} 

    	// set that rules have changed so that pricing cache can be updated
    	update_option('b2bking_dynamic_rules_have_changed', 'yes');

    	// delete all b2bking transients
    	global $wpdb;
    	$plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%transient_b2bking%'" );
    	foreach( $plugin_options as $option ) {
    	    delete_option( $option->option_name );
    	}

    	$rule_what = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_rule_select_what'));
    	$rule_applies = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_rule_select_applies'));
    	$rule_who = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_rule_select_who'));
    	$rule_quantity_value = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_rule_select_quantity_value'));
    	$rule_howmuch = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_rule_select_howmuch'));
    	$rule_conditions = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_rule_select_conditions'));
    	$rule_discount_show_everywhere = sanitize_text_field(filter_input(INPUT_POST, 'b2bking_dynamic_rule_discount_show_everywhere_checkbox_input'));

    	if ($rule_what !== NULL){
    		update_post_meta( $post_id, 'b2bking_rule_what', $rule_what);
    	}
    	if ($rule_applies !== NULL){
    		update_post_meta( $post_id, 'b2bking_rule_applies', $rule_applies);
    	}
    	if ($rule_who !== NULL){
    		update_post_meta( $post_id, 'b2bking_rule_who', $rule_who);
    	}
    	if ($rule_quantity_value !== NULL){
    		update_post_meta( $post_id, 'b2bking_rule_quantity_value', $rule_quantity_value);
    	}
    	if ($rule_howmuch !== NULL){
    		update_post_meta( $post_id, 'b2bking_rule_howmuch', $rule_howmuch);
    	}
    	if ($rule_conditions !== NULL){
    		update_post_meta( $post_id, 'b2bking_rule_conditions', $rule_conditions);
    	}
    	if ($rule_discount_show_everywhere !== NULL){
    		update_post_meta( $post_id, 'b2bking_rule_discount_show_everywhere', $rule_discount_show_everywhere);
    	}

    	if (isset($_POST['b2bking_select_multiple_product_categories_selector_select'])){
    		$rule_applies_multiple_options = $_POST['b2bking_select_multiple_product_categories_selector_select'];
    	} else {
    		$rule_applies_multiple_options = NULL;
    	}

    	if (isset($_POST['b2bking_select_multiple_users_selector_select'])){
    		$rule_who_multiple_options = $_POST['b2bking_select_multiple_users_selector_select'];
    	} else {
    		$rule_who_multiple_options = NULL;
    	}

    	if ($rule_applies_multiple_options !== NULL){
    		$options_string = '';
    		foreach ($rule_applies_multiple_options as $option){
    			$options_string .= sanitize_text_field ($option).',';
    		}
    		// remove last comma
    		$options_string = substr($options_string, 0, -1);
    		update_post_meta( $post_id, 'b2bking_rule_applies_multiple_options', $options_string);
    	}

    	if ($rule_who_multiple_options !== NULL){
    		$options_string = '';
    		foreach ($rule_who_multiple_options as $option){
    			$options_string .= sanitize_text_field ($option).',';
    		}
    		// remove last comma
    		$options_string = substr($options_string, 0, -1);
    		update_post_meta( $post_id, 'b2bking_rule_who_multiple_options', $options_string);
    	}

    	$rule_replaced =  sanitize_text_field(filter_input(INPUT_POST, 'b2bking_rule_select_applies_replaced')); 
    	$rule_replaced_array = explode(',',$rule_replaced);
    	$rule_replaced_string = '';
    	foreach ($rule_replaced_array as $element){
    		$rule_replaced_string.= 'product_'.trim($element).',';
    	}
    	// remove last comma
    	$rule_replaced_string = substr($rule_replaced_string, 0, -1);

    	// if rule applies is product & variation IDS, set applies as b2bking_rule_select_applies_replaced
    	if ($rule_applies === 'replace_ids'){
    		if ($rule_replaced !== NULL){
    			update_post_meta( $post_id, 'b2bking_rule_applies', 'multiple_options');
    			update_post_meta( $post_id, 'b2bking_rule_applies_multiple_options', $rule_replaced_string);
    			update_post_meta( $post_id, 'b2bking_rule_replaced', 'yes');
    		}
    	} else {
    		update_post_meta( $post_id, 'b2bking_rule_replaced', 'no');
    	}

    	// finally save rule to user list ids
    	if ($newedit === 'new'){
    		$vendor_rules = get_user_meta($userid,'b2bking_marketking_vendor_rules_list_ids', true);
    		if (empty($vendor_rules) || $vendor_rules === NULL){
    			$vendor_rules = '';
    		}
    		$vendor_rules .=','.$rule_id.',';
    		update_user_meta($userid,'b2bking_marketking_vendor_rules_list_ids', $vendor_rules );
    	}


    	// calculate the number of rules for each rule and set them as an option, to improve speed
    	B2bking_Admin::b2bking_calculate_rule_numbers_database();
    	echo 'success';
    	exit();
    }

    function b2bking_delete_ajax_rule(){
    	// Check security nonce. 
    	if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
    	  	wp_send_json_error( 'Invalid security token sent.' );
    	    wp_die();
    	}

    	$ruleid = sanitize_text_field($_POST['ruleid']);
    	$userid = sanitize_text_field($_POST['userid']);
    	// check that ID belongs to author
    	if (intval(get_post_field( 'post_author', $ruleid )) === intval($userid)){
    		// delete offer
    		wp_delete_post($ruleid);

    		// remove from user meta as author
    		$vendor_rules = get_user_meta($userid,'b2bking_marketking_vendor_rules_list_ids', true);
    		$vendor_rules = explode(',', $vendor_rules);
    		$vendor_rules_string = '';
    		foreach ($vendor_rules as $index=> $rule_id){
    			if ($rule_id === NULL || empty($rule_id) || $offer_id === $ruleid || get_post_type($rule_id) !== 'b2bking_rule'){
    				// do nothing
    			} else {
    				// add to string
    				$vendor_rules_string .=$rule_id.',';
    			}
    		}

    		update_user_meta($userid,'b2bking_marketking_vendor_rules_list_ids', $vendor_rules_string );
    	}

    	echo 'success';
    	exit();
    }

    function b2bking_get_rule_data(){
    	// Check security nonce. 
    	if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
    	  	wp_send_json_error( 'Invalid security token sent.' );
    	    wp_die();
    	}

    	$ruleid = sanitize_text_field($_POST['ruleid']);
    	$userid = sanitize_text_field($_POST['userid']);
    	// check that ID belongs to author
    	if (intval(get_post_field( 'post_author', $ruleid )) === intval($userid)){
    		// get offer data
    		$b2bking_rule_what = get_post_meta($ruleid, 'b2bking_rule_what', true);
    		$b2bking_rule_applies = get_post_meta($ruleid, 'b2bking_rule_applies', true);
    		$b2bking_rule_who = get_post_meta($ruleid, 'b2bking_rule_who', true);
    		$b2bking_rule_quantity_value = get_post_meta($ruleid, 'b2bking_rule_quantity_value', true);
    		$b2bking_rule_howmuch = get_post_meta($ruleid, 'b2bking_rule_howmuch', true);
    		$b2bking_rule_conditions = get_post_meta($ruleid, 'b2bking_rule_conditions', true);
    		$b2bking_rule_discount_show_everywhere = get_post_meta($ruleid, 'b2bking_rule_discount_show_everywhere', true);
    		$b2bking_rule_applies_multiple_options = get_post_meta($ruleid, 'b2bking_rule_applies_multiple_options', true);
    		$b2bking_rule_who_multiple_options = get_post_meta($ruleid, 'b2bking_rule_who_multiple_options', true);
    		$title = get_the_title($ruleid);

    		$sendstring = $b2bking_rule_what.'*'.$b2bking_rule_applies.'*'.$b2bking_rule_who.'*'.$b2bking_rule_quantity_value.'*'.$b2bking_rule_howmuch.'*'.$b2bking_rule_conditions.'*'.$b2bking_rule_discount_show_everywhere.'*'.$b2bking_rule_applies_multiple_options.'*'.$b2bking_rule_who_multiple_options.'*'.$title;

    		echo $sendstring;
    	} else {
    		esc_html_e('no permission','marketking');
    	}

    	exit();
    }

    function b2bking_get_conversation_data(){
    	// Check security nonce. 
    	if ( ! check_ajax_referer( 'b2bking_security_nonce', 'security' ) ) {
    	  	wp_send_json_error( 'Invalid security token sent.' );
    	    wp_die();
    	}
    	
		$conversation_id = sanitize_text_field($_POST['conversationid']);

		$nr_messages = get_post_meta ($conversation_id, 'b2bking_conversation_messages_number', true);
		$type = get_post_meta ($conversation_id, 'b2bking_conversation_type', true);

		ob_start();

		?>
		<div id="b2bking_conversation_messages_container">
		<?php				

		// if conversation is REFUND CONVERSATION, show refund reason as first message
		if (get_post_type($conversation_id) === 'marketking_refund'){
			$reason = get_post_meta($conversation_id,'reason', true);
			$refundauthor = get_post_field ('post_author', $conversation_id);
			$refundauthor = new WP_User($refundauthor);
			$refundstatus = get_post_meta($conversation_id,'request_status', true);

			?>
			<div class="b2bking_conversation_message">
				<?php echo '<strong>'.esc_html__('Refund reason:','marketking').'</strong> '.nl2br($reason); ?>
				<div class="b2bking_conversation_message_time">
					<?php
					echo esc_html($refundauthor->user_login).' - ';
					echo esc_html(get_the_date('', $conversation_id)); ?>
				</div>
			</div>


			<?php	
		}
			
		$guest_message = 'no';
		// loop through and display messages
		for ($i = 1; $i <= $nr_messages; $i++) {
		    // get message details
		    $message = get_post_meta ($conversation_id, 'b2bking_conversation_message_'.$i, true);
		    $author = get_post_meta ($conversation_id, 'b2bking_conversation_message_'.$i.'_author', true);
		    if (strpos($author, '@') !== false) {
		    	// if it contains an email, it's not necessarily a guest message. Check if it has an account
		    	$acc = get_user_by('login', $author, true);
		    	if ($acc !== false){
		    		// has acc

		    		// check if user is B2C in hybrid mode (does not have access to conversation) and not a vendor
		    		$userobj = get_user_by('login', $author);
		    		if (get_user_meta($userobj->ID, 'b2bking_b2buser', true) !== 'yes' && get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid'){
		    			// if not vendor
		    			$user_meta=get_userdata($userobj->ID); 
		    			$user_roles=$user_meta->roles;
		    			if (!in_array("vendor", $user_roles) && !in_array("seller", $user_roles)){
		    				$guest_message = 'yes';
		    			}
		    		}
		    	} else {
		    		$guest_message = 'yes';
		    	}
		    	
		    } else {
		    	// check if user is B2C in hybrid mode (does not have access to conversation)
		    	$userobj = get_user_by('login', $author);
		    	if (get_user_meta($userobj->ID, 'b2bking_b2buser', true) !== 'yes' && get_option( 'b2bking_plugin_status_setting', 'b2b' ) === 'hybrid'){
		    		$user_meta=get_userdata($userobj->ID); 
		    		$user_roles=$user_meta->roles;
		    		if (!in_array("vendor", $user_roles) && !in_array("seller", $user_roles)){
		    			$guest_message = 'yes';
		    		}
		    	}
		    }
		    $time = get_post_meta ($conversation_id, 'b2bking_conversation_message_'.$i.'_time', true);
		    // check if message author is self, parent, or subaccounts
		    $current_user_id = get_current_user_id();

		    $current_id = get_current_user_id();
		    if (marketking()->is_vendor_team_member()){
		    	$current_id = marketking()->get_team_member_parent();
		    	$current_user_id = $current_id;
		    }

		    $subaccounts_list = get_user_meta($current_user_id,'b2bking_subaccounts_list', true);
		    $subaccounts_list = explode(',', $subaccounts_list);
		    $subaccounts_list = array_filter($subaccounts_list);
		    array_push($subaccounts_list, $current_user_id);

			// add parent account+all subaccounts lists
		    $account_type = get_user_meta($current_user_id, 'b2bking_account_type', true);
		    if ($account_type === 'subaccount'){
				$parent_account = get_user_meta($current_user_id, 'b2bking_account_parent', true);
	    		$parent_subaccounts_list = explode(',', get_user_meta($parent_account, 'b2bking_subaccounts_list', true));
	    		$parent_subaccounts_list = array_filter($parent_subaccounts_list); // filter blank, null, etc.
	    		array_push($parent_subaccounts_list, $parent_account); // add parent itself to form complete parent accounts list

	    		$subaccounts_list = array_merge($subaccounts_list, $parent_subaccounts_list);
		    }

		    foreach ($subaccounts_list as $user){
		    	$subaccounts_list[$user] = get_user_by('id', $user)->user_login;
		    }
		    if (in_array($author, $subaccounts_list)){
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
		    		<?php echo esc_html($author).' - '; ?>
		    		<?php echo esc_html($timestring); ?>
		    	</div>
		    </div>
		    <?php
		}
		?>
		</div>
		<input type="hidden" id="b2bking_conversation_id">

		<?php 
		$show_button_send = 'yes';
		if (get_post_type($conversation_id) === 'marketking_refund'){
			if ($refundstatus !== 'open'){
				$show_button_send = 'no';
			}
		}


		if ($guest_message === 'no'){
			if ($show_button_send === 'yes'){
				?>
				<textarea name="b2bking_conversation_user_new_message" id="b2bking_conversation_user_new_message"></textarea><br />
				<?php
			} else {
				// show approved / rejected
				?>
				<div class="marketking_refund_decision_made">
					<?php
					if ($refundstatus === 'approved'){
						esc_html_e('This refund has been approved.','marketking');
					} else if ($refundstatus === 'rejected'){
						esc_html_e('This refund has been denied.','marketking');
					}
					?>
				</div>
				<?php
			}
		}
		?>
		<div class="b2bking_myaccount_conversation_endpoint_bottom">
			<?php 
			if ($guest_message === 'no'){

				if ($show_button_send === 'yes'){
				?>

			    	<button id="b2bking_conversation_message_submit_vendor" class="b2bking_myaccount_conversation_endpoint_button" type="button" value="<?php echo esc_attr($conversation_id);?>">
			    		<svg class="b2bking_myaccount_conversation_endpoint_button_icon" xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 21 21">
					  	<path fill="#fff" d="M5.243 12.454h9.21v4.612c0 .359-.122.66-.368.906-.246.245-.567.377-.964.396H5.243L1.955 21v-2.632h-.651a1.19 1.19 0 01-.907-.396A1.414 1.414 0 010 17.066V8.52c0-.358.132-.67.397-.934.264-.264.567-.387.907-.368h3.939v5.236zM19.696.002c.378 0 .69.123.936.368.245.245.368.566.368.962V9.85c0 .359-.123.66-.368.906a1.37 1.37 0 01-.936.396h-.652v2.632l-3.287-2.632H6.575v-9.82c0-.377.123-.698.368-.962.246-.264.558-.387.936-.368h11.817z"/>
						</svg>
			    		<?php esc_html_e('Send Message','marketking'); ?>
			    	</button>

		    	<?php
		    	}

		    	// approve reject refunds buttons
		    	if (get_post_type($conversation_id) === 'marketking_refund'){
		    		if($refundstatus === 'open'){
			    		?>
	    		    	<button id="marketking_refund_approve_button" class="b2bking_myaccount_conversation_endpoint_button marketking_refund_approve" type="button" value="<?php echo esc_attr($conversation_id);?>">
	    		    		<em class="marketking_refund_icon b2bking_myaccount_conversation_endpoint_button_icon icon ni ni-check-round-fill"></em>
	    		    		<?php esc_html_e('Approve Refund','marketking'); ?>
	    		    	</button>
		    	    	<button id="marketking_refund_reject_button" class="b2bking_myaccount_conversation_endpoint_button marketking_refund_reject" type="button" value="<?php echo esc_attr($conversation_id);?>">
		    	    		<em class="marketking_refund_icon b2bking_myaccount_conversation_endpoint_button_icon icon ni ni-cross-round-fill"></em>
		    	    		<?php esc_html_e('Deny Refund','marketking'); ?>
		    	    	</button>
			    		<?php
		    		}
		    	}
		    }
		    if ($type === 'quote'){
		    	if (defined('B2BKING_DIR') && defined('MARKETKINGPRO_DIR') && intval(get_option('marketking_enable_b2bkingintegration_setting', 1)) === 1){

		    	    if (intval(get_option('b2bking_enable_offers_setting', 1)) === 1){
		    	        if(marketking()->vendor_has_panel('b2bkingoffers')){
					    	?>
					    	<button id="b2bking_conversation_make_offer_vendor" type="button" class="b2bking_myaccount_conversation_endpoint_button" value="<?php echo esc_attr($conversation_id);?>" ><?php esc_html_e('Make Offer','b2bking'); ?></button>
					    	<?php
					    }
					}
				}
		    }
		    ?>
	
		</div>
		<?php
		$output = ob_get_clean();
		echo $output;
		exit();
    }

    function filter_conversation_permission_list($subaccounts_list, $conversationid, $current_user_id, $conversationuser){
    	$vendor = get_post_meta($conversationid,'b2bking_conversation_vendor', true);
    	$vendor_id = get_user_by('login', $vendor)->ID;
    	$conversationuserid = get_user_by('login', $conversationuser)->ID;

    	array_push($subaccounts_list, $vendor_id);
    	array_push($subaccounts_list, $conversationuserid);
    	return $subaccounts_list; // array
    }

    function filter_message_recipient($recipient, $conversationid){

    	$current_id = get_current_user_id();
    	if (marketking()->is_vendor_team_member()){
    		$current_id = marketking()->get_team_member_parent();
    	}

    	$current_user_id = $current_id;
    	// based on current user id, calculate the other user's id and get email
    	$conversation_user = get_post_meta($conversationid, 'b2bking_conversation_user', true);

    	$conversation_vendor = get_post_meta($conversationid, 'b2bking_conversation_vendor', true);
    	// $conversation_vendor is the store name. get the username
    	$users = get_users(array('meta_key' => 'marketking_store_name', 'meta_value' => $conversation_vendor));

    	$userobj = get_user_by('login', $conversation_user);
    	$vendorobj = $users[0];

    	if (intval($current_user_id) === intval($userobj->ID)){
    		return $vendorobj->user_email;
    	} else if (intval($current_user_id) === intval($vendorobj->ID)){
    		return $userobj->user_email;
    	} 

 		return $recipient;
    }

    function filter_message_recipient_quote($recipient, $conversationid){

       	// get if conversation has vendor (or vendor is store)
       	$conversation_vendor = get_post_meta($conversationid, 'b2bking_conversation_vendor', true);

       	if ($conversation_vendor === null || empty($conversation_vendor)){
       		// vendor is store
       		return $recipient;
       	}

       	// $conversation_vendor is the store name. get the username
       	$users = get_users(array('meta_key' => 'marketking_store_name', 'meta_value' => $conversation_vendor));
       	if (!empty($users)){
       		$vendorobj = $users[0];
       	} else {
       		$vendorobj = get_user_by('login', $conversation_vendor);
       	}
       	return $vendorobj->user_email;


	}
		
}

