<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $product, $post;?>
<?php                             
    if ( post_password_required() ) {
        echo '<div class="rh-container"><div class="rh-content-wrap clearfix"><div class="main-side clearfix full_width" id="content"><div class="post text-center">';
            echo get_the_password_form();
        echo '</div></div></div></div>';
        return;
    }
?>
<?php 
$script = '
var cenoprice = document.getElementById("nopricehsection");
if(cenoprice !== null){
    document.getElementById("section-woo-ce-pricehistory").remove();
    document.getElementById("tab-title-woo-ce-pricehistory").remove();
}
    ';
wp_add_inline_script('rehub', $script);
?>
<?php $unique_id = $module_id = $itemsync = $syncitem = $youtubecontent = $replacetitle = '';?>
<?php $postid = $post->ID;?>
<?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
    <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($postid);?>
    <?php $domain = $merchant = '';?>
    <?php if(!empty($itemsync)):?>
        <?php 
            $unique_id = $itemsync['unique_id']; 
            $module_id = $itemsync['module_id'];
            $domain = $itemsync['domain']; 
            $merchant = $itemsync['merchant'];                            
            $syncitem = $itemsync;                            
        ?>
    <?php endif;?>
    
    <?php $youtubecontent = \ContentEgg\application\components\ContentManager::getViewData('Youtube', $postid);?>    
