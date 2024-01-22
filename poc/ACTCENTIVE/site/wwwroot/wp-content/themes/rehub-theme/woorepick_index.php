<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
    
    /* Template Name: Grid with filters Woo (enable Repick subset) */

?>
<?php
    if ( !class_exists( 'woocommerce' ) )
        wp_die('WARNING! Woocommerce plugin is not installed or activated!');
?>
<?php          
?>
<?php get_header(); ?>
<?php if ( get_query_var('paged') ) { $paged = get_query_var('paged'); } else if ( get_query_var('page') ) {$paged = get_query_var('page'); } else {$paged = 1; } ?>
<?php 
$enable_pagination ='2';
$infinitescrollwrap = ' re_aj_pag_auto_wrap';
$show = 12;
$count_ads = rehub_option('rehub_grid_ad_count');
if (!empty ($count_ads)) {
    foreach ($count_ads as $count_ad) {
        $show--;
    }
}

$containerid = 'rh_loop_' . mt_rand();
$ajaxoffset = $show; 
$args = array(
    'posts_per_page' => $show,
    'paged' => $paged,
    'post_type' => 'product',
);
$aff_link = (rehub_option('disable_inner_links') == 1) ? 1 : 0;
$additional_vars = array('aff_link'=>$aff_link);
$jsonargs = json_encode($args);
$json_innerargs = json_encode($additional_vars);

?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side page woocommerce clearfix full_width">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <?php $contpage = get_the_content(); if (!empty($contpage)) :?>
                    <article class="top_rating_text mb15"><?php echo do_shortcode($contpage); ?></article><div class="clearfix"></div>
                <?php endif; ?>
            <?php endwhile; endif; ?> 
            <?php $prepare_filter = array();?>
            <?php 
                $prepare_filter[] = array (
                    'filtertitle' => esc_html__('Latest', 'rehub-theme'),
                    'filtertype' => 'all',
                    'filterorderby' => 'date',
                    'filterorder'=> 'DESC', 
                    'filterdate' => 'all',                        
                );
                $prepare_filter[] = array (
                    'filtertitle' => esc_html__('Hottest', 'rehub-theme'),
                    'filtertype' => 'meta',
                    'filterorderby' => 'date',
                    'filterorder'=> 'DESC', 
                    'filterdate' => 'all',                        
                );
                $prepare_filter[] = array (
                    'filtertitle' => esc_html__('Popular', 'rehub-theme'),
                    'filtertype' => 'meta',
                    'filtermetakey' => 'rehub_views_mon',
                    'filterorder'=> 'DESC', 
                    'filterdate' => 'all',                        
                );                                        
                $prepare_filter[] = array (
                    'filtertitle' => esc_html__('Discussed', 'rehub-theme'),
                    'filtertype' => 'comment',
                    'filterorderby' => 'date',
                    'filterorder'=> 'DESC', 
                    'filterdate' => 'all',                        
                );
                $prepare_filter[] = array (
                    'filtertitle' => esc_html__('Favorite', 'rehub-theme'),
                    'filtertype' => 'meta',
                    'filtermetakey' => 'post_wish_count',
                    'filterorderby' => 'date',
                    'filterorder'=> 'DESC', 
                    'filterdate' => 'all',                        
                );                      
                $prepare_filter[] = array (
                    'filtertitle' => esc_html__('Random', 'rehub-theme'),
                    'filtertype' => 'all',
                    'filterorderby' => 'rand',
                    'filterorder'=> 'DESC', 
                    'filterdate' => 'all',                        
                );  
                $prepare_filter = urlencode(json_encode($prepare_filter));             
            ?>
            <div class="filter_home_pick">
            <?php rehub_vc_filterpanel_render($prepare_filter, $containerid);?>
            </div>               
            <div class="clearfix"></div>
            <?php echo rh_generate_incss('masonry');?>    
            <div class="masonry_grid_fullwidth col_wrap_three products pb30 <?php echo esc_attr($infinitescrollwrap);?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="woogridpartajax" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>              
                    <?php $query = new WP_Query( $args ); ?>
                    <?php if ($query->have_posts()) : ?>
                    <?php 
                    $count = 0; 
                    $count_ad_descs = explode("\n", rehub_option('rehub_grid_ads_desc'));
                    while ($query->have_posts()) : $query->the_post(); ?>
                        <?php              
                            $count++;
                            $count_ad_code = rehub_option('rehub_grid_ads_code');                
                        ?>
                        <?php 
                            $custom_col = 'yes'; 
                            $custom_img_height = 383; 
                            $custom_img_width = 383; 
                        ?>                        
                        <?php include(rh_locate_template('inc/parts/woogridpartajax.php')); ?> 
                    <?php endwhile; endif;?>                
                <div class="clearfix"></div>
                <?php wp_enqueue_script('rhajaxpagination');?>
                <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>       
                <?php wp_reset_query(); ?> 
            </div>                                          
                            
        </div>  
        <!-- /Main Side -->  
    </div>
</div>
<!-- /CONTENT -->     
<!-- FOOTER -->
<?php get_footer(); ?>