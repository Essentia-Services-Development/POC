<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php


if( !function_exists('rehub_get_woo_offer') ) {
function rehub_get_woo_offer($review_woo_link, $title_tag='h3'){
	?>
	<?php $review_woo_link = trim($review_woo_link);?>
	<?php global $woocommerce; if($woocommerce && $review_woo_link) :?>
		<?php
			$args = array(
				'post_type' 		=> 'product',
				'posts_per_page' 	=> 1,
				'no_found_rows' 	=> 1,
				'post_status' 		=> 'publish',
				'p'					=> $review_woo_link,

			);
		?>
		<?php wp_enqueue_script('rhwootabs', get_template_directory_uri() . '/js/wootabs.js', array('jquery'), '1.0', true);?>
		<?php $products = new WP_Query( $args ); if ( $products->have_posts() ) : ?>
    		<?php while ( $products->have_posts() ) : $products->the_post(); global $product; ?>
    			<?php $random_key = rand(0, 100);?>
				<?php $offer_price = $product->get_price_html() ?>
	            <?php $woolink = ($product->get_type() =='external' && $product->add_to_cart_url() !='') ? $product->add_to_cart_url() : get_post_permalink($review_woo_link) ;?>
	            <?php $offer_title = $product->get_title(); $woo_aff_links_inreview = '' ?>
	            <?php $attributes = $product->get_attributes();  ?>
	            <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = esc_html__('Buy It Now', 'rehub-theme') ;?><?php endif ;?>
	            <?php $gallery_images = $product->get_gallery_image_ids(); ?>
	            <?php if (defined('\ContentEgg\PLUGIN_PATH')) :?>
	            	<?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($review_woo_link);?>
	            	<?php if(!empty($itemsync)){$woo_aff_links_inreview = 1;}?>
	            <?php endif;?>
	            <?php $offer_coupon = get_post_meta( $review_woo_link, 'rehub_woo_coupon_code', true ) ?>
	            <?php $offer_coupon_date = get_post_meta( $review_woo_link, 'rehub_woo_coupon_date', true ) ?>
	            <?php $offer_coupon_mask = get_post_meta( $review_woo_link, 'rehub_woo_coupon_mask', true ) ?>
	            <?php $offer_coupon_url = esc_url( $product->add_to_cart_url() ); ?>
	            <?php $post_image_videos = get_post_meta( $review_woo_link, 'rh_product_video', true ); ?>
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
					  $coupon_text = $days.' '.__('days left', 'rehub-theme');
					  $coupon_style = '';
					  $expired = 'no';
					}
					elseif ($days == 0){
					  $coupon_text = esc_html__('Last day', 'rehub-theme');
					  $coupon_style = '';
					  $expired = 'no';
					}
					else {
					  $coupon_text = esc_html__('Expired', 'rehub-theme');
					  $coupon_style = 'expired_coupon';
					  $expired = '1';
					}
					?>
	          	<?php endif ;?>
				<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?>
				<?php $reveal_enabled = ($coupon_mask_enabled =='1') ? ' reveal_enabled' : '';?>
				<?php $outsidelinkpart = ($coupon_mask_enabled=='1') ? ' data-codeid="'.$review_woo_link.'" data-dest="'.$offer_coupon_url.'" data-clipboard-text="'.$offer_coupon.'" class="masked_coupon"' : '';?>									            
    			<div class="rehub_woo_review clearbox woocommerce">
    				<?php if (!empty ($attributes) || !empty ($gallery_images) || !empty ($woo_aff_links_inreview)) :?>
    					<ul class="rehub_woo_tabs_menu">
				            <li><?php esc_html_e('Product', 'rehub-theme') ?></li>
				            <?php if (!empty ($attributes)) :?><li><?php esc_html_e('Specification', 'rehub-theme') ?></li><?php endif ;?>
				            <?php if (!empty ($gallery_images)) :?><li><?php esc_html_e('Photos', 'rehub-theme') ?></li><?php endif ;?>
				            <?php if (!empty ($post_image_videos)) :?><li><?php esc_html_e('Videos', 'rehub-theme') ?></li><?php endif ;?>				            
				            <?php if (!empty ($woo_aff_links_inreview)) :?><li class='woo_deals_tab'><?php esc_html_e('Deals', 'rehub-theme') ?></li><?php endif ;?>
						</ul>
						<?php endif ;?>
						<div class="rehub_feat_block rh_actions_padd <?php echo ''.$reveal_enabled.$coupon_style;?>">
							<div class="rehub_woo_review_tabs">								
					            <div class="rh_grid_image_3_col">			            
						            <div class="rh_gr_img_first offer_thumb width-150 rh-flex-center-align rh-flex-justify-center mr0 ml0 mb0 mt0">
						            	<a href="<?php echo esc_url($woolink) ;?>" target="_blank" rel="nofollow" class="re_track_btn">
											<?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?>
						            	</a>
						            </div>
									<div class="rh_gr_top_middle">										
						            	<<?php echo ''.$title_tag?> class="font115 mb15 mt0 mobfont110 lineheight25"><a href="<?php echo esc_url($woolink) ;?>" target="_blank" rel="nofollow" class="re_track_btn"><?php echo rh_expired_or_not($product->get_id(), 'span');?><?php echo esc_attr($offer_title) ;?></a></<?php echo ''.$title_tag?>>
						                <?php $loop_code_zone = rehub_option('woo_code_zone_loop');?>        
						                <?php if ($loop_code_zone):?>
						                    <div class="woo_code_zone_loop mb10">
						                        <?php echo do_shortcode($loop_code_zone);?>
						                    </div>
						                <?php endif;?>						            	
						                <?php if ($product->get_price() !='') : ?>
						                <?php echo '<span class="pricefont110 rehub-main-color mobpricefont90 fontbold mb10 mr10 lineheight20 floatleft"><span class="price">'.$product->get_price_html().'</span></span>';?>
						                <?php endif ;?>
						                <?php 
						                    if($product->is_on_sale() && $product->get_regular_price() && $product->get_price() > 0 && !$product->is_type( 'variable' )){
						                        $offer_price_calc = (float) $product->get_price();
						                        $offer_price_old_calc = (float) $product->get_regular_price();
						                        $sale_proc = 0 -(100 - ($offer_price_calc / $offer_price_old_calc) * 100); 
						                        $sale_proc = round($sale_proc);
						                        echo '<span class="rh-label-string mr10 mb5 floatleft">'.$sale_proc.'%</span>';
						                    }

						                ?>                                             
						                <div class="clearfix"></div>						            	
						            </div>
							        <div class="rh_gr_middle_desc font80 lineheight20">
							            <?php kama_excerpt('maxchar=150&save_tags=<ul><li>'); ?>
										<div class="woo-button-actions-area tabletblockdisplay pt5 border-top mt15">
											<?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
											<?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
											<?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
											<?php echo RH_get_wishlist($review_woo_link, $wishlistadd, $wishlistadded, $wishlistremoved);?>
											<?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>           
												<?php 
													$cmp_btn_args = array(); 
													$cmp_btn_args['class']= 'rhwoosinglecompare';
													if(rehub_option('compare_woo_cats') != '') {
														$cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
													}
												?>                                                  
												<?php echo wpsm_comparison_button($cmp_btn_args); ?> 
											<?php endif;?>                      
										</div> 
							        </div> 					            
						            <div class="rh_gr_btn_block">					            	
							            <div class="priced_block clearfix">
							                <div>
							                	<?php if ($product->get_type() =='external' && $product->add_to_cart_url() ==''  && !empty ($woo_aff_links_inreview)) :?>
							                		<a class='btn_offer_block choose_offer_woo' href="#"><?php esc_html_e('Check Deals', 'rehub-theme') ;?></a>
							                	<?php else :?>

								                    <?php if ( $product->is_in_stock() &&  $product->add_to_cart_url() !='') : ?>
								                        <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
								                            sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn woo_loop_btn btn_offer_block %s %s product_type_%s"%s%s>%s</a>',
								                            esc_url( $product->add_to_cart_url() ),
								                            esc_attr( $review_woo_link ),
								                            esc_attr( $product->get_sku() ),
								                            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
								                            $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
								                            esc_attr( $product->get_type() ),
								                            $product->get_type() =='external' ? ' target="_blank"' : '',
								                            $product->get_type() =='external' ? ' rel="nofollow"' : '',
								                            esc_html( $product->add_to_cart_text() )
								                            ),
								                        $product );?>
								                    <?php endif; ?> 

								                    <?php if ($coupon_mask_enabled =='1') :?>
								                        <?php wp_enqueue_script('zeroclipboard'); ?>                
								                        <a class="woo_loop_btn coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" href="<?php echo esc_url($woolink); ?>"<?php if ($product->get_type() =='external'){echo ' target="_blank" rel="nofollow"'; echo ''.$outsidelinkpart; } ?>>
								                            <?php if(rehub_option('rehub_mask_text') !='') :?><?php echo rehub_option('rehub_mask_text') ; ?><?php else :?><?php esc_html_e('Reveal coupon', 'rehub-theme') ?><?php endif ;?>
								                        </a>
								                    <?php else :?> 
								                        <?php if(!empty($offer_coupon)) : ?>
								                            <?php wp_enqueue_script('zeroclipboard'); ?>
								                            <div class="rehub_offer_coupon not_masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_html($offer_coupon) ?>">
								                                <i class="rhicon rhi-scissors fa-rotate-180"></i>
								                                <span class="coupon_text"><?php echo esc_html($offer_coupon) ?></span>
								                            </div>
								                        <?php endif ;?>                                               
								                    <?php endif;?>
								                    <?php if(!empty($offer_coupon_date)) {echo '<div class="time_offer">'.$coupon_text.'</div>';} ?>
		            							<?php endif; ?>
							                </div>
							            </div>
								        <div class="brand_logo_small">       
								        	<?php WPSM_Woohelper::re_show_brand_tax('list'); //show brand taxonomy?>
								        </div>
						            </div>
				        		</div>
			        		</div>
			        		<?php if (!empty ($attributes)) :?>
					        	<div class="rehub_woo_review_tabs">
					     			<div><?php wc_display_product_attributes($product) ;?></div>

					        	</div>
				        	<?php endif ;?>
			        		<?php if (!empty ($gallery_images)) :?>
			        			<?php wp_enqueue_script('modulobox'); wp_enqueue_style('modulobox');?>
					        	<div class="rehub_woo_review_tabs pretty_woo modulo-lightbox">
	                                <?php foreach($gallery_images as $key=>$image_gallery):?>
	                                    <?php if(!$image_gallery) continue;?>
	                                    <a data-rel="rhwoobox_<?php echo ''.$random_key;?>" data-thumb="<?php echo wp_get_attachment_url($image_gallery);?>" href="<?php echo wp_get_attachment_url($image_gallery);?>" target="_blank" class="mb10" data-title="<?php echo esc_attr(get_post_field( 'post_excerpt', $image_gallery));?>"> 
	                                        <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'src'=> wp_get_attachment_url($image_gallery), 'crop'=> false, 'height'=> 60));?>
	                                    </a>                               
	                                <?php endforeach;?>
					        	</div>
				        	<?php endif ;?>
				            <?php if (!empty ($post_image_videos)) :?>
				            	<div class="rehub_woo_review_tabs">
				            		<?php woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&title=no');?>
				            	</div>
				            <?php endif ;?>				        	
				        	<?php if (!empty ($woo_aff_links_inreview)) :?>
				        		<div class="rehub_woo_review_tabs">
									<?php echo do_shortcode('[content-egg-block template=custom/all_offers_logo post_id="'.$review_woo_link.'"]' );?>
				        		</div>
				        	<?php endif ;?>
		        		</div>
		        </div>
		        <div class="clearfix"></div>	        

    		<?php endwhile; endif;  wp_reset_query(); ?>

	<?php endif ;?>
	<?php
}
}

