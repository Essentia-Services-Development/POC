<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
<?php $author_ID = bp_displayed_user_id(); ?>
<div id="buddypress" class="register_wrap_type<?php echo bp_get_member_type($author_ID); ?>">
	<?php include(rh_locate_template('buddypress/members/single/cover-image-header.php')); ?> 
	<!-- CONTENT -->
	<div class="rh-container clearfix mb30">
		<?php
			do_action( 'bp_after_member_header' );
			do_action( 'template_notices' ); 
		?>	
		<div class="rh-mini-sidebar floatleft tabletblockdisplay mb0">
			<?php 
				if(function_exists('mycred_render_shortcode_my_balance')){
					if(rehub_option('rh_mycred_custom_points')){
						$custompoint = rehub_option('rh_mycred_custom_points');
						$mycredpoint = mycred_render_shortcode_my_balance(array('type'=>$custompoint, 'user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
						$mycredlabel = mycred_get_point_type_name($custompoint, false);
					}
					else{
						if(!rehub_option('cashback_points')){
							$mycredpoint = mycred_render_shortcode_my_balance(array('user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
							$mycredlabel = mycred_get_point_type_name('', false);							
						}
									
					}
			    	if(!empty($mycredpoint)){
			    		echo '<div class="rh-bp-mycred-profile-points rh-mycred-profile-points-gen mb20 rehub-main-color-bg rehub-sec-smooth"><div>';
			    			echo '<div class="rh-bp-mycred-title mb10">'.__('User Balance', 'rehub-theme').'<i class="rhicon rhi-walletbig floatright font150"></i></div>';
			    			echo '<div class="rh-bp-mycred-points-pr"><span>'.$mycredpoint.'</span>'.esc_html($mycredlabel).'</div>';
			    		echo '</div></div>';
			    	}					
					$cashpoint = 0;
					if(rehub_option('cashback_points') && function_exists('mycred_render_shortcode_my_balance') && (bp_is_my_profile() || current_user_can( 'manage_options' ))){
						$cashpoint = rehub_option('cashback_points');
						$cashpendingpoint = rehub_option('cashback_pending_points');
						$cashdeclinedpoint = rehub_option('cashback_declined_points');
						$mycashpoint = mycred_render_shortcode_my_balance(array('type'=>$cashpoint, 'user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
						$mycashlabel = mycred_get_point_type_name($cashpoint, false);
			    		echo '<div class="rh-bp-mycred-profile-points mb20 rehub-sec-color-bg rehub-sec-smooth"><div>';
			    			echo '<div class="rh-bp-mycred-title mb10">'.esc_html__('Cashback Balance', 'rehub-theme').'<i class="rhicon rhi-walletbig floatright font150"></i></div>';
			    			echo '<div class="rh-bp-mycred-points-pr"><span>'.$mycashpoint.'</span>'.esc_html($mycashlabel).'</div>';
			    			if($cashpendingpoint){
								$mycashpendingpoint = mycred_render_shortcode_my_balance(array('type'=>$cashpendingpoint, 'user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
								$mycashpendinglabel = mycred_get_point_type_name($cashpendingpoint, false);
								if($mycashpendingpoint){
									echo '<div class="rh-bp-mycred-points-pr mt5 font70"><span>'.$mycashpendingpoint.'</span>'.esc_html($mycashpendinglabel).'<span class="font90 fontnormal ml5 mr5"></span></div>';
								}			    				
			    			}
			    			if($cashdeclinedpoint){
								$mycashdeclinedpoint = mycred_render_shortcode_my_balance(array('type'=>$cashdeclinedpoint, 'user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
								$mycashdeclinedlabel = mycred_get_point_type_name($cashdeclinedpoint, false);
								if($mycashdeclinedpoint){
									echo '<div class="rh-bp-mycred-points-pr mt5 font70"><span>'.$mycashdeclinedpoint.'</span>'.esc_html($mycashdeclinedlabel).'<span class="font90 fontnormal ml5 mr5"></span></div>';
								}			    				
			    			}			    			
			    		echo '</div></div>';						
					}								    			
				}
			?>

			<?php 	
				$count_likes = ( get_user_meta( $author_ID, 'overall_post_likes', true) ) ? get_user_meta( $author_ID, 'overall_post_likes', true) : 0;
				$count_wishes = ( get_user_meta( $author_ID, 'overall_post_wishes', true) ) ? get_user_meta( $author_ID, 'overall_post_wishes', true) : 0;
				$count_p_votes = (int)$count_likes + (int)$count_wishes; 
				$is_vendor = '';
				
				if( class_exists( 'WeDevs_Dokan' ) ) {
					$is_vendor = dokan_is_user_seller( $author_ID );
					$shop_url = dokan_get_store_url( $author_ID );
					$sold_by = get_user_meta( $author_ID, 'dokan_store_name', true );
				}
	            elseif( defined('WCFMmp_TOKEN') ) {
	            	$is_vendor = wcfm_is_vendor( $author_ID );
	                $shop_url = wcfmmp_get_store_url( $author_ID );
	                $sold_by = get_user_meta( $author_ID, 'store_name', true );
	            }				
				elseif(defined('wcv_plugin_dir')) {
					$is_vendor = WCV_Vendors::is_vendor( $author_ID );
					$shop_url = WCV_Vendors::get_vendor_shop_page( $author_ID );
					$sold_by = WCV_Vendors::get_vendor_sold_by( $author_ID );
				}

			?>
			<?php if( $is_vendor ) : ?>
				<div class="rh-cartbox user-profile-div text-center widget rehub-sec-smooth">
					<div class="widget-inner-title rehub-main-font"><?php esc_html_e('Owner of shop', 'rehub-theme');?></div>
					<div class="mb20"><a href="<?php echo esc_url($shop_url); ?>"><img src="<?php echo rh_show_vendor_avatar($author_ID, 150, 150);?>" class="vendor_store_image_single" width=150 height=150 /></a>
					</div>
					<div class="profile-usertitle-name font110 fontbold mb20">
		            	<a href="<?php echo esc_url($shop_url);?>">
		            		<?php echo ''.$sold_by; ?>
		            	</a>	                    
		            </div>
		            <div class="lineheight25 margincenter mb10 profile-stats">
						<div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-heartbeat mr5 rtlml5"></i><?php esc_html_e( 'Product Votes', 'rehub-theme' ); echo ': ' . $count_p_votes; ?></div>
		                <div class="pt5 pb5 pl10 pr10"><i class="rhicon rhi-briefcase mr5 rtlml5"></i><?php esc_html_e( 'Total products', 'rehub-theme' ); echo ': ' . count_user_posts( $author_ID, $post_type = 'product' ); ?></div>	  	
		            </div>                    						
				</div>
			<?php endif;?>        		
		</div>		
		<div class="rh-mini-sidebar-content-area floatright tabletblockdisplay">
			<article> 
				<?php do_action( 'bp_before_member_home_content' ); ?>
				<div id="item-body" class="separate-item-bp-nav">
					<?php do_action( 'bp_before_member_body' ); ?>
					<?php
					if ( bp_is_user_activity() || !bp_current_component() ) :
						bp_get_template_part( 'members/single/activity' );
					elseif ( bp_is_user_blogs() ) :
						bp_get_template_part( 'members/single/blogs' );
					elseif ( bp_is_user_friends() ) :
						bp_get_template_part( 'members/single/friends' );
					elseif ( bp_is_user_groups() ) :
						bp_get_template_part( 'members/single/groups' );
					elseif ( bp_is_user_messages() ) :
						bp_get_template_part( 'members/single/messages' );
					elseif ( bp_is_user_profile() ) :
						bp_get_template_part( 'members/single/profile' );
					elseif ( bp_is_user_notifications() ) :
						bp_get_template_part( 'members/single/notifications' );
					elseif ( function_exists('bp_is_user_members_invitations') && bp_is_user_members_invitations() ) :
						bp_get_template_part( 'members/single/invitations' );
					elseif ( bp_is_user_settings() ) :
						bp_get_template_part( 'members/single/settings' );
					else :
						bp_get_template_part( 'members/single/plugins' );
					endif;
					?>
					<?php do_action( 'bp_after_member_body' ); ?>
				</div><!-- #item-body -->
				<?php do_action( 'bp_after_member_home_content' ); ?>
            </article>
		</div>
		<div class="rh-mini-sidebar floatleft tabletblockdisplay clearboxleft">
			<?php include(rh_locate_template('buddypress/members/single/profile-sidebar.php'));?>
		</div>
	</div>
	<!-- /CONTENT --> 
</div><!-- #buddypress -->    
<!-- FOOTER -->
<?php get_footer(); ?>