<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<aside class="sidebar">            
    <!-- SIDEBAR WIDGET AREA -->
 	<?php if ( (is_singular('blog') || is_tax('blog_category') || is_tax('blog_tag') || is_post_type_archive('blog') ) && is_active_sidebar( 'blog-sidebar' ) ) : ?>
		<?php dynamic_sidebar( 'blog-sidebar' ); ?>   
	<?php elseif ( is_active_sidebar( 'rhsidebar' ) ) : ?>
		<?php dynamic_sidebar( 'rhsidebar' ); ?>
	<?php else : ?>
		<p><?php esc_html_e('No widgets added', 'rehub-theme'); ?></p>
	<?php endif; ?>        
</aside>