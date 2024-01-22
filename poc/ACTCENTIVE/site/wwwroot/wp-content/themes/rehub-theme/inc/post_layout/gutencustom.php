<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- CONTENT -->
<div class="rh-container full_gutenberg"> 
<?php echo rh_generate_incss('fullgutenberg');?> 
    <div class="rh-content-wrap pt0 clearfix">
        <!-- Main Side -->
        <div class="main-side fullgutenberg full_width clearfix"  id="content">      
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <div class="post-inner clearbox">
                        <div class="post-inner-wrapper">     
                        <article <?php post_class('post pt0 pb0 pr0 pl0'); ?> id="post-<?php the_ID(); ?>">                                     
                            <?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
                            <?php if(rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>  
                            <?php the_content(); ?>
                            <div class="clearfix"></div>
                            <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?>
                            <div class="clearfix"></div> 
                            <?php comments_template(); ?> 
                            </article>              
                        </div>
                    </div>                   
            <?php endwhile; endif; ?>
        </div>  
        <!-- /Main Side --> 
    </div>
</div>
<!-- /CONTENT -->     