if( !function_exists('rehub_get_woo_list') ) {
function rehub_get_woo_list( $data_source = '', $type ='', $cat = '', $tag = '', $ids = '', $orderby = '', $order = '', $show = '', $show_coupons_only = ''){
?>
<?php $arg_array = array(
    'data_source' => $data_source,
    'type' => $type,
    'cat' => $cat,
    'tag'=> $tag,
    'ids'=> $ids,
    'orderby' => $orderby,
    'order' => $order,
    'show' => $show,
);?>
<?php echo wpsm_woolist_shortcode($arg_array);?>
<?php
}
}


/*-----------------------------------------------------------------------------------*/
# 	Main Offer Button creating
/*-----------------------------------------------------------------------------------*/

if( !function_exists('rehub_create_btn') ) { //Backward compatibility for old function
function rehub_create_btn ($btn_more='', $showme = '', $am_tag = '', $timer = '') {
	$args =  array(
		'btn_more' => $btn_more,
		'showme' => $showme,
		'am_tag' => $am_tag,
		'timer' => $timer,						
	);
	rehub_generate_offerbtn($args);
}
}


if( !function_exists('rehub_generate_offerbtn') ) {
function rehub_generate_offerbtn($args) {

	$defaults = array(
		'btn_more' => '',
		'showme' => '',
		'am_tag' => '',
		'timer' => '',
		'wrapperclass' => '',
		'coupon' => '1',
		'updateclean'=> '',
		'postId' => ''					
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );
	if($postId){
		 $post_id = $postId;
	}else{
		global $post;
		$post_id = $post->ID;
	}
	

	?>
		<?php
			$offer_url = $offer_btn_text = $offer_coupon = $offer_coupon_date = $offer_coupon_mask = $offer_price_old = $coupon_style = '';
			$offer_url_exist = get_post_meta( $post_id, 'rehub_offer_product_url', true );
			$offer_url = apply_filters('rehub_create_btn_url', $offer_url_exist);
			$offer_url = apply_filters('rh_post_offer_url_filter', $offer_url);			
		 	$offer_price = get_post_meta( $post_id, 'rehub_offer_product_price', true );
			$offer_price = apply_filters('rehub_create_btn_price', $offer_price);					 			
		?>
		<?php if ($offer_url_exist) : ?>
			<?php
			 	$offer_btn_text = get_post_meta( $post_id, 'rehub_offer_btn_text', true );			 	
			 	$offer_coupon = get_post_meta( $post_id, 'rehub_offer_product_coupon', true );
			 	$offer_coupon_date = get_post_meta( $post_id, 'rehub_offer_coupon_date', true );
			 	$offer_coupon_mask = get_post_meta( $post_id, 'rehub_offer_coupon_mask', true );
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
	        			$expired = 'no';
	        		}
	        		elseif ($days == 0){
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
		<?php endif;?>
		<?php if ($offer_price) : ?>
			<?php
		 		$offer_price_old = get_post_meta( $post_id, 'rehub_offer_product_price_old', true );
		 		$offer_price_old = apply_filters('rehub_create_btn_price_old', $offer_price_old);	
			?>
		<?php endif;?>

		<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?> 
		<?php $reveal_enabled = ($coupon_mask_enabled =='1') ? ' reveal_enabled' : '';?>
		<?php if ($offer_price || ($offer_url && $showme !='price')) :?>
	        <div class="priced_block clearfix <?php echo ''.$reveal_enabled; echo ''.$coupon_style; ?> <?php echo esc_html($wrapperclass);?>">
	            <?php if($timer && !empty($offer_coupon_date) && $expired !=1) {
	                echo '<div class="gridcountdown mb10 mt0 mr0 ml0">';
						$year = date('Y',$timestamp1);
						$month = date('m',$timestamp1);
						$day  = date('d',$timestamp1); 
						$hour  = date('H',$timestamp1); 
						$minute  = date('i',$timestamp1); 
						echo wpsm_countdown(array('year'=> $year, 'month'=>$month, 'day'=>$day, 'minute'=>$minute, 'hour'=>$hour));
	                echo '</div>';
	            } ?>  	        	
	            <?php if($offer_price && $showme !='button') : ?>
	            	<span class="rh_price_wrapper">
	            		<span class="price_count">
	            			<span class="rh_regular_price"><?php echo esc_html($offer_price) ?></span>
	            			<?php if($offer_price_old !='' && $offer_price_old !=$offer_price) :?> <del><?php echo esc_html($offer_price_old) ; ?></del><?php endif ;?>
	            		</span>
	            	</span>
	            <?php endif ;?>
	    		<?php if($offer_url && $showme !='price') : ?>
	    			<span class="rh_button_wrapper">
		            	<a href="<?php echo esc_url ($offer_url) ?>" class="btn_offer_block re_track_btn" target="_blank" rel="nofollow sponsored">
			            <?php if($offer_btn_text !='') :?>
			            	<?php echo esc_html ($offer_btn_text); ?>
			            <?php elseif(rehub_option('rehub_btn_text') !='') :?>
			            	<?php echo rehub_option('rehub_btn_text') ; ?>
			            <?php else :?>
			            	<?php esc_html_e('Buy It Now', 'rehub-theme') ?>
			            <?php endif ;?>
			            <?php if ($am_tag == 1):?>
			            	<?php         
			            		$shop = parse_url($offer_url, PHP_URL_HOST);
	    						$shop = preg_replace('/^www\./', '', $shop);
	    						if (strpos($shop, 'am') === false) {
	    							echo '<span class="dest-shop-btn mtinside">@ '.ucfirst($shop).'</span>';
	    						}
	    					?>
			            <?php endif;?>
		            </a>
		        	</span>
	            <?php endif;?>	
		    	<?php if ($coupon_mask_enabled =='1') :?>
		    		<?php if($showme !='price') : ?>
			    		<div class="post_offer_anons">
			    			<?php wp_enqueue_script('zeroclipboard'); ?>
		                	<span class="coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_html ($offer_coupon) ?>" data-codeid="<?php echo (int)$post_id?>" data-dest="<?php echo esc_url($offer_url) ?>">
		                		<?php if($offer_btn_text !='') :?>
			            			<?php echo esc_html ($offer_btn_text) ; ?>
		                		<?php elseif(rehub_option('rehub_mask_text') !='') :?>
		                			<?php echo rehub_option('rehub_mask_text') ; ?>
		                		<?php else :?>
		                			<?php esc_html_e('Reveal coupon', 'rehub-theme') ?>
		                		<?php endif ;?>
		                	</span>
		            	</div>
	            	<?php endif;?>
		    	<?php else : ?>
					<?php if(!empty($offer_coupon) && $showme !='price' && $coupon =='1') : ?>
						<?php wp_enqueue_script('zeroclipboard'); ?>
					  	<div class="rehub_offer_coupon not_masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_html($offer_coupon) ?>"><span class="coupon_text"><?php echo esc_html($offer_coupon); ?></span> <i class="rhicon rhi-scissors fa-rotate-180"></i>
					  	</div>
				  	<?php endif;?>		    		
		        <?php endif; ?>	            	        
	        </div>
        <?php endif ;?>
    	<?php if ($showme !='price') :?>
    		<?php if ( $btn_more =='both' || (!$offer_url && $btn_more =='yes')) :?>
				<?php if (rehub_option('rehub_readmore_text') !=''): ?>
			  		<a href="<?php the_permalink();?>" class="btn_more"><?php echo strip_tags(rehub_option('rehub_readmore_text'));?></a>
			  	<?php else: ?>
					<a href="<?php the_permalink();?>" class="btn_more"><?php esc_html_e('READ MORE  +', 'rehub-theme') ;?></a>
			  	<?php endif ?>
		  	<?php endif ;?>
    	<?php endif ;?>
		<?php if ($updateclean) :?>
			<?php 
				$offer_price_clean = rehub_price_clean($offer_price); 
				$offer_price_clean_old = get_post_meta( $post_id, 'rehub_main_product_price', true );
				if ( $offer_price_clean !='' && $offer_price_clean_old !='' && $offer_price_clean != $offer_price_clean_old ){
					update_post_meta($post_id, 'rehub_main_product_price', $offer_price_clean); 
				}
				elseif($offer_price_clean !='' && $offer_price_clean_old =='') {
					update_post_meta($post_id, 'rehub_main_product_price', $offer_price_clean); 
				}
			?> 
		<?php endif ;?>

	<?php
}
}

