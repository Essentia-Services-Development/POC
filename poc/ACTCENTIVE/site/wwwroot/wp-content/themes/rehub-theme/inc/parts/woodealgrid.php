<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
global $post; global $product;
?>  
<?php if (empty( $product ) ) {return;}?>
<?php $classes = array('product', 'col_item', 'type-product', 'border-lightgrey', 'hide_sale_price', 'rehub-main-smooth', 'position-relative', 'rh-shadow5', 'flowvisible', 'woodealgrid', 'whitebg');?>
<?php $attrelpanel = (isset($attrelpanel)) ? $attrelpanel : '';?>
<?php $woolinktype = (isset($woolinktype)) ? $woolinktype : '';?>
<?php $iscart = (isset($iscart)) ? $iscart : '';?>
<?php $woo_aff_btn = rehub_option('woo_aff_btn');?>
<?php $affiliatetype = ($product->get_type() =='external') ? true : false;?>
<?php $soldout = (isset($soldout)) ? $soldout : '';?>
<?php $price_meta = rehub_option('price_meta_woogrid');?>
<?php if($affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn)) :?>
    <?php $woolink = $product->add_to_cart_url(); $wootarget = ' target="_blank" rel="nofollow sponsored"';?>
<?php else:?>
    <?php $woolink = get_post_permalink($post->ID); $wootarget = '';?>
<?php endif;?>
<?php $sales_html = ''; if ( $product->is_on_sale()) : ?>
    <?php 
    $percentage=0;
    if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0 && is_numeric($product->get_price()) ) {
        $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
    }
    if ($percentage && $percentage>0 && !$product->is_type( 'variable' )) {
        $sales_html = '<div class="font90 accentblue"><span>-' . $percentage . '%</span></div>';
        $classes[] = 'prodonsale';
    }
    ?>
<?php endif; ?>
<?php $offer_coupon_date = get_post_meta( $post->ID, 'rehub_woo_coupon_date', true ) ?>
<?php $offer_coupon = get_post_meta( $post->ID, 'rehub_woo_coupon_code', true ) ?>
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
        $coupon_style = ' expired';
        $expired = '1';
    }                 
    ?>
