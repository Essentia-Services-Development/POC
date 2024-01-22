<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
<?php global $post;?>
<?php $rh_post_layout_style = get_post_meta($post->ID, '_post_layout', true);?>
<?php if ($rh_post_layout_style == '') {
    if($post->post_type =='blog'){
        $rh_post_layout_style = rehub_option('blog_layout_style');
    }else{
        $rh_post_layout_style = rehub_option('post_layout_style'); 
    }
} ?>
<?php if ($rh_post_layout_style == '') :?>
    <?php  
    $theme_subset = rehub_option('theme_subset');     
    if ($theme_subset == 'recash') {
        $rh_post_layout_style = 'meta_compact'; 
    }
    elseif ($theme_subset == 'repick') {
        $rh_post_layout_style = 'corner_offer';
    }
    elseif ($theme_subset == 'rething') {
        $rh_post_layout_style = 'meta_center';
    }
    elseif ($theme_subset == 'revendor') {
        $rh_post_layout_style = 'meta_outside';
    }   
    elseif ($theme_subset == 'redirect') {
        $rh_post_layout_style = 'meta_compact_dir';
    }                                     
    else{
        $rh_post_layout_style = 'default';       
    }?>
<?php endif;?>


<?php if($rh_post_layout_style === 'default') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-default.php')); ?>
<?php elseif($rh_post_layout_style === 'default_text_opt') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-default-readopt.php')); ?>  
<?php elseif($rh_post_layout_style === 'default_full_opt') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-full-opt.php')); ?> 
<?php elseif($rh_post_layout_style === 'guten_auto') : ?>
    <?php include(rh_locate_template('inc/post_layout/guten-auto.php')); ?>   
<?php elseif($rh_post_layout_style === 'video_block') : ?>
    <?php include(rh_locate_template('inc/post_layout/video_block.php')); ?> 
<?php elseif($rh_post_layout_style === 'meta_outside') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-meta-outside.php')); ?> 
<?php elseif($rh_post_layout_style === 'meta_center') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-meta-center.php')); ?> 
<?php elseif($rh_post_layout_style === 'meta_compact') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-meta-compact.php')); ?>
<?php elseif($rh_post_layout_style === 'meta_compact_dir') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-meta-compact-dir.php')); ?>   
<?php elseif($rh_post_layout_style === 'corner_offer') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-corner-offer.php')); ?>
<?php elseif($rh_post_layout_style === 'meta_in_image') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-inimage.php')); ?>
<?php elseif($rh_post_layout_style === 'meta_in_imagefull') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-inimagefull.php')); ?>
<?php elseif($rh_post_layout_style === 'big_post_offer') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-big-offer.php')); ?>         
<?php elseif($rh_post_layout_style === 'offer_and_review') : ?>
    <?php include(rh_locate_template('inc/post_layout/single-offer-reviewscore.php')); ?>     
<?php elseif($rh_post_layout_style === 'gutencustom') : ?>
    <?php include(rh_locate_template('inc/post_layout/gutencustom.php')); ?>   
<?php else:?>
    <?php include(rh_locate_template('inc/post_layout/single-default.php')); ?>                               
<?php endif;?>

<!-- FOOTER -->
<?php get_footer(); ?>