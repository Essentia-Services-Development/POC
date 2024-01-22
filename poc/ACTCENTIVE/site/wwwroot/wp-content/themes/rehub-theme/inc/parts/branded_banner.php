<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if (rehub_option('rehub_branded_banner_image')) :?>
    <?php $branded_banner_image = rehub_option('rehub_branded_banner_image');?>
    <div class="text-center">
        <div id="branded_img position-relative flowhidden text-center">
            <?php if (stripos($branded_banner_image, 'http') === 0) : ?>
        	   <img alt="image" src="<?php echo esc_url($branded_banner_image); ?>">
            <?php else :?>
        	    <?php echo do_shortcode ($branded_banner_image);?>
            <?php endif;?>
        </div>  
    </div>
<?php endif; ?>