<?php endif ;?>
<?php $classes[] = $coupon_style;?>
<?php $classes[] = rh_expired_or_not($post->ID, 'class');?>
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
<?php $coupon_mask_enabled = (!empty($offer_coupon) && $affiliatetype && ($woolinktype == 'aff' || $woo_aff_btn) && $expired!='1') ? '1' : ''; ?>
<?php $reveal_enabled = ($coupon_mask_enabled =='1') ? ' reveal_enabled' : '';?>
<?php $outsidelinkpart = ($coupon_mask_enabled=='1') ? ' data-codeid="'.$product->get_id().'" data-dest="'.esc_url( $product->add_to_cart_url() ).'" data-clipboard-text="'.$offer_coupon.'" class="re_track_btn masked_coupon"' : ' class="re_track_btn"';?>
<div class="<?php echo implode(' ', $classes); ?>">   
    <div class="pt15 pr15 pl15">  
        <div class="rh-flex-columns rh-flex-nowrap"> 
            <div class="woodealgridtitle"> 
                <?php if(($price_meta != '4' && $price_meta != '3')):?>
                    <div class="mb5 fontbold"><?php echo ''.$sales_html; ?></div>
                <?php else:?>
                    <div class="upper-text-trans font70 lineheight15 mb5">
                        <?php $categories = wc_get_product_terms($post->ID, 'product_cat');  ?>
                        <?php if (!empty($categories)) {
                            $first_cat = $categories[0]->term_id;
                            echo '<a href="'.get_term_link((int)$categories[0]->term_id, 'product_cat').'" class="woocat greycolor">'.$categories[0]->name.'</a>'; 
                        } ?>                         
                    </div>                     
                <?php endif ;?>
                <h3 class="text-clamp text-clamp-3 mb10 mt0 font110 fontnormal lineheight20 rh-flex-columns">
                    <?php if ( $product->is_featured() ) : ?>
                        <i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i>
                    <?php endif; ?>
                    <a href="<?php echo esc_url($woolink);?>"<?php echo ''.$wootarget;?> <?php echo ''.$outsidelinkpart; ?>><?php the_title();?></a>
                </h3> 
                <?php if($offer_coupon_date):?>
                    <div class="rh_opacity_5 font80"><i class="rhicon rhi-clock mr5"></i><?php echo ''.$coupon_text;?></div>
                <?php endif;?>
            </div>
            <?php if($price_meta != '4' && !$iscart):?>
                <div class="rh-flex-right-align pl15">
                    <?php if($syncitem && $price_meta == '1'):?>
                        <?php $celogo = \ContentEgg\application\helpers\TemplateHelper::getMerhantLogoUrl($syncitem, true);?>
                        <?php if($celogo) :?>
                            <div class="roundbd8im roundborder8 width-80 height-80 rh-flex-center-align rh-flex-justify-center text-center rh-shadow3 pt5 pb5 pl5 pr5">
                                <div>
                                    <img src="<?php echo ''.$celogo; ?>" alt="<?php echo esc_attr($syncitem['title']); ?>" height="50" />
                                </div> 
                            </div>
                        <?php endif ;?>                                            
                    <?php elseif($price_meta == '2'):?>
                        <div class="roundbd8im roundborder8 width-80 height-80 rh-flex-center-align rh-flex-justify-center text-center rh-shadow3 pt5 pb5 pl5 pr5">
                            <div>       
                                <?php WPSM_Woohelper::re_show_brand_tax('logo'); //show brand logo?>
                            </div>  
                        </div>   
                    <?php elseif($price_meta == '3'):?>
                        <div class="width-80 height-80 rh-flex-center-align rh-flex-justify-center text-center rh-shadow3 pt5 pb5 pl5 pr5 roundborder8">      
                            <div class="font150 fontbold">
                                <?php if($sales_html):?>
                                    <?php echo ''.$sales_html; ?>
                                <?php else:?>
                                    <?php wc_get_template( 'loop/price.php' ); ?>
                                <?php endif ;?>
                            </div>
                        </div>                           
                    <?php endif ;?>
                </div>
            <?php elseif($iscart):?>
                <div class="rh-flex-right-align pl15">
                    <div class="roundbd8im roundborder8 width-80 height-80 rh-flex-center-align rh-flex-justify-center text-center rh-shadow3 pt5 pb5 pl5 pr5">
                        <div>       
                        <?php echo WPSM_image_resizer::show_wp_image('woocommerce_thumbnail'); ?>
                        </div>  
                    </div> 
                </div>
            <?php endif ;?>
        </div>           
        <?php if($soldout):?>
            <?php rh_soldout_bar($post->ID);?>
        <?php endif; ?>         
        <?php rh_wooattr_code_loop($attrelpanel);?>     
        <?php do_action( 'woocommerce_after_shop_loop_item' );?>
        <?php if(rehub_option('woo_wholesale')):?>
            <div class="mb30 clearbox"></div>
        <?php else :?>
            <div class="mb15 clearbox"></div>
        <?php endif;?>
    </div>
    <div class="pt10 pr5 pl15 pb15 rh-flex-center-align abposbot">
        <div class="woodealgridbtn text-center">

            <?php if(!empty($offer_coupon)) : ?>
                <div class="clearfix">
                    <?php $offer_post_url = esc_url( $product->add_to_cart_url() );
                        $offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
                        $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
                        
                    ?>
                    <?php wp_enqueue_script('zeroclipboard'); ?>
                    <a class="btn_offer_block coupon_btn masked_coupon re_track_btn rehub_offer_coupon text-center woo_loop_btn" data-clipboard-text="<?php echo esc_html($offer_coupon); ?>" data-codeid="<?php echo ''.$product->get_id() ?>" data-dest="<?php echo esc_url($offer_url) ?>"><?php if(rehub_option('rehub_mask_text') !='') :?><?php echo rehub_option('rehub_mask_text') ; ?><?php else :?><?php esc_html_e('Reveal coupon', 'rehub-theme') ?><?php endif ;?>
                    </a>                            
                </div>
            <?php else :?>
                <?php if($countoffers > 1):?>
                    <a href="<?php echo get_post_permalink($post->ID);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn woo_loop_btn btn_offer_block   product_type_external" target="_blank" rel="nofollow sponsored">
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
                        ), $product );?>                                     
                <?php endif;?>                             
            <?php endif;?>
            <?php do_action( 'rh_woo_button_loop' ); ?>            
        </div>  
        <div class="rh-flex-right-align pr5 text-right-align button_action position-static rh-flex-center-align">
            <?php if(rehub_option('woo_quick_view')):?>
                <div class="floatleft rtlfloatleft">
                    <?php echo RH_get_quick_view($post->ID, 'icon', 'pl5 pr5'); ?>
                </div>
            <?php endif;?>
            <div class="floatleft rtlfloatleft">
                <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                <?php echo RH_get_wishlist($post->ID, '', $wishlistadded, $wishlistremoved);?>  
            </div>          
        </div>        
    </div>                                        
</div>