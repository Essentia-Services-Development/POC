<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!-- Title area -->
<?php wp_enqueue_script('rhreadingprogress' ); wp_enqueue_script('rhalignfull');wp_enqueue_script('rhyall');?>
<div id="rh_p_l_fullwidth_opt">
    <?php echo rh_generate_incss('fullwidthopt');?>
    <div class="rh-container mb25">
        <div class="rh_post_header_holder pt20">
            <div class="title_single_area mt30 mb25 single post-readopt clearfix full_width">
                <?php echo rh_generate_incss('postreadopt');?>                         
                <h1 class="mb30 rehub-main-color"><?php the_title(); ?></h1>
                <?php if(!empty($post->post_excerpt)):?>
                <div class="mb30 font120 rh_opacity_5 fontbold pb10 rh-post-excerpt">
                    <?php echo ''.$post->post_excerpt;?>
                </div>
                <?php endif;?>
                <?php rh_post_header_meta_big('2');?>
                         
            </div>                     
        </div> 
        <?php $disableimage = get_post_meta($post->ID, 'show_featured_image', true);?>
        <?php if ( (has_post_thumbnail()) && rehub_option('rehub_disable_feature_thumb') !='1' && !$disableimage ) { ?>
            <?php 
                $imagefull = get_the_post_thumbnail_url( $post->ID,'full' );
                echo rh_generate_incss('postwideimage');
            ?>
            <div id="rh_wide_inimage">
                <figure class="position-relative text-center flowhidden lightgreybg" style='background: url("<?php echo esc_url($imagefull);?>") no-repeat center center transparent;background-size:cover'>
                </figure> 
            </div>
            <div class="mb20"></div>
        <?php } ?>
    </div>
</div>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap pt0 clearfix">
        <!-- Main Side -->
        <div class="main-side single post-readopt alignfulloutside clearfix full_width">            
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <article <?php post_class('post pt0 pb0 pr0 pl0'); ?> id="post-<?php the_ID(); ?>">
                    <div class="post-inner clearbox">
                        <div class="post-inner-wrapper">                                          
                            <?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
                            <?php if(rehub_option('rehub_single_before_post') && $disableads != '1') : ?><div class="mediad mediad_before_content mb15"><?php echo do_shortcode(rehub_option('rehub_single_before_post')); ?></div><?php endif; ?>  
                            <?php $contentsticky = wpsm_contents_shortcode(array('headers'=>'h2')); echo wpsm_stickypanel_shortcode('', $contentsticky); ?>
                            <?php the_content(); ?>
                            <div class="clearfix"></div>
                            <?php include(rh_locate_template('inc/post_layout/single-common-footer.php')); ?>
                            <div class="clearfix"></div> 
                            <?php comments_template(); ?>              
                        </div>
                    </div>
                </article>                    
            <?php endwhile; endif; ?>
        </div>  
        <!-- /Main Side --> 
    </div>
</div>
<!-- /CONTENT -->     