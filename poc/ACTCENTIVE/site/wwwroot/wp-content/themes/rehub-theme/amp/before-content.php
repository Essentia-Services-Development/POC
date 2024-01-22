<?php if ( ! defined( 'ABSPATH' ) ) {exit; }?>
<?php global $post;?>
<?php     
	$post_image_gallery = get_post_meta( $post->ID, 'rh_post_image_gallery', true );
	$post_image_videos = get_post_meta( $post->ID, 'rh_post_image_videos', true );
?>
<?php if(!function_exists( 'ampforwp_is_amp_endpoint' ) && (!empty($post_image_gallery) || !empty($post_image_videos)) ) :?>
<amp-accordion class="mt30 mb30">
	<?php if(!empty($post_image_gallery) ) :?>
		<section>
			<header class="rehub-amp-subheading text-center">
				<svg width="25" height="21" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M896 672q119 0 203.5 84.5t84.5 203.5-84.5 203.5-203.5 84.5-203.5-84.5-84.5-203.5 84.5-203.5 203.5-84.5zm704-416q106 0 181 75t75 181v896q0 106-75 181t-181 75h-1408q-106 0-181-75t-75-181v-896q0-106 75-181t181-75h224l51-136q19-49 69.5-84.5t103.5-35.5h512q53 0 103.5 35.5t69.5 84.5l51 136h224zm-704 1152q185 0 316.5-131.5t131.5-316.5-131.5-316.5-316.5-131.5-316.5 131.5-131.5 316.5 131.5 316.5 316.5 131.5z"/></svg>
				<span class="rehub-amp-subhead"><?php esc_html_e('Thumbnails', 'rehub-theme');?></span>
			</header>
			<div class="amp-section-thumbs">	
			    <?php $post_image_gallery = array_map('trim', explode(',', $post_image_gallery));?>
		        <?php foreach($post_image_gallery as $key=>$image_gallery):?>
		        	
		        	<?php if($image_gallery):?>                
		                <?php        
		                    $img = wp_get_attachment_image_src($image_gallery, 'full');
		                    $imgurl = $img[0]; 
		                    $imgwidth = $img[1];
		                    $imgheight = $img[2]; 
						?>
						<amp-img
						    on="tap:lightboxthumbs<?php echo esc_attr($key); ?>"
						    role="button"
						    tabindex="0"
						    layout="fixed"
						    aria-describedby="lightboxthumbcaption<?php echo esc_attr($key); ?>" 
						    src="<?php echo esc_url($imgurl); ?>" width=100 height=100></amp-img>
						    <span id="lightboxthumbcaption<?php echo esc_attr($key); ?>" class="rhhidden"><?php echo esc_attr(get_post_meta( $image_gallery, '_wp_attachment_image_alt', true));?></span>
						<amp-image-lightbox id="lightboxthumbs<?php echo esc_attr($key); ?>" layout="nodisplay"></amp-image-lightbox>                                                                         
		            <?php endif;?>                              
		        <?php endforeach;?>
	        </div>		
		</section>
	<?php endif;?>
	<?php if(!empty($post_image_videos) ) :?>
		<section>
			<header class="rehub-amp-subheading text-center">
				<svg width="21" height="21" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1792 352v1088q0 42-39 59-13 5-25 5-27 0-45-19l-403-403v166q0 119-84.5 203.5t-203.5 84.5h-704q-119 0-203.5-84.5t-84.5-203.5v-704q0-119 84.5-203.5t203.5-84.5h704q119 0 203.5 84.5t84.5 203.5v165l403-402q18-19 45-19 12 0 25 5 39 17 39 59z"/></svg>
				<span class="rehub-amp-subhead"><?php esc_html_e('Videos', 'rehub-theme');?></span>
			</header>
			<div class="amp-section-videos">	
			    <?php $post_image_videos = array_map('trim', explode(PHP_EOL, $post_image_videos));?>
		        <?php foreach($post_image_videos as $key=>$video):?>   	
		        	<?php if($video):?>                
						<amp-youtube
						    data-videoid="<?php echo parse_video_url(esc_url($video), 'url'); ?>"
						    layout="responsive"
						    width="480" height="270"></amp-youtube>
						<div class="mb20"></div>                                                                         
		            <?php endif;?>                              
		        <?php endforeach;?>	
	        </div>		
		</section>		
	<?php endif;?>