if( !function_exists('rehub_create_affiliate_link') ) {
function rehub_create_affiliate_link() {
$out='';
global $post;
if ($post->post_type == 'product'){
	global $product;
	$offer_url_exist = $product->add_to_cart_url();
}
else{
	$offer_url_exist = get_post_meta( $post->ID, 'rehub_offer_product_url', true );
}
$offer_url_exist = apply_filters('rehub_create_btn_url', $offer_url_exist);
if(!empty($offer_url_exist) ) :
	$offer_url = apply_filters('rh_post_offer_url_filter', $offer_url_exist );
	$out = esc_url($offer_url); 
else :
	$out = get_the_permalink();
endif;
return $out;
}
}


if( !function_exists('rehub_create_price_for_list') ) {
function rehub_create_price_for_list($id) {
	?>

		<?php
			$offer_price = get_post_meta($id, 'rehub_offer_product_price', true );
			$offer_price = apply_filters('rehub_create_btn_price', $offer_price);
		if (!empty($offer_price)) : ?>			
    		<span class="simple_price_count greencolor fontnormal">
    			<?php $offer_price_old = get_post_meta($id, 'rehub_offer_product_price_old', true );?>
    			<?php echo esc_html($offer_price) ?>
    			<?php if($offer_price_old !='' && $offer_price_old !='0') :?> <del class="greycolor font80"><?php echo esc_html($offer_price_old) ; ?></del><?php endif ;?>
    		</span>
        <?php elseif ('product' == get_post_type($id)) : ?>
        	<?php global $product;?>
        	<span class="simple_price_count greencolor fontnormal"><?php echo ''.$product->get_price_html();?></span>        	
	    <?php endif ;?>	    

	<?php
}
}

