<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Title area -->
        <div class="rh_post_layout_inner_image mb25">
            <?php           
                $image_id = get_post_thumbnail_id(get_the_ID());  
                $image_url = wp_get_attachment_image_src($image_id,'full');
                $image_url = $image_url[0];
                if (function_exists('_nelioefi_url')){
                    $image_nelio_url = get_post_meta( $post->ID, _nelioefi_url(), true );
                    if (!empty($image_nelio_url)){
                        $image_url = esc_url($image_nelio_url);
                    }           
                } 
            ?>  
            <div id="rh_post_layout_inimage">
                <style scoped>
                    #rh_post_layout_inimage{background-image: url(<?php echo ''.$image_url;?>);}
                    #rh_post_layout_inimage{color:#fff; background-position: center center; background-repeat: no-repeat; background-size: cover; background-color: #333;position: relative;width: 100%;z-index: 1;}
                    .rh_post_layout_inner_image #rh_post_layout_inimage{min-height: 500px;}
                    #rh_post_layout_inimage .rh_post_breadcrumb_holder{z-index: 2;position: absolute;top: 0;left: 0;min-height: 35px;}
                    #rh_post_layout_inimage .breadcrumb a, #rh_post_layout_inimage h1, #rh_post_layout_inimage .post-meta span a, #rh_post_layout_inimage .post-meta a.admin, #rh_post_layout_inimage .post-meta a.cat, #rh_post_layout_inimage .post-meta{color: #fff;text-shadow: 0 1px 1px #000;}
                    #rh_post_layout_inimage .breadcrumb{color: #f4f4f4}

                    .rh_post_layout_fullimage .rh-container{overflow: hidden; z-index:2; position:relative; min-height: 420px;}
                    .rh_post_layout_inner_image .rh_post_header_holder{position: absolute;bottom: 0;padding: 0 20px 0;z-index: 2;color: white;width: 100%; }

                    @media screen and (max-width: 1023px) and (min-width: 768px){
                        .rh_post_layout_inner_image #rh_post_layout_inimage, .rh_post_layout_fullimage .rh-container{min-height: 370px;}
                        #rh_post_layout_inimage .title_single_area h1{font-size: 28px; line-height: 34px}
                    }

                    @media screen and (max-width: 767px){   
                        .rh_post_layout_inner_image #rh_post_layout_inimage, .rh_post_layout_fullimage .rh-container{min-height: 300px;}
                        #rh_post_layout_inimage .title_single_area h1{font-size: 24px; line-height: 24px}   
                    }

                    .rtl #rh_post_layout_inimage .rh_post_breadcrumb_holder {left:auto;right: 0;}
                    .rh_post_layout_fullimage .title_single_area h1{ font-size: 44px; line-height: 46px; }

                </style>
                <?php echo re_badge_create('ribbon'); ?>
                <div class="rh_post_breadcrumb_holder padd15 pr30 mr30">
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
                </div>
                <div class="rh_post_header_holder">
                    <div class="title_single_area mb25"> 
                        <?php rh_post_header_cat('post');?>                           
                        <h1><?php the_title(); ?></h1>                                
                        <div class="meta post-meta mb20 flowhidden">
                            <?php rh_post_header_meta(true, true, true, true, false);?> 
                        </div>                           
                    </div>                     
                </div>
                <span class="rh-post-layout-image-mask"></span>
            </div>
        </div>    
	    <!-- Main Side -->
        <div class="main-side single<?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?> full_width<?php endif; ?> clearfix"> 
            <div class="rh-post-wrapper">           
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php $postclasses = array('post-inner', 'post');?>
                    <article <?php post_class($postclasses); ?> id="post-<?php the_ID(); ?>">
                        <?php if(rehub_option('rehub_disable_share_top') =='1')  : ?>
                        <?php else :?>
                            <div class="top_share">
                                <?php include(rh_locate_template('inc/parts/post_share.php')); ?>
                            </div>
                            <div class="clearfix"></div> 
                        <?php endif; ?>
                        <?php $no_featured_image_layout = 1;?>
                        <?php include(rh_locate_template('inc/parts/top_image.php')); ?>                                       
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