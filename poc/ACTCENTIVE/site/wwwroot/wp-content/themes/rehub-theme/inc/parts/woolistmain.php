<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product; global $post; ?>
<?php if (empty( $product )) {return;}?>
<?php $classes = array('product', 'type-product');?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $offer_coupon_mask = (isset($offer_coupon_mask)) ? $offer_coupon_mask : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>
<?php $offer_coupon = get_post_meta( get_the_ID(), 'rehub_woo_coupon_code', true ) ?>
<?php $offer_coupon_date = get_post_meta( get_the_ID(), 'rehub_woo_coupon_date', true ) ?>
<?php $offer_url = esc_url( $product->add_to_cart_url() ); ?>
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
<?php $coupon_mask_enabled = (!empty($offer_coupon) && ($offer_coupon_mask =='1' || $offer_coupon_mask =='on') && $expired!='1') ? '1' : ''; ?>
<?php if($coupon_mask_enabled =='1') {$classes[] = 'reveal_enabled';}?>
<div class="r_offer_details news-community clearfix <?php echo rh_expired_or_not($post->ID, 'class');?> <?php echo implode(' ', $classes); ?>">
    <?php do_action('woocommerce_before_shop_loop_item');?>
    <?php  $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
    <?php if ($badge !='' && $badge !='0') :?> 
        <?php echo re_badge_create('ribbonleft'); ?>
    <?php elseif ( $product->is_featured() ) : ?>
        <?php echo apply_filters( 'woocommerce_featured_flash', '<span class="re-ribbon-badge badge_2 left-badge"><span>' . esc_html__( 'Featured!', 'rehub-theme' ) . '</span></span>', $post, $product ); ?>
    <?php endif; ?> 
    <div class="button_action">
        <div class="floatleft mr5">
            <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
            <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
            <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
        </div>
        <?php if(rehub_option('woo_quick_view')):?>
            <div class="floatleft">
                <?php echo RH_get_quick_view($post->ID, 'icon', 'pl10 pr10'); ?>
            </div>
        <?php endif;?>
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
	<div class="rh_grid_image_3_col">		
        <div class="rh_gr_img_first">
            <figure>               
                <a href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>>
                    <?php if ( $product->is_on_sale()) : ?>
                        <?php 
                        $percentage=0;
                        if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0 && is_numeric($product->get_price()) ){
                            $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
                        }
                        if ($percentage && $percentage>0 && !$product->is_type( 'variable' )) {
                            $sales_html = '<span class="onsale"><span>- ' . $percentage . '%</span></span>';
                        } else {
                            $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale">' . esc_html__( 'Sale!', 'rehub-theme' ) . '</span>', $post, $product );
                        }
                        ?>
                        <?php echo ''.$sales_html; ?>
                    <?php endif; ?>                            
                    <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?> 
                </a>
                <?php do_action( 'rh_woo_thumbnail_loop' ); ?>
            </figure>
                                                
        </div>        
        <?php $syncitem = $ceofferurl = ''; $countoffers = 0;?>
        <?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
            <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($post->ID);?>
            <?php if(!empty($itemsync)):?>
                <?php                            
                    $syncitem = $itemsync;                            
                ?>
                <?php $countoffers = rh_ce_found_total_offers($post->ID);?>
            <?php endif;?>
        <?php endif;?>        
    	<div class="rh_gr_top_middle mb10 colored_rate_bar">
            <?php $reviewscore = wpsm_reviewbox(array('compact'=>'smallsquare', 'id'=> $product->get_id()));?><?php echo ''.$reviewscore;?>
		    <h3 class="font130 mt0 mb10 mobilesblockdisplay <?php echo getHotIconclass($post->ID, true); ?>"><a href="<?php echo esc_url($woolink) ;?>"<?php echo ''.$wootarget ;?>><?php the_title();?></a></h3>
            <div class="meta post-meta">
                <?php rh_post_header_meta( true, true, false, false, true ); ?>                               
            </div>    
        </div>
        <div class="rh_gr_middle_desc">                         
    	    <div class="font90 lineheight20 mb10"><?php echo strip_shortcodes($post->post_excerpt); ?></div>
            <?php rh_wooattr_code_loop($attrelpanel);?> 
            <?php wc_get_template( 'loop/rating.php' );?> 
            <?php     
                $scoredetails = false;                        
                $criteriascore = rehub_exerpt_function(array('reviewcriterias'=> 'editor'));
                $prosvalues = get_post_meta($post->ID, '_review_post_pros_text', true);
                $consvalues = get_post_meta($post->ID, '_review_post_cons_text', true);
                if($criteriascore || $prosvalues) $scoredetails = true;
            ?>
            <?php if($scoredetails):?>
                <span class="rehub-main-color blockstyle r_show_hide mt10 font80"><?php esc_html_e('More details +', 'rehub-theme');?></span>
            <?php endif;?>                         
        </div>
                        
        <div class="rh_gr_btn_block">
            <div class="rehub-btn-font mb10 mt15 pricefont110 redbrightcolor"><?php wc_get_template( 'loop/price.php' ); ?></div>
            <?php if (rehub_option('woo_btn_disable') != '1'):?>
                <div class="block_btnblock priced_block">
                    <?php if($countoffers > 1):?>
                        <a href="<?php echo get_post_permalink($post->ID);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn woo_loop_btn btn_offer_block product_type_cegg">
                            <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?>
                                <?php echo rehub_option('rehub_btn_text_aff_links') ; ?>
                            <?php else :?>
                                <?php esc_html_e('Choose offer', 'rehub-theme') ?>
                            <?php endif ;?>
                        </a>
                    <?php elseif($countoffers == 1 && !empty($itemsync['url'])):?>
                        <?php $ceofferurl = apply_filters('rh_post_offer_url_filter', $itemsync['url']);?>
                        <a href="<?php echo esc_url($ceofferurl);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn woo_loop_btn btn_offer_block product_type_external" target="_blank" rel="nofollow sponsored">
                            <?php if(rehub_option('rehub_btn_text') !='') :?>
                                <?php echo rehub_option('rehub_btn_text') ; ?>
                            <?php else :?>
                                <?php esc_html_e('Buy Now', 'rehub-theme') ?>
                            <?php endif ;?>
                        </a>
                    <?php elseif ( $product->add_to_cart_url() !='') : ?>
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
                    <?php do_action( 'rh_woo_button_loop' ); ?>
                    <div class="mt5">
                        <?php if(!empty($syncitem)):?>
                            <div class="font80 greycolor lineheight15">
                            <?php echo rh_best_syncpost_deal($itemsync, 'mb10 compare-domain-icon', false);?>
                            <?php $amazonupdate = get_post_meta($post->ID, \ContentEgg\application\components\ContentManager::META_PREFIX_LAST_ITEMS_UPDATE.'Amazon', true);?>
                            <?php $product_update = \ContentEgg\application\helpers\TemplateHelper::getLastUpdateFormatted('Amazon', $post->ID);?>
                            <?php if($amazonupdate && $product_update):?>
                                <div class="font60 lineheight20 mt5"><?php esc_html_e('Last price update was:', 'rehub-theme');?> <?php echo ''.$product_update;?></div>
                            <?php endif;?>
                            </div>
                        <?php else:?>
                            <?php do_action( 'rehub_vendor_show_action' ); ?>        
                        <?php endif;?>  
                     </div>
                    <?php if ( $product->managing_stock() && ! $product->is_in_stock() ):?>
                        <div class="stock out-of-stock mt10"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
                    <?php endif;?>
                </div>  
            <?php endif;?>          
        </div>    
    </div>
    <?php if($scoredetails):?>
        <div class="open_dls_onclk flowhidden">
            <?php $summary = get_post_meta((int)$post->ID, '_review_post_summary_text', true);?>
            <?php $heading = rehub_exerpt_function(array('reviewheading'=> '1'));?>
            <?php if ($summary):?>
                <div class="border-grey-bottom mt15 pb15">
                    <?php if ($heading):?><div class="font140 fontbold mb15"><?php echo esc_html($heading);?></div><?php endif;?>
                    <?php echo rehub_kses($summary);?>
                </div>
            <?php endif;?>
            <?php $colclass = ($criteriascore) ? 'wpsm-one-third' : 'wpsm-one-half';?>
            <?php if($criteriascore) : ?>
                <div class="pt20 pb20 floatleft <?php echo ''.$colclass?>">
                    <?php echo ''.$criteriascore; ?>
                </div>
            <?php endif; ?>     
            <!-- PROS CONS BLOCK-->
            <?php if(!empty($prosvalues)):?>
                <div class="wpsm_pros pt20 pb20 floatleft font90 <?php echo ''.$colclass?>">
                    <div class="title_pros"><?php esc_html_e('PROS:', 'rehub-theme');?></div>
                    <ul>        
                        <?php $prosvalues = explode(PHP_EOL, $prosvalues);?>
                        <?php foreach ($prosvalues as $prosvalue) {
                            if(!$prosvalue) continue;
                            echo '<li class="mb5">'.$prosvalue.'</li>';
                        }?>
                    </ul>
                </div>
            <?php endif;?>  
            <?php if(!empty($consvalues)):?>
                <div class="disablemobilepadding wpsm_cons floatleft pt20 pb20 font90 <?php echo ''.$colclass?>">
                    <div class="title_cons"><?php esc_html_e('CONS:', 'rehub-theme');?></div>
                    <ul>
                        <?php $consvalues = explode(PHP_EOL, $consvalues);?>
                        <?php foreach ($consvalues as $consvalue) {
                            if(!$consvalue) continue;
                            echo '<li class="mb5">'.$consvalue.'</li>';
                        }?>
                    </ul>
                </div>
            <?php endif;?>  
            <!-- PROS CONS BLOCK END-->                                 
        </div>
    <?php endif;?>    
    <?php do_action('woocommerce_after_shop_loop_item');?>    
</div>