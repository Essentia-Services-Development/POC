<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<aside class="sidebar">            
    <!-- SIDEBAR WIDGET AREA -->
	<?php if ( is_active_sidebar( 'wooshopsidebar' ) ) : ?>
		<?php dynamic_sidebar( 'wooshopsidebar' ); ?>
	<?php else : ?>
		<p><?php esc_html_e('No woocommerce widgets added', 'rehub-theme'); ?></p>
	<?php endif; ?>        
</aside>