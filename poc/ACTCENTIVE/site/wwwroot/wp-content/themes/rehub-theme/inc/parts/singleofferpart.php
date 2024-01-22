<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
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
            $coupon_style = ' expired_coupon';
            $expired = '1';
        }
    ?>
<?php endif ;?> 
<?php do_action('post_change_expired', $expired); //Here we update our expired?>
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?> <?php $reveal_enabled = ($coupon_mask_enabled =='1') ? ' reveal_enabled' : '';?>
<div class="rehub_bordered_block rh_listofferitem rh_list_mbl_im_left <?php echo ''.$reveal_enabled; echo ''.$coupon_style; ?><?php echo ''.($disclaimer) ? ' mb0' : '';?>">
    <div class="rh-flex-center-align rh-flex-justify-center mobileblockdisplay">
            <div class="rh_listcolumn rh_listcolumn_image text-center">
            <a href="<?php echo esc_url($offer_url) ?>" target="_blank" rel="nofollow sponsored" class="re_track_btn">
                <?php if (!empty($offer_thumb) ) :?>
                    <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $offer_thumb, 'width'=> 90, 'title' => $offer_title, 'height'=> 90, 'crop'=> false));?>
                <?php else :?>
                    <?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'width'=> 90, 'title' => $offer_title, 'height'=> 90, 'crop'=> false));?>
                <?php endif ;?>
            </a>
            </div>
        <div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">
            <div class="font120 fontbold rehub-main-font lineheight20"><?php echo esc_html($offer_title) ;?></div>
            <div class="mt10 greycolor font90 lineheight20"><?php echo wp_kses_post($offer_desc);  ?></div>
        </div>
        <div class="rh_listcolumn rh_listcolumn_price text-center">
            <span class="rh_price_wrapper"><span class="price_count rehub-main-color fontbold"><ins><?php echo esc_html($offer_price) ?></ins><?php if($offer_price_old !='') :?> <del><?php echo esc_html($offer_price_old) ; ?></del><?php endif ;?></span></span>
            <div class="brand_logo_small">              
                <?php WPSM_Postfilters::re_show_brand_tax('logo'); //show brand logo?>                 
            </div>
        </div>
        <div class="text-right-align rh_listcolumn_btn">
            <div class="priced_block clearfix">
                <div>
                    <a href="<?php echo esc_url ($offer_url) ?>" class="re_track_btn btn_offer_block" target="_blank" rel="nofollow sponsored">
                        <?php if($offer_btn_text !='') :?>
                            <?php echo ''.$offer_btn_text ; ?>
                        <?php elseif(rehub_option('rehub_btn_text') !='') :?>
                            <?php echo rehub_option('rehub_btn_text') ; ?>
                        <?php else :?>
                            <?php esc_html_e('Buy this item', 'rehub-theme') ?>
                        <?php endif ;?>
                    </a>
                </div>
            <?php if ($coupon_mask_enabled =='1') :?>
                <?php wp_enqueue_script('zeroclipboard'); ?>
                <a class="mt10 coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_html($offer_coupon); ?>" data-codeid="<?php echo (int)$postid?>" data-dest="<?php echo esc_url($offer_url) ?>">
                    <?php if($offer_btn_text !='') :?>
                        <?php echo esc_html ($offer_btn_text) ; ?>
                    <?php elseif(rehub_option('rehub_mask_text') !='') :?>
                        <?php echo rehub_option('rehub_mask_text') ; ?>
                    <?php else :?>
                        <?php esc_html_e('Reveal coupon', 'rehub-theme') ?>
                    <?php endif ;?>                 
                </a>
            <?php else :?>
                <?php if(!empty($offer_coupon)) : ?>
                    <?php wp_enqueue_script('zeroclipboard'); ?>
                    <div class="mt10 rehub_offer_coupon not_masked_coupon <?php if(!empty($offer_coupon_date)) {echo ''.$coupon_style ;} ?>" data-clipboard-text="<?php echo esc_html($offer_coupon); ?>"><i class="rhicon rhi-scissors fa-rotate-180"></i><span class="coupon_text"><?php echo esc_html($offer_coupon); ?></span>
                    </div>
                <?php endif;?>
            <?php endif;?>                  
            <?php if(!empty($offer_coupon_date)) {echo '<div class="time_offer">'.$coupon_text.'</div>';} ?>
            </div>
        </div>
    </div>
</div>
<?php if($disclaimer):?>
    <div class="rev_disclaimer lightgreybg font60 greycolor lineheight15 pt5 pb5 pl15 pr15"><?php echo wp_kses($disclaimer, 'post');?></div>
<?php endif;?> 
<?php //save clean price to post meta
    $offer_price_clean = rehub_price_clean($offer_price); 
    $offer_price_clean_old = get_post_meta( $postid, 'rehub_main_product_price', true );
    if ( $offer_price_clean !='' && $offer_price_clean_old !='' && $offer_price_clean != $offer_price_clean_old ){
        update_post_meta($postid, 'rehub_main_product_price', $offer_price_clean); 
    }
    elseif($offer_price_clean !='' && $offer_price_clean_old =='') {
        update_post_meta($postid, 'rehub_main_product_price', $offer_price_clean); 
    }
 ?> 