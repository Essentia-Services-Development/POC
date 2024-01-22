<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
  Name: Sorted list with store logo
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
$product_price_update = $items[0]['last_update'];
?>

<div class="rh_listoffers">
    <?php $i=0; foreach ($items as $key => $item): ?>
        <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
        <?php $offer_price_old = (!empty($item['price'])) ? $item['old_price'] : ''; ?>
        <?php $offer_post_url = $item['url'] ;?>
        <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
        <?php $aff_thumb = rh_ae_logo_get($item['orig_url']); if (empty($aff_thumb)) {$aff_thumb = $item['img'];} ?>
        <?php $offer_title = wp_trim_words( $item['title'], 10, '...' ); ?>
        <?php $i++;?>  
        <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = esc_html__('Buy this item', 'rehub-theme') ;?><?php endif ;?>  
        <div class="rh_listofferitem rh_list_mbl_im_left border-grey-bottom<?php if ($i == 1){echo' best_price_item';}?>">
            
                <div class="rh_listcolumn rh_listcolumn_image text-center">   
                    <a rel="nofollow sponsored" class="re_track_btn" target="_blank" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'lazy'=>false, 'height'=> 50, 'title' => $offer_title, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>                                    
                    </a>
                </div>
                <div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">
                    <div class="simple_title">
                        <a rel="nofollow sponsored" target="_blank" class="re_track_btn font100 blackcolor blockstyle rehub-main-font lineheight20" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                            <?php echo esc_attr($offer_title); ?>
                        </a>
                    </div>                                
                </div>                    
                <div class="rh_listcolumn rh_listcolumn_price text-center">
                    <?php if(!empty($offer_price)) : ?>
                        <span class="price_count">
                            <ins class="rehub-main-color font110"><?php echo TemplateHelper::formatPriceCurrency($item['price_raw'], $item['currency_code'], '', ''); ?></ins>
                            <?php if(!empty($offer_price_old)) : ?>
                                <del><?php echo ''.$item['old_price_raw'];?></del>
                            <?php endif ;?>                                      
                        </span>                          
                    <?php endif ;?>                        
                </div>
                <div class="text-right-align rh_listcolumn_btn">
                    <div class="priced_block clearfix">
                        <div>
	                        <a class="re_track_btn btn_offer_block re_track_btn" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?> target="_blank" rel="nofollow sponsored">
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
                    			                        
                        </div>
                    </div>
                </div>
                                                                      
        </div>
    <?php endforeach; ?>                 
    <?php if (!empty($product_price_update)) :?>
        <div class="last_update"><?php esc_html_e('Last price update: ', 'rehub-theme'); ?><?php echo esc_attr($product_price_update) ;?></div>
    <?php endif ;?>
    <?php if ($see_more_uri): ?>
            <div class="text-center see-more-cat"> 
                <a rel="nofollow sponsored" target="_blank" href="<?php echo esc_url($see_more_uri); ?>"><?php esc_html_e('See more from category', 'rehub-theme');?></a>
            </div>
    <?php endif; ?>    
</div>
<div class="clearfix"></div>