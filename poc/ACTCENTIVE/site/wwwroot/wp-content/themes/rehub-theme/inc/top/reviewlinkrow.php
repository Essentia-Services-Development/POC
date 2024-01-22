<?php if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}?>
<div><a href="<?php the_permalink();?>" class="read_full" target="_blank"><?php if(rehub_option('rehub_review_text') !='') :?><?php echo rehub_option('rehub_review_text') ; ?><?php else :?><?php esc_html_e('Read review', 'rehub-theme'); ?><?php endif ;?></a></div>