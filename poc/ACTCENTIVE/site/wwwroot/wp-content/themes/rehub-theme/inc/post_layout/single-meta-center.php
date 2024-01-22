<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- CONTENT -->
<?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
	    <!-- Main Side -->
        <div class="main-side single<?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?> full_width<?php endif; ?> clearfix"> 
            <div class="rh-post-wrapper">           
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php $postclasses = array('post-inner', 'post');?>
                    <article <?php post_class($postclasses); ?> id="post-<?php the_ID(); ?>">
                        <?php echo rh_generate_incss('postfullmax');?> 
                        <!-- Title area -->
                        <div class="rh_post_layout_default rh_post_layout_center text-center">
                            <div class="title_single_area mb15">
                                <?php echo re_badge_create('labelsmall'); ?><?php rh_post_header_cat('post');?>            
                                <h1><?php the_title(); ?></h1>                                
                                <div class="meta post-meta mb20 flowhidden">
                                    <?php rh_post_header_meta(true, true, true, true, false);?> 
                                </div>       
                            </div>
                            <?php if(rehub_option('rehub_disable_share_top') =='1' || $disableads)  : ?>
                            <?php else :?>
                                <div class="top_share">
                                    <?php include(rh_locate_template('inc/parts/post_share.php')); ?>
                                </div>
                                <div class="clearfix"></div> 
                            <?php endif; ?>
                        </div>
                        <?php if(rehub_option('rehub_single_after_title')) : ?><div class="mediad mediad_top mb15"><?php echo do_shortcode(rehub_option('rehub_single_after_title')); ?></div><div class="clearfix"></div><?php endif; ?>

                        <?php include(rh_locate_template('inc/parts/top_image.php')); ?>                                       
                        <?php if(rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>  

                        <?php the_content(); ?>
                        <div class="clearfix"></div>
                        <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?> 
                    </article>                   
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