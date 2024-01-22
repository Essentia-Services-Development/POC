<?php 
/**
 * Display the vendor store page
 *
 * @package    WCVendors / WCVendors_Pro
 * @version    2.0.10 / 1.5.4 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
wp_enqueue_script('rhcuttab');
$vendormap = $verified_vendor = $verified_vendor_label = $wcfreephone = $wcfreeadress = $vacation_mode = $vacation_msg =''; 	
$vendor = get_userdata( $vendor_id );
$shop_url = WCV_Vendors::get_vendor_shop_page( $vendor_id );
$shop_name = get_user_meta( $vendor_id, 'pv_shop_name', true ); 
$seller_info = get_user_meta( $vendor_id, 'pv_seller_info', true );
$shop_description = do_shortcode(nl2br($vendor->pv_shop_description));
$vendor_email = $vendor->user_email;
$vendor_login = $vendor->user_login;
$vendor_name = $vendor->display_name;
$totaldeals = count_user_posts( $vendor_id, $post_type = 'product' );
if(function_exists('mycred_get_users_rank')){
	if(rehub_option('rh_mycred_custom_points')){
		$custompoint = rehub_option('rh_mycred_custom_points');
		$mycredrank = mycred_get_users_rank($vendor_id, $custompoint );
	}
	else{
		$mycredrank = mycred_get_users_rank($vendor_id);		
	}
}
if(function_exists('mycred_display_users_total_balance') && function_exists('mycred_render_shortcode_my_balance')){
	if(rehub_option('rh_mycred_custom_points')){
		$custompoint = rehub_option('rh_mycred_custom_points');
		$mycredpoint = mycred_render_shortcode_my_balance(array('type'=>$custompoint, 'user_id'=>$vendor_id, 'wrapper'=>'', 'balance_el' => '') );
		$mycredlabel = mycred_get_point_type_name($custompoint, false);
	}
	else{
		$mycredpoint = mycred_render_shortcode_my_balance(array('user_id'=>$vendor_id, 'wrapper'=>'', 'balance_el' => '') );
		$mycredlabel = mycred_get_point_type_name('', false);           
	}
}	
$count_p_votes = ( get_user_meta( $vendor_id, 'overall_post_likes', true) ) ? get_user_meta( $vendor_id, 'overall_post_likes', true) : 0;
$count_wishes = ( get_user_meta( $vendor_id, 'overall_post_wishes', true) ) ? get_user_meta( $vendor_id, 'overall_post_wishes', true) : 0;
$count_p_votes = (int)$count_p_votes + (int)$count_wishes;
if ( class_exists( 'WCVendors_Pro' ) ) {
	$vendor_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($vendor_id ) );
	$verified_vendor 	= ( array_key_exists( '_wcv_verified_vendor', $vendor_meta ) ) ? $vendor_meta[ '_wcv_verified_vendor' ] : false; 
	$verified_vendor_label 	= WCVendors_Pro::get_option( 'verified_vendor_label' );	
	$vacation_mode 		= get_user_meta( $vendor_id , '_wcv_vacation_mode', true ); 
	$vacation_msg 		= ( $vacation_mode ) ? get_user_meta( $vendor_id , '_wcv_vacation_mode_msg', true ) : '';	
	$company_url = get_user_meta( $vendor_id , '_wcv_company_url', true ); 
}	
else{
	$wcfreephone	= get_user_meta( $vendor_id, 'rh_vendor_free_phone', true );
	$wcfreeadress	= get_user_meta( $vendor_id, 'rh_vendor_free_address', true );
}
if (function_exists('gmw_get_user_address')){
		$vendormap = true;
		$address = gmw_get_user_address(array('user_id' => $vendor_id));
}else{
	$address = $wcfreeadress;
}

$active_contact_widget = is_active_widget( '', '', 'wcv_store_contact_widget' ) ? true : false;
$active_address_and_map = is_active_widget( '', '', 'wcv_store_address_and_map') ? true : false;
$active_short_description = is_active_widget( '', '', 'wcv_store_short_description' ) ? true : false;

?>
<div class="wcvendor_store_wrap_bg">
	<style scoped>#wcvendor_image_bg{<?php echo rh_show_vendor_bg($vendor_id);?>}</style>
	<div id="wcvendor_image_bg">	
		<div id="wcvendor_profile_wrap">
			<div class="rh-container">
	    		<div id="wcvendor_profile_logo" class="wcvendor_profile_cell">
						<img src="<?php echo rh_show_vendor_avatar($vendor_id, 150, 150); ?>" class="vendor_store_image_single" width=150 height=150 />        
	    		</div>
	    		<div id="wcvendor_profile_act_desc" class="wcvendor_profile_cell">
	    			<div class="wcvendor_store_name">
						<?php if ( $verified_vendor ) : ?>	   			
							<div class="wcv-verified-vendor">
								<i class="rhicon rhi-shield-check" aria-hidden="true"></i> <?php echo esc_attr($verified_vendor_label); ?>
							</div>
						<?php endif; ?>	    			
	    				<h1><?php echo esc_html($shop_name);?></h1> 	    				
	    			</div>
	    			<div class="wcvendor_store_desc">
					    <?php if ( class_exists( 'WCVendors_Pro' ) ) :?>
						    <div class="wcvendor_store_stars">
							    <?php if ( !WCVendors_Pro::get_option( 'wcvendors_ratings_management_cap' ) ) echo WCVendors_Pro_Ratings_Controller::ratings_link( $vendor_id, true );?>
						    </div>
						    <?php 
						    $address1 			= ( array_key_exists( '_wcv_store_address1', $vendor_meta ) ) ? $vendor_meta[ '_wcv_store_address1' ] : '';
						    $address2 			= ( array_key_exists( '_wcv_store_address2', $vendor_meta ) ) ? $vendor_meta[ '_wcv_store_address2' ] : '';
						    $city	 			= ( array_key_exists( '_wcv_store_city', $vendor_meta ) ) ? $vendor_meta[ '_wcv_store_city' ]  : '';
						    $state	 			= ( array_key_exists( '_wcv_store_state', $vendor_meta ) ) ? $vendor_meta[ '_wcv_store_state' ] : '';
						    $phone				= ( array_key_exists( '_wcv_store_phone', $vendor_meta ) ) ? $vendor_meta[ '_wcv_store_phone' ]  : '';
						    $store_postcode		= ( array_key_exists( '_wcv_store_postcode', $vendor_meta ) ) ? $vendor_meta[ '_wcv_store_postcode' ]  : '';

						    $twitter_username 	= get_user_meta( $vendor_id , '_wcv_twitter_username', true );
						    $instagram_username = get_user_meta( $vendor_id , '_wcv_instagram_username', true );
						    $facebook_url 		= get_user_meta( $vendor_id , '_wcv_facebook_url', true );
						    $linkedin_url 		= get_user_meta( $vendor_id , '_wcv_linkedin_url', true );
						    $youtube_url 		= get_user_meta( $vendor_id , '_wcv_youtube_url', true );
						    $googleplus_url 	= get_user_meta( $vendor_id , '_wcv_googleplus_url', true );
						    $pinterest_url 		= get_user_meta( $vendor_id , '_wcv_pinterest_url', true );	
						    $social_icons = empty( $twitter_username ) && empty( $instagram_username ) && empty( $facebook_url ) && empty( $linkedin_url ) && empty( $youtube_url ) && empty( $googleplus_url ) && empty( $pinterst_url ) ? false : true;
						    $address 			= ( $address1 != '') ? $address1 .', ' . $city .', '. $state .', '. $store_postcode : '';
						    ?>
							<?php if ($address) : ?>
								<i class="rhicon rhi-map-marker-alt"></i> <?php echo esc_html($address); ?>
								<?php if ( $active_address_and_map ) { ?>
								<span class="rehub_scroll" data-scrollto="#wcvendors_pro_map_widget"><?php esc_html_e( '(Show on map)', 'rehub-theme' ); ?></span>
								<?php } ?>
							<?php endif; ?>
						<?php else: ?>
							<?php if ($vendormap == true) :?>
								<?php echo esc_html($address); ?>
							<?php else:?>
								<?php echo esc_html($wcfreeadress); ?>
							<?php endif;?>
						<?php endif;?>				
					</div>
	    		</div>	        			        		
	    		<div id="wcvendor_profile_act_btns" class="wcvendor_profile_cell">
	    			<span class="wpsm-button medium red"><?php echo getShopLikeButton($vendor_id); ?></span>	    			
				    <?php if ( class_exists( 'BuddyPress' ) ) : ?>
				    	<?php if ( bp_loggedin_user_id() && bp_loggedin_user_id() != $vendor_id ) :?>
							<?php 
								if ( function_exists( 'bp_follow_add_follow_button' ) ) {
							        bp_follow_add_follow_button( array(
							            'leader_id'   => $vendor_id,
							            'follower_id' => bp_loggedin_user_id(),
							            'link_class'  => 'wpsm-button medium green'
							        ) );
							    }
							?>
						<?php endif; ?>
						<?php if ( bp_is_active('messages') && !$active_contact_widget ) { ?>
							<?php $link = (is_user_logged_in()) ? wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( $vendor_id)) : '#'; ?>
							<?php $class = (!is_user_logged_in() && rehub_option('userlogin_enable') == '1') ? ' act-rehub-login-popup' : ''; ?>
							<a href="<?php echo esc_url($link); ?>" class="wpsm-button medium white<?php echo esc_attr($class); ?>"><?php esc_html_e('Contact vendor', 'rehub-theme'); ?></a>
						<?php } ?>
					<?php endif; ?>
					<?php if ( $active_contact_widget ) { ?>
						<span class="wpsm-button rehub_scroll medium white" data-scrollto="#wcv_pro_quick_contact_form"><?php esc_html_e( 'Contact vendor', 'rehub-theme' ); ?></span>
					<?php } ?>
	    		</div>	        			
			</div>
		</div>
		<span class="wcvendor-cover-image-mask"></span>
	</div>
	<div id="wcvendor_profile_menu">
		<div class="rh-container litesearchstyle">			
			<?php 
			if(isset($_GET['rh_wcv_vendor_cat']) && !empty($_GET['rh_wcv_vendor_cat'])): 
				$search_in_cat = $_GET['rh_wcv_vendor_cat']; 
				$placeholder = esc_html__('Search in this category...', 'rehub-theme');
			else :
				$search_in_cat = '';
				$placeholder = esc_html__('Search in this shop...', 'rehub-theme');
			endif; 
			?>
			<form id="wcvendor_search_shops" role="search" action="<?php echo esc_url($shop_url); ?>" method="get" class="wcvendor-search-inside search-form">
				<input type="text" id="wcv-store-search-field-<?php echo esc_attr($vendor_id); ?>" name="rh_wcv_search" placeholder="<?php echo esc_html($placeholder); ?>" value="<?php echo get_search_query(); ?>">
				<?php if($search_in_cat): ?>
					<input type="hidden" name="rh_wcv_vendor_cat" value="<?php echo sanitize_text_field($search_in_cat); ?>">
				<?php endif; ?>
				<button type="submit" class="btnsearch"><i class="rhicon rhi-search"></i></button>					
			</form>
			<ul class="wcvendor_profile_menu_items">		
				<li class="active"><a href="#vendor-items" aria-controls="vendor-items" role="tab" data-toggle="tab" aria-expanded="true"><?php esc_html_e('Items', 'rehub-theme');?></a></li>
				<?php if ( class_exists( 'WCVendors_Pro' ) ) :?>
					<?php $feedback_form_page = WCVendors_Pro::get_option( 'feedback_page_id' );?>
					<?php if ( $feedback_form_page ) :?>
						<?php $url = apply_filters( 'wcv_ratings_link_url', WCVendors_Pro_Vendor_Controller::get_vendor_store_url($vendor_id) . 'ratings/' ); ?>
						<li><a href="<?php echo esc_url($url); ?>"><?php esc_html_e('Reviews', 'rehub-theme');?></a></li>	
					<?php endif;?>
				<?php endif;?>
				<?php if( !$active_short_description ) : ?>
				<li><a href="#vendor-about" aria-controls="vendor-about" role="tab" data-toggle="tab" aria-expanded="true" data-scrollto="#vendor-about"><?php esc_html_e('About', 'rehub-theme');?></a>
				</li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>

<!-- CONTENT -->
<div class="rh-container wcvcontent"> 
    <div class="rh-content-wrap clearfix">
	    <!-- Main Side -->
	    <div class="rh-mini-sidebar-content-area woocommerce page clearfix floatright tabletblockdisplay">
	        <article class="post" id="page-<?php the_ID(); ?>">
	        	<?php do_action( 'woocommerce_before_main_content' ); ?>
	        	<?php if ($vacation_msg) :?>
	        		<div class="wpsm_box green_type nonefloat_box">
	        			<div>
	        				<?php echo wp_kses_data($vacation_msg); ?>
						</div>
					</div>
	        	<?php endif;?>
	        	<div role="tabvendor" class="tab-pane active" id="vendor-items">
				<?php if ( have_posts() ) : ?>
					<?php
						/**
						 * woocommerce_before_shop_loop hook
						 *
						 * @hooked woocommerce_result_count - 20
						 * @hooked woocommerce_catalog_ordering - 30
						 */
						do_action( 'woocommerce_before_shop_loop' );
					?>
					<?php $classes = array();?>
					<?php 
						if(rehub_option('width_layout') == 'extended'){
							$classes[] = 'col_wrap_fourth';
						}
						else{
							$classes[] = 'col_wrap_three';
						}
					?>	
					<?php $current_design = rehub_option('woo_design');?>				
					<?php if ($current_design == 'grid') {
						$classes[] = 'rh-flex-eq-height';
					}
					elseif ($current_design == 'list' || $current_design == 'deallist') {
						$classes[] = 'list_woo';
						if ($current_design == 'deallist') {
							$classes[] = 'woo_offer_list';
						}
					}
					elseif ($current_design == 'gridtwo'){
						echo rh_generate_incss('offergrid');
					    $classes[] = 'eq_grid pt5 rh-flex-eq-height';
					}
					elseif ($current_design == 'gridmart'){
						echo rh_generate_incss('gridmart');
					    $classes[] = 'grid_mart rh-flex-eq-height';
					}
					elseif ($current_design == 'gridrev' || $current_design == 'griddigi') {
						$classes[] = 'rh-flex-eq-height woogridrev';
					}										
					else {
						$classes[] = 'column_woo';
					}
					?>					
					<div class="products <?php echo implode(' ',$classes);?>">
						<?php while ( have_posts() ) : the_post(); ?>
							<?php 
								if(rehub_option('width_layout') == 'extended'){
									$columns = '4_col';
								}
								else{
									$columns = '3_col';
								}
							?>						
							<?php if ($current_design == 'list'){
							    include(rh_locate_template('inc/parts/woolistmain.php'));
							}
							elseif ($current_design == 'grid'){
							    include(rh_locate_template('inc/parts/woogridpart.php'));
							}
							elseif ($current_design == 'gridmart'){
							    include(rh_locate_template('inc/parts/woogridmart.php'));
							}
							elseif ($current_design == 'deallist'){
							    include(rh_locate_template('inc/parts/woolistpart.php'));
							}
							elseif ($current_design == 'gridrev'){
								include(rh_locate_template('inc/parts/woogridrev.php'));
							}
							elseif ($current_design == 'griddigi'){
								include(rh_locate_template('inc/parts/woogriddigi.php'));
							}							
							elseif ($current_design == 'gridtwo'){
							    include(rh_locate_template('inc/parts/woogridcompact.php'));
							}							
							else{
								$custom_col = 'yes'; 
								$custom_img_height = 284; 
								$custom_img_width = 284; 								
							    include(rh_locate_template('inc/parts/woocolumnpart.php'));
							} ?>
						<?php endwhile; // end of the loop. ?>
					</div>
					<?php
						/**
						 * woocommerce_after_shop_loop hook
						 *
						 * @hooked woocommerce_pagination - 10
						 */
						do_action( 'woocommerce_after_shop_loop' );
					?>
				<?php else : ?>
					<?php wc_get_template( 'loop/no-products-found.php' ); ?>
				<?php endif; ?>
				</div>
				<?php if( !$active_short_description ) : ?>
				<div role="tabvendor" class="tab-pane" id="vendor-about">
					<?php echo wp_kses_post($shop_description); ?>
				</div>
				<?php endif; ?>
				
				<?php do_action( 'woocommerce_after_main_content' ); ?>				
				
			</article>
		</div>	    
	    <aside class="rh-mini-sidebar user-profile-div floatleft tabletblockdisplay">
	    	<div class="rh-cartbox widget">
	            <div>
	            	<div class="widget-inner-title rehub-main-font"><?php esc_html_e('Shop owner:', 'rehub-theme');?></div>
	                <div class="profile-avatar text-center">
	                    <?php echo get_avatar( $vendor_email, '128', '', $vendor_name ); ?>
	                </div>
	                <div class="profile-usertitle text-center mt20">
	                    <div class="profile-usertitle-name font110 fontbold mb20">
	                    <?php if ( function_exists('bp_core_get_user_domain') ) : ?>
	                    	<a href="<?php echo bp_core_get_user_domain( $vendor_id ); ?>">
	                    <?php endif;?>
	                        <?php echo esc_html($vendor_name); ?> 						
	                        <?php 	
								if (function_exists('bp_get_member_type')){			
									$membertype = bp_get_member_type($vendor_id);
									$membertype_object = bp_get_member_type_object($membertype);
									$membertype_label = (!empty($membertype_object) && is_object($membertype_object)) ? $membertype_object->labels['singular_name'] : '';
									if($membertype_label){
										echo '<span class="rh-user-rank-mc rh-user-rank-'.$membertype.'">'.$membertype_label.'</span>';
									}
								}
							?>
	                        <?php if ( function_exists('bp_core_get_user_domain') ) : ?></a><?php endif;?>
	                    </div>
	                </div>
	                <div class="lineheight25 margincenter mb10 profile-stats">
                    <div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-user mr5 rtlml5"></i> <?php esc_html_e( 'Registration', 'rehub-theme' );  echo ': ' .date_i18n( get_option( "date_format" ), strtotime( $vendor->user_registered )); ?></div>
	                    <div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-heartbeat mr5 rtlml5"></i><?php esc_html_e( 'Product Votes', 'rehub-theme' ); echo ': ' . $count_p_votes; ?></div>
	                    <div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-briefcase mr5 rtlml5"></i><?php esc_html_e( 'Total submitted', 'rehub-theme' ); echo ': ' . $totaldeals; ?></div>
	                    <?php if (!empty($mycredpoint)) :?><div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-chart-bar mr5 rtlml5"></i><?php echo esc_html($mycredlabel); ?>: <?php echo esc_attr($mycredpoint); ?> </div><?php endif; ?>
	                </div>
					<?php if ( class_exists( 'WCVendors_Pro' ) ) : ?>
						<div class="profile-description lineheight25 margincenter mb10">
							<div class="pt5 pb5 pl10 pr10">
								<span class="border-grey-bottom blockstyle width-100p mb5 fontbold"><?php esc_html_e( 'Contact', 'rehub-theme' ); ?></span>
								<p class="fontitalic font80">
								<?php if( !$active_address_and_map ) : ?>
									<?php if( $address || $wcfreeadress ) : ?>
										<?php $address = empty( $address ) ? $wcfreeadress : $address; ?>
										<?php echo esc_html($address); ?><br />
									<?php endif; ?>
								<?php endif; ?>
								<?php if( !$active_contact_widget ) : ?>
									<?php if ($phone):?>
										<a href="tel:<?php echo esc_attr($phone); ?>"><i class="rhicon rhi-mobile-android-alt"></i> <?php echo esc_html($phone); ?></a><br />
									<?php endif;?>
									<?php if ($company_url):?>
										<a href="<?php echo esc_url($company_url); ?>" target="_blank" rel="nofollow"><i class="rhicon rhi-globe"></i> <?php echo esc_url($company_url); ?></a>
									<?php endif;?>
								<?php endif; ?>
								</p>
							</div>
						</div>
						<?php if ($social_icons):?>
			                <div class="profile-socbutton lineheight25 margincenter mb10">
			                    <div class="social_icon small_i pt5 pb5 pl10 pr10">
				                    <?php if ( $facebook_url != '') { ?><a href="<?php echo esc_url($facebook_url); ?>" target="_blank" class="author-social fb" rel="nofollow"><i class="rhicon rhi-facebook"></i></a><?php } ?>
				                    <?php if ( $instagram_username != '') { ?><a href="//instagram.com/<?php echo esc_attr($instagram_username); ?>" target="_blank" class="author-social fb" rel="nofollow"><i class="rhicon rhi-instagram"></i></a><?php } ?>
				                    <?php if ( $twitter_username != '') { ?><a href="//twitter.com/<?php echo esc_attr($twitter_username); ?>" target="_blank" class="author-social tw" rel="nofollow"><i class="rhicon rhi-twitter"></i></a><?php } ?>
				                    <?php if ( $googleplus_url != '') { ?><a href="<?php echo esc_url($googleplus_url); ?>" target="_blank" class="author-social gp" rel="nofollow"><i class="rhicon rhi-google-plus"></i></a><?php } ?>
				                    <?php if ( $pinterest_url != '') { ?><a href="<?php echo esc_url($pinterest_url); ?>" target="_blank" class="author-social gp" rel="nofollow"><i class="rhicon rhi-pinterest"></i></a><?php } ?>
				                    <?php if ( $youtube_url != '') { ?><a href="<?php echo esc_url($youtube_url); ?>" target="_blank" class="author-social yt" rel="nofollow"><i class="rhicon rhi-youtube"></i></a><?php } ?>
				                    <?php if ( $linkedin_url != '') { ?><a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" class="author-social fb" rel="nofollow"><i class="rhicon rhi-linkedin"></i></a><?php } ?>
			                     </div>
			                </div>
		           		<?php endif;?>
		           	<?php else:?>
						<div class="profile-description lineheight25 margincenter mb10">
							<div class="pt5 pb5 pl10 pr10">
								<span class="border-grey-bottom blockstyle width-100p mb5 fontbold"><?php esc_html_e( 'Contact', 'rehub-theme' ); ?></span>
								<p class="fontitalic font80">
									<?php if( $address ) : ?>
										<?php echo esc_html($address); ?><br />
									<?php endif; ?>
									<?php if ($wcfreephone):?>
										<a href="tel:<?php echo esc_html($wcfreephone); ?>"><i class="rhicon rhi-mobile-android-alt"></i> <?php echo esc_html($wcfreephone); ?></a>
									<?php endif;?>
								</p>
							</div>
						</div>		           		
					<?php endif;?>
	            <?php if ( !empty( $vendor->description ) ) : ?>
	                <div class="profile-description lineheight25 margincenter mb10">
	                    <div class="pt5 pb5 pl10 pr10">
	                        <span class="border-grey-bottom blockstyle width-100p mb5 fontbold"><?php esc_html_e( 'About author', 'rehub-theme' ); ?></span>
	                        <p class="fontitalic font80"><?php echo wp_kses_data($vendor->description); ?></p>
	                    </div>
	                </div>
	            <?php endif; ?>
	            <?php if ( function_exists( 'mycred_get_users_badges' ) ) : ?>
	                <div class="profile-achievements mb15 text-center"><div><?php rh_mycred_display_users_badges( $vendor_id ) ?></div></div>
	            <?php endif; ?>
                <?php if ( function_exists('bp_core_get_user_domain') ) : ?>
                	<?php if ( bp_is_active( 'xprofile' ) ) : ?>
						<?php if ( bp_has_profile( array( 'profile_group_id' => 1, 'fetch_field_data' => true, 'user_id'=>$vendor_id ) ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
							<?php $numberfields = explode(',', bp_get_the_profile_field_ids());?>
							<?php $count = (!empty($numberfields) && is_array($numberfields)) ? count($numberfields) : '';?>
							<?php $bp_profile_description = rehub_option('rh_bp_seo_description');?>
							<?php $bp_profile_phone = rehub_option('rh_bp_phone');	?>

							<?php if ($count > 1) :?>
								<ul id="xprofile-in-wcstore">
									<?php $fieldid = 0; while ( bp_profile_fields() ) : bp_the_profile_field(); $fieldid++; ?>
										<?php if ($fieldid == 1) continue;?>
										<?php $fieldname = bp_get_the_profile_field_name();?>
										<?php if($fieldname == $bp_profile_phone) continue;?>
										<?php if($fieldname == $bp_profile_description) continue;?>
										<?php if ( bp_field_has_data() ) : ?>
											<li>
												<div class="floatleft mr5"><?php echo esc_html($fieldname); ?>: </div>
												<div class="floatleft"><?php bp_the_profile_field_value() ?></div>	
											</li>
										<?php endif; ?>
									<?php endwhile; ?>
								</ul>
							<?php endif; ?>
						<?php endwhile; endif; ?>
                	<?php endif;?>
                    <div class="profile-usermenu mt20">
	                    <ul class="user-menu-tab pt5 pb5 pr10 pl10" role="tablist">
	                        <li class="text-center">
	                            <a href="<?php echo bp_core_get_user_domain( $vendor_id ); ?>" class="position-relative blockstyle pt10 pb10 pl15 pr15"><i class="rhicon rhi-folder-open mr5 rtlml5"></i><?php esc_html_e( 'Show full profile', 'rehub-theme' ); ?></a>
	                        </li>
	                    </ul>
                    </div>
                <?php endif; ?>
	            </div>	    		
	    	</div>
			
	        <?php if ( is_active_sidebar( 'wcw-storepage-sidebar' ) ) : ?>
	            <?php dynamic_sidebar( 'wcw-storepage-sidebar' ); ?>
	        <?php endif;?>
	    </aside>
		<!-- /Main Side --> 
    </div>
</div>
<!-- /CONTENT -->


<?php get_footer(); ?>