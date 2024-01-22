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
<?php $unique_id = $module_id = $itemsync = $syncitem = $youtubecontent = $replacetitle = '';?>
<?php $postid = $post->ID;?>
<?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
    <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($post->ID);?>
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

<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <div id="contents-section-woo-area" class="rh-stickysidebar-wrapper">                      
            <div class="ce_woo_auto_sections ce_woo_blocks ce_woo_list main-side rh-sticky-container clearfix <?php echo (is_active_sidebar( 'sidebarwooinner' )) ? 'woo_default_w_sidebar' : 'full_width woo_default_no_sidebar'; ?>" id="content">
                <style scoped>
                    .ce_woo_blocks nav.woocommerce-breadcrumb{font-size: 13px; margin-bottom: 18px}
                    .ce_woo_blocks .woo_bl_title h1{font-size: 22px; line-height: 26px; margin: 0 0 15px 0; font-weight: normal;}
                </style>
                <div class="post">
                    <?php do_action( 'woocommerce_before_main_content' );?>                    
                    <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>

                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php do_action( 'woocommerce_before_single_product' );?>     
                        <div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>                         
                            <div class="ce_woo_block_top_holder">
                                <div class="woo_bl_title flowhidden mb10">
                                    <div class="floatleft tabletblockdisplay pr20 rtlpr20">
                                        <h1 class="<?php if(rehub_option('wishlist_disable') !='1') :?><?php echo getHotIconclass($post->ID, true); ?><?php endif ;?>"><?php the_title(); ?></h1>
                                        <?php do_action('rh_woo_single_product_title');?>
                                         <?php echo wpsm_reviewbox(array('compact'=>'text', 'id'=> $post->ID, 'scrollid'=>'tab-title-description'));?> 
                                    </div>
                                    <div class="woo-top-actions tabletblockdisplay floatright">
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
                                <div class="border-grey-bottom clearfix"></div>

                                <div class="wpsm-one-third wpsm-column-first pt20 tabletblockdisplay compare-full-images modulo-lightbox mb30">
                                    <?php 
                                        wp_enqueue_script('modulobox');
                                        wp_enqueue_style('modulobox');
                                    ?>                                                         
                                    <figure class="text-center">
                                        <?php  $badge = get_post_meta($post->ID, 'is_editor_choice', true); ?>
                                        <?php if ($badge !='' && $badge !='0') :?> 
                                            <?php echo re_badge_create('ribbon'); ?>
                                        <?php else:?>                                        
                                            <?php woocommerce_show_product_sale_flash();?>
                                        <?php endif;?>
                                        <?php           
                                            $image_id = get_post_thumbnail_id($post->ID);  
                                            $image_url = wp_get_attachment_image_src($image_id,'full');
                                            $image_url = $image_url[0]; 
                                        ?> 
                                        <a data-rel="rh_top_gallery" href="<?php echo ''.$image_url;?>" target="_blank" data-thumb="<?php echo ''.$image_url;?>">            
                                            <?php echo WPSM_image_resizer::show_wp_image('woocommerce_single', '', array('lazydisable'=>true, 'loading'=>'eager')); ?>
                                        </a>
                                        <?php do_action( 'rehub_360_product_image' ); ?>
                                    </figure>
                                    <?php $post_image_gallery = $product->get_gallery_image_ids();?>
                                    <?php if(!empty($post_image_gallery)) :?> 
                                        <div class="rh-flex-eq-height rh_mini_thumbs compare-full-thumbnails mt15 mb15">
                                            <?php foreach($post_image_gallery as $key=>$image_gallery):?>
                                                <?php if(!$image_gallery) continue;?>
                                                <?php $image = wp_get_attachment_image_src($image_gallery, 'full'); $imgurl = (!empty($image[0])) ? $image[0] : ''; ?>
                                                <a data-rel="rh_top_gallery" data-thumb="<?php echo esc_url($imgurl);?>" href="<?php echo esc_url($imgurl);?>" target="_blank" class="rh-flex-center-align mb10" data-title="<?php echo esc_attr(get_post_field( 'post_excerpt', $image_gallery));?>"> 
                                                    <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>false, 'src'=> esc_url($imgurl), 'crop'=> false, 'height'=> 60, 'width'=>60));?>
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
                                        <?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
                                            <?php if (!empty($itemsync['extra']['imageSet'])){
                                                $ceimages = $itemsync['extra']['imageSet'];
                                            }elseif (!empty($itemsync['extra']['images'])){
                                                $ceimages = $itemsync['extra']['images'];
                                            }
                                            else {
                                                $qwantimages = \ContentEgg\application\components\ContentManager::getViewData('GoogleImages', $post->ID);
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
                                                        <a data-thumb="<?php echo esc_url($image)?>" data-rel="rh_top_gallery" href="<?php echo esc_url($image); ?>" data-title="<?php echo esc_attr($itemsync['title']);?>" class="rh-flex-center-align mb10"> 
                                                            <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $image, 'height'=> 65, 'width'=>65, 'title' => $itemsync['title'], 'no_thumb_url' => get_template_directory_uri().'/images/default/noimage_100_70.png'));?>  
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
                                    <?php endif;?> 
                                    <?php do_action('rh_woo_after_single_image');?> 
                                </div>
                                <div class="wpsm-two-third rh-line-left pl20 rtlpr20 pt10 tabletblockdisplay wpsm-column-last mb30 disablemobileborder disablemobilepadding" id="section-woo-ce-pricelist">

                                    <div class="rh-flex-center-align woo_top_meta mobileblockdisplay mb10">
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
                                        <span class="floatleft disablefloatmobile meta post-meta mt0 mb0">
                                            <?php
                                            if(rehub_option('post_view_disable') != 1){ 
                                                $rehub_views = get_post_meta ($post->ID,'rehub_views',true); 
                                                echo '<span class="greycolor postview_meta mr10">'.$rehub_views.'</span>';
                                            } 
                                            $categories = wc_get_product_terms($post->ID, 'product_cat', array("fields" => "all"));
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
                                                                               
                                    </div>                                     
                                    <div class="rh-line mb15 mt15"></div> 
                                    <div class="rh_post_layout_rev_price_holder position-relative">

                                        <?php echo wpsm_woocompare_shortcode(array('field'=>'_sku', 'compact'=>1));?>
                                        <?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
                                            <?php echo do_shortcode('[content-egg-block template=custom/all_simple_list]');?>
                                        <?php endif;?>
                                        <?php do_action('rh_woo_single_product_price');?>
                                        <?php rh_woo_code_zone('button');?>          
                                    </div>
                                    <div class="mt10">                               
                                        <div class="font90 lineheight20 woo_desc_part">
                                            <?php rh_woo_code_zone('content');?>                               
                                            <?php if(has_excerpt($post->ID)):?>
                                                <?php woocommerce_template_single_excerpt();?>
                                            <?php endif;?>
                                            <?php do_action('rh_woo_single_product_description');?>
                                        </div>                                   
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="woo-single-meta font80">
                                        <?php do_action( 'woocommerce_product_meta_start' ); ?>
                                        <?php $term_ids =  wc_get_product_terms($post->ID, 'store', array("fields" => "ids")); ?>
                                        <?php if (!empty($term_ids) && ! is_wp_error($term_ids)) :?>
                                            <div class="woostorewrap flowhidden mb10">         
                                                <div class="store_tax">       
                                                    <?php WPSM_Woohelper::re_show_brand_tax(); //show brand taxonomy?>
                                                </div>  
                                            </div>
                                        <?php endif;?>                              
                                        <?php do_action( 'woocommerce_product_meta_end' ); ?>
                                    </div> 
                                    <div class="top_share notextshare">
                                        <?php woocommerce_template_single_sharing();?>
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

                            if ( ! empty( $tabs ) ) : ?>

                                <?php if (defined('\ContentEgg\PLUGIN_PATH')):?>

                                    <?php 
                                        $replacetitle = apply_filters('woo_product_section_title', get_the_title().' ');
                                        if(!empty($youtubecontent)){
                                            $tabs['woo-ce-videos'] = array(
                                                'title' => $replacetitle.__('Videos', 'rehub-theme'),
                                                'priority' => '21',
                                                'callback' => 'woo_ce_video_output'
                                            );
                                        }
                                        $googlenews = get_post_meta($post->ID, '_cegg_data_GoogleNews', true);
                                        if(!empty($googlenews)){
                                            $tabs['woo-ce-news'] = array(
                                                'title' => esc_html__('World News', 'rehub-theme'),
                                                'priority' => '23',
                                                'callback' => 'woo_ce_news_output'
                                            );
                                        }                                                                                 
                                        uasort( $tabs, '_sort_priority_callback' );                                 
                                    ?>

                                <?php endif;?>

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
                                                        <?php $tab_title = str_replace($replacetitle, '', $tab['title']);?>
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
                                                    <a href="#section-woo-ce-pricelist" class="single_add_to_cart_button rehub_scroll">
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

                        </div><!-- #product-<?php the_ID(); ?> -->
                        <?php do_action( 'woocommerce_after_single_product' ); ?>
                    <?php endwhile; // end of the loop. ?>
                    <?php do_action( 'woocommerce_after_main_content' ); ?>                                   

                </div>

            </div>
            <?php if ( is_active_sidebar( 'sidebarwooinner' ) ) : ?>
                <?php wp_enqueue_script('stickysidebar');?>
                <aside class="sidebar rh-sticky-container">            
                    <?php dynamic_sidebar( 'sidebarwooinner' ); ?>      
                </aside> 
            <?php endif; ?>                           
        </div>    
    </div>
</div>
<!-- /CONTENT --> 

<?php rh_woo_code_zone('bottom');?>