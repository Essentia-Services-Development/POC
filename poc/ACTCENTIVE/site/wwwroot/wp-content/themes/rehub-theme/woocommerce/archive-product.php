<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header(); 
wp_enqueue_script('rhniceselect'); 
?>
<?php $left_sidebar = (rehub_option('rehub_sidebar_left_shop')) ? true : false;?>
<?php $mobile_sidebar = true;?>
<?php do_action('rh_woo_args_query');?>

<?php 
    $vendor_id = $vendor_pro = '';
    if (defined('wcv_plugin_dir')){
        $vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
        $vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );
    }
    if ($vendor_id){
        return include(rh_locate_template('inc/wcvendor/storepage.php'));
    }
?>

<?php if (is_tax('store')):?>  
      <?php include(rh_locate_template('woocommerce/brandarchive.php')); ?>                                     
<?php else :?> 

<?php $custom_shop_layout = rehub_option('woo_columns'); ?>
<?php if (is_numeric($custom_shop_layout) && function_exists('rh_wp_reusable_render')) : ?>
    <div class="rh-container rh_woo_main_archive">
        <?php echo rh_wp_reusable_render(array('id' => $custom_shop_layout));?> 
    </div>
<?php else : ?>
<!-- CONTENT -->
<?php $display_type = '';?>
<?php $display_type = woocommerce_get_loop_display_mode();?>
<?php if(is_product_taxonomy()) {
    $term = get_queried_object();
    $termid = $term->term_id;
    $page_title = apply_filters( 'woocommerce_page_title', $term->name );
    $catimage = get_term_meta( $termid, 'brandimage', true );
    if($catimage){
        echo '<div class="position-relative text-center woo_cat_head" id="woo_cat_head">';
        echo '<style scoped>#woo_cat_head{background-image: url('.$catimage.');background-size:cover; background-position:center center}</style>
                <span class="rh-post-layout-image-mask"></span>';
            echo '<div class="pr25 pl25 position-relative zind2"><div class="pt30 pb30"></div><h1 class="mt0 whitecolor font250">'.$page_title.'</h1>';
                echo '<div class="mt20 rehub-main-font hideonmobile">'.wpsm_tax_archive_shortcode(array('taxonomy'=>'product_cat', 'limit'=>5, 'child_of'=>$termid, 'type'=>'inlinelinks', 'classitem'=> 'whitecolor rh-hov-bor-line below-border mr10')).'</div>';
            echo '<div class="pt30 pb30"></div></div>';
        echo '</div>';
    }
}  ?>
<div class="rh-container rh_woo_main_archive"> 
    <div class="rh-content-wrap clearfix <?php if($left_sidebar):?>left-sidebar-archive<?php endif;?>"<?php if($mobile_sidebar){echo ' id="rh_woo_mbl_sidebar"';}?>>
        <?php echo rh_generate_incss('niceselect');?>
        
        <?php if($left_sidebar && rehub_option('woo_columns') !='4_col'):?>
            <!-- Sidebar -->
            <?php echo rh_generate_incss('widgetfilters');?>
            <?php get_sidebar('shop'); ?>
            <!-- /Sidebar -->
        <?php endif;?>
                 
        <!-- Main Side -->
        <div class="main-side woocommerce page<?php if(rehub_option('woo_columns') =='4_col') {echo ' full_width';}?>" id="content">
            <article class="post" id="page-<?php the_ID(); ?>">
                <?php echo rh_generate_incss('woobreadcrumbs');?>
                <?php do_action( 'woocommerce_before_main_content' );?>
                <?php if(!rehub_option('rehub_disable_breadcrumbs')){woocommerce_breadcrumb();}?>   
                <?php if(is_product_taxonomy() && !$catimage) {echo'<h1 class="arc-main-title">'.$page_title.'</h1>';}  ?>       
                <?php do_action( 'woocommerce_archive_description' ); ?>
                <?php if ( woocommerce_product_loop() ) : ?>
                    <?php if($mobile_sidebar && is_active_sidebar('wooshopsidebar')):?>
                        <div class="border-grey cursorpointer floatright font90 ml10 pl10 pr10 rehub-main-color rtlmr10 rhhidden" id="mobile-trigger-sidebar"><i class="fa-sliders-v fal"></i> <?php esc_html_e('Filter', 'rehub-theme');?></div>
                    <?php endif;?>
                    <?php $shop_global = rehub_option('rh_woo_shop_global');?>
                    <?php if ($shop_global):?>
                        <?php if ( 'subcategories' === $display_type || 'both' === $display_type ):?>
                            <?php 
                                    if(rehub_option('woo_columns') == '4_col'  || rehub_option('woo_columns') == '4_col_side') {
                                        $woocatclass = 'col_wrap_fourth';
                                    }
                                    elseif(rehub_option('woo_columns') == '5_col_side') {
                                        $woocatclass = 'col_wrap_fifth';
                                    }   
                                    else {
                                        $woocatclass = 'col_wrap_three'; 
                                    }
                            ?>                            
                            <?php woocommerce_output_product_categories(array( 'before' => '<div class="'.$woocatclass.' smart-scroll-mobile rh-flex-eq-height products_category_box column_woo">', 'after' => '</div>', 'parent_id' => is_product_category() ? get_queried_object_id() : 0)); ?>
                        <?php endif; ?>                        
                        <div class="clearfix"></div>
                        <?php echo do_shortcode($shop_global);?>
                        <div class="clearfix"></div>
                    <?php else:?>
                        <?php
                            /**
                             * woocommerce_before_shop_loop hook
                             *
                             * @hooked woocommerce_result_count - 20
                             * @hooked woocommerce_catalog_ordering - 30
                             */
                            do_action( 'woocommerce_before_shop_loop' );
                        ?> 
                        <?php if (function_exists('woocommerce_get_loop_display_mode')):?>
                            
                            <?php if ( 'subcategories' === $display_type || 'both' === $display_type ):?>
                                <?php 
                                        if(rehub_option('woo_columns') == '4_col'  || rehub_option('woo_columns') == '4_col_side') {
                                            $woocatclass = 'col_wrap_fourth';
                                        }
                                        elseif(rehub_option('woo_columns') == '5_col_side') {
                                            $woocatclass = 'col_wrap_fifth';
                                        }   
                                        else {
                                            $woocatclass = 'col_wrap_three'; 
                                        }
                                ?>                            
                                <?php woocommerce_output_product_categories(array( 'before' => '<div class="'.$woocatclass.' smart-scroll-mobile rh-flex-eq-height products_category_box column_woo">', 'after' => '</div>', 'parent_id' => is_product_category() ? get_queried_object_id() : 0)); ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php woocommerce_product_loop_start(); ?>   
                            <?php if ($display_type != 'subcategories'):?>                      
                                <?php while ( have_posts() ) : the_post(); ?>
                                    <?php do_action( 'woocommerce_shop_loop' ); wc_get_template_part( 'content', 'product' ); ?>
                                <?php endwhile; // end of the loop. ?>
                            <?php endif; ?>
                        <?php woocommerce_product_loop_end(); ?>
                        <?php
                            /**
                             * woocommerce_after_shop_loop hook
                             *
                             * @hooked woocommerce_pagination - 10
                             */
                            do_action( 'woocommerce_after_shop_loop' );
                        ?>
                    <?php endif; ?>                    
                <?php else : ?>
                    <?php wc_get_template( 'loop/no-products-found.php' ); ?>
                <?php endif; ?>
                <?php
                if ( (is_product_category() || is_product_tag()) && 0 === absint( get_query_var( 'paged' ))) {
                    if($term){
                        $cat_sec_desc = get_term_meta( $termid, 'brand_second_description', true );
                        if($cat_sec_desc){
                            echo '<div class="woo_cat_sec_description clearbox">' . wc_format_content( $cat_sec_desc ) . '</div>';
                        }
                    }
                }
                ?>                
                <?php
                    /**
                     * woocommerce_after_main_content hook.
                     *
                     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
                     */
                    do_action( 'woocommerce_after_main_content' );
                ?>                
            </article>
        </div>
        <!-- /Main Side --> 

        <?php if(!$left_sidebar && rehub_option('woo_columns') !='4_col'):?>
            <!-- Sidebar -->
            <?php echo rh_generate_incss('widgetfilters');?>
            <?php get_sidebar('shop'); ?>
            <!-- /Sidebar -->
        <?php endif;?> 

    </div>
</div>
<!-- /CONTENT -->
<?php endif;?> 

<?php endif;?>  
<?php get_footer(); ?>