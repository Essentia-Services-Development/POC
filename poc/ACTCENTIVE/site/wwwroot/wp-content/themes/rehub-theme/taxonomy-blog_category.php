<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
<!-- CONTENT -->
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side clearfix<?php if (rehub_option('blog_archive_layout') == 'gridfull_blog' || rehub_option('blog_archive_layout') == 'cardblogfull') : ?> full_width<?php endif ;?>">
            <?php
                if(isset($_GET['author_name'])) :
                $curauth = get_userdatabylogin($author_name);
            else :
                $curauth = get_userdata(intval($author));
            endif;?>

            <?php /* If this is a category archive */ if (is_tax('blog_category')) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><h5 class="font140"><?php single_cat_title(); ?></h5></div>
            <?php if( !is_paged()) : ?><article class='top_rating_text post mb15'><?php echo category_description(); ?></article><?php endif ;?>             
            <?php /* If this is a tag archive */ } elseif( is_tax('blog_tag') ) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><h5 class="font140"><?php single_tag_title(); ?></h5></div>
            <article class='top_rating_text mb15'><?php echo tag_description(); ?></article>				
            <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><h5 class="font140"><span><?php esc_html_e('Archive:', 'rehub-theme'); ?></span> <?php the_time('F jS, Y'); ?></h5></div>
            <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><h5 class="font140"><span><?php esc_html_e('Browsing Archive', 'rehub-theme'); ?></span> <?php the_time('F, Y'); ?></h5></div>
            <?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
            <div class="wpsm-title position-relative flowhidden mb25 middle-size-title wpsm-cat-title"><h5 class="font140"><span><?php esc_html_e('Browsing Archive', 'rehub-theme'); ?></span> <?php the_time('Y'); ?></h5></div>			
            <?php } ?>             
            <?php if (rehub_option('blog_archive_layout') == 'grid_blog') : ?>
                <?php echo rh_generate_incss('masonry');?>
                <div class="masonry_grid_fullwidth col_wrap_two">
            <?php elseif (rehub_option('blog_archive_layout') == 'gridfull_blog') : ?> 
                <?php echo rh_generate_incss('masonry');?>  
                <div class="masonry_grid_fullwidth col_wrap_three">
            <?php elseif (rehub_option('blog_archive_layout') == 'cardblog') : ?>
                <div class="coloredgrid rh-flex-eq-height col_wrap_three">
            <?php elseif (rehub_option('blog_archive_layout') == 'cardblogfull') : ?>   
                <div class="coloredgrid rh-flex-eq-height col_wrap_fourth">                                                     
            <?php endif ;?>                        
            <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php if (rehub_option('blog_archive_layout') == 'big_blog') : ?>
                    <?php include(rh_locate_template('inc/parts/query_type2.php')); ?>
                <?php elseif (rehub_option('blog_archive_layout') == 'list_blog') : ?>
                    <?php include(rh_locate_template('inc/parts/query_type1.php')); ?>
                <?php elseif (rehub_option('blog_archive_layout') == 'grid_blog' || rehub_option('blog_archive_layout') == 'gridfull_blog') : ?>
                    <?php include(rh_locate_template('inc/parts/query_type3.php')); ?> 
                <?php elseif (rehub_option('blog_archive_layout') == 'cardblog' || rehub_option('blog_archive_layout') == 'cardblogfull') : ?>
                    <?php include(rh_locate_template('inc/parts/color_grid.php')); ?>
                <?php else : ?>
                    <?php include(rh_locate_template('inc/parts/query_type1.php')); ?>	
                <?php endif ;?>
            <?php endwhile; ?>
            <?php else : ?>		
            <h5 class="font140"><?php esc_html_e('Sorry. No posts in this category yet', 'rehub-theme'); ?></h5>	
            <?php endif; ?>	
            <?php if (rehub_option('blog_archive_layout') == 'grid_blog' || rehub_option('blog_archive_layout') == 'gridfull_blog' || rehub_option('blog_archive_layout') == 'cardblog' || rehub_option('blog_archive_layout') == 'cardblogfull') : ?></div><?php endif ;?>
            <div class="clearfix"></div>
            <?php rehub_pagination(); ?>

            <?php $catID = get_queried_object()->term_id; ?>
            <?php $cat_seo_description = get_term_meta( $catID, 'brand_second_description', true );?>
            <?php if($cat_seo_description):?>
                <div class="mt30"></div>
                <article class="cat_seo_description mt30 pt30 post"><?php echo wpautop( wptexturize(do_shortcode($cat_seo_description)));?></article>
            <?php endif;?>


        </div>	
        <!-- /Main Side -->
        <?php if (rehub_option('blog_archive_layout') != 'gridfull_blog' && rehub_option('blog_archive_layout') != 'cardblogfull') : ?>
            <!-- Sidebar -->
            <?php get_sidebar(); ?>
            <!-- /Sidebar --> 
        <?php endif ;?>
    </div>
</div>
<!-- /CONTENT -->     
<!-- FOOTER -->
<?php get_footer(); ?>