<?php endif;?>
<?php if (defined('\ContentEgg\PLUGIN_PATH') && !empty($itemsync)) :?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <div class="ce_woo_auto ce_woo_auto_sections full_width" id="content">
            <div class="post">
                <?php do_action( 'woocommerce_before_main_content' );?>
                <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php do_action( 'woocommerce_before_single_product' );?>                    
                    <div id="product-<?php echo (int)$postid; ?>" <?php post_class(); ?>>
                        <div class="rh_post_layout_compare_full padd20">
                            <?php echo rh_generate_incss('cewoosection');?>
                            <div class="wpsm-one-third wpsm-column-first tabletblockdisplay compare-full-images modulo-lightbox mb30">
                                <?php 
                                    wp_enqueue_script('modulobox');
                                    wp_enqueue_style('modulobox');
                                ?>                                                         
                                <figure class="text-center">
                                    <?php  $badge = get_post_meta($postid, 'is_editor_choice', true); ?>
                                    <?php if ($badge !='' && $badge !='0') :?> 
                                        <?php echo re_badge_create('ribbon'); ?>
                                    <?php else:?>                                        
                                        <?php woocommerce_show_product_sale_flash();?>
                                    <?php endif;?>
                                    <?php           
                                        $image_id = get_post_thumbnail_id($postid);  
                                        $image_url = wp_get_attachment_image_src($image_id,'full');
                                        $image_url = $image_url[0]; 
                                    ?> 
                                    <a data-rel="rh_top_gallery" href="<?php echo ''.$image_url;?>" target="_blank" data-thumb="<?php echo ''.$image_url;?>">            
                                        <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'thumb'=> true, 'crop'=> false, 'height'=> 500, 'width'=> 500,'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_500_500.png'));?>
                                    </a>
                                </figure>
                                <?php $post_image_gallery = $product->get_gallery_image_ids();?>
                                <?php if(!empty($post_image_gallery)) :?> 
                                    <div class="rh-flex-eq-height rh_mini_thumbs compare-full-thumbnails mt15 mb15">
                                        <?php foreach($post_image_gallery as $key=>$image_gallery):?>
                                            <?php if(!$image_gallery) continue;?>
                                            <?php $image = wp_get_attachment_image_src($image_gallery, 'full'); $imgurl = (!empty($image[0])) ? $image[0] : ''; ?>
                                            <a data-rel="rh_top_gallery" data-thumb="<?php echo esc_url($imgurl);?>" href="<?php echo esc_url($imgurl);?>" target="_blank" class="rh-flex-center-align mb10" data-title="<?php echo esc_attr(get_post_field( 'post_excerpt', $image_gallery));?>"> 
                                                <?php echo WPSM_image_resizer::show_wp_image('woocommerce_gallery_thumbnail', $image_gallery); ?> 
                                            </a>                               
                                        <?php endforeach;?> 
                                        <?php if(!empty($youtubecontent)):?>
                                            <?php foreach($youtubecontent as $videoitem):?>
                                                <a href="<?php echo esc_url($videoitem['url']);?>" data-rel="rh_top_gallery" target="_blank" class="rh-flex-center-align mb10 rh_videothumb_link" data-poster="<?php echo parse_video_url($videoitem['url'], 'hqthumb'); ?>" data-thumb="<?php echo esc_url($videoitem['img'])?>"> 
                                                    <img src="<?php echo esc_url($videoitem['img'])?>" alt="<?php echo esc_attr($videoitem['title'])?>" width="115" height="65" />
                                                </a>                                                    
                                            <?php endforeach;?> 
                                        <?php endif;?>
                                        <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=no&title=no');?>                                                                  
                                    </div>                                      
                                <?php else :?>
                                    <?php if (!empty($itemsync['extra']['imageSet'])){
                                        $ceimages = $itemsync['extra']['imageSet'];
                                    }elseif (!empty($itemsync['extra']['images'])){
                                        $ceimages = $itemsync['extra']['images'];
                                    }
                                    else {
                                        $qwantimages = \ContentEgg\application\components\ContentManager::getViewData('GoogleImages', $postid);
                                        if(!empty($qwantimages)) {
                                            $ceimages = wp_list_pluck( $qwantimages, 'img' );
                                        }else{
                                            $ceimages = '';                                                
                                        } 
                                    } ?> 
                                    <?php if(!empty($ceimages)):?>
                                        <div class="rh_mini_thumbs compare-full-thumbnails limited-thumb-number mt15 mb15">
                                            <?php foreach ((array)$ceimages as $gallery_img) :?>
                                                <?php if (isset($gallery_img['LargeImage'])){
                                                    $image = $gallery_img['LargeImage'];
                                                }else{
                                                    $image = $gallery_img;
                                                }?>                                               
                                                <a data-thumb="<?php echo esc_url($image)?>" data-rel="rh_top_gallery" href="<?php echo esc_url($image); ?>" data-title="<?php echo esc_attr($itemsync['title']);?>"> 
                                                    <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $image, 'height'=> 65, 'width'=> 65, 'title' => $itemsync['title'], 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>  
                                                </a>
                                            <?php endforeach;?>
                                            <?php if(!empty($youtubecontent)):?>
                                                <?php foreach($youtubecontent as $videoitem):?>
                                                    <a href="<?php echo esc_url($videoitem['url']);?>" data-rel="rh_top_gallery" target="_blank" class="mb10 rh-flex-center-align rh_videothumb_link" data-poster="<?php echo parse_video_url($videoitem['url'], 'hqthumb'); ?>" data-thumb="<?php echo esc_url($videoitem['img'])?>"> 
                                                        <img src="<?php echo esc_url($videoitem['img'])?>" alt="<?php echo esc_attr($videoitem['title'])?>" width="115" height="65" />
                                                    </a>                                                    
                                                <?php endforeach;?> 
                                            <?php endif;?>
                                            <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link&rel=rh_top_gallery&wrapper=no&title=no');?>                                                                                             
                                        </div>
                                    <?php endif;?>                
                                <?php endif;?> 
                                <?php do_action('rh_woo_after_single_image');?> 
                            </div>
                            <div class="wpsm-two-third tabletblockdisplay wpsm-column-last">
                                <div class="title_single_area mb15">
                                    <h1 class="<?php if(rehub_option('wishlist_disable') !='1') :?><?php echo getHotIconclass($postid, true); ?><?php endif ;?>"><?php the_title(); ?></h1>
                                    <?php do_action('rh_woo_single_product_title');?>
                                </div> 
                                <div class="meta-in-compare-full rh-flex-center-align mobileblockdisplay woo_top_meta">
                                    <?php if ( 'no' !== get_option( 'woocommerce_enable_review_rating' ) ):?> 
                                        <div class="floatleft mr15 disablefloatmobile">
                                            <?php $rating_count = $product->get_rating_count();?>
                                            <?php if ($rating_count < 1):?>
                                                <span data-scrollto="#reviews" class="rehub_scroll cursorpointer font80 greycolor"><?php esc_html_e("Add your review", "rehub-theme");?></span>
                                            <?php else:?>
                                                <?php woocommerce_template_single_rating();?>
                                            <?php endif;?>
                                        </div>
                                    <?php endif;?>
                                    <span class="floatleft meta post-meta mt0 mb0 disablefloatmobile">
                                        <?php
                                        $categories = wc_get_product_terms($postid, 'product_cat', array("fields" => "all"));
                                        $separator = '';
                                        $output = '';
                                        if ( ! empty( $categories ) ) {
                                            foreach( $categories as $category ) {
                                                $output .= '<a class="mr5 ml5 rh-cat-inner rh-cat-'.$category->term_id.'" href="' . esc_url( get_term_link( $category->term_id, 'product_cat' ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all posts in %s', 'rehub-theme' ), $category->name ) ) . '">' . esc_html( $category->name ) . '</a>' . $separator;
                                            }
                                            echo trim( $output, $separator );
                                        }
                                        ?>                                     
                                    </span>
                                    <span class="rh-flex-right-align mobileblockdisplay mobmb10">
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
                                    </span>
                                </div>                
                                <div class="wpsm-one-half wpsm-column-first">
                                    <?php echo wpsm_reviewbox(array('compact'=>1, 'id'=> $postid, 'scrollid'=>'section-description'));?>                                    
                                    <?php rh_woo_code_zone('content');?> 
                                    <?php if(has_excerpt($postid)):?>
                                        <?php woocommerce_template_single_excerpt();?>
                                    <?php else :?>
                                        <?php if(!empty($itemsync['extra']['itemAttributes']['Feature'])){
                                            $features = $itemsync['extra']['itemAttributes']['Feature'];
                                        }
                                        elseif(!empty($itemsync['extra']['keySpecs'])){
                                            $features = $itemsync['extra']['keySpecs'];
                                        }
                                        ?> 
                                        <?php if (!empty ($features)) :?>
                                            <ul class="featured_list">
                                                <?php $length = $maxlength = 0;?>
                                                <?php foreach ($features as $k => $feature): ?>
                                                    <?php if(is_array($feature)){continue;}?>
                                                    <?php $length = strlen($feature); $maxlength += $length; ?> 
                                                    <li><?php echo esc_attr($feature); ?></li>
                                                    <?php if($k >= 5 || $maxlength > 200) break; ?>                             
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else:?>
                                            <?php $currency_code = rehub_option('ce_custom_currency');?>
                                            <?php echo do_shortcode('[content-egg-block template=price_statistics currency='.$currency_code.']');?>
                                        <?php endif ;?> 
                                        <div class="clearfix"></div>                               
                                    <?php endif;?>
                                    <?php do_action('rh_woo_single_product_description');?>

                                    <div class="compare-button-holder mb20">
                                        <?php if (!empty($itemsync)):?>
                                            <?php woocommerce_template_single_price();?>
                                            <?php do_action('rh_woo_single_product_price');?>
                                            <?php echo rh_best_syncpost_deal($itemsync, 'mb10 compare-domain-icon lineheight20', true);?>
                                            <?php $offer_post_url = $itemsync['url'] ;?>
                                            <?php $afflink = apply_filters('rh_post_offer_url_filter', $offer_post_url );?>  
                                            <?php $aff_btn_text = get_post_meta($postid, '_button_text', true);?>       
                                            <?php 
                                                if($aff_btn_text) {
                                                    $buy_best_text = $aff_btn_text;
                                                } 
                                                elseif(rehub_option('buy_best_text') !=''){
                                                    $buy_best_text = rehub_option('buy_best_text');
                                                } 
                                                else{
                                                    $buy_best_text = esc_html__('Buy for best price', 'rehub-theme'); 
                                                } 
                                            ?>                        
                                            <a href="<?php echo esc_url($afflink);?>" class="re_track_btn wpsm-button rehub_main_btn btn_offer_block" target="_blank" rel="nofollow sponsored"><?php echo ''.$buy_best_text;?>
                                            </a>            
                                        <?php endif;?>                
                                    </div>
                                    <?php rh_show_vendor_info_single(); ?>
                                </div>
                                <div class="wpsm-one-half wpsm-column-last summary"> 
                                    <div id="celistrh"></div>
                                    <?php echo do_shortcode('[content-egg-block template=custom/all_merchant_widget_group]');?>
                                    <?php rh_woo_code_zone('button');?>
                                    <div class="woo-button-actions-area floatright mt15 pr5 pl10 pb5">
                                        <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
                                        <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                                        <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                                        <?php echo RH_get_wishlist($postid, $wishlistadd, $wishlistadded, $wishlistremoved);?>                                        
                                    </div>
                                    <div class="top_share floatleft notextshare mt20">
                                        <?php woocommerce_template_single_sharing();?>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div class="woo-single-meta font80">
                                        <?php do_action( 'woocommerce_product_meta_start' ); ?>
                                        <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
                                            <?php $sku = $product->get_sku();?>
                                            <?php 
                                                if(!$sku){
                                                    $sku = esc_html__( 'N/A', 'rehub-theme' );
                                                };
                                            ?>
                                            <span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'rehub-theme' ); ?> <span class="sku"><?php echo esc_html($sku); ?></span></span>
                                        <?php endif; ?>
                                        <?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'rehub-theme' ) . ' ', '</span>' ); ?>                                
                                        <?php do_action( 'woocommerce_product_meta_end' ); ?>
                                    </div>                                     
                                    <?php
                                        /**
                                         * woocommerce_single_product_summary hook. was removed in theme and added as functions directly in layout
                                         *
                                         * @dehooked woocommerce_template_single_title - 5
                                         * @dehooked woocommerce_template_single_rating - 10
                                         * @dehooked woocommerce_template_single_price - 10
                                         * @dehooked woocommerce_template_single_excerpt - 20
                                         * @dehooked woocommerce_template_single_add_to_cart - 30
                                         * @dehooked woocommerce_template_single_meta - 40
                                         * @dehooked woocommerce_template_single_sharing - 50
                                         * @hooked WC_Structured_Data::generate_product_data() - 60
                                         */
                                        do_action( 'woocommerce_single_product_summary' );
                                    ?>                                                                           
                                </div>                                                                                                
                            </div>
                        </div> 

                        <div class="other-woo-area clearfix">
                            <div class="rh-container">
                                <?php
                                    /**
                                     * woocommerce_after_single_product_summary hook.
                                     *
                                     * @hooked woocommerce_output_product_data_tabs - 10
                                     * @hooked woocommerce_upsell_display - 15
                                     * @hooked woocommerce_output_related_products - 20
                                     */
                                    do_action( 'woocommerce_after_single_product_summary' );
                                ?>
                            </div>  
                        </div> 
                        
                        <div id="contents-section-woo-area" class="rh-stickysidebar-wrapper">                      
                            <div class="main-side rh-sticky-container clearfix <?php if ( is_active_sidebar( 'sidebarwooinner' ) ) : ?>woo_default_w_sidebar<?php else:?>full_width woo_default_no_sidebar<?php endif; ?>">
                                <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() );

                                if ( ! empty( $tabs ) ) : ?>

                                    <?php 
                                        $youtubecontent = \ContentEgg\application\components\ContentManager::getViewData('Youtube', $postid);
                                        $googlenews = get_post_meta($postid, '_cegg_data_GoogleNews', true);
                                        $replacetitle = apply_filters('woo_product_section_title', get_the_title().' ');
                                        if(!empty($youtubecontent)){
                                            $tabs['woo-ce-videos'] = array(
                                                'title' => $replacetitle.__('Videos', 'rehub-theme'),
                                                'priority' => '21',
                                                'callback' => 'woo_ce_video_output'
                                            );
                                        }
                                        if(!empty($googlenews)){
                                            $tabs['woo-ce-news'] = array(
                                                'title' => esc_html__('World News', 'rehub-theme'),
                                                'priority' => '23',
                                                'callback' => 'woo_ce_news_output'
                                            );
                                        }                                        
                                        $tabs['woo-ce-pricehistory'] = array(
                                            'title' => esc_html__('Price History', 'rehub-theme'),
                                            'priority' => '22',
                                            'callback' => 'woo_ce_history_output'
                                        );                                            
                                        uasort( $tabs, '_sort_priority_callback' );                                 
                                    ?>

                                    <?php wp_enqueue_script('customfloatpanel');?> 
                                    <div class="flowhidden rh-float-panel" id="float-panel-woo-area">
                                        <div class="rh-container rh-flex-center-align pt10 pb10">
                                            <div class="float-panel-woo-image">
                                                <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'thumb'=> true, 'width'=> 50, 'height'=> 50));?>
                                            </div>
                                            <div class="float-panel-woo-info wpsm_pretty_colored rh-line-left pl15 ml15">
                                                <div class="float-panel-woo-title rehub-main-font mb5 font110">
                                                    <?php the_title();?>
                                                </div>
                                                <ul class="float-panel-woo-links list-unstyled list-line-style font80 fontbold lineheight15">
                                                    <?php foreach ( $tabs as $key => $tab ) : ?>
                                                        <li class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                                            <?php $tab_title = str_replace($replacetitle, '', $tab['title'] );?>                            
                                                            <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab_title ), $key ); ?></a>
                                                        </li>                                                
                                                    <?php endforeach; ?>                                        
                                                </ul>                                  
                                            </div>
                                            <div class="float-panel-woo-btn rh-flex-columns rh-flex-right-align rh-flex-nowrap">
                                                <div class="float-panel-woo-price rh-flex-center-align font120 rh-flex-right-align">
                                                    <?php woocommerce_template_single_price();?>
                                                </div>
                                                <div class="float-panel-woo-button rh-flex-center-align rh-flex-right-align">
                                                    <?php if(!rehub_option('woo_btn_inner_disable')) :?>
                                                        <a href="#celistrh" class="single_add_to_cart_button rehub_scroll">
                                                            <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?>
                                                                <?php echo rehub_option('rehub_btn_text_aff_links') ; ?>
                                                            <?php else :?>
                                                                <?php esc_html_e('Choose offer', 'rehub-theme') ?>
                                                            <?php endif ;?>
                                                        </a> 
                                                    <?php endif ;?> 
                                                    <?php rh_woo_code_zone('float');?>            
                                                </div>                                        
                                            </div>                                    
                                        </div>                           
                                    </div>                                    

                                    <div class="content-woo-area">
                                        <?php foreach ( $tabs as $key => $tab ) : ?>
                                            <div class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block rh-tabletext-wooblock whitebg width-100p" id="section-<?php echo esc_attr( $key ); ?>">
                                                <div class="rh-tabletext-block-heading fontbold border-grey-bottom">
                                                    <span class="cursorpointer floatright lineheight15 ml10 toggle-this-table rtlmr10"></span>
                                                    <h4 class="rh-heading-icon"><?php echo ''.$tab['title'];?></h4>
                                                </div>
                                                <div class="rh-tabletext-block-wrapper padd20">
                                                    <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                                                </div>
                                            </div>                                            
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Related -->
                                <?php $sidebar = (is_active_sidebar( 'sidebarwooinner' ) ) ? true : false; ?>
                                <?php include(rh_locate_template( 'woocommerce/single-product/related-compact.php' ) ); ?>
                                <!-- /Related --> 
                                <!-- Upsell -->
                                <?php include(rh_locate_template( 'woocommerce/single-product/upsell-compact.php' ) ); ?>
                                <!-- /Upsell -->                                                                 

                            </div>
                            <?php if ( is_active_sidebar( 'sidebarwooinner' ) ) : ?>
                                <?php wp_enqueue_script('stickysidebar');?>
                                <aside class="sidebar rh-sticky-container">            
                                    <?php dynamic_sidebar( 'sidebarwooinner' ); ?>      
                                </aside> 
                            <?php endif; ?>                           
                        </div>                               

                    </div><!-- #product-<?php the_ID(); ?> -->

                    <?php do_action( 'woocommerce_after_single_product' ); ?>
                <?php endwhile; // end of the loop. ?>
                <?php do_action( 'woocommerce_after_main_content' ); ?>                              
            </div>
        </div>  
    </div>
</div>
<!-- /CONTENT --> 
<?php else:?>
    <?php echo '<div class="rh-container"><div class="rh-content-wrap clearfix">';echo 'This product layout requires Content Egg Plugin to be active and Product must have Content Egg offers. For details, check Rehub docs - Affiliate Settings - Content Egg'; echo '</div></div>';?>
<?php endif;?>   
<?php rh_woo_code_zone('bottom');?>