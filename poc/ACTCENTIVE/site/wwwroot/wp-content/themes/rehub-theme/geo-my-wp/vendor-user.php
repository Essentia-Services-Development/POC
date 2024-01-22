<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php $vendor_id = $author_ID = bp_get_member_user_id();?>
<?php 
if( class_exists( 'WeDevs_Dokan' ) && dokan_is_user_seller( $vendor_id ) ){
    $store_info = dokan_get_store_info( $vendor_id );
    $shop_link = dokan_get_store_url( $vendor_id );
    $shop_name = esc_html( $store_info['store_name'] );
}
elseif( defined('WCFMmp_TOKEN') && wcfm_is_vendor( $vendor_id ) ) {
    $shop_link = wcfmmp_get_store_url( $vendor_id );
    $shop_name = get_user_meta( $vendor_id, 'store_name', true );
}
elseif( defined('wcv_plugin_dir') && WCV_Vendors::is_vendor( $vendor_id ) ) {
    $shop_link = WCV_Vendors::get_vendor_shop_page( $vendor_id );
    $shop_name = WCV_Vendors::get_vendor_sold_by( $vendor_id );
}
else{
    $shop_link = bp_get_member_permalink();
    $shop_name = $member->display_name;                 
}
?>     
<li <?php bp_member_class( array('col_item') ); ?>>
    <?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>        
    <div class="member-inner-list">
        <div class="vendor-list-like act-rehub-login-popup"><?php echo getShopLikeButton($vendor_id);?></div>
        <a href="<?php echo esc_url($shop_link); ?>">
            <span class="cover_logo" style="<?php echo rh_show_vendor_bg($vendor_id); ?>"></span>
        </a>   
        <div class="member-details">                    
            <div class="item-avatar">
                <a href="<?php echo esc_url($shop_link); ?>">
                    <img src="<?php echo rh_show_vendor_avatar($vendor_id, 80, 80);?>" class="vendor_store_image_single" width=80 height=80 />
                </a>
            </div>  
            <a href="<?php echo esc_url($shop_link); ?>" class="wcv-grid-shop-name"><?php echo esc_attr($shop_name); ?></a>
            <?php if ( class_exists( 'WCVendors_Pro' ) ) {
                if ( ! WCVendors_Pro::get_option( 'ratings_management_cap' ) ) {
                    echo '<div class="wcv_grid_rating">';
                    echo WCVendors_Pro_Ratings_Controller::ratings_link( $vendor_id, true );
                    echo '</div>';
                }
            }?>
            <div class="font70 greycolor"><?php bp_member_last_active(); ?></div>
            <div class="adress-vendor-gmw-list">
                <?php do_action( 'gmw_search_results_before_distance', $gmw, $member); ?>                        
                <!-- distance -->
                <div class="distance-to-user-geo"><?php gmw_distance_to_location( $member, $gmw ); ?></div>
                <?php do_action( 'gmw_fl_search_results_member_items', $gmw, $member ); ?> 
                <div class="adress-user-geo">
                    <?php do_action( 'gmw_search_results_before_address', $gmw, $member ); ?>         
                    <!-- address -->
                    <?php gmw_search_results_address( $member, $gmw ); ?>
                    <?php gmw_search_results_directions_link( $member, $gmw ); ?>              
                </div>                        
            </div> 
        </div>                              

        <?php do_action( 'gmw_search_results_loop_item_end', $gmw, $member ); ?>                
    </div>
</li>