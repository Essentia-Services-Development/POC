<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
<?php
$price_meta = rehub_option('price_meta_grid');
$disable_btn = (rehub_option('rehub_enable_btn_recash') == 1) ? 0 : 1;
$disable_act = (rehub_option('disable_grid_actions') == 1) ? 1 : 0;
$aff_link = (rehub_option('disable_inner_links') == 1) ? 1 : 0;
$search_layout = rehub_option('search_layout');
?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side clearfix<?php if ($search_layout == 'gridfull' || $search_layout == 'mobilegridfull' || $search_layout == 'dealgridfull' || $search_layout == 'compactgridfull' || $search_layout == 'columngridfull' || $search_layout == 'cardblogfull') : ?> full_width<?php endif ;?>">
            <?php $cursearch = get_search_query();?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><div class="font140"><span><?php esc_html_e('Search results for:', 'rehub-theme'); ?></span> <?php echo esc_html($cursearch); ?></div></div> 
            <?php if ($search_layout == 'blog') : ?>
                <div class="">

            <?php elseif ($search_layout == 'newslist') : ?>
                <div class="rh-num-counter-reset"> 

            <?php elseif ($search_layout == 'communitylist') : ?>
                <div class="">

            <?php elseif ($search_layout == 'deallist') : ?>
                <div class="woo_offer_list " > 

            <?php elseif ($search_layout == 'grid') : ?>  
                <?php echo rh_generate_incss('masonry');?>            
                <div class="masonry_grid_fullwidth col_wrap_two">
            <?php elseif ($search_layout == 'gridfull') : ?>
                <?php echo rh_generate_incss('masonry');?>
                <div class="masonry_grid_fullwidth col_wrap_three"> 
            <?php elseif ($search_layout == 'columngrid') : ?>               
                <div class="columned_grid_module rh-flex-eq-height col_wrap_three" >

            <?php elseif ($search_layout == 'columngridfull') : ?>               
                <div class="columned_grid_module rh-flex-eq-height col_wrap_fourth">  
                
            <?php elseif ($search_layout == 'compactgrid') : ?>               
                <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fifth' : 'col_wrap_fourth';?>">
                    <?php echo rh_generate_incss('offergrid');?>

            <?php elseif ($search_layout == 'cardblog') : ?>               
                <div class="coloredgrid rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fourth' : 'col_wrap_three';?>">  
                
            <?php elseif ($search_layout == 'cardblogfull') : ?>               
                <div class="coloredgrid rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fifth' : 'col_wrap_fourth';?>">                                  

            <?php elseif ($search_layout == 'compactgridfull') : ?> 
                <?php echo rh_generate_incss('offergrid');?>              
                <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_six' : 'col_wrap_fifth';?>">

            <?php elseif ($search_layout == 'dealgrid' || $search_layout == 'mobilegrid') : ?>               
                <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fourth' : 'col_wrap_three';?>">
                <?php echo rh_generate_incss('offergrid');?>

            <?php elseif ($search_layout == 'dealgridfull' || $search_layout == 'mobilegridfull') : ?>               
                <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_six' : 'col_wrap_fifth';?>">
                <?php echo rh_generate_incss('offergrid');?>                                                                      
            <?php else : ?>
                <div class="">   
            <?php endif ;?>
            <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php if ($search_layout == 'blog') : ?>
                    <?php include(rh_locate_template('inc/parts/query_type2.php')); ?>

                <?php elseif ($search_layout == 'newslist') : ?>
                    <?php $type='2'; ?>
                    <?php include(rh_locate_template('inc/parts/query_type1.php')); ?> 

                <?php elseif ($search_layout == 'communitylist') : ?>
                    <?php include(rh_locate_template('inc/parts/query_type1.php')); ?>

                <?php elseif ($search_layout == 'deallist') : ?>
                    <?php include(rh_locate_template('inc/parts/postlistpart.php')); ?>                                                
                <?php elseif ($search_layout == 'grid' || $search_layout == 'gridfull') : ?>
                    <?php include(rh_locate_template('inc/parts/query_type3.php')); ?>

                <?php elseif ($search_layout == 'columngrid' || $search_layout == 'columngridfull') : ?>
                    <?php include(rh_locate_template('inc/parts/column_grid.php')); ?> 

                <?php elseif ($search_layout == 'cardblog' || $search_layout == 'cardblogfull') : ?>
                        <?php include(rh_locate_template('inc/parts/color_grid.php')); ?>                      

                <?php elseif ($search_layout == 'compactgrid' || $search_layout == 'compactgridfull') : ?>
                    <?php $gridtype = 'compact'; include(rh_locate_template('inc/parts/compact_grid.php')); ?>    
                <?php elseif ($search_layout == 'mobilegrid' || $search_layout == 'mobilegridfull') : ?>
                    <?php $gridtype = 'mobile'; include(rh_locate_template('inc/parts/compact_grid.php')); ?>                                           
                <?php elseif ($search_layout == 'dealgrid' || $search_layout == 'dealgridfull') : ?>
                    <?php include(rh_locate_template('inc/parts/compact_grid.php')); ?>
             
                <?php else : ?>
                    <?php include(rh_locate_template('inc/parts/query_type1.php')); ?>  
                <?php endif ;?>                
            <?php endwhile; ?>
            <?php rehub_pagination(); ?>
            <?php else : ?>     
                <div class="no-results not-found">
                    <h5 class="font140"><?php esc_html_e('Sorry. No posts in this category yet', 'rehub-theme'); ?></h5> 
                </div>   
            <?php endif; ?> 
            </div>
            <div class="clearfix"></div>
        </div>  
        <!-- /Main Side -->
        <?php if ($search_layout == 'gridfull' || $search_layout == 'dealgridfull' || $search_layout == 'mobilegridfull' || $search_layout == 'compactgridfull' || $search_layout == 'columngridfull' || $search_layout == 'cardblogfull' ) : ?>
            <!-- Sidebar -->
        <?php else:?>
            <?php get_sidebar(); ?>
            <!-- /Sidebar --> 
        <?php endif ;?>
    </div>
</div>
<!-- /CONTENT -->     
<!-- FOOTER -->
<?php get_footer(); ?>