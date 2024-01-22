<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php wp_enqueue_script( 'rh-navgreedy', get_template_directory_uri() . '/js/navgreedy.js', array( 'jquery' ), 1.0, true );?>
<?php	
	$author_ID = bp_displayed_user_id();
	$seo_user_description = $phone = '';
	$count_comments = get_comments( array( 'user_id' => $author_ID, 'count' => true ) );
	$count_likes = ( get_user_meta( $author_ID, 'overall_post_likes', true) ) ? get_user_meta( $author_ID, 'overall_post_likes', true) : 0;
	$count_wishes = ( get_user_meta( $author_ID, 'overall_post_wishes', true) ) ? get_user_meta( $author_ID, 'overall_post_wishes', true) : 0;
	$count_p_votes = (int)$count_likes + (int)$count_wishes;
	$totaldeals = count_user_posts( $author_ID, $post_type = 'product' );
	$totalposts = count_user_posts( $author_ID, $post_type = 'post' );
	$totalsubmitted = $totaldeals + $totalposts;
	if(function_exists('mycred_get_users_rank')){
		if(rehub_option('rh_mycred_custom_points')){
			$custompoint = rehub_option('rh_mycred_custom_points');
			$mycredrank = mycred_get_users_rank($author_ID, $custompoint );
		}
		else{
			$mycredrank = mycred_get_users_rank($author_ID);		
		}
	}
	$membertype = bp_get_member_type($author_ID);
	$membertype_object = bp_get_member_type_object($membertype);
	$membertype_label = (!empty($membertype_object) && is_object($membertype_object)) ? $membertype_object->labels['singular_name'] : '';
	if(function_exists('bp_get_profile_field_data')){
		$profile_description = rehub_option('rh_bp_seo_description');
		$profile_phone = rehub_option('rh_bp_phone');
		if ($profile_description){
			$seo_user_description = bp_get_profile_field_data('field='. esc_html($profile_description));
		}	
		if ($profile_phone){
			$phone = bp_get_profile_field_data('field='. esc_html($profile_phone));
		}					
	}