/*-----------------------------------------------------------------------------------*/
# 	Quick offer function
/*-----------------------------------------------------------------------------------*/

if( !function_exists('rehub_quick_offer') ) {
function rehub_quick_offer($id=''){
	global $post;
	$postid = (!empty($id)) ? (int)$id : $post->ID;

	$offer_post_url = get_post_meta( $postid, 'rehub_offer_product_url', true );
	$offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
	$offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
	$offer_price = get_post_meta( $postid, 'rehub_offer_product_price', true );
	$offer_title = get_post_meta( $postid, 'rehub_offer_name', true );
	$offer_thumb = get_post_meta( $postid, 'rehub_offer_product_thumb', true );
	$offer_btn_text = get_post_meta( $postid, 'rehub_offer_btn_text', true );
	$offer_price_old = get_post_meta( $postid, 'rehub_offer_product_price_old', true );
	$offer_coupon = get_post_meta( $postid, 'rehub_offer_product_coupon', true );
	$offer_coupon_date = get_post_meta( $postid, 'rehub_offer_coupon_date', true );
	$offer_coupon_mask = get_post_meta( $postid, 'rehub_offer_coupon_mask', true );
	$offer_desc = get_post_meta( $postid, 'rehub_offer_product_desc', true );
	$offer_brand_url = esc_url(get_post_meta( $postid, 'rehub_offer_logo_url', true ));
	$disclaimer = get_post_meta($postid, 'rehub_offer_disclaimer', true);
	include(rh_locate_template('inc/parts/singleofferpart.php'));		
}
}

/*-----------------------------------------------------------------------------------*/
# 	Hook offer after content
/*-----------------------------------------------------------------------------------*/

if( !function_exists('set_content_end') ) {
function set_content_end($content) {
	global $post;

	if( is_feed() || !is_singular()) return $content;

	$output = '';
	ob_start();
	wp_link_pages(array( 'before' => '<div class="page-link"><span class="page-link-title">' . esc_html__( 'Pages:', 'rehub-theme' ).'</span>', 'after' => '</div>', 'pagelink' => '<span>%</span>' ));
	$output .= ob_get_clean();

	$posttype = $post->post_type;

	if ($posttype == 'post' || $posttype == 'blog'){ 
		//Old deprecated Review post format
		$reviewformat = get_post_meta($post->ID, 'rehub_framework_post_type', true);
		if(($reviewformat == 'review' || rehub_option('type_user_review') == 'full_review') && !has_block('rehub/reviewbox', $post)) :
			$review_post = rehub_get_review_data();
			if(empty($review_post['review_post_product_shortcode'])) :
				ob_start();
				rehub_get_review();
				$output .= ob_get_clean();
			endif;	
		endif;
	}


	return $content.$output;
}
}
add_filter ('the_content', 'set_content_end');


//Save data from CE
function rehub_sort_price_ce ($a, $b) {
	if (!$a['price']) return 1;
	if (!$b['price']) return -1;
	return ($a['price'] < $b['price']) ? -1 : 1;
}
if (!function_exists('rehub_save_meta_ce')) {
    function rehub_save_meta_ce($data, $module_id, $post_id) {
		if (!$post_id){
			global $post;
			if (isset($post)){
				$post_id = $post->ID;
			}
			else{
				return false; // Error: no POST ID
			}
		}
        $cegg_field_array = rehub_option('save_meta_for_ce');
        $cegg_fields = array();
    	if (!empty($cegg_field_array) && is_array($cegg_field_array)) {

        	foreach ($cegg_field_array as $cegg_field) {
	        	if ($cegg_field == 'none' || $cegg_field == ''){ continue;}
        		$cegg_field_value = \ContentEgg\application\components\ContentManager::getViewData($cegg_field, $post_id);
        		if (!empty ($cegg_field_value) && is_array($cegg_field_value)) {
                    foreach ($cegg_field_value as $key => $value) {
                        $value['module_id'] = $cegg_field;
                        $cegg_fields[$key] = $value;
                    }        			
        			//$cegg_fields += $cegg_field_value;
        		}		
        	}

        	$postsync = get_post_meta($post_id, '_rh_post_offer_sync_ce', true); 
			if (!empty($cegg_fields) && is_array($cegg_fields)) {

				//Check how to sync
	        	if($postsync){
	        		if($postsync == 'none'){
	        			delete_post_meta($post_id, '_rehub_product_unique_id');
	        			delete_post_meta($post_id, '_rehub_module_ce_id');	        			
	        			return false;
	        		}
	        		elseif($postsync == 'lowest'){
	        			$keyupdate = 0;
	        			usort($cegg_fields, 'rehub_sort_price_ce'); 
	        		}
	        		else{
	        			$keyupdate = $postsync;
	        			if(!isset($cegg_fields[$keyupdate])){
	        				update_post_meta($post_id, '_rh_post_offer_sync_ce', 'lowest'); 
			        		$keyupdate = 0;
			        		usort($cegg_fields, 'rehub_sort_price_ce');
	        			}
	        		}
	        	}
	        	else{
	        		$keyupdate = 0;
	        		usort($cegg_fields, 'rehub_sort_price_ce'); 
	        	}

				$price_sale = $price_old = $merchant = $unique_id = $logo = $domain = '';        				
				$currency_code = (!empty($cegg_fields[$keyupdate]['currencyCode'])) ? $cegg_fields[$keyupdate]['currencyCode'] : '';
	    		if(!empty ($cegg_fields[$keyupdate]['price'])) { //Saving price with price pattern
	    			$locale = \ContentEgg\application\helpers\CurrencyHelper::getInstance(\ContentEgg\application\admin\GeneralConfig::getInstance()->option('lang'));
					$price_sale = \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($cegg_fields[$keyupdate]['price'], $currency_code);
	    		}	
	    		if(!empty ($cegg_fields[$keyupdate]['priceOld'])) {
	    			$price_old = \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($cegg_fields[$keyupdate]['priceOld'], $currency_code);
	    		}
	    		if(!empty ($cegg_fields[$keyupdate]['unique_id'])) {
	    			$unique_id = $cegg_fields[$keyupdate]['unique_id']; 
	    		}	    		
				if ('product' == get_post_type($post_id)) {
					if(!empty($cegg_fields[$keyupdate]['percentageSaved'])) {
						update_post_meta($post_id, '_rehub_offer_discount', $cegg_fields[$keyupdate]['percentageSaved']);
					}else{
						delete_post_meta($post_id, '_rehub_offer_discount');
					}										
				}	
				else {
					if(isset($cegg_fields[$keyupdate]['price']) && $cegg_fields[$keyupdate]['price'] == '0'){
						delete_post_meta($post_id, 'rehub_main_product_price');
					}else{
						update_post_meta($post_id, 'rehub_main_product_price', $cegg_fields[$keyupdate]['price']);
					}			
		    		if(!empty($cegg_fields[$keyupdate]['currencyCode'])){
		    			update_post_meta($post_id, 'rehub_main_product_currency', $cegg_fields[$keyupdate]['currencyCode']);
		    		}
			    	update_post_meta($post_id, 'rehub_offer_product_price', $price_sale);
			    	if ($price_old == '') {
			    		delete_post_meta($post_id, 'rehub_offer_product_price_old');
			    	}
			    	else{
			    		update_post_meta($post_id, 'rehub_offer_product_price_old', $price_old);
			    	}	
					if(!empty($cegg_fields[$keyupdate]['percentageSaved'])) {
						update_post_meta($post_id, '_rehub_offer_discount', $cegg_fields[$keyupdate]['percentageSaved']);
					}else{
						delete_post_meta($post_id, '_rehub_offer_discount');
					}			    		    					 
		    		update_post_meta($post_id, 'rehub_offer_product_url', $cegg_fields[$keyupdate]['url']);	 
		    		if(!empty ($cegg_fields[$keyupdate]['title'])) {
		    			update_post_meta($post_id, 'rehub_offer_name', esc_html($cegg_fields[$keyupdate]['title'])); 
		    		}
		    		if(!empty ($cegg_fields[$keyupdate]['domain'])) {
		    			$domain = $cegg_fields[$keyupdate]['domain'];
		    		}		    		
		    		elseif(!empty ($cegg_fields[$keyupdate]['extra']['domain'])) {
		    			$domain = $cegg_fields[$keyupdate]['extra']['domain']; 
		    		}		
            		update_post_meta($post_id, 'rehub_offer_domain', $domain);            			 
	    			    			    			    		
		    		if(!empty ($cegg_fields[$keyupdate]['description'])) {
		    			update_post_meta($post_id, 'rehub_offer_product_desc', esc_html($cegg_fields[$keyupdate]['description'])); 
		    		}
		    		if(!empty ($cegg_fields[$keyupdate]['img'])) {
		    			update_post_meta($post_id, 'rehub_offer_product_thumb', $cegg_fields[$keyupdate]['img']); 
		    		}
		    		if(!empty ($cegg_fields[$keyupdate]['module_id'])) {
		    			update_post_meta($post_id, '_rehub_module_ce_id', $cegg_fields[$keyupdate]['module_id']); 
		    		}else{
		    			delete_post_meta($post_id, '_rehub_module_ce_id');		    
		    		}
		    		if($unique_id) {
		    			update_post_meta($post_id, '_rehub_product_unique_id', $unique_id); 
		    		}
					else{
		    			delete_post_meta($post_id, '_rehub_product_unique_id');		    
		    		}		    		

	    		}	        	

			}
    	}
    }
}
add_action('content_egg_save_data', 'rehub_save_meta_ce', 13, 3);


