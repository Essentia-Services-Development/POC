<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wp_enqueue_script('rhcuttab');
$store_user = dokan()->vendor->get( get_query_var( 'author' ) );
$store_id = $store_user->get_id();
$store_rating = $store_user->get_rating();
$store_featured = $store_user->is_featured();
$map_location = $store_user->get_location();
// $store_trusted = $store_user->is_trusted();
$store_url = $store_user->get_shop_url();
$store_tnc_enable = $store_user->toc_enabled();
$store_tnc_content = $store_user->get_store_tnc();
$store_info = $store_user->get_shop_info();
$store_tabs = dokan_get_store_tabs( $store_id );
$store_address = dokan_get_seller_address( $store_id, TRUE );
$general_tnc_enable = dokan_get_option( 'seller_enable_terms_and_conditions', 'dokan_general', 'off' );

$dokan_store_time_enabled = isset( $store_info['dokan_store_time_enabled'] ) ? $store_info['dokan_store_time_enabled'] : '';
$store_open_notice        = isset( $store_info['dokan_store_open_notice'] ) && ! empty( $store_info['dokan_store_open_notice'] ) ? $store_info['dokan_store_open_notice'] : __( 'Store Open', 'rehub-theme' );
$store_closed_notice      = isset( $store_info['dokan_store_close_notice'] ) && ! empty( $store_info['dokan_store_close_notice'] ) ? $store_info['dokan_store_close_notice'] : __( 'Store Closed', 'rehub-theme' );
$show_store_open_close    = dokan_get_option( 'store_open_close', 'dokan_appearance', 'on' );

if ( !empty( $store_address['state'] ) && !empty( $store_address['country'] ) ) {
    $short_address = $store_address['state'] . ', ' . $store_address['country'];
} else if ( !empty( $store_address['country'] ) ) {
    $short_address = $store_address['country'];
} else {
    $short_address = '';
}

$store_address = apply_filters( 'dokan_store_header_adress', $short_address, $store_address );
?>
<div class="wcvendor_store_wrap_bg">
	<style scoped>#wcvendor_image_bg{<?php echo rh_show_vendor_bg($store_id); ?>}</style>
	<div id="wcvendor_image_bg">	
		<div id="wcvendor_profile_wrap">
			<div class="rh-container">
	    		<div id="wcvendor_profile_logo" class="wcvendor_profile_cell">
	    			<a href="<?php echo esc_url($store_url); ?>"><img src="<?php echo rh_show_vendor_avatar($store_id, 150, 150); ?>" class="vendor_store_image_single" width=150 height=150 /></a>
	    		</div>
	    		<div id="wcvendor_profile_act_desc" class="wcvendor_profile_cell">
	    			<div class="wcvendor_store_name" style="display: flex;align-items: center;gap: 10px;flex-wrap: wrap;">
						<?php if ($store_featured) : ?>
							<div class="wcv-verified-vendor">
								<i class="rhicon rhi-shield-check" aria-hidden="true"></i> <?php esc_html_e( 'Featured vendor', 'rehub-theme' ); ?>
							</div>
						<?php endif; ?>						
	    				<h1><?php echo esc_html( $store_user->get_shop_name() ); ?></h1> 
						<div class="wcv-badges-vendor" style="margin-bottom:12px">
							<?php apply_filters( 'dokan_store_header_after_store_name', $store_user ); ?>
						</div>
	    			</div>
	    			<div class="wcvendor_store_desc">
						<div class="wcvendor_store_stars woocommerce">
							<?php if (!empty($store_rating['count']) && $store_rating['count'] > 0):?>
								<?php echo rh_woo_rating_icons_wrapper('', $store_rating['rating'], $store_rating['count']); ?>
							<?php endif;?>
						</div>
                        <?php if ( dokan()->widgets->is_exists( 'store_location' ) && dokan_get_option( 'store_map', 'dokan_general', 'on' ) == 'on'  && ! empty( $map_location ) ) { ?>
                            <i class="rhicon rhi-map-marker-alt"></i> <?php echo esc_attr($store_address); ?>
							<span class="rehub_scroll" data-scrollto="#dokan-store-location"><?php esc_html_e( '(Show on map)', 'rehub-theme' ); ?></span>
                        <?php } ?>	
					</div>
                    <?php if ( $show_store_open_close == 'on' && $dokan_store_time_enabled == 'yes') : ?>
                        <div class="dokan-store-open-close mt10">
                            <i class="rhicon rhi-clock"></i>
                            <?php if ( dokan_is_store_open( $store_id ) ) {
                                echo esc_attr( $store_open_notice );
                            } else {
                                echo esc_attr( $store_closed_notice );
                            } ?>
                        </div>
                    <?php endif ?>
                    <?php do_action( 'dokan_store_header_info_fields',  $store_id ); ?>
	    		</div>	        			        		
	    		<div id="wcvendor_profile_act_btns" class="wcvendor_profile_cell">
	    			<span class="wpsm-button medium red act-rehub-login-popup"><?php echo getShopLikeButton( $store_id );?></span>	    			
				    <?php if ( class_exists( 'BuddyPress' ) ) :?>
				    	<?php if ( bp_loggedin_user_id() && bp_loggedin_user_id() != $store_id ) :?>
							<?php 
								if ( function_exists( 'bp_follow_add_follow_button' ) ) {
							        bp_follow_add_follow_button( array(
							            'leader_id'   => $store_id,
							            'follower_id' => bp_loggedin_user_id(),
							            'link_class'  => 'wpsm-button medium green'
							        ) );
							    }
							?>
						    <?php
						        if ( bp_is_active( 'messages' ) && dokan_get_option( 'contact_seller', 'dokan_general', 'on' ) != 'on' ) {
							    $link = (is_user_logged_in()) ? wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( $store_id )) : '#';
							    $class = ( !is_user_logged_in() && rehub_option( 'userlogin_enable' ) == '1' ) ? ' act-rehub-login-popup' : '';
							    echo ' <a href="'. $link .'" class="wpsm-button medium white'.$class.'">'. esc_html__( 'Contact vendor', 'rehub-theme' ) .'</a>';
						    }?>
					    <?php endif;?>
					<?php endif;?>
	    		</div>	        			
			</div>
		</div>
		<span class="wcvendor-cover-image-mask dokan-cover-image-mask"></span>
	</div>
	
	<div id="wcvendor_profile_menu">
		<div class="rh-container litesearchstyle">			
			<?php if ( $store_tabs ) { ?>
			<ul class="wcvendor_profile_menu_items">
            <?php foreach( $store_tabs as $key => $tab ) { ?>
				<li>
				<?php if( $key == 'terms_and_conditions' ){ ?>
					<?php if($general_tnc_enable == 'on' && $store_tnc_enable && !empty($store_tnc_content)) :?>
						<a href="<?php echo esc_url( $tab['url'] ); ?>" aria-controls="vendor-about" role="tab" data-toggle="tab" aria-expanded="true" data-scrollto="#vendor-about"><?php echo esc_attr($tab['title']); ?></a>
					<?php endif;?>
				<?php } else { ?>
					<a href="<?php echo esc_url( $tab['url'] ); ?>"><?php echo esc_attr($tab['title']); ?></a>
				<?php } ?>
				</li>
            <?php } ?>
			
			<?php do_action( 'dokan_after_store_tabs', $store_id ); ?>
			</ul>
			<?php } ?>
		</div>
	</div>
</div>