<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_shipping_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('shipping')){
            ?>
            <div class="nk-content marketking_shipping_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block">
                            <div class="card">
                                <div class="card-aside-wrap">
                                    <div class="card-inner card-inner-lg">
                                        <div class="nk-block-head nk-block-head-lg">
                                            <div class="nk-block-between">
                                                <div class="nk-block-head-content">
                                                    <h4 class="nk-block-title"><em class="icon ni ni-truck"></em>&nbsp;&nbsp;<?php esc_html_e('Shipping Settings','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start d-lg-none">
                                                    <a href="#" class="toggle btn btn-icon btn-trigger mt-n1" data-target="userAside"><em class="icon ni ni-menu-alt-r"></em></a>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        ?>
                                        

                                        <div class="marketking_shipping_help_alert"><div class="alert alert-light alert-icon"><em class="icon ni ni-help"></em><?php echo esc_html__( 'A shipping zone is a geographic region where a certain set of shipping methods are offered.', 'woocommerce' ) . ' ' . esc_html__( 'WooCommerce will match a customer to a single zone using their shipping address and present the shipping methods within that zone to them.', 'woocommerce' ); ?></div></div><br>
                                        <?php
                                        $data_store = WC_Data_Store::load( 'shipping-zone' );
                                        $raw_zones = $data_store->get_zones();
                                        foreach ( $raw_zones as $raw_zone ) {
                                          $zones[] = new WC_Shipping_Zone( $raw_zone );
                                        }

                                        if (!empty($zones)){
                                            ?>
                                            <table class="table">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col"><?php esc_html_e( 'Zone name', 'woocommerce' ); ?></th>
                                                        <th scope="col"><?php esc_html_e( 'Region(s)', 'woocommerce' ); ?></th>
                                                        <th scope="col"><?php esc_html_e( 'Shipping method(s)', 'woocommerce' ); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    

                                                    foreach ( $zones as $zone ) {
                                                       $zone_id = $zone->get_id();
                                                       $zone_name = $zone->get_zone_name();
                                                       $zone_order = $zone->get_zone_order();
                                                       $zone_locations = $zone->get_zone_locations();
                                                       $zone_formatted_location = $zone->get_formatted_location();
                                                       $zone_shipping_methods = $zone->get_shipping_methods();

                                                       // only display if the method marketking shipping exists
                                                       $marketking_method_exists = 'no';
                                                       foreach ($zone_shipping_methods as $method){
                                                         if ($method->id === 'marketking_shipping'){
                                                            $marketking_method_exists = 'yes';
                                                            break;
                                                         }
                                                       }
                                                       if ($marketking_method_exists === 'yes'){
                                                            ?>
                                                            <tr>
                                                                <th scope="row"><a href="<?php echo esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'shippingzone/'.esc_attr($zone->get_id()); ?>"><?php echo esc_html($zone_name);?></a></th>
                                                                <td><?php echo esc_html($zone_formatted_location);?></td>
                                                                <td><?php 

                                                                $zone_methods = '';
                                                                $vendor_shipping_methods = get_user_meta($user_id,'marketking_vendor_shipping_methods', true);
                                                                if (empty($vendor_shipping_methods)){
                                                                    $vendor_shipping_methods = array();
                                                                }
                                                                foreach ($vendor_shipping_methods as $index => $method){
                                                                    if (intval($method['zoneid']) === intval($zone_id)){
                                                                        // get method title
                                                                        $settings = get_option('woocommerce_'.$method['value'].'_'.$method['instanceid'].'_settings');
                                                                        if (!empty($settings)){
                                                                            $method['name'] = $settings['title'];
                                                                        }


                                                                        $zone_methods.=$method['name'].', ';
                                                                    }
                                                                }
                                                                $zone_methods=substr($zone_methods, 0, -2);
                                                                echo esc_html($zone_methods);

                                                                if (empty($zone_methods)){
                                                                    echo '-';
                                                                }
                                                                ?></td>
                                                            </tr>
                                                            <?php
                                                       }
                                                      
                                                    }
                                                    ?>                                                
                                                   
                                                </tbody>
                                            </table>  
                                            <?php
                                        } else {
                                            echo '<p>'.esc_html__('There are no shipping zones defined yet...','marketking').'</p>';
                                        }
                                        ?>
                                            
                                        <br><br>
                                        

                                    
                                    </div>
                                    <?php include(MARKETKINGCORE_DIR.'/public/dashboard/templates/profile-sidebar.php'); ?>
                                </div><!-- .card-inner -->
                            </div><!-- .card-aside-wrap -->
                        </div><!-- .nk-block -->
                    </div>
                </div>
            </div>
            </div>
            <?php
        }
    }
}