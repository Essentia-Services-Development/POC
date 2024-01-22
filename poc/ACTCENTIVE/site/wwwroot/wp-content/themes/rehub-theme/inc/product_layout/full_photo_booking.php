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
<div class="woo_full_photo_booking" id="content">
    <?php do_action( 'woocommerce_before_main_content' );?>
    <?php while ( have_posts() ) : the_post(); ?>
        <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

            <div class="rh_post_layout_fullimage mb0">
                <?php           
                    $image_url = get_post_meta($post->ID, '_woo_review_image_bg', true);
                    if(!$image_url){
                        $image_id = get_post_thumbnail_id($post->ID);  
                        $image_url = wp_get_attachment_image_src($image_id,'full');
                        if(!empty($image_url)) $image_url = $image_url[0];
                    }
                ?>  

                <div id="rh_post_layout_inimage">
                    <style scoped>
                        #rh_post_layout_inimage{background-image: url(<?php echo ''.$image_url;?>);}
                    </style>
                    <?php echo rh_generate_incss('fullwidthphotowoo');?>
                    <div class="rh-container">
                        <div class="rh_post_breadcrumb_holder tabletrelative padd15">
                            <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>
                        </div>                        
                        <div class="rh-flex-eq-height rh-woo-fullimage-holder tabletrelative tabletblockdisplay">
                            <div class="rh-336-content-area tabletblockdisplay disablefloattablet floatleft mb20">
                                <?php echo wpsm_reviewbox(array('compact'=>'circle', 'id'=> $post->ID, 'scrollid'=>'tab-title-description'));?> 
                                <div class="woo-title-area mb10 flowhidden">
                                    <div class="rh-cat-list-title mb10 inlinestyle lineheight15 woo-cat-string-block">
                                        <?php echo re_badge_create('labelsmall'); ?>
                                        <?php
                                        $categories = wc_get_product_terms($post->ID, 'product_cat', array("fields" => "all"));
                                        $separator = '';
                                        $output = '';
                                        if ( ! empty( $categories ) ) {
                                            foreach( $categories as $category ) {
                                                $output .= '<a class="rh-cat-label-title rh-cat-'.$category->term_id.'" href="' . esc_url(  get_term_link( $category->term_id, 'product_cat' ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all products in %s', 'rehub-theme' ), $category->name ) ) . '">' . esc_html( $category->name ) . '</a>' . $separator;
                                            }
                                            echo trim( $output, $separator );
                                        }
                                        ?> 
                                        <?php if ( $product->is_on_sale()) : ?>
                                            <?php 
                                            $percentage=0;
                                            if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0) {
                                                $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
                                            }
                                            if ($percentage && $percentage>0 && !$product->is_type( 'variable' )) {
                                                $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="rh-label-string redbg"><span>- ' . $percentage . '%</span></span>', $post, $product );
                                            } else {
                                                $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="rh-label-string redbg">' . esc_html__( 'Sale!', 'rehub-theme' ) . '</span>', $post, $product );
                                            }
                                            ?>
                                            <?php echo ''.$sales_html; ?>
                                        <?php endif; ?>                                                                                     
                                    </div>
                                    <div>

                                        <h1 class="product_title whitecolor entry-title <?php if(rehub_option('wishlist_disable') !='1') :?><?php echo getHotIconclass($post->ID, true); ?><?php endif ;?>">
                                        <?php if ( $product->is_featured() ) : ?>
                                            <i class="rhicon rhi-bolt mr5 ml5 orangecolor" aria-hidden="true"></i>
                                        <?php endif; ?>                                            
                                        <?php the_title();?>
                                        </h1>
                                        <?php do_action('rh_woo_single_product_title');?>
                                    </div>
                                    <div><?php woocommerce_template_single_rating();?></div>
                                </div>
                            </div>
                            <div class="floatright tabletblockdisplay position-relative mb0 rh-336-sidebar disablefloattablet rh-flex-right-align">                    
                                <div class="woo-price-area tabletrelative darkhalfopacitybg text-center">
                                    <div class="rehub-btn-font font130">
                                        <?php woocommerce_template_single_price();?>
                                    </div>
                                </div> 
                                <?php do_action('rh_woo_single_product_price');?>
                            </div>                     
                        </div>
                    </div>
                    <span class="rh-post-layout-image-mask"></span>
                </div>
            </div>

            <?php wp_enqueue_script('stickysidebar');?>
            <div class="content-woo-area rh-container flowhidden mb35 rh-stickysidebar-wrapper">
                <div class="rh-336-sidebar floatright rh-sticky-container tabletblockdisplay">
                    <div class="padd20 summary border-grey whitebg rh_vert_bookable stickyonfloatpanel mb30"> 
                        <?php echo rh_generate_incss('vertbookable');?>
                        <?php rh_show_vendor_info_single(); ?>
                        <div class="woo-button-area mb10" id="woo-button-area"><?php do_action('rhwoo_template_single_add_to_cart');?></div>
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
                        <?php rh_woo_code_zone('button');?>
                    </div> 
                    <div class="tabletblockdisplay pt10 pr20 pl20 pb20 summary border-grey whitebg mb30 text-center">
                        <div class="woo-button-actions-area tabletblockdisplay">
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
                        <div class="woo-single-meta font80 mb10">
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
                        <?php woocommerce_template_single_sharing();?>                      
                    </div> 

                </div>                                
                <div class="rh-336-content-area post tabletblockdisplay floatleft rh-sticky-container">
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
                        <div class="rh-line mb20"></div>
                    
                    <?php endif;?>               

                    <div class="re_wooinner_info">
                        <?php  wc_print_notices(); ?> 
                        <?php rh_woo_code_zone('content');?> 
                        <?php woocommerce_template_single_excerpt();?>    
                        <?php do_action('rh_woo_single_product_description');?>        
                    </div> 

                    <?php foreach ( $tabs as $key => $tab ) : ?>
                        <div class="rh-line mb30 mt20"></div>
                        <div class="pb20 content-woo-section--<?php echo esc_attr( $key ); ?>" id="section-<?php echo esc_attr( $key ); ?>">
                            <?php call_user_func( $tab['callback'], $key, $tab ); ?>
                        </div>
                    <?php endforeach; ?> 
                    <div class="other-woo-area">
                        <div class="mb20">
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
                        <?php wp_enqueue_script('customfloatpanel');?>
                        <div class="flowhidden rh-float-panel" id="float-panel-woo-area">
                            <div class="rh-container rh-flex-eq-height rh-flex-nowrap">
                                <div class="pt5 pb5 rh-336-content-area rh-flex-center-align float-panel-img-wrap hideonsmobile">
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
                                                    <a href="#section-<?php echo esc_attr( $key ); ?>"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></a>
                                                </li>                                                
                                            <?php endforeach; ?>                                        
                                        </ul>
                                    </div>
                                </div>
                                <div class="darkbg float-panel-woo-btn rh-flex-center-align mb0 rh-336-sidebar rh-flex-right-align">
                                    <div class="whitecolor float-panel-woo-price font120 margincenter"><?php woocommerce_template_single_price();?>
                                    </div> 
                                    <div class="float-panel-woo-button rhhidden rh-flex-right-align showonmobile">
                                        <?php if(!rehub_option('woo_btn_inner_disable')) :?>
                                            <?php if ( $product->add_to_cart_url() !='') : ?>
                                                <?php if($product->get_type() == 'variable' || $product->get_type() == 'booking') {
                                                    $url = '#woo-button-area';
                                                }else{
                                                    $url = esc_url( $product->add_to_cart_url() );
                                                }

                                                ?>
                                                <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
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
                                        <?php endif; ?>
                                        <?php rh_woo_code_zone('float');?>                                       
                                    </div>                                                                           
                                </div>                                                                   
                            </div>                           
                        </div>                                                                                                                   
                </div>    
            </div>

        </div><!-- #product-<?php the_ID(); ?> -->

        <?php do_action( 'woocommerce_after_single_product' ); ?>
    <?php endwhile; // end of the loop. ?> 
    <?php do_action( 'woocommerce_after_main_content' ); ?>              
</div>  
<!-- Related -->
<?php include(rh_locate_template( 'woocommerce/single-product/full-width-related.php' ) ); ?>                     
<!-- /Related -->

<!-- Upsell -->
<?php include(rh_locate_template( 'woocommerce/single-product/full-width-upsell.php' ) ); ?>
<!-- /Upsell -->  

<?php rh_woo_code_zone('bottom');?>