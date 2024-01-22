<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $post;?>
<?php $postid = $post->ID; ?>
<?php 
    $offer_post_url = esc_url(get_post_meta( $postid, 'rehub_offer_product_url', true ));
    $offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
?>
<?php $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url ); ?>
<?php if(empty($offer_url)) {$offer_url = get_the_permalink($postid);}?>
<?php $offer_coupon = get_post_meta( $postid, 'rehub_offer_product_coupon', true ); ?>
<?php $offer_coupon_date = get_post_meta( $postid, 'rehub_offer_coupon_date', true ); ?>
<?php $offer_coupon_mask = get_post_meta( $postid, 'rehub_offer_coupon_mask', true ); ?>
<?php $offer_price = get_post_meta( $postid, 'rehub_offer_product_price', true );$offer_price = apply_filters('rehub_create_btn_price', $offer_price);?>
<?php $offer_price_old = get_post_meta( $postid, 'rehub_offer_product_price_old', true );$offer_price_old = apply_filters('rehub_create_btn_price_old', $offer_price_old);?>
<?php $offer_btn_text = get_post_meta( $postid, 'rehub_offer_btn_text', true );?>
<?php $offer_desc_meta = get_post_meta( $postid, 'rehub_offer_product_desc', true );?>
<?php $offer_title_meta = $offer_title = get_post_meta( $postid, 'rehub_offer_name', true );?>
<?php $offer_desc = (!empty($offer_desc_meta)) ? $offer_desc_meta : kama_excerpt('maxchar=200&echo=false');?>
<?php $offer_title = (!empty($offer_title_meta)) ? $offer_title_meta : get_the_title(); ?>
<?php $disclaimer = get_post_meta($post->ID, 'rehub_offer_disclaimer', true);?>
<?php $discountpercentage = get_post_meta($post->ID, 'rehub_offer_discount', true);?>
<?php $coupon_style = $expired =''; if(!empty($offer_coupon_date)) : ?>
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
<?php endif;?>
<?php $aff_link = (isset($aff_link)) ? $aff_link : '';?>
<?php 
if ($aff_link == '1') {
    $link = $offer_url;
    $target = ' rel="nofollow sponsored" target="_blank"';  
}
else {
    $link = get_the_permalink();
    $target = '';              
}
?>
<?php
$dealcat = '';       
if(rehub_option('enable_brand_taxonomy') == 1){ 
    $dealcats = wp_get_post_terms($postid, 'dealstore', array("fields" => "all")); 
    if( ! empty( $dealcats ) && ! is_wp_error( $dealcats ) ) {
        $dealcat = $dealcats[0];                   
    }                               
}
?>
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?>
<?php $outsidelinkpart = ($coupon_mask_enabled=='1' && $aff_link=='1') ? ' data-codeid="'.$postid.'" data-dest="'.$offer_url.'" data-clipboard-text="'.$offer_coupon.'" class="masked_coupon"' : ' class="re_track_btn"';?>
<?php 
if (!empty($offer_coupon)) {
    $deal_type = ' coupontype';
    $deal_type_string = esc_html__('Coupon', 'rehub-theme');
}
elseif (!empty($offer_price_old)){
    $deal_type = ' saledealtype';
    $deal_type_string = esc_html__('Sale', 'rehub-theme');
}
else {
    $deal_type = ' defdealtype';
    $deal_type_string = esc_html__('Deal', 'rehub-theme');
}
?>
<div class="rh_offer_list <?php echo ''.$coupon_style.$deal_type; ?><?php echo rh_expired_or_not($postid, 'class');?><?php echo ''.($disclaimer) ? ' pt0 pb0 pl0 pr0 w_disclaimer' : '';?>"> 
    <?php echo re_badge_create('ribbonleft'); ?>         
    <div class="rh_grid_image_3_col">
        <div class="rh_gr_img_first offer_thumb"> 
            <div class="border-grey deal_img_wrap position-relative text-center width-100"> 
            <div class="favorrightside wishonimage"><?php echo RH_get_wishlist($postid);?></div>      
            <a title="<?php echo ''.$offer_title ;?>" href="<?php echo ''.$link;?>" <?php echo ''.$target;?> <?php echo ''.$outsidelinkpart; ?>>
            <?php if ($discountpercentage) :?>
                <span class="height-80 rh-flex-center-align rh-flex-justify-center sale_tag_inwoolist text-center"><div class="font150 sale_letter fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0"><?php echo esc_html($discountpercentage);?></div></span>
            <?php elseif (!has_post_thumbnail() && !empty($offer_price_old) && !empty($offer_price)) :?>
                <?php           
                    $offer_pricesale = (float)rehub_price_clean($offer_price); //Clean price from currence symbols
                    $offer_priceold = (float)rehub_price_clean($offer_price_old); //Clean price from currence symbols
                    if ($offer_priceold !='0' && is_numeric($offer_priceold) && $offer_priceold > $offer_pricesale) {
                        $off_proc = 0 -(100 - ($offer_pricesale / $offer_priceold) * 100);
                        $off_proc = round($off_proc);
                        echo '<span class="height-80 rh-flex-center-align rh-flex-justify-center sale_tag_inwoolist text-center"><div class="font130 fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0"><div class="sale_letter font150 fontbold greencolor mb0 ml0 mr0 mt0 overflow-elipse pb0 pl0 pr0 pt0">'.$off_proc.'%</div></div></span>';
                    }
                ?>
            <?php else :?>              
                <?php WPSM_image_resizer::show_static_resized_image(array('thumb'=> true, 'crop'=> false, 'height'=> 92));?>
            <?php endif;?>
            </a>
            <div class="<?php echo esc_attr($deal_type);?>_deal_string deal_string border-top font70 lineheight25 text-center upper-text-trans"><?php echo esc_attr($deal_type_string);?></div>
            </div>

        </div>
        <div class="rh_gr_top_middle"> 
            <div class="woo_list_desc">
                <div class="woolist_meta mb10">
                    <?php if(rehub_option('exclude_date_meta') != 1):?>
                        <span class="date_ago mr5">
                            <i class="rhicon rhi-clock"></i> 
                            <?php if(rehub_option('date_publish')):?>
                                <?php printf( esc_html__( '%s ago', 'rehub-theme' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
                            <?php else:?>
                                <?php printf( esc_html__( '%s ago', 'rehub-theme' ), human_time_diff( get_the_modified_time( 'U' ), current_time( 'timestamp' ) ) ); ?>
                            <?php endif;?>
                        </span>
                    <?php endif;?>

                    <?php if(!empty($offer_coupon_date)) {echo '<span class="listtimeleft mr5 rh-nowrap"> <i class="rhicon rhi-hourglass"></i> '.$coupon_text.'</span>';} ?>   
                    <?php 
                        $verify = get_post_meta( $postid, 'rehub_offer_verify_label', true );
                        if($verify) {echo '<span class="verifymeta mr5 greencolor"><i class="rhicon rhi-shield-check"></i> '.esc_attr($verify).'</span>';}
                    ?>                      
                </div>                        
                <h2 class="font110 mb10 mt0 moblineheight20 <?php echo getHotIconclass($postid, true); ?>"><a href="<?php echo ''.$link;?>" <?php echo ''.$target;?> <?php echo ''.$outsidelinkpart; ?>><?php echo rh_expired_or_not($postid, 'span');?><?php echo ''.$offer_title ;?></a></h2>
                <?php rehub_generate_offerbtn('showme=price&wrapperclass=pricefont110 rehub-main-color mobpricefont90 fontbold mb5 mr10 lineheight20 floatleft');?>
                <?php 
                    if($offer_price_old && $offer_price){
                        $offer_pricesale = (float)rehub_price_clean($offer_price); 
                        $offer_priceold = (float)rehub_price_clean($offer_price_old);
                        if ($offer_priceold !='0' && is_numeric($offer_priceold) && $offer_priceold > $offer_pricesale) {
                            $off_proc = 0 -(100 - ($offer_pricesale / $offer_priceold) * 100);
                            $off_proc = round($off_proc);
                            echo '<span class="rh-label-string mr10 mb5 floatleft rehub-sec-color-bg">'.$off_proc.'%</span>';
                        }
                    }

                ?> 
                <?php $custom_notice = get_post_meta($postid, '_notice_custom', true);?>
                <?php 
                    if($custom_notice){
                        echo '<div class="rh_custom_notice mr10 mb5 lineheight20 floatleft fontbold font90 rehub-sec-color">'.esc_html($custom_notice).'</div>' ;
                    }
                    elseif (!empty($dealcat)) {
                        $dealcat_notice = get_term_meta($dealcat->term_id, 'cashback_notice', true );
                        if($dealcat_notice){
                            echo '<div class="rh_custom_notice mr10 mb5 lineheight20 floatleft fontbold font90 rehub-sec-color">'.esc_html($dealcat_notice).'</div>' ;
                        }
                    } 
                ?>                 
                <div class="clearfix"></div>                                                                                         
            </div>               
        </div>
        <div class="rh_gr_middle_desc font80 lineheight15">
            <?php echo (wp_kses_post($offer_desc)); ?>
        </div>  
        <?php rh_post_code_loop();?>
        <div class="rh_gr_btn_block">
            <?php rehub_generate_offerbtn('btn_more=yes&showme=button&wrapperclass=mobile_block_btnclock mb0');?>
        </div>        
    </div>
    <?php if($disclaimer):?>
        <div class="rev_disclaimer lightgreybg font60 greycolor lineheight15 pt5 pb5 pl15 pr15"><?php echo wp_kses($disclaimer, 'post');?></div>
    <?php endif;?>    
</div>