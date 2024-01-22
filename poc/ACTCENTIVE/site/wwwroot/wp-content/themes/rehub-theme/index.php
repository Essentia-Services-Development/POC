<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
<?php
$price_meta = rehub_option('price_meta_grid');
$disable_btn = (rehub_option('rehub_enable_btn_recash') == 1) ? 0 : 1;
$disable_act = (rehub_option('disable_grid_actions') == 1) ? 1 : 0;
$aff_link = (rehub_option('disable_inner_links') == 1) ? 1 : 0;
$archive_layout = rehub_option('archive_layout');
?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side clearfix<?php if ($archive_layout == 'gridfull' || $archive_layout == 'dealgridfull' || $archive_layout == 'compactgridfull' || $archive_layout == 'columngridfull' || $archive_layout == 'cardblogfull') : ?> full_width<?php endif ;?>">
            <h1 class="wpsm-title position-relative flowhidden mb25 under-title-line middle-size-title">
                <?php if ( is_home() && ! is_front_page() ) : ?>
                    <?php single_post_title(); ?>
                <?php else:?>
                    <?php esc_html_e('Latest Posts', 'rehub-theme'); ?>
                <?php endif;?>  
            </h1>  
            <?php if (have_posts()) : ?>
                <?php if ($archive_layout == 'blog') : ?>
                    <div class="">

                <?php elseif ($archive_layout == 'newslist') : ?>
                    <div class="rh-num-counter-reset"> 

                <?php elseif ($archive_layout == 'communitylist') : ?>
                    <div class="">

                <?php elseif ($archive_layout == 'deallist') : ?>
                    <div class="woo_offer_list " > 

                <?php elseif ($archive_layout == 'grid') : ?>  
                    <?php echo rh_generate_incss('masonry');?>              
                    <div class="masonry_grid_fullwidth col_wrap_two">
                <?php elseif ($archive_layout == 'gridfull') : ?>
                    <?php echo rh_generate_incss('masonry');?>
                    <div class="masonry_grid_fullwidth col_wrap_three"> 
                <?php elseif ($archive_layout == 'columngrid') : ?>               
                    <div class="columned_grid_module rh-flex-eq-height col_wrap_three" >

                <?php elseif ($archive_layout == 'columngridfull') : ?>               
                    <div class="columned_grid_module rh-flex-eq-height col_wrap_fourth">  
                    
                <?php elseif ($archive_layout == 'compactgrid') : ?>               
                    <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fifth' : 'col_wrap_fourth';?>">
                        <?php echo rh_generate_incss('offergrid');?>

                <?php elseif ($archive_layout == 'compactgridfull') : ?>               
                    <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_six' : 'col_wrap_fifth';?>">
                        <?php echo rh_generate_incss('offergrid');?>

                <?php elseif ($archive_layout == 'cardblog') : ?>               
                    <div class="coloredgrid rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fourth' : 'col_wrap_three';?>">  
                    
                <?php elseif ($archive_layout == 'cardblogfull') : ?>               
                    <div class="coloredgrid rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fifth' : 'col_wrap_fourth';?>">                     

                <?php elseif ($archive_layout == 'dealgrid') : ?>               
                    <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fourth' : 'col_wrap_three';?>">
                        <?php echo rh_generate_incss('offergrid');?>

                <?php elseif ($archive_layout == 'dealgridfull') : ?>               
                    <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fifth' : 'col_wrap_fourth';?>"> 
                    <?php echo rh_generate_incss('offergrid');?>                                                                     
                <?php else : ?>
                    <div class="">   
                <?php endif ;?>                
                    <?php while (have_posts()) : the_post(); ?>
                        <?php if ($archive_layout == 'blog') : ?>
                            <?php include(rh_locate_template('inc/parts/query_type2.php')); ?>

                        <?php elseif ($archive_layout == 'newslist') : ?>
                            <?php $type='2'; ?>
                            <?php include(rh_locate_template('inc/parts/query_type1.php')); ?> 

                        <?php elseif ($archive_layout == 'communitylist') : ?>
                            <?php include(rh_locate_template('inc/parts/query_type1.php')); ?>

                        <?php elseif ($archive_layout == 'deallist') : ?>
                            <?php include(rh_locate_template('inc/parts/postlistpart.php')); ?>                                                
                        <?php elseif ($archive_layout == 'grid' || $archive_layout == 'gridfull') : ?>
                            <?php include(rh_locate_template('inc/parts/query_type3.php')); ?>

                        <?php elseif ($archive_layout == 'columngrid' || $archive_layout == 'columngridfull') : ?>
                            <?php include(rh_locate_template('inc/parts/column_grid.php')); ?>

                        <?php elseif ($archive_layout == 'cardblog' || $archive_layout == 'cardblogfull') : ?>
                                <?php include(rh_locate_template('inc/parts/color_grid.php')); ?>                        

                        <?php elseif ($archive_layout == 'compactgrid' || $archive_layout == 'compactgridfull') : ?>
                            <?php $gridtype = 'compact'; include(rh_locate_template('inc/parts/compact_grid.php')); ?>          
                        <?php elseif ($archive_layout == 'compactgrid' || $archive_layout == 'compactgridfull') : ?>
                            <?php $gridtype = 'compact'; include(rh_locate_template('inc/parts/compact_grid.php')); ?>                                    
                        <?php elseif ($archive_layout == 'dealgrid' || $archive_layout == 'dealgridfull') : ?>
                            <?php include(rh_locate_template('inc/parts/compact_grid.php')); ?>
                     
                        <?php else : ?>
                            <?php include(rh_locate_template('inc/parts/query_type1.php')); ?>  
                        <?php endif ;?>                
                    <?php endwhile; ?>
                </div>
                <div class="pagination"><?php rehub_pagination();?></div>
            <?php else : ?>     
            <h5 class="font140"><?php esc_html_e('Sorry. No posts in this category yet', 'rehub-theme'); ?></h5>    
            <?php endif; ?> 
            <div class="clearfix"></div>
        </div>  
        <!-- /Main Side -->
        <?php if ($archive_layout == 'gridfull' || $archive_layout == 'dealgridfull' || $archive_layout == 'compactgridfull' || $archive_layout == 'columngridfull' || $archive_layout == 'cardblogfull') : ?>
        <?php else:?>
            <!-- Sidebar -->
            <?php get_sidebar(); ?>
            <!-- /Sidebar --> 
        <?php endif ;?>
    </div>
</div>
<!-- /CONTENT -->     
<!-- FOOTER -->
<?php get_footer(); ?>