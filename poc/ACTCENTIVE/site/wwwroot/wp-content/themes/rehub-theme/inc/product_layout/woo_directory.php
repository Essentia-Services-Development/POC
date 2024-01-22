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
<div class="woo_directory_layout lightgreybg" id="content">
    <?php echo rh_generate_incss('woodirectory');?>
    <div class="post mb0">
        <?php while ( have_posts() ) : the_post(); ?>       
            <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="border-grey-bottom flowhidden whitebg">
                    <div class="mt20 rh-container flowhidden">
                        <div class="rh-flex-eq-height rh-flex-nowrap mobileblockdisplay content-woo-area rh-300-content-area floatleft">
                            <div class="woo-image-part position-relative mr25 mb15 rtlml25 tabletsblockdisplay">

                                <?php 
                                    wp_enqueue_script('modulobox');
                                    wp_enqueue_style('modulobox');
                                ?>                                                         
                                <figure class="text-center margincenter">
                                    <?php woocommerce_show_product_sale_flash();?>
                                    <?php echo WPSM_image_resizer::show_wp_image('woocommerce_single', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>
                                </figure>
                                <?php do_action('rh_woo_after_single_image');?>
                            </div>
                            <div class="woo-title-area mb10 flowhidden rh-flex-grow1">
                                <?php
                                    do_action( 'woocommerce_before_single_product' );
                                ?>
                                <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                                <?php echo re_badge_create('labelsmall'); ?>
                                <?php woocommerce_template_single_title();?>
                                <?php do_action('rh_woo_single_product_title');?>
                                <div class="font130 rehub-main-color mb15">
                                    <?php woocommerce_template_single_price();?>
                                </div>
                                <?php do_action('rh_woo_single_product_price');?>
                                <div class="meta post-meta">
                                    <?php rh_post_header_meta('full', false, true, false, true);?> <span class="more-from-store-a ml5 mr5"><?php WPSM_Woohelper::re_show_brand_tax('list');?></span>   
                                </div>                                 
                                <?php if ( 'no' !== get_option( 'woocommerce_enable_review_rating' ) ):?>
                                    <div class="woo_top_meta mb15">
                                        <?php $rating_count = $product->get_rating_count();?>
                                        <?php if ($rating_count < 1):?>
                                            <span data-scrollto="#reviews" class="rehub_scroll cursorpointer font80 greycolor"><?php esc_html_e("Add your review", "rehub-theme");?></span>
                                        <?php else:?>
                                            <?php woocommerce_template_single_rating();?>
                                        <?php endif;?>
                                    </div>
                                <?php endif;?>
                                <div class="re_wooinner_info">
                                      <?php rh_woo_code_zone('content');?>  
                                      <?php do_action('rh_woo_single_product_description');?>                       
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
                        <div class="rh-300-sidebar mt20 mb10 floatright mobileblockdisplay">
                            <div class="woo-button-area woo-ext-btn" id="woo-button-area">
                                <?php do_action('rhwoo_template_single_add_to_cart');?>
                                <?php rh_woo_code_zone('button');?>
                                <div class="woo-button-actions-area tabletblockdisplay text-center mt15">
                                    <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
                                    <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
                                    <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                                    <?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?>
                                    <?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>           
                                        <?php 
                                            $cmp_btn_args = array(); 
                                            $cmp_btn_args['class']= 'rhwoosinglecompare mb15';
                                            if(rehub_option('compare_woo_cats') != '') {
                                                $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                            }
                                        ?>                                                  
                                        <?php echo wpsm_comparison_button($cmp_btn_args); ?> 
                                    <?php endif;?> 
                                </div>                                 
                                
                            </div>
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
                <div class="border-grey-bottom flowhidden whitebg mb10 rh-shadow2">
                    <div class="rh-container">
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
                                <ul class="smart-scroll-desktop clearfix contents-woo-area rh-big-tabs-ul">
                                    <?php $i = 0; foreach ( $tabs as $key => $tab ) : ?>
                                        <li class="rh-hov-bor-line <?php if($i == 0) echo 'active '; ?>rh-big-tabs-li <?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
                                            <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
                                        </li>
                                        <?php $i ++;?>
                                    <?php endforeach; ?>
                                </ul> 
                            </div>          
                        <?php endif;?>                         
                    </div>
                </div>                

                <?php wp_enqueue_script('stickysidebar');?>
                <div class="content-woo-area rh-container flowhidden rh-stickysidebar-wrapper">
                    <div class="rh-300-content-area tabletblockdisplay floatleft pt15 rh-sticky-container">
                        <?php do_action( 'woocommerce_before_main_content' );?>
                        <?php 
                            $score = get_post_meta((int)$id, 'rehub_review_overall_score', true); 
                        ?>                        
                        <?php if (!empty($score)):?>
                            <?php $rate_position = rh_get_product_position($post->ID);?>
                            <div class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block rh-tabletext-wooblock whitebg width-100p" id="section-woo-rev">
                                <?php if (!empty($rate_position['rate_pos'])):?>
                                    <div class="rh-tabletext-block-heading fontbold border-grey-bottom flowhidden lineheight20 ">
                                        <div class="floatleft mobileblockdisplay"><?php echo wpsm_reviewbox(array('compact'=>'text', 'id'=> $post->ID, 'scrollid'=>'tab-title-description'));?></div> 
                                        <span class="floatright font90 fontnormal mobileblockdisplay rh-pr-rated-block">
                                            <?php 
                                                if($rate_position['rate_pos'] < 3){
                                                    echo '<i class="rhicon rhi-trophy-alt font150 orangecolor mr10 vertmiddle rtlml10"></i>';
                                                }
                                            ?> 
                                            <?php esc_html_e( 'Product is rated as', 'rehub-theme' ); ?> <strong>#<?php echo ''.$rate_position['rate_pos'];?></strong> <?php esc_html_e( 'in category', 'rehub-theme' ); ?> <a href="<?php echo esc_url($rate_position['link']);?>"><?php echo esc_attr($rate_position['cat_name']); ?></a>                                                               
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <div class="rh-tabletext-block-wrapper padd20 flowhidden pt0 pb0">
                                    <?php $summary = get_post_meta((int)$post->ID, '_review_post_summary_text', true);?>
                                    <?php if ($summary):?>
                                        <div class="border-grey-bottom mt15 pb15 font90"><?php echo rehub_kses($summary);?></div>
                                    <?php endif;?>                                    
                                    <?php                             
                                        $criteriascore = rehub_exerpt_function(array('reviewcriterias'=> 'editor'));
                                    ?>
                                    <?php $colclass = ($criteriascore) ? 'wpsm-one-third' : 'wpsm-one-half';?>
                                    <?php if($criteriascore) : ?>
                                        <div class="pt20 pb20 floatleft <?php echo ''.$colclass?>">
                                            <?php echo ''.$criteriascore; ?>
                                        </div>
                                    <?php endif; ?>     
                                    <?php   
                                        $prosvalues = get_post_meta($post->ID, '_review_post_pros_text', true);
                                        $consvalues = get_post_meta($post->ID, '_review_post_cons_text', true);
                                    ?> 
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
                                        <div class="wpsm_cons floatleft pt20 pb20 font90 <?php echo ''.$colclass?>">
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
                            </div>
                        <?php endif; ?>                         
                        <?php foreach ( $tabs as $key => $tab ) : ?>
                            <div class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block rh-tabletext-wooblock whitebg width-100p" id="section-<?php echo esc_attr( $key ); ?>">

                                <div class="rh-tabletext-block-wrapper padd20">
                                    <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                                </div>
                            </div>                                            
                        <?php endforeach; ?>
                        <?php do_action( 'woocommerce_after_main_content' ); ?>             
                    </div>  
                    <div class="rh-300-sidebar mt20 floatright rh-sticky-container tabletblockdisplay">
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
                                <div class="float-panel-woo-price rh-flex-center-align font120 rh-flex-right-align rehub-main-color fontbold">
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