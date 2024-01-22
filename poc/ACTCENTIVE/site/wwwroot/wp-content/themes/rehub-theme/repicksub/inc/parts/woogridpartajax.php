<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product; global $post;?>
<?php if (empty( $product ) || ! $product->is_visible() ) {return;}?>
<?php $classes = array('product', 'col_item', 'type-product', 'woo_column_item', 'woo_column_item');?>
<?php if (rehub_option('woo_btn_disable') == '1'){$classes[] = 'non_btn';}?>

<?php $disable_thumbs = (isset($disable_thumbs)) ? $disable_thumbs : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $woolink = ((rehub_option('aff_link_image') == '1' || $woolinktype == 'aff') && $product->get_type() =='external') ? $product->add_to_cart_url() : get_post_permalink($post->ID) ;?>
<?php $wootarget = ((rehub_option('aff_link_image') == '1' || $woolinktype == 'aff') && $product->get_type() =='external') ? ' target="_blank" rel="nofollow sponsored"' : '' ;?>
<?php $woolinktitle = ((rehub_option('aff_link_title') == '1' || $woolinktype == 'aff') && $product->get_type() =='external') ? $product->add_to_cart_url() : get_post_permalink($post->ID) ;?>
<?php $wootargettitle = ((rehub_option('aff_link_title') == '1' || $woolinktype == 'aff') && $product->get_type() =='external') ? ' target="_blank" rel="nofollow sponsored"' : '' ;?>
<?php $offer_coupon = get_post_meta( $post->ID, 'rehub_woo_coupon_code', true ) ?>
<?php $offer_coupon_date = get_post_meta( $post->ID, 'rehub_woo_coupon_date', true ) ?>
<?php $offer_coupon_mask = '1' ?>
<?php $offer_url = esc_url( $product->add_to_cart_url() ); ?>
<?php $coupon_style = $expired = ''; if(!empty($offer_coupon_date)) : ?>
    <?php 
    $timestamp1 = strtotime($offer_coupon_date);
    if(strpos($offer_coupon_date, ':') ===false){
        $timestamp1 += 86399;
    }
    $seconds = $timestamp1 - time(); 
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
        $coupon_style = ' expired_coupon';
        $expired = '1';
    }                 
    ?>
<?php endif ;?>

<?php do_action('woo_change_expired', $expired); //Here we update our expired ?>

