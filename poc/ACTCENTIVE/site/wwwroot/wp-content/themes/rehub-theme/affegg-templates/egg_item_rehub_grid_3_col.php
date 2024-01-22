<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
  Name: Grid of products (3 column)
 */
use Keywordrush\AffiliateEgg\TemplateHelper;   
?>
<?php if (isset($title) && $title): ?>
    <h3 class="cegg-shortcode-title"><?php echo esc_html($title); ?></h3>
<?php endif; ?>
<?php
// sort items by price
usort($items, function($a, $b) {
    if (!$a['price_raw']) return 1;
    if (!$b['price_raw']) return -1;
    return $a['price_raw'] - $b['price_raw'];
});
?>
<div class="masonry_grid_fullwidth col_wrap_three egg_grid">
<?php echo rh_generate_incss('masonry');?>
<?php $i=0; foreach ($items as $key => $item): ?>
    <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
    <?php $offer_price_old = (!empty($item['price'])) ? $item['old_price'] : ''; ?>
    <?php $offer_post_url = $item['url'] ;?>
    <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
    <?php $aff_thumb = $item['img'] ;?>
    <?php $offer_title = wp_trim_words( $item['title'], 20, '...' ); ?>
    <?php $i++;?>  
    <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = esc_html__('Buy this item', 'rehub-theme') ;?><?php endif ;?>  
    <div class="small_post col_item">
        <div>
            <figure class="width-100p position-relative mb20">
                <?php if (!empty($item['discount_perc'])): ?>
                    <span class="sale_a_proc">- <?php echo round($item['discount_perc']); ?>%</span>              
                <?php endif; ?>                                
                <a rel="nofollow sponsored" target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                    <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'width'=> 336, 'title' => $offer_title, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_336_220.png'));?>                                    
                </a>
            </figure>
            <div class="affegg_grid_title">
                <a rel="nofollow sponsored" target="_blank" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                    <?php echo esc_attr($offer_title); ?>
                </a>
            </div>
            <div class="buttons_col width-100p">
                <div class="priced_block clearfix">
                    <?php if(!empty($offer_price)) : ?>
                        <div class="rh_price_wrapper">
                            <span class="price_count">
                                <ins><?php echo TemplateHelper::formatPriceCurrency($item['price_raw'], $item['currency_code'], '', ''); ?></ins>
                                <?php if(!empty($offer_price_old)) : ?>
                                    <del><?php echo ''.$item['old_price_raw'];?></del>
                                <?php endif ;?>                                      
                            </span>                        
                        </div>
                    <?php endif ;?>
                    <div>
                        <a class="re_track_btn btn_offer_block" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?> target="_blank" rel="nofollow sponsored">
                            <?php echo esc_attr($btn_txt) ; ?>
                        </a>
                        <?php $offer_coupon_mask = 1 ?>
                        <?php if(!empty($item['extra']['coupon']['code_date'])) : ?>
                            <?php 
                            $timestamp1 = strtotime($item['extra']['coupon']['code_date']); 
                            $seconds = $timestamp1 - time(); 
                            $days = floor($seconds / 86400);
                            $seconds %= 86400;
                            if ($days > 0) {
                              $coupon_text = $days.' '.__('days left', 'rehub-theme');
                              $coupon_style = '';
                            }
                            elseif ($days == 0){
                              $coupon_text = esc_html__('Last day', 'rehub-theme');
                              $coupon_style = '';
                            }
                            else {
                              $coupon_text = esc_html__('Coupon is Expired', 'rehub-theme');
                              $coupon_style = 'expired_coupon';
                            }                 
                            ?>
                        <?php endif ;?>
                        <?php  if(!empty($item['extra']['coupon']['code'])) : ?>
                            <?php wp_enqueue_script('zeroclipboard'); ?>
                            <?php if ($offer_coupon_mask !='1' && $offer_coupon_mask !='on') :?>
                                <div class="rehub_offer_coupon not_masked_coupon <?php if(!empty($item['extra']['coupon']['code_date'])) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_attr($item['extra']['coupon']['code']) ?>"><i class="rhicon rhi-scissors fa-rotate-180"></i><span class="coupon_text"><?php echo esc_attr($item['extra']['coupon']['code']) ?></span></div>   
                            <?php else :?>
                                <?php wp_enqueue_script('affegg_coupons'); ?>
                                <div class="rehub_offer_coupon masked_coupon <?php if(!empty($item['extra']['coupon']['code_date'])) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_attr($item['extra']['coupon']['code']) ?>" data-codetext="<?php echo esc_attr($item['extra']['coupon']['code']) ?>" data-dest="<?php echo esc_url($item['url']) ?>"<?php echo ''.$item['ga_event'] ?>><?php if(rehub_option('rehub_mask_text') !='') :?><?php echo rehub_option('rehub_mask_text') ; ?><?php else :?><?php esc_html_e('Reveal coupon', 'rehub-theme') ?><?php endif ;?><i class="rhicon rhi-external-link-square"></i></div>   
                            <?php endif;?>
                            <?php if(!empty($item['extra']['coupon']['code_date'])) {echo '<div class="time_offer">'.$coupon_text.'</div>';} ?>    
                        <?php endif ;?> 
                        <div class="aff_tag mt10 small_size"><?php echo rehub_get_site_favicon($item['orig_url']); ?></div>                            
                    </div>
                </div>
            </div>            
        </div>
    </div>
<?php endforeach; ?>
</div>   
<div class="clearfix"></div>