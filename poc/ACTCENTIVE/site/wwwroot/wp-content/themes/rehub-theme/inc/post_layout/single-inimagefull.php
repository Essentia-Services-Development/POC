<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- Title area -->
<?php wp_enqueue_script('rhreadingprogress' ); wp_enqueue_script('rhalignfull');wp_enqueue_script('rhyall');?>
<?php 
    $imagefull = get_the_post_thumbnail_url( $post->ID,'full' );
    $randomclass = 'lbg'.mt_rand();
    echo rh_generate_incss('lazybgsceleton', $randomclass, array('imageurl'=>$imagefull));
?> 
<div class="rh_post_layout_fullimage mb25 <?php echo ''.$randomclass;?>">
    <div id="rh_post_layout_inimage" class="flowhidden lazy-bg rh-sceleton darkbg">
        <?php echo rh_generate_incss('singleimagefull');?>
        <div class="rh-container rh-flex-center-align rh-flex-justify-center">
        <div class="rh_post_header_holder text-center">
            <div class="title_single_area mb25">                          
                <h1 class="mb30"><?php the_title(); ?></h1>
                <div class="date_big_meta font120 mb30"><?php rh_post_header_meta(false, true, false, false, false);?></div>
                <div class="meta post-meta mb20 flowhidden font105">
                    <?php rh_post_header_meta('fullbig', false, true, true, true);?> 
                </div>                           
            </div>                     
        </div>
        </div>
        <span class="rh-post-layout-image-mask"></span>
    </div>
</div>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side single alignfulloutside post-readopt clearfix<?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?> full_width<?php else:?> w_sidebar<?php endif; ?>"> 
            <?php echo rh_generate_incss('postreadopt');?>           
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article <?php post_class('post pt0 pb0 pr0 pl0'); ?> id="post-<?php the_ID(); ?>">
                    <div class="post-inner clearbox">
                        <div class="post-inner-wrapper">                                          
                            <?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
                            <?php if(rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>  
                            <?php the_content(); ?>
                            <div class="clearfix"></div>
                            <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?>               
                        </div>
                    </div>
                </article>
                <div class="clearfix"></div>                    
            <?php endwhile; endif; ?>
            <?php comments_template(); ?>
        </div>  
        <!-- /Main Side --> 
        <!-- Sidebar -->
        <?php if(get_post_meta($post->ID, 'post_size', true) == 'full_post' || rehub_option('disable_post_sidebar')) : ?><?php else : ?><?php get_sidebar(); ?><?php endif; ?>
        <!-- /Sidebar --> 
    </div>
</div>
<!-- /CONTENT -->     