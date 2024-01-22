<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if(function_exists('rehub_social_share')):?>
	<div class="post_share">
	    <?php echo rehub_social_share('row', 1);?>
	</div>
<?php endif;?>