<?php
/**
 * Members locator "vendors" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * $member - each member in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "vendors" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/friends/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: vendors".
 */
?>	
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-fl-vendors-results-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">
    <?php if ( $gmw_form->has_locations() ) : ?>
        <div id="buddypress">
    	<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
    	
        <div id="pag-top" class="geo-pagination">

            <!-- results message -->
            <div class="pag-count floatleft tabletblockdisplay" id="member-dir-count-top">
                <?php gmw_results_message( $gmw, false ); ?>
            </div>

            <!-- per page -->
            <?php gmw_per_page( $gmw ); ?>
            <!-- pagination -->
            <div class="pagination-links tabletblockdisplay" id="member-dir-pag-top">
                <?php gmw_pagination( $gmw ); ?>
            </div>
        </div>
        <div class="clear"></div>

        <!-- GEO my WP Map -->
        <?php gmw_results_map( $gmw ); ?>
        
        <?php do_action( 'bp_before_directory_members_list' ); ?>
        <div class="mt20"></div>
        <ul class="wcv_vendorslist geowp-item-wc-list col_wrap_three rh-flex-eq-height">
        <?php while ( bp_members() ) : bp_the_member(); ?>
            
            <!-- do not remove this line -->
            <?php $member = $members_template->member; ?>
            <?php $vendor_id = bp_get_member_user_id();?>
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
                <!-- do not remove this line -->
                <?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>        
                <?php 
                    $author_ID = bp_get_member_user_id();
                ?>
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
        <?php endwhile; ?>
        </ul>

        <?php do_action( 'bp_after_directory_members_list' ); ?>

        <?php bp_member_hidden_fields(); ?>
    	
    	<?php do_action( 'gmw_search_results_end', $gmw ); ?>	
        </div>
    <?php else : ?>

        <div class="gmw-no-results">
            
            <?php do_action( 'gmw_no_results_start', $gmw ); ?>

            <?php gmw_no_results_message( $gmw ); ?>
            
            <?php do_action( 'gmw_no_results_end', $gmw ); ?> 

        </div>

    <?php endif; ?>         
</div>