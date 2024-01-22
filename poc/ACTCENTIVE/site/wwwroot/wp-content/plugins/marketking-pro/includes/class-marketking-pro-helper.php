<?php

class Marketkingpro_Helper{

	private static $instance = null;

	public static function init() {
	    if ( self::$instance === null ) {
	        self::$instance = new self();
	    }

	    return self::$instance;
	}

	public static function marketkingpro_is_rest_api_request() {
	    if ( empty( $_SERVER['REQUEST_URI'] ) ) {
	        // Probably a CLI request
	        return false;
	    }

	    $rest_prefix         = trailingslashit( rest_get_url_prefix() );
	    $is_rest_api_request = strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) !== false;

	    if (defined('REST_REQUEST')){
	    	$is_rest_api_request = true;
	    }

	    return apply_filters( 'is_rest_api_request', $is_rest_api_request );
	}

	// return non-vendor participant in a conversation
	// returns 'shop', or user id

	public static function get_conversation_party($conversationid, $get){

		if ($get === 'vendor'){
			$conversationuser = get_post_meta ($conversationid, 'marketking_message_user', true);
			$conversationuser2 = get_post_meta ($conversationid, 'marketking_message_message_1_author', true);
			$conversationuser = get_user_by('login', $conversationuser);
			$conversationuser2 = get_user_by('login', $conversationuser2);
			if (marketking()->is_vendor($conversationuser->ID) || marketking()->is_admin($conversationuser->ID)){
				return $conversationuser->ID;
			} else {
				return $conversationuser2->ID;
			}
		}


		$conversationuser = get_post_meta ($conversationid, 'marketking_message_user', true);
		$conversationuser2 = get_post_meta ($conversationid, 'marketking_message_message_1_author', true);

		if ($conversationuser === 'shop' || $conversationuser === 'shop'){
			return 'shop';
		}

		$conversationuser = get_user_by('login', $conversationuser);
		$conversationuser2 = get_user_by('login', $conversationuser2);

		if (marketking()->is_vendor($conversationuser->ID) || marketking()->is_admin($conversationuser->ID)){
			$party = $conversationuser2;
		} else {
			$party = $conversationuser;
		}

		if($party === false){
			return 'guest';
		}

		if ($party->has_cap('manage_woocommerce')){
			return 'shop';
		}

		return $party->ID;
	}

	public static function clear_all_vendor_badges_cache(){
		$vendors = marketking()->get_all_vendors();
		foreach ($vendors as $vendor){
			delete_transient('marketking_badges_cache_vendor_'.$vendor->ID);
		}
	}


	public static function get_vendor_badges($vendor_id){
		$badges = get_transient('marketking_badges_cache_vendor_'.$vendor_id);
		$cache_time = get_transient('marketking_badges_cache_time_vendor_'.$vendor_id);

		// if cache is empty, or it's been 24 hrs, recalculate cache
		if (empty($badges) || (time()-intval($cache_time))>86400 ){

			$all_badges = get_posts( array( 
			    'post_type' => 'marketking_badge',
			    'numberposts' => -1,
			    'post_status'    => 'publish',
			    'fields'    => 'ids',
			    'meta_key' => 'marketking_badge_sort_order',
			    'orderby' => 'meta_value_num',
			    'order' => 'ASC',
			));

			$applicable_badges = $all_badges;
			// go through all badges, and remove them if conditions don't apply

			// 1. First check if either vendor or group is in visibility, otherwise remove badge
			foreach ($applicable_badges as $index => $badge_id){
				
				$user_or_group_applies = 'no';
				$users = get_post_meta($badge_id,'marketking_group_visible_vendors_settings', true);
				$groups = get_post_meta($badge_id,'marketking_group_visible_groups_settings', true);

				if (!empty($users)){
					$users = explode(',', $users);
					if (in_array($vendor_id, $users)){
						$user_or_group_applies = 'yes';
					}
				}

				if (!empty($groups)){
					$groups = explode(',', $groups);
					$vendor_group = get_user_meta($vendor_id,'marketking_group', true);
					if (in_array($vendor_group, $groups)){
						$user_or_group_applies = 'yes';
					}
				}

				if ($user_or_group_applies === 'no'){
					// remove badge
					unset($applicable_badges[$index]);
				}
			}

			// 2. Now check conditions
			foreach ($applicable_badges as $index => $badge_id){
				
				$condition = get_post_meta($badge_id,'marketking_badge_condition', true);
				if ($condition === 'none' || empty($condition)){
					// badge applies, go to the next one, continue
					continue;
				}

				$conditionvalue = intval(get_post_meta($badge_id,'marketking_badge_condition_value', true));

				if ($condition === 'salesvalue'){
					$salesvalue = marketking()->get_vendor_total_sales($vendor_id);
					if ($salesvalue > $conditionvalue){
						continue;
					} else {
						unset($applicable_badges[$index]);
					}
				}

				if ($condition === 'ordernumber'){
					$ordernumber = marketking()->get_vendor_order_number($vendor_id);
					if ($ordernumber > $conditionvalue){
						continue;
					} else {
						unset($applicable_badges[$index]);
					}
				}

				if ($condition === 'registrationtime'){
					$udata = get_userdata( $vendor_id );
	            $registered_time = intval(strtotime($udata->user_registered));
	            if ((time()-$registered_time) > (86400*$conditionvalue) ){
	            	continue;
	            } else {
	            	unset($applicable_badges[$index]);
	            }

				}
				
			}

			// set badges cache
			set_transient('marketking_badges_cache_vendor_'.$vendor_id, $applicable_badges);
			$badges = $applicable_badges;

			// set last calculation time
			set_transient('marketking_badges_cache_time_vendor_'.$vendor_id, time());
		}

		// badges array
		return $badges;

	}

	public static function get_tracking_providers(){
	    $providers = array(
	        'sp-australia-post' => array(
	            'label' => esc_html__( 'Australia Post', 'marketking' ),
	            'url'   => 'https://auspost.com.au/mypost/track/#/search?tracking={tracking_number}',
	        ),
	        'sp-canada-post' => array(
	            'label' => esc_html__( 'Canada Post', 'marketking' ),
	            'url'   => 'https://www.canadapost.ca/track-reperage/en#/home/?tracking={tracking_number}',
	        ),
	        'sp-city-link' => array(
	            'label' => esc_html__( 'City Link', 'marketking' ),
	            'url'   => 'https://www.citylinkexpress.com/tracking-result/?track0={tracking_number}',
	        ),
	        'sp-dhl' => array(
	            'label' => esc_html__( 'DHL', 'marketking' ),
	            'url'   => 'https://www.dhl.com/en/express/tracking.html?AWB={tracking_number}&brand=DHL',
	        ),
	        'sp-dpd' => array(
	            'label' => esc_html__( 'DPD', 'marketking' ),
	            'url'   => 'https://tracking.dpd.de/status/en_NL/parcel/{tracking_number}',
	        ),
	        'sp-fastway-south-africa' => array(
	            'label' => esc_html__( 'Fastway South Africa', 'marketking' ),
	            'url'   => 'https://www.fastway.co.za/our-services/track-your-parcel/?track={tracking_number}',
	        ),
	        'sp-fedex' => array(
	            'label' => esc_html__( 'Fedex', 'marketking' ),
	            'url'   => 'https://www.fedex.com/fedextrack/no-results-found?trknbr={tracking_number}',
	        ),
	        'sp-ontrac' => array(
	            'label' => esc_html__( 'OnTrac', 'marketking' ),
	            'url'   => 'https://www.ontrac.com/trackingdetail.asp/?track={tracking_number}',
	        ),
	        'sp-parcelforce' => array(
	            'label' => esc_html__( 'ParcelForce', 'marketking' ),
	            'url'   => 'https://www.parcelforce.com/track-trace/?trackNumber={tracking_number}',
	        ),
	        'sp-polish-shipping-providers' => array(
	            'label' => esc_html__( 'Polish shipping providers', 'marketking' ),
	            'url'   => 'https://www.parcelmonitor.com/track-poland/track-it-online/?pParcelIds={tracking_number}',
	        ),
	        'sp-royal-mail' => array(
	            'label' => esc_html__( 'Royal Mail', 'marketking' ),
	            'url'   => 'https://www.royalmail.com/track-your-item#/?track={tracking_number}',
	        ),
	        'sp-sapo' => array(
	            'label' => esc_html__( 'SAPO', 'marketking' ),
	            'url'   => 'https://tracking.postoffice.co.za/TrackNTrace/TrackNTrace.aspx?id={tracking_number}',
	        ),
	        'sp-tnt-express' => array(
	            'label' => esc_html__( 'TNT Express', 'marketking' ),
	            'url'   => 'https://www.tnt.com/express/site/tracking.html/?track={tracking_number}',
	        ),
	        'sp-fedex-sameday' => array(
	            'label' => esc_html__( 'FedEx Sameday', 'marketking' ),
	            'url'   => 'https://www.fedex.com/fedextrack/?action=track&tracknumbers={tracking_number}',
	        ),
	        'sp-ups' => array(
	            'label' => esc_html__( 'UPS', 'marketking' ),
	            'url'   => 'https://www.ups.com/track/?trackingNumber={tracking_number}',
	        ),
	        'sp-usps' => array(
	            'label' => esc_html__( 'USPS', 'marketking' ),
	            'url'   => 'https://tools.usps.com/go/TrackConfirmAction?tRef=fullpage&tLabels={tracking_number}',
	        ),
	        'sp-dhl-us' => array(
	            'label' => esc_html__( 'DHL US', 'marketking' ),
	            'url'   => 'https://www.dhl.com/us-en/home/tracking/tracking-global-forwarding.html?submit=1&tracking-id={tracking_number}',
	        ),
	        'sp-other' => array(
	            'label' => esc_html__( 'Other', 'marketking' ),
	            'url'   => '',
	        ),
	    );

	    return apply_filters( 'marketking_shipping_status_tracking_providers_list', $providers );
	}

	public static function display_vendor_badges($vendor_id, $max = 1000, $width = 25){
		
		// array of vendor badge ids
		$vendor_badges = marketkingpro()->get_vendor_badges($vendor_id);

		$i = 1;
		foreach ($vendor_badges as $badge_id){

			if ($i > $max){
				continue;
			}
			// get image
			$badge_image = get_the_post_thumbnail_url($badge_id);
			$description = get_post_meta($badge_id,'marketking_badge_description', true);

			echo '<img class="marketking_vendor_badge_display" src="'.esc_attr($badge_image).'" width="'.esc_attr($width).'" title="'.esc_attr($description).'">';
			$i++;
		}
	}

	public static function get_vendor_socials($vendor_id){
		$active_vendor_socials = array();

		$socials_possible = array('facebook', 'instagram', 'twitter', 'youtube', 'linkedin', 'pinterest');
		foreach ($socials_possible as $social){
			if (marketking()->social_site_active($social)){
				if (!empty(get_user_meta($vendor_id,'marketking_'.$social, true))){
					array_push($active_vendor_socials, $social);
				}
			}
		}
		
		return $active_vendor_socials;
	}

	public static function display_social_links($vendor_id, $max = 1000, $width = 25){
		
		$vendor_socials = marketkingpro()->get_vendor_socials($vendor_id);

		$width = apply_filters('marketking_social_icons_width', $width);

		$i = 1;
		foreach ($vendor_socials as $social){

			if ($i > $max){
				continue;
			}
			// get image
			$social_icon = plugins_url('assets/images/'.$social.'_small.png', __FILE__);

			$grayscale = '';
			if (intval(get_option( 'marketking_social_icons_grayscale_setting', 0 )) === 1){
				$grayscale = 'marketking_icon_grayscale';
			}

			$social_link = esc_attr(get_user_meta($vendor_id,'marketking_'.$social, true));
			// process link
			// If the user included a different URL other than the social URL, prevent link from working, or add it
			$info = parse_url($social_link);

			if (isset($info['host'])){
				$host = $info['host'];
			} else {
				$host = '';
			}

			if ($social === 'facebook'){
				if ( !(strpos($host, 'facebook.com') !== false)) {
					// does not include
					$social_link = 'https://www.facebook.com/'.$social_link;
				}
			}
			if ($social === 'twitter'){
				if ( !(strpos($host, 'twitter.com') !== false)) {
					// does not include
					$social_link = 'https://www.twitter.com/'.$social_link;
				}
			}
			if ($social === 'instagram'){
				if ( !(strpos($host, 'instagram.com') !== false)) {
					// does not include
					$social_link = 'https://www.instagram.com/'.$social_link;
				}
			}
			if ($social === 'linkedin'){
				if ( !(strpos($host, 'linkedin.com') !== false)) {
					// does not include
					$social_link = 'https://www.linkedin.com/in/'.$social_link;
				}
			}
			if ($social === 'youtube'){
				if ( !(strpos($host, 'youtube.com') !== false)) {
					// does not include
					$social_link = 'https://www.youtube.com/@'.$social_link;
				}
			}
			if ($social === 'pinterest'){
				if ( !(strpos($host, 'pinterest.com') !== false)) {
					// does not include
					$social_link = 'https://www.pinterest.com/'.$social_link;
				}
			}

			echo '<a class="marketking_vendor_social_link" target="_blank" href="'.$social_link.'"><img class="marketking_vendor_social_display '.$grayscale.'" src="'.esc_attr($social_icon).'" width="'.esc_attr($width).'" title="'.ucfirst(esc_attr($social)).'"></a>';
			
			$i++;
		}
	}

	public static function get_all_dashboard_panels(){

		$panels = array(	
			'announcements' => esc_html__('Announcements', 'marketking'),
			'dashboard' => esc_html__('Dashboard', 'marketking'),
			'messages' => esc_html__('Messages', 'marketking'),
			'coupons' => esc_html__('Coupons', 'marketking'),
			'products' => esc_html__('Products', 'marketking'),
			'orders' => esc_html__('Orders', 'marketking'),
			'teams' => esc_html__('My Team', 'marketking'),
			'earnings' => esc_html__('Earnings', 'marketking'),
			'payouts' => esc_html__('Payouts', 'marketking'),
			'reviews' => esc_html__('Reviews', 'marketking'),
			'refunds' => esc_html__('Refunds', 'marketking'),
			'vendordocs' => esc_html__('Docs', 'marketking'),
			'memberships' => esc_html__('Membership', 'marketking'),
			'profile' => esc_html__('Settings', 'marketking'),
			'shipping' => esc_html__('Shipping', 'marketking'),
			'vendorinvoices' => esc_html__('Invoicing', 'marketking'),
			'support' => esc_html__('Support', 'marketking'),
			'vacation' => esc_html__('Vacation', 'marketking'),
			'storenotice' => esc_html__('Store Notice', 'marketking'),
			'storepolicy' => esc_html__('Store Policies', 'marketking'),
			'storecategories' => esc_html__('Store Categories', 'marketking'),
			'storeseo' => esc_html__('SEO Settings', 'marketking'),
			'subscriptions' => esc_html__('Subscriptions', 'marketking'),
			'social' => esc_html__('Social Sharing', 'marketking'),
			'verification' => esc_html__('Verification', 'marketking'),
			'profile-settings' => esc_html__('Profile Settings', 'marketking'),
			'b2bkingoffers' => esc_html__('B2B Offers', 'marketking'),
			'b2bkingconversations' => esc_html__('B2B Conversations', 'marketking'),
			'b2bkingrules' => esc_html__('B2B Dynamic Rules', 'marketking'),
			'b2bkingvisibility' => esc_html__('B2B Product Visibility', 'marketking'),
			'b2bkingtables' => esc_html__('B2B Tiered & Info Tables', 'marketking'),
			'b2bkingpricing' => esc_html__('B2B Pricing', 'marketking'),
			'bookings' => esc_html__('Bookings', 'marketking'),
			'advertising' => esc_html__('Advertising', 'marketking'),
		);

		if (intval(get_option( 'marketking_enable_announcements_setting', 1 )) !== 1){
			unset($panels['announcements']);
		}
		if (intval(get_option( 'marketking_enable_messages_setting', 1 )) !== 1){
			unset($panels['messages']);
		}
		if (intval(get_option( 'marketking_enable_social_setting', 1 )) !== 1){
			unset($panels['social']);
		}
		if (intval(get_option( 'marketking_enable_coupons_setting', 1 )) !== 1){
			unset($panels['coupons']);
		}
		if (intval(get_option( 'marketking_enable_teams_setting', 1 )) !== 1){
			unset($panels['teams']);
		}
		if (intval(get_option( 'marketking_enable_earnings_setting', 1 )) !== 1){
			unset($panels['earnings']);
		}
		if (intval(get_option( 'marketking_enable_payouts_setting', 1 )) !== 1){
			unset($panels['payouts']);
		}
		if (intval(get_option( 'marketking_enable_reviews_setting', 1 )) !== 1){
			unset($panels['reviews']);
		}
		if (intval(get_option( 'marketking_enable_refunds_setting', 1 )) !== 1){
			unset($panels['refunds']);
		}
		if (intval(get_option( 'marketking_enable_vendordocs_setting', 1 )) !== 1){
			unset($panels['vendordocs']);
		}
		if (intval(get_option( 'marketking_enable_memberships_setting', 1 )) !== 1){
			unset($panels['memberships']);
		}
		if (intval(get_option( 'marketking_enable_vendorinvoices_setting', 1 )) !== 1){
			unset($panels['vendorinvoices']);
		}
		if (intval(get_option( 'marketking_enable_vacation_setting', 1 )) !== 1){
			unset($panels['vacation']);
		}
		if (intval(get_option( 'marketking_enable_storenotice_setting', 1 )) !== 1){
			unset($panels['storenotice']);
		}
		if (intval(get_option( 'marketking_enable_storepolicy_setting', 1 )) !== 1){
			unset($panels['storepolicy']);
		}
		if (intval(get_option( 'marketking_enable_storecategories_setting', 1 )) !== 1){
			unset($panels['storecategories']);
		}
		if (intval(get_option( 'marketking_enable_storeseo_setting', 1 )) !== 1){
			unset($panels['storeseo']);
		}
		if (intval(get_option( 'marketking_enable_verification_setting', 1 )) !== 1){
			unset($panels['verification']);
		}
		if (intval(get_option( 'marketking_enable_shipping_setting', 1 )) !== 1){
			unset($panels['shipping']);
		}
		if (intval(get_option( 'marketking_enable_bookings_setting', 0 )) !== 1){
			unset($panels['bookings']);
		}
		if (intval(get_option( 'marketking_enable_advertising_setting', 0 )) !== 1){
			unset($panels['advertising']);
		}
		
		if (!defined('B2BKING_DIR') || intval(get_option( 'marketking_enable_b2bkingintegration_setting', 1 )) !== 1){
			unset($panels['b2bkingoffers']);
			unset($panels['b2bkingconversations']);
			unset($panels['b2bkingrules']);
			unset($panels['b2bkingvisibility']);
			unset($panels['b2bkingtables']);
			unset($panels['b2bkingpricing']);
		}
		

		return apply_filters('marketking_vendor_dashboard_panels', $panels);
	}

	public static function get_all_vendor_available_dashboard_panels(){

		$panels = marketkingpro()->get_all_dashboard_panels();
		foreach ($panels as $panel_slug => $panel_name){
			if (!marketking()->vendor_has_panel($panel_slug)){
				unset($panels[$panel_slug]);
			}
		}

		// team members do not have access to teams or memberships
		if (isset($panels['teams'])){
			unset($panels['teams']);
		}
		if (isset($panels['memberships'])){
			unset($panels['memberships']);
		}
		
		return $panels;
	}

	public static function get_product_support_content($product_id){
		$vendor_id = marketking()->get_product_vendor($product_id);
		// get option for support of this vendor
		$support_option = get_user_meta($vendor_id,'marketking_support_option', true);
		if ($support_option === 'email' || $support_option === 'messaging'){
			$support_email = get_user_meta($vendor_id,'marketking_support_email', true);

			?>
			<input type="hidden" name="marketking_product_id" id="marketking_product_id" value="<?php echo esc_attr($product_id);?>">
			<span id="marketking_send_inquiry_textarea_abovetext"><?php esc_html_e( 'You have purchased this product.', 'marketking' ); ?><br><?php esc_html_e('Send a support message to the vendor below:', 'marketking');?></span>
			<textarea id="marketking_send_support_textarea"></textarea>

			<button type="button" id="marketking_send_support_button" class="button" value="<?php echo esc_attr($vendor_id); ?>">
				<?php esc_html_e( 'Send support request', 'marketking' ); ?>
			</button>
			<?php
		} else if ($support_option === 'external') {
			$support_url = get_user_meta($vendor_id,'marketking_support_url', true);
			// show button that goes to URL
			?>
			<a href="<?php echo esc_attr($support_url);?>" target="_blank" rel="noreferrer noopener"><button><?php echo apply_filters('marketking_get_support_text',esc_html__('Get Support','marketking'));?></button></a>
			<?php
		}
	}

	public static function get_inquiries_form($vendor_id, $product_id = false){

		ob_start();

		if ($product_id!==false){
			$vendor_id = $product_id;
			?>
			<input type="hidden" name="marketking_product_id" id="marketking_product_id" value="<?php echo esc_attr($product_id);?>">
			<?php
		}
		?>

		<?php
		if (!is_user_logged_in()){
			?>
			<span class="marketking_send_inquiry_text_label"><?php esc_html_e( 'Your name:', 'marketking' ); ?></span>
			<input type="text" id="marketking_send_inquiry_name" name="marketking_send_inquiry_name">

			<span class="marketking_send_inquiry_text_label"><?php esc_html_e( 'Your email address:', 'marketking' ); ?></span>
			<input type="text" id="marketking_send_inquiry_email" name="marketking_send_inquiry_email">

			<?php
			if (apply_filters('marketking_inquiry_enable_phone', false)){
				?>
				<span class="marketking_send_inquiry_text_label"><?php esc_html_e( 'Your phone number:', 'marketking' ); ?></span>
				<input type="text" id="marketking_send_inquiry_phone" name="marketking_send_inquiry_phone">
				<?php
			}
		}
		
		?>
		<span id="marketking_send_inquiry_textarea_abovetext"><?php esc_html_e( 'Your message:', 'marketking' ); ?></span>
		<textarea id="marketking_send_inquiry_textarea"></textarea>

		<button type="button" id="marketking_send_inquiry_button" class="button" value="<?php echo esc_attr($vendor_id); ?>">
			<?php esc_html_e( 'Send inquiry', 'marketking' ); ?>
		</button>
		<?php

		$content = ob_get_clean();
		echo apply_filters('marketking_get_inquiries_form', $content);
	}

	public static function get_vendor_inquiries_tab($vendor_id){
	  	
	  	?>

	  	<h3><?php echo apply_filters('marketking_contact_tab_name',esc_html__('Contact','marketking')); ?></h3>
	  	<p class="marketking_get_in_touch_inquiries_text"><?php esc_html_e('Get in touch with this vendor by filling out the form below.','marketking');?></p>

	  	<?php echo marketkingpro()->get_inquiries_form($vendor_id); ?>

	  	<?php
	}

	public static function get_page($page){
		ob_start();
		if ( $page === 'earnings') {
			include( apply_filters('marketking_dashboard_template', MARKETKINGPRO_DIR . 'public/dashboard/earnings.php' ));
		} else if ( $page === 'announcements') {
			if (intval(get_option( 'marketking_enable_announcements_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template', MARKETKINGPRO_DIR . 'public/dashboard/announcements.php' ));
			}
		} else if ( $page === 'announcement') {
			if (intval(get_option( 'marketking_enable_announcements_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template', MARKETKINGPRO_DIR . 'public/dashboard/announcement.php' ));
			}
		} else if ( $page === 'messages') {
			if (intval(get_option( 'marketking_enable_messages_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template', MARKETKINGPRO_DIR . 'public/dashboard/messages.php' ));
			}
		} else if ( $page === 'coupons') {
			if (intval(get_option( 'marketking_enable_coupons_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template', MARKETKINGPRO_DIR . 'public/dashboard/coupons.php' ));
			}
		} else if ( $page === 'edit-coupon') {
			if (intval(get_option( 'marketking_enable_coupons_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/edit-coupon.php' ));
			}
		} else if ( $page === 'edit-team') {
			if (intval(get_option( 'marketking_enable_teams_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/edit-team.php' ));
			}
		} else if ( $page === 'colorscheme') {
			if (intval(get_option( 'marketking_enable_colorscheme_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/colorscheme.php' ));
			}
		} else if ( $page === 'rules') {
			if (intval(get_option( 'marketking_enable_colorscheme_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/rules.php' ));
			}
		} else if ( $page === 'offers') {
			if (intval(get_option( 'b2bking_enable_offers_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/offers.php' ));
			}
		} else if ( $page === 'b2bmessaging') {
			if (intval(get_option( 'b2bking_enable_conversations_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/b2bmessaging.php' ));
			}
		} else if ( $page === 'support') {
			if (intval(get_option( 'marketking_enable_support_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/store-support.php' ));
			}
		} else if ( $page === 'shipping') {
			if (intval(get_option( 'marketking_enable_shipping_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/store-shipping.php' ));
			}
		} else if ( $page === 'shippingzone') {
			if (intval(get_option( 'marketking_enable_shipping_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/shipping-zone.php' ));
			}
		} else if ( $page === 'vacation') {
			if (intval(get_option( 'marketking_enable_vacation_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/vendor-vacation.php' ));
			}
		} else if ( $page === 'storenotice') {
			if (intval(get_option( 'marketking_enable_storenotice_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/store-notice.php' ));
			}
		} else if ( $page === 'storepolicy') {
			if (intval(get_option( 'marketking_enable_storepolicy_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/store-policy.php' ));
			}
		} else if ( $page === 'storecategories') {
			if (intval(get_option( 'marketking_enable_storecategories_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/store-categories.php' ));
			}
		} else if ( $page === 'vendorinvoices') {
			if (intval(get_option( 'marketking_enable_vendorinvoices_setting', 1 )) === 1 && (defined('WPO_WCPDF_VERSION') || defined('WF_PKLIST_VERSION'))){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/vendor-invoices.php' ));
			}
		} else if ( $page === 'storeseo') {
			if (intval(get_option( 'marketking_enable_storeseo_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/store-seo.php' ));
			}
		} else if ( $page === 'social') {
			if (intval(get_option( 'marketking_enable_social_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/store-social.php' ));
			}
		} else if ( $page === 'reviews') {
			if (intval(get_option( 'marketking_enable_reviews_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/reviews.php' ));
			}
		} else if ( $page === 'refunds') {
			if (intval(get_option( 'marketking_enable_refunds_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/refunds.php' ));
			}
		} else if ( $page === 'docs') {
			if (intval(get_option( 'marketking_enable_vendordocs_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/vendordocs.php' ));
			}
		} else if ( $page === 'docssingle') {
			if (intval(get_option( 'marketking_enable_announcements_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/vendordoc.php' ));
			}
		} else if ( $page === 'verification') {
			if (intval(get_option( 'marketking_enable_verification_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/verification.php' ));
			}
		} else if ( $page === 'import-products') {
			if (intval(get_option( 'marketking_enable_importexport_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/import-products.php' ));
			}
		} else if ( $page === 'export-products') {
			if (intval(get_option( 'marketking_enable_importexport_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/export-products.php' ));
			}
		} else if ($page === 'team'){
           if (intval(get_option( 'marketking_enable_teams_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/teams.php' ));
			}
        } else if ($page === 'memberships'){
           if (intval(get_option( 'marketking_enable_memberships_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/memberships.php' ));
			}
        } else if ($page === 'subscriptions'){
           if (intval(get_option( 'marketking_enable_subscriptions_setting', 1 )) === 1){
				include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/subscriptions.php' ));
			}
        } else if ($page === 'rma'){
			include( apply_filters('marketking_dashboard_template',MARKETKINGPRO_DIR . 'public/dashboard/rma.php' ));
        }

		$content = ob_get_clean();
		return $content;
	}


}