//////////////////////////////////////////////////////////////////
//EXPIRE FUNCTION
//////////////////////////////////////////////////////////////////
add_action('post_change_expired', 'post_change_expired_function', 10, 1);
if (!function_exists('post_change_expired_function')) {
function post_change_expired_function($expired=''){
	global $post;
	if (isset($post)){
		$post_id = $post->ID;
	}
	else{
		return false; // Error: no POST ID
	}
	$expired_exist = get_post_meta($post_id, 're_post_expired', true);
	if($expired ==1 && $expired_exist !=1){
		update_post_meta($post_id, 're_post_expired', 1);
		wp_set_object_terms($post_id, 'yes', 'offerexpiration', false );
	}
	elseif($expired =='no'){
		update_post_meta($post_id, 're_post_expired', 0);
		wp_set_object_terms($post_id, NULL, 'offerexpiration', false );
	}
	elseif($expired_exist==0){
	}		
	elseif($expired_exist==''){
		update_post_meta($post_id, 're_post_expired', 0);
		wp_set_object_terms($post_id, NULL, 'offerexpiration', false );
	}
}
}

if (!function_exists('rh_expired_or_not')) {
function rh_expired_or_not($id, $type='class'){
	if (empty($id) || !is_numeric($id)) return;
	$expired = get_post_meta($id, 're_post_expired', true);
	if ($type == 'class'){
		if ($expired == true) {
			return ' rh-expired-class';
		}
	}
	if ($type == 'span'){
		if ($expired == true) {
			return '<span class="rh-expired-notice">'.__('Expired', 'rehub-theme').'</span>';
		}
	}	
}
}


