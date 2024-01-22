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
        <div class="main-side clearfix<?php if ($archive_layout == 'gridfull' || $archive_layout == 'mobilegridfull' || $archive_layout == 'dealgridfull' || $archive_layout == 'compactgridfull' || $archive_layout == 'columngridfull' || $archive_layout == 'cardblogfull') : ?> full_width<?php endif ;?>">
            <?php /* If this is a category archive */ if (is_category()) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><div class="font140 fontbold rehub-main-font"><?php single_cat_title(); ?></div></div>
            <?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><div class="font140 fontbold rehub-main-font"><?php single_tag_title(); ?></div></div>
            <article class='top_rating_text mb15'><?php echo tag_description(); ?></article>
            <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><div class="font140 fontbold rehub-main-font"><span><?php esc_html_e('Archive:', 'rehub-theme'); ?></span> <?php the_time('F jS, Y'); ?></div></div>
            <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><div class="font140 fontbold rehub-main-font"><span><?php esc_html_e('Browsing Archive', 'rehub-theme'); ?></span> <?php the_time('F, Y'); ?></div></div>
            <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><div class="font140 fontbold rehub-main-font"><span><?php esc_html_e('Browsing Archive', 'rehub-theme'); ?></span> <?php the_time('Y'); ?></div></div>
            <?php } ?>
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

                <?php elseif ($archive_layout == 'dealgrid' || $archive_layout == 'mobilegrid') : ?>
                    <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_fourth' : 'col_wrap_three';?>">
                        <?php echo rh_generate_incss('offergrid');?>

                <?php elseif ($archive_layout == 'dealgridfull' || $archive_layout == 'mobilegridfull') : ?>
                    <div class="eq_grid pt5 rh-flex-eq-height <?php echo (rehub_option('width_layout') =='extended') ? 'col_wrap_six' : 'col_wrap_fifth';?>">
                    <?php echo rh_generate_incss('offergrid');?>
                <?php else : ?>
                    <div class="" data-template="query_type1">
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
                        <?php elseif ($archive_layout == 'mobilegrid' || $archive_layout == 'mobilegridfull') : ?>
                            <?php $gridtype = 'mobile'; include(rh_locate_template('inc/parts/compact_grid.php')); ?>
                        <?php elseif ($archive_layout == 'dealgrid' || $archive_layout == 'dealgridfull') : ?>
                            <?php include(rh_locate_template('inc/parts/compact_grid.php')); ?>

                        <?php else : ?>
                            <?php include(rh_locate_template('inc/parts/query_type1.php')); ?>
                        <?php endif ;?>
                    <?php endwhile; ?>
                </div>
                <div class="pagination"><?php rehub_pagination();?></div>
            <?php else : ?>
                <div class="font140 fontbold rehub-main-font"><?php esc_html_e('Sorry. No posts in this category yet', 'rehub-theme'); ?></div>
            <?php endif; ?>
            <div class="clearfix"></div>
        </div>
        <!-- /Main Side -->
        <?php if ($archive_layout == 'gridfull' || $archive_layout == 'mobilegridfull' || $archive_layout == 'dealgridfull' || $archive_layout == 'compactgridfull' || $archive_layout == 'columngridfull' || $archive_layout == 'cardblogfull') : ?>
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