?>
<div class="bprh_wrap_bg mb30">
	<div id="item-header" role="complementary">
		<?php
		/**
		 * Fires before the display of a member's header.
		 *
		 * @since 1.2.0
		 */
		do_action( 'bp_before_member_header' ); ?>
		<div id="rh-cover-image-container">
			<div id="rh-header-cover-image">
				<div id="rh-header-bp-content-wrap">
					<div class="rh-container" id="rhbp-header-profile-cont">		
						<div id="rh-header-bp-avatar">	
							<?php bp_displayed_user_avatar( 'type=full&width=140&height=140' ); ?>
						</div>
						<div id="rh-header-bp-content">
							<h2 class="user-nicename"> 
								<?php the_author_meta( 'nickname',$author_ID); ?>
								<?php if ($phone && preg_match('/\\d/', $phone) > 0): ?>
	                                <span class="bp_user_phone_details">
	                                    <i class="rhicon rhi-mobile-android-alt" aria-hidden="true"></i> <?php echo ''.$phone;?>
	                                </span>                   
	                            <?php endif; ?>									
								<?php if (!empty($mycredrank) && is_object( $mycredrank)) :?>
									<span class="rh-user-rank-mc rh-user-rank-<?php echo (int)$mycredrank->post_id; ?>"><?php echo esc_html($mycredrank->title) ;?></span>
								<?php endif;?>
								<?php if($membertype_label):?>
    								<span class="rh-user-rank-mc rh-user-rank-<?php echo ''.$membertype;?>"><?php echo ''.$membertype_label;?></span>
    							<?php endif; ?>							
							</h2>	
			            	<?php if ( function_exists( 'rh_mycred_display_users_badges' ) ) : ?>
				                <div class="rh-profile-achievements">
				                        <div>
				                            <?php rh_mycred_display_users_badges( $author_ID ) ?>
				                        </div>
				                </div>
				            <?php endif; ?>												            			
							<?php do_action( 'bp_before_member_header_meta' ); ?>	
							<div id="item-meta">	
								<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>			
									<span class="last-activity-profile"><?php esc_html_e( 'Last active', 'rehub-theme' );?>: <span><?php bp_last_activity( bp_displayed_user_id() ); ?></span></span>
								<?php endif; ?>
								<?php if ($seo_user_description): ?>
	                                <div class="bp_user_about_details">
	                                    <?php $desc_len = strlen(esc_html($seo_user_description));?>
	                                    <?php if ($desc_len > 180) :?>                           
	                                        <p><?php kama_excerpt('maxchar=180&text='.$seo_user_description); ?> <a href="<?php echo bp_core_get_user_domain($author_ID);?>profile/#item-body"><?php esc_html_e('(Read more)', 'rehub-theme');?></a></p>
	                                    <?php else :?>
	                                        <p><?php echo esc_html($seo_user_description); ?></p>
	                                    <?php endif;?>
	                                </div>                     
	                            <?php endif; ?> 			
								<?php do_action( 'bp_profile_header_meta' ); ?>	
								<?php if(function_exists('rehub_social_share')):?>
									<div class="share-profile-bp mt20">
										<?php esc_html_e('SHARE:', 'rehub-theme');?>
										<?php echo rehub_social_share('flat', false, false, 'user');?>
									</div>
								<?php endif;?>			
							</div>								
						</div>
			            <div id="rh-bp-profile-stats">              
			                <div><?php esc_html_e( 'Comments', 'rehub-theme' ); ?>: <span><?php echo (int)$count_comments;?></span></div>
			                <div><?php esc_html_e( 'Likes', 'rehub-theme' ); ?>: <span><?php echo (int)$count_p_votes;?></span></div>
			                <div><?php esc_html_e( 'Submitted', 'rehub-theme' ); ?>: <span><?php echo (int)$totalsubmitted;?></span></div>
							<?php
							if ( function_exists( 'bp_follow_total_follow_counts' ) ) :?>
							    <?php $count = bp_follow_total_follow_counts( array(
							        // change 5 to whatever user ID you need to fetch
							        'user_id' => $author_ID
							    ) );?>
							    <div><?php esc_html_e( 'Followers', 'rehub-theme' ); ?>: <span><?php echo (int)$count['followers']; ?></span></div>
							    <div><?php esc_html_e( 'Following', 'rehub-theme' ); ?>: <span><?php echo (int)$count['following']; ?></span></div>
							<?php endif;?>

			                <?php if(bp_is_active( 'friends' )) :?>
				                <div><?php esc_html_e( 'Friends', 'rehub-theme' ); ?>: <span><?php echo friends_get_total_friend_count();?></span></div>				 				
			                <?php endif;?> 
							<div><?php echo rehub_get_user_rate('admin', 'user', bp_displayed_user_id());?></div>			                   
			            </div>			
						<div id="rh-header-bp-content-btns">
							<div id="item-buttons">
								<?php do_action( 'bp_member_header_actions' ); ?>
							</div><!-- #item-buttons -->			
						</div>
					</div>
				</div>
				<span class="header-cover-image-mask"></span>	
			</div>
		</div><!-- #cover-image-container -->

		<div id="rhbp-iconed-menu">
			<div class="rh-container">
				<div id="item-nav">
					<div class="responsive-nav-greedy item-list-tabs no-ajax rh-flex-eq-height clearfix" id="object-nav" role="navigation">
						<ul class="rhgreedylinks">
							<?php bp_get_displayed_user_nav(); ?>
							<?php do_action( 'bp_member_options_nav' ); ?>
						</ul>
						<span class="togglegreedybtn rhhidden floatright ml5"><?php esc_html_e('More', 'rehub-theme');?></span>
						<ul class='hidden-links rhhidden'></ul>							
					</div>
				</div><!-- #item-nav -->
			</div>
		</div>
	</div><!-- #item-header -->
</div>