//////////////////////////////////////////////////////////////////
//REVIEWS IN FRONTEND FUNCTION
//////////////////////////////////////////////////////////////////
if ( !function_exists( 'rh_review_frontend_fields' ) ) {
function rh_review_frontend_fields($current_values, $form_id){
	$criteriaNamesArray = $review_post_criteria = array();	
	$review_heading = $review_summary = $criteriaInputs = $review_proses = $review_conses = '';
	$reviewCriteria = rh_get_front_review_fields(); //rehub_option('rh_front_review_fields');
	if (!empty($reviewCriteria[$form_id])){
		$currentReviewscore = 0;
		if(!empty($current_values['post_id'])){
			$currentReview = get_post_meta( $current_values['post_id'], 'review_post' );
			$currentReviewscore = (get_post_meta( $current_values['post_id'], 'rehub_review_overall_score', true ) !='') ? get_post_meta( $current_values['post_id'], 'rehub_review_overall_score', true ) * 10 : 0;
		}
		if (!empty($currentReview)){
			$review_heading = $currentReview[0][0]['review_post_heading'];
			$review_summary = $currentReview[0][0]['review_post_summary_text'];
			$review_proses = $currentReview[0][0]['review_post_pros_text'];
			$review_conses = $currentReview[0][0]['review_post_cons_text'];					
		}
		wp_enqueue_style('jquery.nouislider'); 
		wp_enqueue_script('jquery.nouislider'); 
		wp_enqueue_style('rhuserreviews');		
		$reviewCriteria = $reviewCriteria[$form_id];
	    
		for($i = 0; $i < count($reviewCriteria); $i++) {
			$criteriaNamesArray[$i] = $reviewCriteria[$i];
			$scorevalue = (!empty($currentReview[0][0]['review_post_criteria'][$i]['review_post_score'])) ? $currentReview[0][0]['review_post_criteria'][$i]['review_post_score'] : 0;
			$criteriaInputs .= '<label for="criteria_input_'.$i.'">'.$reviewCriteria[$i].'</label>';
			$criteriaInputs .= '<input id="criteria_input_'.$i.'" type="hidden" name="criteria_score_'.$i.'" value="'.$scorevalue.'" class="criteria_hidden_input'.$i.'" /><span class="criteria_visible_input'.$i.'"></span><div class="rh_front_criteria"></div>';
		};
		$criteriaInputs .= '<div class="your_total_score">'.__('Your total score','rehub-theme').' <span class="user_reviews_view_score"><span class="userstar-rating"><span style="width: '.$currentReviewscore.'%"></span></span></span></div><input type="hidden" name="criteria_names" value="'.implode(",", $criteriaNamesArray).'" />';

	    ?> 
	    <div id="user_reviews_in_frontend" class="rate_bar_wrap pt10 mt30">
	    	<div class="wpfepp-form-field-container">
	    		<label><?php esc_html_e('Review heading', 'rehub-theme');?></label>
	        	<input type="text" name="review_heading" value="<?php echo ''.$review_heading; ?>" />
	        </div>
	        <div class="wpfepp-form-field-container">
				<label><?php esc_html_e('Review summary', 'rehub-theme');?></label>
	        	<textarea name="review_summary"><?php echo ''.$review_summary; ?></textarea>
	        </div>
	        <div class="wpfepp-form-field-container">
				<label><?php esc_html_e('PROS. Add each from separate line', 'rehub-theme');?></label>
	        	<textarea name="review_post_pros_text"><?php echo ''.$review_proses; ?></textarea>
	        </div>
	        <div class="wpfepp-form-field-container">
				<label><?php esc_html_e('CONS. Add each from separate line', 'rehub-theme');?></label>
	        	<textarea name="review_post_cons_text"><?php echo ''.$review_conses; ?></textarea>
	        </div>	        	        
	        <div class="wpfepp-form-field-container">        
	        	<?php echo ''.$criteriaInputs; ?>
	        </div>
	    </div>
	    <?php		
	}
}
}
/* Save Review fields from RH Frontend plugin */
if ( !function_exists( 'rh_review_frontend_actions' ) ) {
function rh_review_frontend_actions($data, $form_id){
    $criterianames = $data['criteria_names'];
    $postid = $data['post_id'];
    $posttype = get_post_type($postid);
    if (!empty($criterianames)){
    	$criterianames = explode(',', $criterianames);
		$review_post_criteria = array();
		$review_criteria_overall = $total_counter = 0;
		$postscore = '';    	
		for( $i = 0; $i < count($criterianames); $i++ ) {
			$review_name = $criterianames[$i];
			$review_score = 'criteria_score_' . $i;			
			$review_post_criteria[] = array( 'review_post_name' => $review_name, 'review_post_score' => $data[$review_score] );
			$review_criteria_overall += (float) $data[$review_score];
			$total_counter ++;
		}    
		if( $review_criteria_overall !=0 && $total_counter !=0) {
			$postscore =  $review_criteria_overall / $total_counter ;
			$postscore = round($postscore, 1);			
		} 					
    }
    if($posttype == 'product'){

        $fields = array();
        if( !empty($data['review_heading'] ) ) {
            update_post_meta( $postid, '_review_heading', sanitize_text_field( $data['review_heading'] ) );
            $fields[] = '_review_heading';
        }
        if( !empty($data['review_summary'] ) ) {
            update_post_meta( $postid, '_review_post_summary_text', sanitize_textarea_field( $data['review_summary'] ) );
            $fields[] = '_review_post_summary_text';
        }
        if( !empty($data['review_post_pros_text'] ) ) {
            update_post_meta( $postid, '_review_post_pros_text', sanitize_textarea_field( $data['review_post_pros_text'] ) );
            $fields[] = '_review_post_pros_text';
        }
        if( !empty($data['review_post_cons_text'] ) ) {
            update_post_meta( $postid, '_review_post_cons_text', sanitize_textarea_field( $data['review_post_cons_text'] ) );
            $fields[] = '_review_post_cons_text';
        }
        // Collect Review criteria data
        if( !empty($review_post_criteria) ) {
            foreach($review_post_criteria as $index=>$value){
                update_post_meta( $postid, '_review_score_criteria_'.$total_counter, sanitize_text_field( $value['review_post_score']));
            }
            $review_post_s_array = rh_serialize_data_review( $review_post_criteria );
            update_post_meta( $postid, '_review_post_criteria', $review_post_s_array );
            $fields[] = '_review_post_criteria';
        }
		if (!empty($postscore)) {
			update_post_meta($postid, 'rehub_review_overall_score', $postscore );
		}
		update_post_meta( $postid, 'rehub_review_woo_fields', rh_serialize_data_review( $fields ) );	
    }else{
		$review_post_array = array (
		  array (
			'review_post_heading' => $data['review_heading'],
			'review_post_summary_text' => $data['review_summary'],
			'review_post_pros_text' => $data['review_post_pros_text'],	
			'review_post_cons_text' => $data['review_post_cons_text'],			
			'review_post_product_shortcode' => '0',
			'review_post_score_manual' => '',
			'review_post_criteria' => $review_post_criteria
		  )
		);    
		$review_post_s_array = rh_serialize_data_review( $review_post_array );
		update_post_meta($postid, 'review_post', $review_post_s_array );
		if (!empty($postscore)) {
			update_post_meta($postid, 'rehub_review_overall_score', $postscore );
		}	
		$data_post_fields = array( 'rehub_framework_post_type', 'video_post', 'gallery_post', 'review_post', 'music_post' );
		update_post_meta($postid, 'rehub_post_fields', rh_serialize_data_review( $data_post_fields ) );	
		update_post_meta($postid, 'rehub_framework_post_type', 'review' );
    }
}
}

/* Recount Review fields for RH Frontend plugin */
function rh_get_front_review_fields(){
	$reviewData = array();
	$reviewCriterias = rehub_option('rh_front_review_fields');
	
	if(empty($reviewCriterias))
		return $reviewData;
	
	if(stripos( $reviewCriterias, ':' )){
		$reviewCriteriasArray = array_map('trim', explode(PHP_EOL, $reviewCriterias));
		foreach( $reviewCriteriasArray as $reviewCriteria ){
			$criteriaelements = explode(':', $reviewCriteria);
			if(is_numeric($criteriaelements[0])){
				$criterianames = explode(',', $criteriaelements[1]);
				$reviewData[$criteriaelements[0]] = $criterianames;
			}
		}
	}else{
		$formidforreview = rehub_option('rh_front_reviewform_id');
		$reviewFormIDs = array_map( 'trim', explode( ",", $formidforreview ) );
		$criterianames = explode(',', $reviewCriterias);
		foreach( $reviewFormIDs as $reviewFormID ){
			$reviewData[$reviewFormID] = $criterianames;
		}
	}
	return $reviewData;
}

/* Add Review fields for RH Frontend plugin */
if (rehub_option('rh_front_review_fields') !='') {
	$reviewInputData = rh_get_front_review_fields();
	if( !empty( $reviewInputData ) ){
		foreach ($reviewInputData as $reviewFormID => $reviewInputForm) {
			add_action('wpfepp_form_'.$reviewFormID.'_actions', 'rh_review_frontend_actions', 10, 2);
			add_action('wpfepp_form_'.$reviewFormID.'_fields', 'rh_review_frontend_fields', 10, 2);
		}
	}
}

