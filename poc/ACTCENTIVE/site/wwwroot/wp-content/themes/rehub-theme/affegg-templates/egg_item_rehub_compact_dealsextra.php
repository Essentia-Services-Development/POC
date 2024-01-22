<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/*
  Name: Compact product cart with extra and deals
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
//Check if post has meta for first product
$aff_thumb_overwrite = get_post_meta( get_the_ID(), 'affegg_image_over', true );
$offer_desc_overwrite = get_post_meta( get_the_ID(), 'affegg_desc_over', true );
//Data for main product
$aff_thumb_first = (!empty ($aff_thumb_overwrite)) ? $aff_thumb_overwrite : $items[0]['img'];
$offer_title_first = wp_trim_words( $items[0]['title'], 20, '...' );
$offer_url_first = $items[0]['url'];
$offer_url_first = apply_filters('rh_post_offer_url_filter', $offer_url_first );
$offer_desc_first = (!empty ($aff_thumb_overwrite)) ? $aff_thumb_overwrite : $items[0]['description'] ;
$best_price_value = str_replace(' ', '', $items[0]['price']);
if($best_price_value =='0') {$best_price_value = '';}
$best_price_currency = $items[0]['currency'];
$best_price_text = (rehub_option('rehub_btn_text_best') !='') ? esc_html(rehub_option('rehub_btn_text_best')) : esc_html__('Best price', 'rehub-theme');
if (!empty ($items[0]['features'])) {$attributes = $items[0]['features'];}
if (!empty ($items[0]['extra']['images'])) {$gallery_images = $items[0]['extra']['images'];}
if (!empty($items[0]['extra']['comments'])) {$import_comments = $items[0]['extra']['comments'];} 
?>
<?php wp_enqueue_script('rhwootabs', get_template_directory_uri() . '/js/wootabs.js', array('jquery'), '1.0', true);?>
<div class="rehub_woo_review clearbox product_egg_extra">
        <ul class="rehub_woo_tabs_menu">
            <li><?php esc_html_e('Product', 'rehub-theme') ?></li>
            <li class="dealslist"><?php esc_html_e('Deals', 'rehub-theme') ?></li>
            <?php if (!empty ($attributes)) :?><li><?php esc_html_e('Specification', 'rehub-theme') ?></li><?php endif ;?>
            <?php if (!empty ($gallery_images)) :?><li><?php esc_html_e('Photos', 'rehub-theme') ?></li><?php endif ;?>
            <?php if (!empty ($import_comments)) :?><li class="affrev"><?php esc_html_e('Last reviews', 'rehub-theme') ?></li><?php endif ;?>
        </ul>
    <div class="rehub_feat_block">
        <div class="rehub_woo_review_tabs rh_listitem">
            <div class="rh-flex-center-align rh-flex-justify-center mobileblockdisplay">
                <div class="listbuild_image listitem_column text-center">   
                    <?php if (!empty($aff_thumb_first) ) :?>  
                        <img src="<?php $params = array( 'width' => 126 ); echo bfi_thumb( esc_attr($aff_thumb_first), $params ); ?>" alt="<?php echo esc_attr($offer_title_first); ?>" />
                    <?php else :?>
                        <?php $image_id = get_post_thumbnail_id(get_the_ID());  $image_offer_url = wp_get_attachment_url($image_id);?>
                        <img src="<?php $params = array( 'width' => 126 ); echo bfi_thumb( $image_offer_url, $params ); ?>" alt="<?php echo esc_attr($offer_title_first); ?>" />
                    <?php endif ;?>                                    
                </div>
                <div class="rh-flex-grow1 listitem_title listitem_column">
                <?php if ($items[0]['manufacturer']): ?>
                    <p class="small_size"><span class="aff_manufactor"><?php echo esc_attr ($items[0]['manufacturer']); ?></span></p>
                <?php endif; ?>                 
                <?php if ($offer_desc_first): ?>
                    <div class="font90 greycolor lineheight20"><?php rehub_truncate('maxchar=200&text='.$offer_desc_first.''); ?></div>
                <?php endif; ?>                                                
                </div>
                <div class="listbuild_btn listitem_column text-center pr10">
                    <?php if(!empty($best_price_value)) : ?>
                        <div class="deals-box-pricebest">
                        <span><?php esc_html_e('Start from: ', 'rehub-theme');?></span>
                            <?php echo TemplateHelper::formatPriceCurrency($items[0]['price_raw'], $items[0]['currency_code'], '', ''); ?>                       
                        </div>                                                       
                    <?php endif ;?> 
                    <?php if(!empty($offer_url_first)) : ?> 
                        <div class="priced_block">
                            <div>
                                <a class="btn_offer_block" href="<?php echo esc_url($offer_url_first) ?>"<?php echo ''.$items[0]['ga_event'] ?> target="_blank" rel="nofollow sponsored">
                                    <?php echo esc_attr($best_price_text); ?>                                    
                                </a>
                                
                            </div>
                        </div>
                    <?php endif ;?>
                    <span class="aff_tag mtinside"><?php echo rehub_get_site_favicon($items[0]['orig_url']); ?></span>
                </div>
            </div>
        </div>

        <div class="rehub_woo_review_tabs dealslist">
            <?php $i=0; foreach ($items as $key => $item): ?>
                <?php $offer_price = (!empty($item['price'])) ? $item['price'] : ''; ?>
                <?php $offer_price_old = str_replace(' ', '', $item['old_price']); if($offer_price_old =='0') {$offer_price_old = '';}?>
                <?php $offer_post_url = $item['url'] ;?>
                <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>
                <?php $aff_thumb = $item['img'] ;?>
                <?php $offer_title = wp_trim_words( $item['title'], 10, '...' ); ?>
                <?php $offer_desc = $item['description'] ;?>
                <?php $i++;?>  
                <?php if(rehub_option('rehub_btn_text') !='') :?><?php $btn_txt = rehub_option('rehub_btn_text') ; ?><?php else :?><?php $btn_txt = esc_html__('Buy this item', 'rehub-theme') ;?><?php endif ;?>  
                <div class="rh_listofferitem rh_list_mbl_im_left border-grey-bottom<?php if ($i == 1){echo' best_price_item';}?>">
                    <div class="rh-flex-center-align rh-flex-justify-center pt15 pb15 mobileblockdisplay">
                        <div class="rh_listcolumn rh_listcolumn_image text-center">   
                            <a rel="nofollow sponsored" target="_blank" class="re_track_btn" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                                <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'height'=> 90, 'title' => $offer_title, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>                                    
                            </a>
                        </div>
                        <div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">
                            <div class="simple_title mb15">
                                <a rel="nofollow sponsored" target="_blank" class="re_track_btn font100 blackcolor blockstyle rehub-main-font lineheight20" href="<?php echo esc_url($afflink) ?>"<?php echo ''.$item['ga_event'] ?>>
                                    <?php echo esc_attr($offer_title); ?>
                                </a>
                            </div>                                
                        </div>                    
                        <div class="rh_listcolumn rh_listcolumn_price text-center">
                            <?php if(!empty($offer_price)) : ?>
                                    <span class="price_count rehub-main-color fontbold">
                                        <?php echo TemplateHelper::formatPriceCurrency($item['price_raw'], $item['currency_code'], '<span class="cegg-currency">', '</span>'); ?>
                                        <?php if(!empty($offer_price_old)) : ?>
                                            <strike class="blockstyle"><span class="amount font70 rh_opacity_3 fontnormal"><?php echo TemplateHelper::formatPriceCurrency($item['old_price_raw'], $item['currency_code'], '', ''); ?></span></strike>
                                        <?php endif ;?>                                      
                                    </span>                         
                            <?php endif ;?>                       
                        </div>

                        <div class="text-right-align rh_listcolumn_btn">
                            <div class="priced_block mb0 clearfix">
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
                                                                
                                </div>
                            </div>
                           <div class="aff_tag mt10"><?php echo rehub_get_site_favicon($item['orig_url']); ?></div> 
                           <small class="small_size available_stock"><?php if ($item['in_stock']): ?><span class="yes_available"><?php esc_html_e('In stock', 'rehub-theme') ;?></span><?php endif; ?></small>                                    
                        </div>
                    </div>                                                          
                </div>
            <?php endforeach; ?>
            <?php if (!empty($product_price_update)) :?>
                <div class="last_update"><?php esc_html_e('Last price update: ', 'rehub-theme'); ?><?php echo ''.$product_price_update ;?></div>
            <?php endif ;?>                    
            <div class="clearfix"></div>                
        </div>

        <?php if (!empty ($attributes)) :?>
            <div class="rehub_woo_review_tabs">
                <div>
                    <table class="shop_attributes">
                        <tbody>
                        <?php foreach ($attributes as $feature): ?>
                            <tr>
                                <th><?php echo esc_html($feature['name']) ?></th>
                                <td><p><?php echo esc_html($feature['value']) ?></p></td>
                            </tr>
                        <?php endforeach; ?>                                        
                        </tbody>
                    </table>
                </div>                               
            </div>
        <?php endif ;?> 
        <?php if (!empty ($gallery_images)) :?>
            <div class="rehub_woo_review_tabs pretty_woo modulo-lightbox">
                <?php $randomgallery = 'rh_ceam_gallery'.rand(1, 50);?>            
                <?php wp_enqueue_script('modulobox'); wp_enqueue_style('modulobox');
                    foreach ($gallery_images as $gallery_img) {
                        ?> 
                        <a data-rel="<?php echo ''.$randomgallery;?>" href="<?php echo esc_url($gallery_img) ;?>" data-thumb="<?php echo esc_url($gallery_img);?>" data-title="<?php echo esc_attr($offer_title);?>">                        
                            <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $gallery_img, 'width'=> 100, 'height'=> 100, 'title' => $offer_title_first, 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?> 
                        </a>
                        <?php
                    }
                ?>
            </div>           
        <?php endif ;?>
        <?php if (!empty ($import_comments)) :?>
            <div class="rehub_woo_review_tabs affrev">
                <?php foreach ($import_comments as $key => $comment): ?>
                    <div class="helpful-review black">
                        <div class="quote-top"><i class="rhicon rhi-quote-left"></i></div>
                        <div class="quote-bottom"><i class="rhicon rhi-quote-right"></i></div>
                        <div class="text-elips">
                            <span><?php echo esc_html($comment['comment']); ?></span>
                        </div>
                        <?php if (!empty($comment['date'])): ?>
                            <span class="helpful-date"><?php echo gmdate("F j, Y", $comment['date']); ?></span>
                        <?php endif ;?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif ;?>                                                         
    </div>
</div>
<div class="clearfix"></div>