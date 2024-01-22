<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
// get data
$current_id = $user_id = marketking()->get_data('user_id');

$vendor_group = get_user_meta($user_id, 'marketking_group', true);
$user = get_user_by('id', $user_id) -> user_login;

if (defined('MARKETKINGPRO_DIR')){
    ?>
    <div class="nk-content marketking_rma_page">
        <div class="container wide-xl">
            <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block nk-block-lg">

                            <?php
                            require_once (ABSPATH . '/wp-admin/includes/class-wp-screen.php');
                            require_once (ABSPATH . '/wp-admin/includes/template.php');
                            require_once (ABSPATH . '/wp-admin/includes/screen.php');

                            /*class WC_Product_Vendors_Utils{
                                public static function can_vendor_access_order($order_id, $vendor){
                                    return true;
                                }
                            }*/

                            include WooCommerce_Warranty::$base_path . 'templates/list.php';
                            ?>
                            
                        </div><!-- .nk-block -->
                    </div>
                   
            </div>
        </div>
    </div>
    <?php
}