add_action( 'pre_get_posts', 'rehub_change_post_query' ); //Here we change and extend post loop data
if (!function_exists('rehub_change_post_query')){
	function rehub_change_post_query($q){
		if (rehub_option('rehub_post_exclude_expired') == '1') {
		    if (!is_admin() && (is_post_type_archive('post') || is_category() || is_home() || is_feed()) && $q->get('post_type')=='post') {
			    $q->set( 'tax_query', array(
			    	'relation' => 'AND',
	                array(
	                    'taxonomy' => 'offerexpiration',
	                    'field'    => 'name',
	                    'terms'    => 'yes',
	                    'operator' => 'NOT IN',
	                )			    	 				    	   	
			    ));
			}
		}	
	}
}

if ( !function_exists( 'rh_get_post_ids_on_sale' ) ) {
	function rh_get_post_ids_on_sale() {
		global $wpdb;

		// Load from cache
		$post_ids_on_sale = get_transient( 'rh_posts_onsale' );

		// Valid cache found
		if ( false !== $post_ids_on_sale ) {
			return $post_ids_on_sale;
		}

		$on_sale_posts = $wpdb->get_results( "
			SELECT post.ID, post.post_parent FROM `$wpdb->posts` AS post
			LEFT JOIN `$wpdb->postmeta` AS meta ON post.ID = meta.post_id
			LEFT JOIN `$wpdb->postmeta` AS meta2 ON post.ID = meta2.post_id
			WHERE post.post_type IN ( 'post' )
				AND post.post_status = 'publish'
				AND meta.meta_key = 'rehub_offer_product_url'
				AND meta2.meta_key = 'rehub_offer_product_price'
				AND CAST( meta.meta_value AS CHAR ) != ''
				AND CAST( meta2.meta_value AS CHAR ) != ''
			GROUP BY post.ID;
		" );

		$post_ids_on_sale = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

		set_transient( 'rh_posts_onsale', $post_ids_on_sale, apply_filters( 'rh_update_posts_onsale', DAY_IN_SECONDS * 7 ) );

		return $post_ids_on_sale;
	}
}

if(!function_exists('rh_ce_found_total_offers')){
function rh_ce_found_total_offers($post_id){
	$module_ids = \ContentEgg\application\components\ModuleManager::getInstance()->getAffiliateParsers($only_active = true);
	$total = 0;
	if(!empty($module_ids)){
		$module_ids = array_keys($module_ids);
		foreach ($module_ids as $module_id)
		{
		$data = \ContentEgg\application\components\ContentManager::getViewData($module_id, $post_id);
		$total += count($data);
		}		
	}
	return $total;
}	
}

//////////////////////////////////////////////////////////////////
// AJAX COUPON
//////////////////////////////////////////////////////////////////

if( !function_exists('coupon_get_code') ) {
function coupon_get_code(){
    //check_ajax_referer( 'coupon-nonce', 'security' );

    $codeid = (!empty($_GET['codeid'])) ? (int)$_GET['codeid'] : '';
    if( empty($codeid) ){
        return;
    }    
    $code = get_post( $codeid );
    if(!is_object($code)) return;
    $codeid = $code->ID;
    $shop = $thumb_enable = $printout = $sale_proc = $coupontext = '';
    if( !empty( $code ) ){
        if ('product' == get_post_type($codeid)) {
            $offer_coupon = get_post_meta( $codeid, 'rehub_woo_coupon_code', true );
            $term_ids =  wc_get_product_terms($codeid, 'store', array("fields" => "ids")); 
            if (!empty($term_ids) && ! is_wp_error($term_ids)) {
                $term_id = $term_ids[0];
                $termobj = get_term_by('id', $term_id, 'store');
                $termname = $termobj->name;             
                $brand_url = get_term_meta( $term_id, 'brandimage', true );
                $brand_link = get_term_link( $term_id );
            }
            if (!empty ($brand_url)) {
                $term_brand_image = esc_url($brand_url);
            }   
             
            if (!empty($brand_link)){
                $shop .= '<a href="' . esc_url( $brand_link ) . '" class="shop_in_cpn font80 fontitalic blockstyle greycolor mt10">';
            }
            if (!empty($term_brand_image)) :
                $showbrandimg = new WPSM_image_resizer();
                $showbrandimg->height = '30';
                $showbrandimg->src = $term_brand_image;
                $shop .= '<img src="'.$showbrandimg->get_resized_url().'" alt="'.$termname.'" style="max-width:100px" /> ';                                    
            endif;
            if (!empty($brand_link)){
                $shop .= $termname.'</a>';
            }

            $args = array(
                'post_type'         => 'product',
                'posts_per_page'    => 1,
                'no_found_rows'     => 1,
                'post_status'       => 'publish',
                'p'                 => $codeid,

            );          
            $products = new WP_Query( $args );
            if ( $products->have_posts() ) : while ( $products->have_posts() ) : $products->the_post();
            global $product;
            $offer_link = esc_url( $product->add_to_cart_url() );
            $offer_link = apply_filters('rh_post_offer_url_filter', $offer_link, $codeid );               
            $offer_coupon_date = get_post_meta(get_the_ID(), 'rehub_woo_coupon_date', true );
            $offer_coupon = get_post_meta(get_the_ID(), 'rehub_woo_coupon_code', true );
            $offer_couponimgurl = get_post_meta(get_the_ID(), 'rehub_woo_coupon_coupon_img_url', true );
        	
        	if ($product->is_on_sale() && !$product->is_type( 'variable' ) && $product->get_regular_price() && $product->get_price() > 0 ){
	            $offer_price_calc = (float) $product->get_price();
	            $offer_price_old_calc = (float) $product->get_regular_price();
	            $sale_proc = 100 - ($offer_price_calc / $offer_price_old_calc) * 100; 
	            $sale_proc = round($sale_proc); 
            }

            endwhile; endif;  wp_reset_postdata();
        }
        else{
            $thumb_enable = '1';
            $offer_coupon = get_post_meta( $codeid, 'rehub_offer_product_coupon', true ); 
            $offer_link = get_post_meta( $codeid, 'rehub_offer_product_url', true );
            $offer_link = apply_filters('rehub_create_btn_url', $offer_link, $codeid);
            $offer_link = apply_filters('rh_post_offer_url_filter', $offer_link, $codeid );
            $term_ids =  wp_get_post_terms($codeid, 'dealstore', array("fields" => "ids")); 
            $offer_desc = get_post_meta( $codeid, 'rehub_offer_product_desc', true );
            $offer_coupon_date = get_post_meta( $codeid, 'rehub_offer_coupon_date', true );
            $offer_couponimgurl = get_post_meta( $codeid, 'rehub_offer_product_thumb', true );
            if(!empty($offer_coupon_date)){
				$timestamp1 = strtotime($offer_coupon_date);
				if(strpos($offer_coupon_date, ':') ===false){
					$timestamp1 += 86399;
				}
                $seconds = $timestamp1 - (int)current_time('timestamp',0);
                $days = floor($seconds / 86400);
                $seconds %= 86400;
                if ($days > 0) {
                    $coupon_text = $days.' '.__('days left', 'rehub-theme');
                }
                elseif ($days == 0){
                    $coupon_text = esc_html__('Last day', 'rehub-theme');
                }
                else {
                    $coupon_text = esc_html__('Expired', 'rehub-theme');
                }                
            }else{
                $coupon_text = esc_html__('No expiration date', 'rehub-theme');
            }
            if (!empty($term_ids) && ! is_wp_error($term_ids)) {
                $term_id = $term_ids[0];
                $termobj = get_term_by('id', $term_id, 'dealstore');
                $termname = $termobj->name;             
                $brand_url = get_term_meta( $term_id, 'brandimage', true );
                $brand_link = get_term_link( $term_id );
            }
            if (!empty ($brand_url)) {
                $term_brand_image = esc_url($brand_url);
            }   
             
            if (!empty($brand_link)){
                $shop .= '<a href="' . esc_url( $brand_link ) . '" class="shop_in_cpn font80 fontitalic blockstyle greycolor mt10">';
            }
            if (!empty($term_brand_image)) :
                $showbrandimg = new WPSM_image_resizer();
                $showbrandimg->height = '30';
                $showbrandimg->src = $term_brand_image;
                $shop .= '<img src="'.$showbrandimg->get_resized_url().'" alt="'.$termname.'" /> ';                                    
            endif;
            if (!empty($brand_link)){
                $shop .= $termname.'</a>';
            }           
        }
        $posttitle = get_the_title($codeid);
		$printout .= '<script type="text/javascript">
			jQuery.getScript("'.get_template_directory_uri() . '/js/printcoupon.js");
		</script>';
        $printid = mt_rand().'printid'; 
        $printout .= '<div id="printcoupon'.$printid.'" class="printmecoupondiv"><div class="printcoupon"><div class="printcouponwrap"><div class="printcouponheader"><div class="printcoupontitle">'.$posttitle.'</div>';
        $printout .= '<div class="expired_print_coupon">';
        if(!empty($offer_coupon_date)):
            $printout .= esc_html__('Use before:', 'rehub-theme').esc_html($offer_coupon_date);
        endif;
        $printout .= '</div>';
        $printout .= '<div class="storeprint">'.$shop.'</div>';
        $printout .= '</div><div class="printcouponcentral">';
        if($sale_proc){
            $printout .= '<span class="save_proc_woo_print">';   
            $printout .= '<span class="countprintsale">'.$sale_proc.'</span><span class="procprintsale">%</span><span class="wordprintsale">'.__('Save', 'rehub-theme').'</span></span>'; 
        }
        $printout .= '<div class="printcoupon_wrap">'.esc_html($offer_coupon).'</div></div>';                     
        $printout .= '<div class="printcoupondesc"><div class="printimage">'.get_the_post_thumbnail( $codeid, 'shop_thumbnail' ).'</div>';  
        $printout .= '<span>'.$code->post_excerpt.'</span></div>';                                              
        $printout .= '<div class="couponprintend">'.__('Get more coupons on:', 'rehub-theme').'<span> '.site_url().'</span></div></div></div>';
        if($offer_couponimgurl !='') :
        	$printout .= '<div class="printcouponimg"><img src="'.esc_url($offer_couponimgurl).'" alt="Use coupon image" /></div>';
        endif ;                 
        $printout .= '</div><div class="print_coupon_icon_inside_c lineheight15"><span class="printthecoupon inlinestyle font110 mt10 mb20 cursorpointer whitecolor" data-printid="'.$printid.'">'.__('Print coupon', 'rehub-theme').'</span></div>';          
         
        $offer_coupon_clicks = get_post_meta( $codeid, 'rehub_offer_clicks_count', true );
        if (empty($offer_coupon_clicks)){$offer_coupon_clicks = 0;}
        $offer_coupon_clicks = absint($offer_coupon_clicks) + 1;
        update_post_meta($codeid, 'rehub_offer_clicks_count', $offer_coupon_clicks);

        $response = '<div id="coupon_code_in_modal" class="rhhidden" data-couponid="'.$codeid.'">';
        $response .= '<div class="coupon_code_in_modal"><div class="coupon_top_part text-center violetgradient_bg padd20"><div class="re_title_inmodal rehub-main-font whitecolor font150 pt5 pb15">'.__('Here is your coupon code', 'rehub-theme').'</div>';
        $response .= '<div class="add_modal_coupon font80"><span class="text_copied_coupon pinkLcolor">'.__('Code is copied. Use code on site.', 'rehub-theme').'</span></div>';      

        $response .= '<div class="coupon_modal_coupon position-relative"><div class="cpn_modal_container text-center position-relative roundborder8 inlinestyle"><input type="text" size=20 class="code text-center" value="'.$offer_coupon.'" readonly=""></div>';
        $response .= $printout;
        $response .= '</div></div>';
        $response .='<a href="'.$offer_link.'" target="_blank" rel="nofollow sponsored" class="text-center cpn_btn_inner font150 pb10 pl30 pr30 pt10 rehub-main-btn-bg rehub_main_btn cpn_btn_inner position-relative">'.__('Go to Website', 'rehub-theme').'</a>';

        $cashbacknotice = get_post_meta($codeid, '_notice_custom', true);
        if($cashbacknotice){
            $response .= '<div class="rehub-main-font rehub-main-color text-center font120 cash_n_modal pb15">'.$cashbacknotice.'</div>';
        }  

        $response .= '<div class="cpn_info_bl padd20 flowhidden">';
        $response .= '<div class="cpn_post_title width-80-calc floatleft">';
		$verify = get_post_meta( $codeid, 'rehub_offer_verify_label', true );
		if($verify) {
			$response .= '<span class="verifymeta mr5 mb10 greencolor blockstyle"><i class="rhicon rhi-shield-check"></i> '.esc_attr($verify).'</span>';
		} 
            $response .= '<div class="cpn_title font120 greycolor mb10 fontitalic">'.$posttitle.$shop.'</div>';  
            if(isset($offer_desc)){
                $response .= '<div class="cpn_desc greycolor mb10 fontitalic">'.esc_html($offer_desc).'</div>';
            }
            if($coupontext){
            	$response .= '<div class="coupon_expire mb5 lineheight20">'.__('Valid until:', 'rehub-theme').' <span class="rehub-main-color">'.esc_html($coupon_text).'</span></div>';
        	}
            if($offer_coupon_clicks > 5){
                $response .= '<div class="cpn_rvl_count mb10 lineheight20">'.__('Revealed:', 'rehub-theme').' <span class="rehub-main-color">'.(int)$offer_coupon_clicks.'</span></div>';
            }
                    
        $response .='</div>';
        $response .='<div class="thumb_in_modalcoupon floatright">'.getHotThumb($codeid, false, false).'</div>';
        $response .='</div>';
        if(rehub_option('rehub_ads_coupon_area') !=''){
            $response .='<div class="mt25 rh-line"></div><div class="coupon_custom_code_area">'.do_shortcode(rehub_option('rehub_ads_coupon_area')).'</div>';

        }

        $response .='</div>';
        $response .='</div>';
        $response .='</div>';
    }
    else{
        $response = esc_html__( 'Offer does not exists', 'rehub-theme' );
    }

    echo  ''.$response ;
}
}

if( !function_exists('ce_printRel') ) {
function ce_printRel(){
    if (method_exists('\ContentEgg\application\helpers\TemplateHelper', 'getRelValue'))
        $rel = \ContentEgg\application\helpers\TemplateHelper::getRelValue();
    else
        $rel = 'nofollow sponsored';
    return ' rel="' . esc_attr($rel) . '"';
}
}

?>