</amp-accordion>
<?php endif;?>

<?php /*POST OFFER BUTTON*/ ;?>
<?php
	$offer_url_exist = get_post_meta( $post->ID, 'rehub_offer_product_url', true );
	$offer_url = apply_filters('rh_post_offer_url_filter', $offer_url_exist );
 	$offer_price = get_post_meta( $post->ID, 'rehub_offer_product_price', true );
 	$offer_btn_text = get_post_meta( $post->ID, 'rehub_offer_btn_text', true );
 	$offer_price_old = get_post_meta( $post->ID, 'rehub_offer_product_price_old', true );
 	$offer_coupon = get_post_meta( $post->ID, 'rehub_offer_product_coupon', true );
 	$offer_coupon_date = get_post_meta( $post->ID, 'rehub_offer_coupon_date', true );
 	$domain = get_post_meta($post->ID, 'rehub_offer_domain', true );
?>
<?php $coupon_style = $expired = ''; if(!empty($offer_coupon_date)) : ?>
	<?php
		$timestamp1 = strtotime($offer_coupon_date);
		if(strpos($offer_coupon_date, ':') ===false){
			$timestamp1 += 86399;
		}
		$seconds = $timestamp1 - (int)current_time('timestamp',0);
		$days = floor($seconds / 86400);
		$seconds %= 86400;
		if ($days > 0) {
			$coupon_style = '';
			$coupon_text = $days.' '.__('days left', 'rehub-theme');
			$expired = 'no';
		}
		elseif ($days == 0){
			$coupon_text = esc_html__('Last day', 'rehub-theme');
			$coupon_style = '';
			$expired = 'no';			
		}
		else {
			$coupon_text = esc_html__('Expired', 'rehub-theme');
			$coupon_style = ' expired_coupon';
			$expired = '1';
		}
	?>
<?php endif ;?>
<?php do_action('post_change_expired', $expired); //Here we update our expired?>
<?php if (!empty($offer_price) || !empty($offer_url_exist)):?>
	<div class="rh-line mt20 mb20"></div>
	<?php if ($domain):?>    
	    <div class="mb10 compare-domain-icon text-center">
	    	<span><?php esc_html_e('Best deal at: ', 'rehub-theme');?></span> <span class="compare-domain-text fontbold"><?php echo esc_html($domain); ?></span>
	    </div>    	
	<?php endif ;?>    	
	<div class="single_priced_block_amp <?php echo esc_attr($coupon_style); ?>">
	    <?php if(!empty($offer_price)) : ?>
			<span class="single_price_count rehub-btn-font">
				<?php echo esc_html($offer_price) ?>
				<?php if($offer_price_old !='') :?> <del><?php echo esc_html($offer_price_old) ; ?></del><?php endif ;?>
			</span>
	    <?php endif ;?>
	    <div class="btn_block_part mb30">
	        <a href="<?php echo esc_url($offer_url) ?>" class="btn_offer_block re_track_btn rehub-btn-font" target="_blank" rel="nofollow">
	        <?php if($offer_btn_text !='') :?>
	        	<?php echo esc_html ($offer_btn_text); ?>
	        <?php elseif(rehub_option('rehub_btn_text') !='') :?>
	        	<?php echo rehub_option('rehub_btn_text') ; ?>
	        <?php else :?>
	        	<?php esc_html_e('Buy It Now', 'rehub-theme') ?>
	        <?php endif ;?>
	        </a>
			<?php if(!empty($offer_coupon)) : ?>
			  	<div class="rehub_offer_coupon not_masked_coupon <?php if(!empty($offer_coupon_date)) { echo esc_attr($coupon_style); } ?>" data-clipboard-text="<?php echo esc_attr($offer_coupon) ?>"><i class="rhicon rhi-scissors fa-rotate-180"></i><span class="coupon_text"><?php echo esc_html($offer_coupon) ?></span>
			  	</div>
		  	<?php endif;?>		    		
	        <?php if(!empty($offer_coupon_date)) {echo '<div class="time_offer">'.$coupon_text.'</div>';} ?>	            
	    </div>            	        
	</div>
<?php endif;?>