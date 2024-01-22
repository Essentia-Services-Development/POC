<?php
/**
 * Navigation Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/navigation.php
 *
 * @author        WC Vendors
 * @package       WCVendors/Templates/dashboard
 * @version       2.1.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
if (class_exists('WCVendors_Pro')) {
    $dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id' );
    if(!empty($dashboard_page_ids)){
        $dashboard_page_id  = reset( $dashboard_page_ids );
        $vendor_dasboard = get_permalink($dashboard_page_id);
    }
}
else {
    $vendor_dasboard = get_permalink(get_option('wcvendors_vendor_dashboard_page_id'));
}?>
<div class="wcv-dashboard-navigation">
	<p class="wc_vendors_dash_links rh_tab_links">
		<?php echo rh_generate_incss('rhtablinks');?>
		<script>
			jQuery(document).ready(function($) {
			   'use strict';
			    $('.rh_tab_links').on('click', 'a.active', function(e) {
			        e.preventDefault();
			        $(this).closest('.rh_tab_links').find('a').toggleClass('showtabmobile');
			    }); 
			});
		</script>
		<a href="<?php echo ''.$vendor_dasboard;?>" class="active"><?php echo esc_html_e( 'Dashboard', 'rehub-theme' ); ?></a>
		<?php foreach ( $items as $item_id => $item ) : ?>
			<?php if ( ! isset( $item['url'] ) || ! isset( $item['label'] ) ) {
				continue;
			} ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
				   target="<?php echo isset( $item['target'] ) ? esc_attr( $item['target'] ) : '_self'; ?>"
				   class="<?php echo esc_attr( wcv_get_dashboard_nav_item_classes( $item_id ) ); ?>"
				>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
		<?php endforeach; ?>
	</p>
</div>