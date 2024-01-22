<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php get_header(); ?>
<!-- CONTENT -->
<div class="rh-container full_post_area"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side single full_width clearfix">                  
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>  
            <h1 class="text-center mt30"><?php the_title_attribute(); ?></h1>
            <?php the_content(''); ?>                        
        <?php endwhile; endif; ?>
        </div>  
        <!-- /Main Side -->  
    </div>
</div>
<!-- /CONTENT -->     
<!-- FOOTER -->
<?php get_footer(); ?>