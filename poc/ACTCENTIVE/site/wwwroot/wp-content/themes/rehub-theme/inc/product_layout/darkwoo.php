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
<?php wp_enqueue_script('rhyall');?>
<div class="woodarkdir darkbg" id="content">
    <?php           
        $image_url = get_post_meta($post->ID, '_woo_review_image_bg', true);
        if(!$image_url){
            $image_id = get_post_thumbnail_id($post->ID);  
            $image_url = wp_get_attachment_image_src($image_id,'full');
            if(!empty($image_url)) $image_url = $image_url[0];
        }
    ?> 
    <?php 
        $randomclass = 'lbg'.mt_rand();
        echo rh_generate_incss('lazybgsceleton', $randomclass, array('imageurl'=>$image_url));
    ?> 
    <?php echo rh_generate_incss('woodarkdir');?>
    <div class="post mb0">
        <?php while ( have_posts() ) : the_post(); ?>       
            <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="border-grey-bottom flowhidden woo_white_text_layout top-woo-area whitecolor position-relative <?php echo ''.$randomclass;?>">
                    <div class="mt30 mb25 pt10 zind2 rh-container flowhidden">
                        <div class="rh-flex-eq-height rh-flex-nowrap mobileblockdisplay content-woo-area rh-300-content-area floatleft">
                            <?php $post_image_gallery = $product->get_gallery_image_ids();?>
                            <?php 
                                wp_enqueue_script('modulobox');
                                wp_enqueue_style('modulobox');
                            ?>   
                            <div class="woo-image-part modulo-lightbox width-250 position-relative mr25 rtlml25 mobileblockdisplay<?php if(empty($post_image_gallery)) :?> mb15<?php endif;?>">
                                <?php if(rehub_option('theme_subset') == 'regame'):?>
                                    <figure class="text-center margincenter img-width-auto">
                                    <?php woocommerce_show_product_sale_flash();?>
                                    <?php           
                                        $image_id = get_post_thumbnail_id($post->ID);  
                                        $image_url = wp_get_attachment_image_src($image_id,'full');
                                        $image_url = $image_url[0]; 
                                    ?> 
                                    <a data-rel="rh_top_gallery" href="<?php echo esc_url($image_url);?>" target="_blank" data-thumb="<?php echo esc_url($image_url);?>">            
                                        <?php echo WPSM_image_resizer::show_wp_image('woocommerce_single', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>
                                    </a>
                                    <?php do_action( 'rehub_360_product_image' ); ?>
                                </figure>                                
                                <?php else:?>
                                    <figure class="text-center margincenter img-width-auto img-mobs-maxh-250">
                                    <?php woocommerce_show_product_sale_flash();?>
                                    <?php           
                                        $image_id = get_post_thumbnail_id($post->ID);  
                                        $image_url = wp_get_attachment_image_src($image_id,'full');
                                        $image_url = $image_url[0]; 
                                    ?> 
                                    <a data-rel="rh_top_gallery" href="<?php echo esc_url($image_url);?>" target="_blank" data-thumb="<?php echo esc_url($image_url);?>"> 
                                        <?php echo WPSM_image_resizer::show_wp_image('mediumgrid', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>           
                                    </a>
                                </figure>
                                <?php endif;?>
                                <div class="rh_mini_thumbs mb15 compare-full-thumbnails mt15 smart-scroll-desktop four-col-mob">
                                    <?php $qwantimages = $youtubecontent = ''; if (defined('\ContentEgg\PLUGIN_PATH')):?>
                                        <?php 
                                            $qwantimages = \ContentEgg\application\components\ContentManager::getViewData('GoogleImages', $post->ID);
                                            $youtubecontent = \ContentEgg\application\components\ContentManager::getViewData('Youtube', $post->ID);
                                        ?>
                                    <?php endif;?>
                                    <?php if(!empty($post_image_gallery) || !empty($qwantimages) || !empty($youtubecontent) ) :?> 
                                        <?php foreach($post_image_gallery as $key=>$image_gallery):?>
                                            <?php if(!$image_gallery) continue;?>
                                            <?php $image = wp_get_attachment_image_src($image_gallery, 'full'); $imgurl = (!empty($image[0])) ? $image[0] : ''; ?>
                                            <a data-rel="rh_top_gallery" data-thumb="<?php echo esc_url($imgurl);?>" href="<?php echo esc_url($imgurl);?>" target="_blank" class="rh-flex-center-align mb10 col_item" data-title="<?php echo esc_attr(get_post_field( 'post_excerpt', $image_gallery));?>"> 
                                                <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'src'=> esc_url($imgurl), 'crop'=> false, 'width'=>60, 'height'=> 60,'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>
                                            </a>                               
                                        <?php endforeach;?> 
                                        <?php if(!empty($youtubecontent)):?>
                                            <?php foreach($youtubecontent as $videoitem):?>
                                                <a href="<?php echo esc_url($videoitem['url']);?>" data-rel="rh_top_gallery" target="_blank" class="rh-flex-center-align col_item mb10 rh_videothumb_link" data-poster="<?php echo parse_video_url($videoitem['url'], 'hqthumb'); ?>" data-thumb="<?php echo esc_url($videoitem['img'])?>"> 
                                                    <img src="<?php echo esc_url($videoitem['img'])?>" alt="<?php echo ''.$videoitem['title']?>" width="115" height="65" />
                                                </a>                                                    
                                            <?php endforeach;?> 
                                        <?php endif;?>  
                                        <?php if(!empty($qwantimages)):?>
                                            <?php foreach ((array)$qwantimages as $gallery_img) :?>
                                                <?php if (isset($gallery_img['LargeImage'])){
                                                    $image = $gallery_img['LargeImage'];
                                                }else{
                                                    $image = $gallery_img['img'];
                                                }?>                                               
                                                <a data-thumb="<?php echo esc_url($image)?>" data-rel="rh_top_gallery" href="<?php echo esc_url($image); ?>" data-title="<?php echo esc_attr($gallery_img['title']);?>" class="rh-flex-center-align mb10 col_item"> 
                                                    <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $image, 'height'=> 60, 'width'=>60, 'crop'=> false, 'title' => $gallery_img['title'], 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>  
                                                </a>
                                            <?php endforeach;?>  
                                        <?php endif;?>                                    
                                    <?php endif;?> 
                                    <?php echo woo_custom_video_output('class=rh-flex-center-align mb10 rh_videothumb_link col_item&rel=rh_top_gallery&wrapper=no&title=no');?>  
                                </div>
                                <?php do_action('rh_woo_after_single_image');?>
                            </div>
                            <div class="rhhidden mobileblockdisplay mb30 clearfix"></div>
                            <div class="woo-title-area mb10 flowhidden rh-flex-grow1">
                                <?php
                                    do_action( 'woocommerce_before_single_product' );
                                ?>
                                <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                                <?php echo re_badge_create('labelsmall'); ?>
                                <?php woocommerce_template_single_title();?>
                                <?php do_action('rh_woo_single_product_title');?>
                                <div class="font150 mb15">
                                    <?php woocommerce_template_single_price();?>
                                </div> 
                                <?php do_action('rh_woo_single_product_price');?>
                                <div class="meta post-meta">
                                    <?php rh_post_header_meta('full', false, true, false, false);?> <span class="more-from-store-a ml5 mr5"><?php WPSM_Woohelper::re_show_brand_tax('list');?></span>   
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
                                <div class="re_wooinner_info mb20"> 
                                    <?php woocommerce_template_single_excerpt();?>
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
                        <div class="rh-300-sidebar mt20 mb10 floatright mobileblockdisplay summary">
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
                                            $cmp_btn_args['class']= 'rhwoosinglecompare mb0';
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
                    <div class="other-content-woo-area rh-container position-relative zind2 flowhidden">
                        <?php rh_woo_code_zone('content');?>
                    </div>
                    <div class="darkhalfopacitybg flowhidden position-relative zind2 rh-shadow2">
                        <div class="rh-container">
                            <?php $tabs = apply_filters( 'woocommerce_product_tabs', array() );                        
                            if ( ! empty( $tabs ) ) : ?>
                                <?php                                         
                                    if(!empty($youtubecontent)){
                                        $tabs['woo-ce-videos'] = array(
                                            'title' => $replacetitle.__('Videos', 'rehub-theme'),
                                            'priority' => '21',
                                            'callback' => 'woo_ce_video_output'
                                        );
                                    }
                                ?>
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
                    <span class="rh-post-layout-image-mask"></span>
                    <div class="abdfullwidth rh-image-top-bg lazy-bg rh-sceleton darkbg"></div>                          
                </div>               
                <div class="darkbg position-relative">
                    <div class="other-woo-area clearfix position-relative zind2">
                        <div class="rh-container">
                            <?php rh_woo_code_zone('bottom');?>
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
                    <?php wp_enqueue_script('stickysidebar');?>
                    <div class="content-woo-area rh-container flowhidden rh-stickysidebar-wrapper">
                        <div class="rh-300-content-area position-relative zind2 tabletblockdisplay floatleft pt30 rh-sticky-container">
                            <?php do_action( 'woocommerce_before_main_content' );?>                        
                            <?php foreach ( $tabs as $key => $tab ) : ?>
                                <div class="border-lightgrey clearbox flowhidden mb25 rh-tabletext-block rh-tabletext-wooblock lightgreycolor woo_white_text_layout width-100p" id="section-<?php echo esc_attr( $key ); ?>">

                                    <div class="rh-tabletext-block-wrapper padd20">
                                        <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                                    </div>
                                </div>                                            
                            <?php endforeach; ?>
                            <?php do_action( 'woocommerce_after_main_content' ); ?>             
                        </div>  
                        <div class="rh-300-sidebar position-relative zind2 mt30 floatright rh-sticky-container tabletblockdisplay">
                            <?php 
                                $score = get_post_meta((int)$id, 'rehub_review_overall_score', true); 
                            ?>                        
                            <?php if (!empty($score)):?>
                                <?php $rate_position = rh_get_product_position($post->ID);?>
                                <div class="border-lightgrey whitecolor clearbox flowhidden mb25 padd15 text-center" id="section-woo-rev">
                                    <?php if (!empty($rate_position['rate_pos'])):?>
                                        <div class="fontbold border-grey-bottom">
                                            <div class="mobileblockdisplay mt20 font120 text-center mb15">
                                                <?php echo wpsm_reviewbox(array('compact'=>'textbigcenter', 'id'=> $post->ID));?>
                                            </div> 
                                            <div class="font90 mb20 fontnormal mobileblockdisplay rh-pr-rated-block">
                                                <?php 
                                                    if($rate_position['rate_pos'] < 3){
                                                        echo '<i class="rhicon rhi-trophy-alt font150 orangecolor mr10 vertmiddle rtlml10"></i>';
                                                    }
                                                ?> 
                                                <strong>#<?php echo ''.$rate_position['rate_pos'];?></strong> <?php esc_html_e( 'in category', 'rehub-theme' ); ?> <a href="<?php echo esc_url($rate_position['link']);?>"><?php echo esc_attr($rate_position['cat_name']); ?></a>                                                               
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="pt20 flowhidden">
                                        <?php echo rehub_exerpt_function(array('reviewcriterias'=> 'editor'));?>
                                    </div>
                                </div>
                            <?php endif; ?> 
                            <?php rh_show_vendor_info_single(); ?>
                            <?php if ( is_active_sidebar( 'sidebarwooinner' ) ) : ?>
                                <div class="sidebar_additional">            
                                    <?php dynamic_sidebar( 'sidebarwooinner' ); ?>      
                                </div> 
                            <?php endif; ?>                        
                        </div> 
                        <?php wp_enqueue_script('customfloatpanel');?> 
                        <div class="darkbg woo_white_text_layout flowhidden rh-float-panel" id="float-panel-woo-area">
                            <div class="rh-container rh-flex-center-align pt10 pb10">
                                <div class="float-panel-woo-image">
                                    <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'thumb'=> true, 'width'=> 50, 'height'=> 50));?>
                                </div>
                                <div class="float-panel-woo-info wpsm_pretty_colored rh-line-left pl15 ml15">
                                    <div class="float-panel-woo-title whitecolor rehub-main-font mb5 font110">
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
                                    <div class="float-panel-woo-price rh-flex-center-align font150 rh-flex-right-align rehub-main-color fontbold">
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
                    <div class="woo-btm-area woo_white_text_layout position-relative zind2 border-top">
                        <!-- Related -->
                        <?php include(rh_locate_template( 'woocommerce/single-product/full-width-related.php' ) ); ?>                      
                        <!-- /Related -->

                        <!-- Upsell -->
                        <?php include(rh_locate_template( 'woocommerce/single-product/full-width-upsell.php' ) ); ?>
                        <!-- /Upsell -->  
                    </div>
                    <span class="overbg rh-post-layout-image-mask"></span>
                </div>

            </div><!-- #product-<?php the_ID(); ?> -->

            <?php do_action( 'woocommerce_after_single_product' ); ?>

        <?php endwhile; // end of the loop. ?>               
    </div>
</div>  