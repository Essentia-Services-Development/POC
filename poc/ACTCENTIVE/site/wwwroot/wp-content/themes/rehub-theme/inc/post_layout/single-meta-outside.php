<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- CONTENT -->
<div class="rh_post_layout_default rh_post_layout_outside mb20" id="rh_woo_layout_inimage">
<?php $maincolor = REHUB_MAIN_COLOR;?>
<style scoped>
    #rh_woo_layout_inimage{background: <?php echo hex2rgba($maincolor, 0.05);?>}
</style>
<div class="rh-container"> 
    <div class="pt20 clearfix pb10">
        <!-- Title area -->
            <div class="title_single_area mb0 rh-flex-eq-height rh-flex-justify-btw flowhidden">
                <div class="rh-336-content-area">
                <?php 
                    $crumb = '';
                    if( function_exists( 'yoast_breadcrumb' ) ) {
                        $crumb = yoast_breadcrumb('<div class="breadcrumb">','</div>', false);
                    }
                    else if (function_exists('rank_math_the_breadcrumbs')) {
                        $crumb = rank_math_get_breadcrumbs('wrap_before=<div class="breadcrumb">&wrap_after=</div>');
                    }
                    if( ! is_string( $crumb ) || $crumb === '' ) {
                        if(rehub_option('rehub_disable_breadcrumbs') == '1') {echo '';}
                        elseif (function_exists('dimox_breadcrumbs')) {
                            dimox_breadcrumbs(); 
                        }
                    }
                    echo ''.$crumb;  
                ?> 
                    <div class="mb15 clearfix"></div>
                    <div class="lineheight20 rh-flex-center-align mb10"><?php echo re_badge_create('labelsmall'); ?><?php rh_post_header_cat('post', true);?></div>                         
                    <h1><?php the_title(); ?></h1>
                    <div class="mb20 font120 rh_opacity_5"><?php echo ''.$post->post_excerpt;?></div> 
                    <div class="meta post-meta mb20 flowhidden">
                        <?php rh_post_header_meta('full', true, true, true, false);?> 
                    </div> 
                    <?php if(rehub_option('rehub_disable_share_top') =='1')  : ?>
                    <?php else :?>
                        <div class="top_share">
                            <?php include(rh_locate_template('inc/parts/post_share.php')); ?>
                        </div>
                        <div class="clearfix"></div> 
                    <?php endif; ?> 
                    <?php if(rehub_option('rehub_single_after_title')) : ?><div class="mediad mediad_top mb15"><?php echo do_shortcode(rehub_option('rehub_single_after_title')); ?></div><div class="clearfix"></div><?php endif; ?>                                                
                </div>
                <?php $disableimage = get_post_meta($post->ID, 'show_featured_image', true);?>
                <?php if($disableimage)  : ?>
                <?php else : ?>
                    <div class="post-head-image-part position-relative rh-336-sidebar mb0 rh-flex-center-align rh-flex-justify-center mobmb15">
                        <?php echo WPSM_image_resizer::show_wp_image('large_inner', '', array('lazydisable'=>true, 'loading'=>'eager')); ?> 
                    </div>
                <?php endif; ?>
            </div>            
    </div>
</div>

</div>
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">   
        <!-- Main Side -->
        <div class="main-side single<?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?> full_width<?php endif; ?> clearfix">
            <div class="rh-post-wrapper mb35">            
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php $postclasses = array('post-inner', 'post');?>
                    <article <?php post_class($postclasses); ?> id="post-<?php the_ID(); ?>">                                      
                        <?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
                        <?php if(rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>  

                        <?php the_content(); ?>

                    </article>
                    <div class="clearfix"></div>
                    <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?>                    
                <?php endwhile; endif; ?>
                <?php comments_template(); ?>
            </div>
        </div>  
        <!-- /Main Side -->  
        <!-- Sidebar -->
        <?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?><?php else : ?><?php get_sidebar(); ?><?php endif; ?>
        <!-- /Sidebar -->
    </div>
</div>
<!-- /CONTENT -->     