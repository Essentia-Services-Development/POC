<?php

/*

* @version 1.0.1

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_shipping_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('shipping')){
            ?>
            <div class="nk-content marketking_shipping_zone_page">
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
                                                    <h4 class="nk-block-title"><em class="icon ni ni-truck"></em>&nbsp;&nbsp;<?php esc_html_e('Shipping Zone','marketking');?></h4>
                                                </div>
                                                <div class="nk-block-head-content align-self-start">
                                                    <a href="<?php echo esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'shipping'; ?>"><button class="btn btn-gray" type="button"><?php esc_html_e('Go to Zones List','marketking');?></button></a>
                                                </div>
                                               
                                            </div>
                                        </div>

                                        <?php
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        

                                        $zoneid = intval(marketking()->get_pagenr_query_var());
                                        $zone = new WC_Shipping_Zone( $zoneid );

                                        // check that zone indeed has vendor shipping inside it

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
                                            <input type="hidden" id="marketking_current_zone_id" value="<?php echo esc_attr($zoneid);?>">
                                            <?php

                                            $zone_id = $zone->get_id();
                                            $zone_name = $zone->get_zone_name();
                                            $zone_order = $zone->get_zone_order();
                                            $zone_locations = $zone->get_zone_locations();
                                            $zone_formatted_location = $zone->get_formatted_location();
                                            $zone_shipping_methods = $zone->get_shipping_methods();


                                            ?>
                                           <h5 class="card-title"><?php echo esc_html__( 'Zone name', 'marketking' ).': '.$zone_name; ?></h5>
                                           <h6 class="card-subtitle mb-2 ff-base"><?php echo esc_html__( 'Zone location', 'marketking' ).': '.$zone_formatted_location; ?></h6>
                                         
                                            <br>
                                            <table class="table">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col"><?php esc_html_e( 'Shipping Method Title', 'marketking' ); ?></th>
                                                        <th scope="col"><?php esc_html_e( 'Enabled', 'marketking' ); ?></th>
                                                        <th scope="col"><?php esc_html_e( 'Description', 'marketking' ); ?></th>
                                                        <th scope="col"><?php esc_html_e( 'Actions', 'marketking' ); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    // get vendor methods if any
                                                    // array of methods
                                                    $vendor_shipping_methods = get_user_meta($user_id,'marketking_vendor_shipping_methods', true);
                                                    if (!empty($vendor_shipping_methods)){

                                                        $shipping_class_names = WC()->shipping->get_shipping_method_class_names();

                                                        // methods array
                                                        foreach ($vendor_shipping_methods as $method){

                                                            // remove if not for this zone
                                                            if (intval($method['zoneid']) !== intval($zone_id)){
                                                                continue;
                                                            }

                                                            if (!isset($shipping_class_names[$method['value']])){
                                                                continue;
                                                            }

                                                            // overwrite defaults with settings if they exist
                                                            $settings = get_option('woocommerce_'.$method['value'].'_'.$method['instanceid'].'_settings');
                                                            if (!empty($settings)){
                                                                $method['name'] = $settings['title'];
                                                            }

                                                            ?>
                                                            <tr>
                                                                <th scope="row"><?php echo esc_html($method['name']);?></th>
                                                                <td>
                                                                    <div class="nk-block-content">
                                                                        <div class="gy-3">
                                                                            <div class="g-item">
                                                                                <div class="custom-control custom-switch">
                                                                                    <input id="enable_<?php echo esc_attr($method['instanceid']);?>" type="checkbox" class="custom-control-input marketking_method_enabled" <?php checked(1,intval($method['enabled']), true); ?> value="<?php echo esc_attr($method['instanceid']);?>">
                                                                                    <label class="custom-control-label" for="enable_<?php echo esc_attr($method['instanceid']);?>"></label>
                                                                               
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div><!-- .nk-block-content -->
                                                                </td>
                                                                <td><?php 

                                                                $method_instance = new $shipping_class_names[$method['value']](intval($method['instanceid']));
                                                                echo esc_html($method_instance->method_description);
                                                                
                                                                ?></td>
                                                                <td>
                                                                    <?php

                                                                    if (apply_filters('marketking_allow_configure_shipping_method', true, $method['value'])){
                                                                        ?>
                                                                        <a href="#" class="link link-primary" data-toggle="modal" data-target="#configure-shipping-method"><button type="button" class="btn btn-secondary btn-sm marketking_configure_shipping_button" value="<?php echo esc_attr($method['instanceid']); ?>"><?php esc_html_e('Configure','marketking');?></button></a>

                                                                        <?php
                                                                    }
                                                                    ?>
                                                                    <button type="button" class="btn btn-gray btn-sm marketking_delete_shipping_button" value="<?php echo esc_attr($method['instanceid']); ?>"><?php esc_html_e('Delete','marketking');?>
                                                                </td>
                                                            </tr>   
                                                            <?php
                                                        }
                                                    } else {
                                                        echo '<tr><th><p>'.esc_html__('There are no methods yet...','marketking').'</p></th></tr>';
                                                    }
                                                    ?>                                                                                            
                                                   
                                                </tbody>
                                            </table>  
                                            <br>

                                            <a href="#" class="link link-primary" data-toggle="modal" data-target="#add-shipping-method"><button class="btn btn-secondary" type="button" id="marketking_add_shipping_method" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('+ Add Shipping Method','marketking');?></button></a>

                                                
                                            <?php
                                        }
                                        ?>
                                    
                                    </div>
                                    <?php include(MARKETKINGCORE_DIR.'/public/dashboard/templates/profile-sidebar.php'); ?>
                                </div><!-- .card-inner -->
                            </div><!-- .card-aside-wrap -->
                        </div><!-- .nk-block -->
                    </div>
                </div>
            </div>
            </div>

            <div class="modal fade" tabindex="-1" role="dialog" id="add-shipping-method">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title"><?php esc_html_e('Add Shipping Method','marketking');?></h6>
                            <a href="#" class="close" data-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                        </div>
                        <div class="modal-body p-0">
                            <div class="nk-reply-form-header">
                                <div class="nk-reply-form-group">
                                    <div class="nk-reply-form-input-group">
                                        <div class="wc-shipping-zone-method-selector">
                                            <p><?php esc_html_e( 'Choose the shipping method you wish to add. Only shipping methods which support zones are listed.', 'woocommerce' ); ?></p>


                                            <select name="add_method_id" id="marketking_add_shipping_method_select" >
                                                <?php
                                                foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
                                                    if ( ! $method->supports( 'shipping-zones' ) ) {
                                                        continue;
                                                    }
                                                    if ($method->id === 'marketking_shipping'){
                                                        continue;
                                                    }

                                                    // if a method is admin only, not just do not show it, but also do not allow vendors to add it
                                                    $admin_only = get_option('marketking_admin_only_shipping_methods_setting',array());
                                                    if (!is_array($admin_only)){
                                                        $admin_only = array();
                                                    }

                                                    $admin_only = apply_filters('marketking_admin_only_methods_vendor_dashboard_add', $admin_only);

                                                    if (in_array($method->id, $admin_only)){
                                                        // remove method
                                                        continue;
                                                    }


                                                    echo '<option data-description="' . esc_attr( wp_kses_post( wpautop( $method->get_method_description() ) ) ) . '" value="' . esc_attr( $method->id ) . '">' . esc_html( $method->get_method_title() ) . '</li>';
                                                }
                                                ?>
                                            </select>
                                            <br><br>
                                           <button class="btn btn-primary" type="button" id="marketking_add_shipping_method_insert" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Add Shipping Method','marketking');?></button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                          
                            <div class="nk-reply-form-tools">
                               
                               
                            </div><!-- .nk-reply-form-tools -->
                        </div><!-- .modal-body -->
                    </div><!-- .modal-content -->
                </div><!-- .modla-dialog -->
            </div><!-- .modal -->

            <div class="modal fade" tabindex="-1" role="dialog" id="configure-shipping-method">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title"><?php esc_html_e('Configure Shipping Method','marketking');?></h6>
                            <a href="#" class="close" data-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                        </div>
                        <div class="modal-body p-0">
                            <div class="nk-reply-form-header">
                                <div class="nk-reply-form-group">
                                    <div class="nk-reply-form-input-group">
                                        <div class="wc-shipping-zone-method-selector">
                                            <form id="marketking_configure_method_form">

                                                <div id="marketking_configure_method_details_content">
                                                    

                                                </div>
                                            </form>

                                            <br><br>
                                           <button class="btn btn-primary" type="button" id="marketking_save_shipping_method_insert" value="<?php echo esc_attr($user_id);?>"><?php esc_html_e('Save Shipping Method','marketking');?></button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                          
                            <div class="nk-reply-form-tools">
                               
                               
                            </div><!-- .nk-reply-form-tools -->
                        </div><!-- .modal-body -->
                    </div><!-- .modal-content -->
                </div><!-- .modla-dialog -->
            </div><!-- .modal -->
            <?php
        }
    }
}