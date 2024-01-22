<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php wc_print_notices(); ?>
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
    <a href="<?php echo ''.$shop_page; ?>"><?php echo esc_html_e( 'View Your Store', 'rehub-theme' ); ?></a>
    <a href="<?php echo ''.$settings_page; ?>"><?php esc_html_e('Store Settings', 'rehub-theme') ;?></a>

<?php if ( $can_submit ) { ?>
	<?php if (rehub_option('url_for_add_product') !=''):?>
		<?php $submit_link = esc_url(rehub_option('url_for_add_product')); ?>
        <a target="_TOP" href="<?php echo ''.$submit_link; ?>"><?php esc_html_e('Add New Product', 'rehub-theme') ;?></a>
	<?php endif;?>
	<?php if (rehub_option('url_for_edit_product') !=''):?>
		<?php $edit_link = esc_url(rehub_option('url_for_edit_product')); ?>
        <a target="_TOP" href="<?php echo ''.$edit_link; ?>"><?php esc_html_e('Edit Products', 'rehub-theme') ;?></a>
	<?php endif;?>	
<?php } ?>