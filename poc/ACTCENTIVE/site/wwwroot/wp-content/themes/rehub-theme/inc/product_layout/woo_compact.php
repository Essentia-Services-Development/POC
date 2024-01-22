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
<div class="sections_w_sidebar woo_compact_layout" id="content">
    <?php echo rh_generate_incss('woocompactlayout');?>
    <?php echo rh_generate_incss('section_w_sidebar');?>
    <?php $script = "
            var width_ofcontainer = jQuery(\".right_aff .price_count\").innerWidth() / 2;
            jQuery(\".right_aff .price_count\").append('<span class=\"triangle_aff_price\" style=\"border-width: 14px ' + width_ofcontainer + 'px 0 ' + width_ofcontainer + 'px\"></span>');
        ";?>
    <?php wp_add_inline_script('rehub', $script);?>
    <div class="post mb0">
        <?php while ( have_posts() ) : the_post(); ?>
            <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

                <?php wp_enqueue_script('stickysidebar');?>
                <div class="content-woo-area rh-container flowhidden rh-stickysidebar-wrapper">                                
                    <div class="rh-300-content-area tabletblockdisplay floatleft pt15 rh-sticky-container">
                        <?php do_action( 'woocommerce_before_main_content' );?>
                        <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                        <?php do_action( 'woocommerce_before_single_product' );?>                                                 
                        <div class="position-relative mt10" id="rh_woo_layout_inimage">
                            <?php $image_url = get_post_meta($post->ID, '_woo_review_image_bg', true);?>
                            <?php if($image_url):?>
                                <style scoped>#rh_woo_layout_inimage{background-image: url(<?php echo ''.$image_url;?>);}#rh-model-td-trigger .bluecolor{display:none}#rh-model-td-trigger{margin-top:15px}</style>
                                <span class="rh-post-layout-image-mask"></span>
                            <?php else:?>
                                <style scoped>#rh_woo_layout_inimage{background: linear-gradient(120deg, rgb(39, 43, 47) 15%, rgb(5, 123, 91) 55%, rgb(5, 123, 91) 100%);}#rh-model-td-trigger{margin-top:15px}#rh-model-td-trigger .bluecolor{display:none}</style>
                            <?php endif;?> 
                            <div class="rh-flex-eq-height rh-flex-nowrap mobileblockdisplay pt25 pr25 pl25 pb5 whitecolor position-relative zind2">                             
                                <div class="woo-image-part position-relative mr15 mb15 rtlml15">

                                    <?php 
                                        wp_enqueue_script('modulobox');
                                        wp_enqueue_style('modulobox');
                                    ?>                                                         
                                    <figure class="text-center margincenter">
                                        <?php woocommerce_show_product_sale_flash();?>
                                        <?php           
                                            $image_id = get_post_thumbnail_id($post->ID);  
                                            $image_url = wp_get_attachment_image_src($image_id,'full');
                                            $image_url = $image_url[0]; 
                                        ?> 
                                        <a data-rel="rh_top_gallery" href="<?php echo ''.$image_url;?>" target="_blank" data-thumb="<?php echo ''.$image_url;?>"> 
                                            <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'thumb'=> true, 'crop'=> false, 'height'=> 120, 'width'=> 120,'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_500_500.png'));?>           
                                        </a>
                                    </figure>
                                    <?php do_action('rh_woo_after_single_image');?>
                                </div>
                                <div class="woo-title-area woo_white_text_layout mb10 flowhidden rh-flex-grow1">

                                    <?php echo re_badge_create('labelsmall'); ?>
                                    <h1 class="product_title entry-title whitecolor <?php echo getHotIconclass($post->ID, true); ?>"><?php echo rh_expired_or_not($post->ID, 'span');?><?php the_title();?></h1>
                                    <?php do_action('rh_woo_single_product_title');?>
                                    <div class="meta post-meta">
                                        <?php rh_post_header_meta('full', false, true, false, true);?> <span class="more-from-store-a ml5 mr5"><?php WPSM_Postfilters::re_show_brand_tax('list');?></span>   
                                    </div>                                 
                                    <?php if ( 'no' !== get_option( 'woocommerce_enable_review_rating' ) ):?>
                                        <div class="woo_top_meta mb15">
                                            <?php $rating_count = $product->get_rating_count();?>
                                            <?php if ($rating_count < 1):?>
                                                <span data-scrollto="#reviews" class="rehub_scroll cursorpointer font90 whitecolor"><?php esc_html_e("Add your review", "rehub-theme");?></span>
                                            <?php else:?>
                                                <?php woocommerce_template_single_rating();?>
                                            <?php endif;?>
                                        </div>
                                    <?php endif;?>
                                    <div class="wooline-button-area">
                                        <div class="font130 orangecolor rhhidden mobileblockdisplay">
                                            <?php woocommerce_template_single_price();?>
                                        </div>
                                        <div class="floatleft mr5 mobilesblockdisplay woo-button-area" id="woo-button-area">
                                            <?php do_action('rhwoo_template_single_add_to_cart');?>
                                        </div>
                                        <div class="floatleft mr5 mobilesblockdisplay">
                                            <?php rh_woo_code_zone('button');?>
                                        </div> 
                                        <div class="woo-top-actions tabletblockdisplay floatleft disablefloatmobile">
                                            <div class="woo-button-actions-area pl5 pb5 pr5">
                                                <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
                                                <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                                                <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                                                <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?>
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
                                    </div>                                    
                                    <?php rh_woo_code_zone('content');?>        
                                </div>
                                <?php if($product->get_price() > 0):?>
                                    <div class="right_aff hideonmobile rh-flex-right-align">
                                        <div class="priced_block mt0 mb0 clearfix ">
                                            <div class="rh_price_wrapper">
                                                <div class="price_count"><?php woocommerce_template_single_price();?></div>
                                            </div>                            
                                        </div>
                                    </div>
                                <?php endif;?>
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

                        <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() );

                        $attachment_ids = $product->get_gallery_image_ids();
                        if(!empty($attachment_ids)){
                            $tabs['woo-photo-booking'] = array(
                                'title' => esc_html__('Photos', 'rehub-theme'),
                                'priority' => '22',
                                'callback' => 'woo_photo_booking_out'
                            );                                            
                            uasort( $tabs, '_sort_priority_callback' );                             
                        }
                        if (defined('\ContentEgg\PLUGIN_PATH')){
                            $youtubecontent = \ContentEgg\application\components\ContentManager::getViewData('Youtube', $post->ID);
                            if(!empty($youtubecontent)){
                                $tabs['woo-ce-videos'] = array(
                                    'title' => esc_html__('Videos', 'rehub-theme'),
                                    'priority' => '21',
                                    'callback' => 'woo_cevideo_booking_out'
                                );
                                uasort( $tabs, '_sort_priority_callback' );
                            } 
                        }                         

                        if ( ! empty( $tabs ) ) : ?>
                            <div id="contents-section-woo-area">
                                <ul class="smart-scroll-desktop mb20 rehub-main-font clearfix contents-woo-area lightgreybg rh-big-tabs-ul">
                                    <?php $i = 0; foreach ( $tabs as $key => $tab ) : ?>
                                        <li class="rh-hov-bor-line <?php if($i == 0) echo 'active '; ?>rh-big-tabs-li <?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                            <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
                                        </li>
                                        <?php $i ++;?>
                                    <?php endforeach; ?>
                                </ul> 
                            </div> 
                        
                        <?php endif;?>                       


                        <?php foreach ( $tabs as $key => $tab ) : ?>
                            <div class="padd20 mb20 font90 border-lightgrey whitebg content-woo-section--<?php echo esc_attr( $key ); ?>" id="section-<?php echo esc_attr( $key ); ?>">
                                <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                            </div>
                        <?php endforeach; ?> 
                        <?php do_action( 'woocommerce_after_main_content' ); ?>             
                    </div>  
                    <div class="rh-300-sidebar mt20 floatright rh-sticky-container tabletblockdisplay">
                        <?php $score = get_post_meta($post->ID, 'rehub_review_overall_score', true);?>
                        <?php if($score) :?>  
                            <div class="wpsm_score_box border-lightgrey mb30 whitebg wpsm_score_box blackcolor rh-shadow3">           
                                <div class="font120 lightgreybg lineheight25 pb15 pl20 pr20 pt15 wpsm_score_title">
                                    <span class="overall-text"><?php esc_html_e('Expert Score', 'rehub-theme');?></span>
                                    <span class="floatright font140 fontbold overall-score"><?php echo round($score, 1) ?></span>
                                </div>
                                <div class="wpsm_inside_scorebox padd20">
                                    <?php 
                                        $thecriteria = get_post_meta($post->ID, '_review_post_criteria', true);
                                        $firstcriteria = $thecriteria[0]['review_post_name']; 
                                    ?>
                                    <?php if($firstcriteria) : ?>
                                    <div class="rate_bar_wrap">
                                        <div class="review-criteria mt0 pt25">
                                            <?php foreach ($thecriteria as $criteria) { ?>
                                                <?php $perc_criteria = $criteria['review_post_score']*10; ?>
                                                <div class="rate-bar clearfix" data-percent="<?php echo ''.$perc_criteria; ?>%">
                                                    <div class="rate-bar-title"><span><?php echo ''.$criteria['review_post_name']; ?></span></div>
                                                    <div class="rate-bar-bar r_score_<?php echo round($criteria['review_post_score']); ?>"></div>
                                                    <div class="rate-bar-percent"><?php echo ''.$criteria['review_post_score']; ?></div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>     
                                    <?php   
                                        $prosvalues = get_post_meta($post->ID, '_review_post_pros_text', true);
                                        $consvalues = get_post_meta($post->ID, '_review_post_cons_text', true);
                                    ?> 
                                    <!-- PROS CONS BLOCK-->
                                    <div class="prosconswidget">
                                    <?php if(!empty($prosvalues)):?>
                                        <div class="wpsm_pros mb20">
                                            <div class="title_pros"><?php esc_html_e('PROS:', 'rehub-theme');?></div>
                                            <ul>        
                                                <?php $prosvalues = explode(PHP_EOL, $prosvalues);?>
                                                <?php foreach ($prosvalues as $prosvalue) {
                                                    if(!$prosvalue) continue;
                                                    echo '<li>'.$prosvalue.'</li>';
                                                }?>
                                            </ul>
                                        </div>
                                    <?php endif;?>  
                                    <?php if(!empty($consvalues)):?>
                                        <div class="wpsm_cons">
                                            <div class="title_cons"><?php esc_html_e('CONS:', 'rehub-theme');?></div>
                                            <ul>
                                                <?php $consvalues = explode(PHP_EOL, $consvalues);?>
                                                <?php foreach ($consvalues as $consvalue) {
                                                    if(!$consvalue) continue;
                                                    echo '<li>'.$consvalue.'</li>';
                                                }?>
                                            </ul>
                                        </div>
                                    <?php endif;?>
                                    </div>  
                                    <!-- PROS CONS BLOCK END-->                             
                                </div>
                            </div>
                        <?php endif;?>                             

                        <div class="summary"> 
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
                            <?php do_action('rh_woo_single_product_description');?>                            
                            
                        </div>                       

                        <?php rh_show_vendor_info_single(); ?>

                        <?php if ( is_active_sidebar( 'sidebarwooinner' ) ) : ?>
                            <div class="sidebar_additional">            
                                <?php dynamic_sidebar( 'sidebarwooinner' ); ?>      
                            </div> 
                        <?php endif; ?>                        

                    </div>
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
                                            <?php $tab_title = $tab['title'];?>
                                            <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html($tab_title), $key ); ?></a>
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
                                        <?php if(!empty($itemsync)):?>
                                            <a href="#section-woo-ce-pricelist" class="single_add_to_cart_button rehub_scroll">
                                                <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?>
                                                    <?php echo rehub_option('rehub_btn_text_aff_links') ; ?>
                                                <?php else :?>
                                                    <?php esc_html_e('Choose offer', 'rehub-theme') ?>
                                                <?php endif ;?>
                                            </a> 
                                        <?php else:?>
                                            <?php if ( $product->add_to_cart_url() !='') : ?>
                                                <?php if($product->get_type() == 'variable' || $product->get_type() == 'booking') {
                                                    $url = '#woo-button-area';
                                                }else{
                                                    $url = esc_url( $product->add_to_cart_url() );
                                                }

                                                ?>
                                                <?php  echo apply_filters( 'woo_float_add_to_cart_link',
                                                    sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn btn_offer_block single_add_to_cart_button %s %s product_type_%s"%s %s>%s</a>',
                                                    $url,
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
                                        <?php endif;?>
                                    <?php endif;?> 
                                    <?php rh_woo_code_zone('float');?>                                                            
                                </div>                                        
                            </div>                                    
                        </div>                           
                    </div>                                           
                </div>

            </div><!-- #product-<?php the_ID(); ?> -->

            <?php do_action( 'woocommerce_after_single_product' ); ?>

        <?php endwhile; // end of the loop. ?>               
    </div>
</div>  
<!-- Related -->
<?php include(rh_locate_template( 'woocommerce/single-product/full-width-related.php' ) ); ?>                      
<!-- /Related -->

<!-- Upsell -->
<?php include(rh_locate_template( 'woocommerce/single-product/full-width-upsell.php' ) ); ?>
<!-- /Upsell -->  

<?php rh_woo_code_zone('bottom');?>