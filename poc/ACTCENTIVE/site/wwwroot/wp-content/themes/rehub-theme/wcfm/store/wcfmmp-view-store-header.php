<?php
/**
 * The Template for displaying all store header
 *
 * @package WCfM Markeplace Views Store Header
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$gravatar = $store_user->get_avatar();
$email    = $store_user->get_email();
$phone    = $store_user->get_phone(); 
$address  = $store_user->get_address_string(); 
$vendor_id = $store_user->get_id();
$titleposition = !empty($WCFMmp->wcfmmp_marketplace_options['store_name_position']) ? $WCFMmp->wcfmmp_marketplace_options['store_name_position'] : 'on_banner';
$store_lat    = isset( $store_info['store_lat'] ) ? esc_attr( $store_info['store_lat'] ) : 0;
$store_lng    = isset( $store_info['store_lng'] ) ? esc_attr( $store_info['store_lng'] ) : 0;

?>

<?php do_action( 'wcfmmp_store_before_header', $vendor_id ); ?>

<div id="wcfm_store_header" class="rh-container header_area rh-shadow3 rh-flex-columns rehub-sec-smooth tabletsblockdisplay">
	<div id="wcvendor_profile_logo" class="wcvendor_profile_cell text-center user-profile-div">
		<?php do_action( 'wcfmmp_store_before_avatar', $vendor_id ); ?>
		
		<img src="<?php echo esc_url($gravatar); ?>" alt="Logo"/>
		<div class="after_avatar text-center mb10">
			<?php do_action( 'wcfmmp_store_after_avatar', $vendor_id ); ?>
			<?php if( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) ) { $WCFMmp->wcfmmp_reviews->show_star_rating( 0, $vendor_id ); } ?>  
		</div>
		<?php if( $titleposition == 'on_header' ) { ?>
			<div class="lineheight25 margincenter mb15 profile-stats">
				<?php
					$count_likes = ( get_user_meta( $vendor_id, 'overall_post_likes', true) ) ? get_user_meta( $vendor_id, 'overall_post_likes', true) : 0;
					$count_wishes = ( get_user_meta( $vendor_id, 'overall_post_wishes', true) ) ? get_user_meta( $vendor_id, 'overall_post_wishes', true) : 0;
					$count_p_votes = (int)$count_likes + (int)$count_wishes;
					$totaldeals = count_user_posts( $vendor_id, $post_type = 'product' );
				?> 
				<div class="text-center pt5 pb5 pl10 pr10"><i class="rhicon rhi-heartbeat mr5 rtlml5"></i><?php esc_html_e( 'Product Votes', 'rehub-theme' ); echo ': ' . $count_p_votes; ?></div>
				<div class="text-center pt5 pb5 pl10 pr10"><i class="rhicon rhi-briefcase mr5 rtlml5"></i><?php esc_html_e( 'Total submitted', 'rehub-theme' ); echo ': ' . $totaldeals; ?></div>

			</div>	
		<?php } ?>		
	</div>
	<div id="wcvendor_profile_act_desc" class="wcvendor_profile_cell rh-flex-grow1">
		<div class="address">
			<?php if( $titleposition == 'on_header' ) { ?>
				<h1 class="wcfm_store_title fontbold"><?php echo apply_filters( 'wcfmmp_store_title', $store_info['store_name'], $vendor_id ); ?></h1>
			<?php } ?>

			<div class="wcfmmp_store_mobile_badges">
				<?php do_action( 'wcfmmp_store_mobile_badges', $vendor_id ); ?>
				<div class="spacer"></div>
				<?php if ( function_exists( 'mycred_get_users_badges' ) ) : ?>
                <div class="mycred_store_badges mb10">
                    <div>
                        <?php rh_mycred_display_users_badges( $vendor_id ) ?>
                    </div>
                </div>
                <div class="spacer"></div>
            	<?php endif; ?>					 
			</div>
			<div class="spacer"></div>				  
		  
			<?php do_action( 'before_wcfmmp_store_header_info', $vendor_id ); ?>
			<?php do_action( 'wcfmmp_store_before_address', $vendor_id ); ?>
			
			<?php if( $address && ( $store_info['store_hide_address'] == 'no' ) && wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_address' ) ) { ?>
				<p class="rh_header_store_name wcfmmp_store_header_address">
					<i class="wcfm rhicon rhi-map-marker-alt" aria-hidden="true"></i>
					<?php if( apply_filters( 'wcfmmp_is_allow_address_map_linked', true ) ) { 
						$map_search_link = 'https://google.com/maps/place/' . rawurlencode( $address ) . '/@' . $store_lat . ',' . $store_lng . '&z=16';
						if( wcfm_is_mobile() || wcfm_is_tablet() ) {
							$map_search_link = 'https://maps.google.com/?q=' . rawurlencode( $address ) . '&z=16';
						}
					?>
						<a href="<?php echo ''.$map_search_link; ?>" target="_blank"><span><?php echo esc_attr($address); ?></span></a>
					<?php } else { ?>
						<?php echo esc_attr($address); ?>
					<?php } ?>
				</p>
			<?php } ?>
			
			<?php do_action( 'wcfmmp_store_after_address', $vendor_id ); ?>
			
			<div>
				<?php do_action( 'wcfmmp_store_before_phone', $vendor_id ); ?>
				<?php if( $phone && ( $store_info['store_hide_phone'] == 'no' ) && wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_phone' ) ) { ?>
					<div class="store_info_parallal wcfmmp_store_header_phone mr10">
						<i class="wcfm rhicon rhi-phone" aria-hidden="true"></i>
						<span>
						<?php if( apply_filters( 'wcfmmp_is_allow_tel_linked', true ) ) { ?>
							<a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_attr($phone) ?></a>
						<?php } else { ?>
							<?php echo esc_attr($phone); ?>
						<?php } ?>
						</span>
					</div>
				<?php } ?>
				<?php do_action( 'wcfmmp_store_after_phone', $vendor_id ); ?>

				<?php do_action( 'wcfmmp_store_before_email', $vendor_id ); ?>
				<?php if( $email && ( $store_info['store_hide_email'] == 'no' ) && wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_email' ) ) { ?>
					<div class="store_info_parallal wcfmmp_store_header_email">
						<i class="wcfm rhicon rhi-envelope" aria-hidden="true"></i>
						<span>
						<?php if( apply_filters( 'wcfmmp_is_allow_mailto_linked', true ) ) { ?>
							<a href="mailto:<?php echo apply_filters( 'wcfmmp_mailto_email', $email, $store_user->get_id() ); ?>"><?php echo esc_html($email); ?></a>
						<?php } else { ?>
							<?php echo esc_html($email); ?>
						<?php } ?>
						</span>
					</div>
				<?php } ?>
				<div class="spacer"></div>  
			</div>
			
			<?php do_action( 'wcfmmp_store_after_email', $vendor_id ); ?>
			<?php do_action( 'after_wcfmmp_store_header_info', $vendor_id ); ?>
			<?php do_action( 'wcfmmp_before_store_tabs', $store_user->data, $store_info ); ?>
				
				<?php $WCFMmp->template->get_template( 'store/wcfmmp-view-store-tabs.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'store_tab' => $store_tab ) ); ?>
				
			<?php do_action( 'wcfmmp_after_store_tabs', $store_user->data, $store_info ); ?>  
		</div>
	</div>			
	<div id="wcvendor_profile_act_btns" class="wcvendor_profile_cell">
		  <?php do_action( 'before_wcfmmp_store_header_actions', $vendor_id ); ?>
		
			<?php do_action( 'wcfmmp_store_before_enquiry', $vendor_id ); ?>
			
			<?php if( apply_filters( 'wcfm_is_pref_enquiry', true ) && apply_filters( 'wcfm_is_pref_enquiry_button', true ) && apply_filters( 'wcfmmp_is_allow_store_header_enquiry', true ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'enquiry' ) ) { ?>
				<?php do_action( 'wcfmmp_store_enquiry', $vendor_id ); ?>
			<?php } ?>
			
			<?php do_action( 'wcfmmp_store_after_enquiry', $vendor_id ); ?>
			<?php do_action( 'wcfmmp_store_before_follow_me', $vendor_id ); ?>
			
			<?php 
			if( apply_filters( 'wcfm_is_pref_vendor_followers', true ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'vendor_follower' ) ) {
				do_action( 'wcfmmp_store_follow_me', $vendor_id );
			}
			?>
			
			<?php do_action( 'wcfmmp_store_after_follow_me', $vendor_id ); ?>
			
			<?php do_action( 'after_wcfmmp_store_header_actions', $vendor_id ); ?> 				
	</div>
  <div class="spacer"></div>    
</div>

<?php do_action( 'wcfmmp_store_after_header', $vendor_id ); ?>