<?php $classes[] = $coupon_style;?>
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?>
<?php if($coupon_mask_enabled =='1') {$classes[] = 'reveal_enabled';}?>
<article class="repick_item small_post col_item<?php if(rehub_option('rehub_grid_images') =='center') : ?> centered_im_grid<?php else : ?> contain_im_grid<?php endif ; ?> <?php echo implode(' ', $classes); ?>">
    <?php  $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
    <?php if ($badge !='' && $badge !='0') :?> 
        <?php echo re_badge_create('ribbonleft'); ?> 
	<?php elseif ( $product->is_featured() ) : ?>
		<?php echo apply_filters( 'woocommerce_featured_flash', '<span class="re-ribbon-badge left-badge badge_4"><span>' . esc_html__( 'Featured', 'rehub-theme' ) . '</span></span>', $post, $product ); ?>
	<?php endif; ?>
    <div class="button_action">
        <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>
            <span class="compare_for_grid floatleft">            
                <?php 
                    $cmp_btn_args = array(); 
                    $cmp_btn_args['class']= 'comparecompact';
                    if(rehub_option('compare_woo_cats') != '') {
                        $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                    }
                ?>                                                  
                <?php echo wpsm_comparison_button($cmp_btn_args); ?> 
            </span>
        <?php endif;?>                                                           
    </div>
    <figure class="width-100p position-relative mb20 <?php if(rehub_option('rehub_grid_images') =='center') : ?> pad_wrap<?php endif ;?>">
        <a href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>>
            <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?> 
        </a>
        <?php if(rehub_option('repick_social_disable') !='1' && function_exists('rehub_social_share')) :?> <?php echo rehub_social_share(''); ?> <?php endif;?> 
        <div class="favour_in_image favour_btn_red"> 
            <?php $wishlistadd = esc_html__('Save', 'rehub-theme');?>           
            <?php $wishlistadded = esc_html__('Saved', 'rehub-theme');?>
            <?php $wishlistremoved = esc_html__('Removed', 'rehub-theme');?>
            <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?>
        </div>        
        <?php if(function_exists('wprc_report_submission_form')) :?> <?php wprc_report_submission_form(); ?> <?php endif;?> 
        <?php do_action( 'repick_inside_grid_figure' ); ?>
    </figure>
	
	<div class="wrap_thing">
        <div class="hover_anons meta_enabled">
			<?php echo rh_expired_or_not($post->ID, 'span');?>  
            <h2>
				<a class="<?php echo getHotIconclass($post->ID); ?>" href="<?php echo esc_url($woolinktitle);?>"<?php echo ''.$wootargettitle;?>><?php the_title();?></a>
			</h2>
            <div class="repick_grid_meta">
                <?php if(rehub_option('exclude_author_meta') != 1) :?>
                <?php global $post; $author_id=$post->post_author; $name = get_the_author_meta( 'display_name', $author_id );?>
                <span class="admin_meta_grid">
                    <a class="admin" href="<?php echo get_author_posts_url( $author_id ) ?>"><?php echo get_avatar( $author_id, '22', '', $name ); ?><?php echo esc_attr($name); ?>
                    </a>
                </span> 
                <?php endif ?>
                <?php if(rehub_option('hotmeter_disable') !='1') :?><?php echo getHotThumb(get_the_ID(), true);?><?php endif;?>               
            </div>
			<?php do_action( 'rehub_vendor_show_action' ); ?> 
            <p><?php kama_excerpt('maxchar=320'); ?></p>
        </div>
		<div class="priced_block clearfix">
			<?php if ( $price_html = $product->get_price_html() ) : ?>
				<span class="rh_price_wrapper">
					<span class="price_count">
						<?php echo ''.$price_html; ?>
					</span>
				</span>
			<?php endif; ?>

			<?php if (rehub_option('woo_btn_disable') != '1'):?>
                <?php if ( $product->add_to_cart_url() !='') : ?>
                    <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
                        sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn woo_loop_btn btn_offer_block %s %s product_type_%s"%s %s>%s</a>',
                        esc_url( $product->add_to_cart_url() ),
                        esc_attr( $product->get_id() ),
                        esc_attr( $product->get_sku() ),
                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
                        $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
                        esc_attr( $product->get_type() ),
                        $product->get_type() =='external' ? ' target="_blank"' : '',
                        $product->get_type() =='external' ? ' rel="nofollow sponsored"' : '',
                        esc_html( $product->add_to_cart_text() )
                        ),
                    $product );?>  
				<?php endif; ?>
				
				<?php if ($coupon_mask_enabled =='1') :?>
					<?php wp_enqueue_script('zeroclipboard'); ?>
					<a class="woo_loop_btn coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_html($offer_coupon); ?>" data-codeid="<?php echo ''.$product->get_id() ?>" data-dest="<?php echo esc_url($offer_url) ?>"><?php if(rehub_option('rehub_mask_text') !='') :?><?php echo rehub_option('rehub_mask_text') ; ?><?php else :?><?php esc_html_e('Reveal coupon', 'rehub-theme') ?><?php endif ;?>
					</a>
				<?php else :?>
					<?php if(!empty($offer_coupon)) : ?>
						<?php wp_enqueue_script('zeroclipboard'); ?>
						<div class="rehub_offer_coupon not_masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_html($offer_coupon); ?>"><i class="rhicon rhi-scissors fa-rotate-180"></i><span class="coupon_text"><?php echo esc_html($offer_coupon); ?></span>
						</div>
					<?php endif;?>
				<?php endif;?> 
				
			<?php endif;?>            
		</div>
	</div>
</article>

<?php if (isset ($count) && isset ($count_ads) && isset ($count_ad_code) && !empty($count_ads) && !empty($count_ad_code) && in_array($count, $count_ads)) : ?>    
    <article class="repick_item col_item small_post inf_scr_item contain_im_grid">
        <figure class="mediad_wrap_pad width-100p position-relative mb20">
            <?php echo ''.$count_ad_code; ?>
        </figure>
        <div class="wrap_thing">
            <div class="hover_anons<?php if(rehub_option('enable_grid_meta_repick') == '1'){echo ' meta_enabled';} ?>">
                <?php if (isset ($count_ad_descs) && !empty($count_ad_descs) ) : ?>
                    <?php if ($count_ad_descs) {
                        $randomKey = array_rand($count_ad_descs, 1); 
                        $count_ad_desc = $count_ad_descs[$randomKey]; 
                        unset($count_ad_descs[$randomKey]);
                    }?>
                    <p><?php echo ''.$count_ad_desc; ?></p>
                <?php endif ;?>                         
            </div>
        </div>
    </article> 
<?php endif ;?>