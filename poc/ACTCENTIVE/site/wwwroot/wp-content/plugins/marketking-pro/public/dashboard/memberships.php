<?php

/*

* @version 1.0.2

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
// get data
$current_id = $user_id = marketking()->get_data('user_id');

$vendor_group = get_user_meta($user_id, 'marketking_group', true);
$user = get_user_by('id', $user_id) -> user_login;

if (defined('MARKETKINGPRO_DIR')){
    if (intval(get_option('marketking_enable_memberships_setting', 1)) === 1){
        if(marketking()->vendor_has_panel('memberships')){
            ?>
            <div class="nk-content marketking_memberships_page">
                <div class="container wide-xl">
                    <div class="nk-content-inner">
                            <div class="nk-content-body">
                                <div class="nk-block nk-block-lg">

                                    <?php

                                    // Get all packages
                                    $packs = get_posts([
                                      'post_type' => 'marketking_mpack',
                                      'post_status' => 'publish',
                                      'numberposts' => -1,
                                      'meta_key' => 'marketking_pack_sort_order',
                                      'orderby' => 'meta_value_num',
                                      'order' => 'ASC',
                                    ]);


                                    // for each pack, check if it's visible to the current vendor group, otherwise, remove it
                                    foreach ($packs as $index => $pack){
                                        $visibility = get_post_meta($pack->ID,'marketking_group_visible_groups_settings', true);
                                        // if visibility is empty, then it's visible to all.
                                        if (empty($visibility)){
                                            continue;
                                        }

                                        $visible_groups = explode(',', $visibility);
                                        if (!in_array($vendor_group, $visible_groups)){
                                            // not visible, remove pack
                                            unset($packs[$index]);
                                        }

                                    }

                                    if (!empty($packs)){

                                        ?>
                                        <div class="nk-block-head">
                                            <div class="nk-block-between g-3">
                                                <div class="nk-block-head-content">
                                                    <h3 class="nk-block-title page-title"><?php echo get_option('marketking_memberships_page_title_setting', esc_html__('Available Options','marketking'));?></h3>
                                                    <div class="nk-block-des text-soft">
                                                        <p><?php echo get_option('marketking_memberships_page_description_setting', esc_html__('Choose your desired option and start enjoying our service.','marketking'));?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- .nk-block-head -->
                                        <?php

                                        $packs_new = array();
                                        foreach ($packs as $pack){
                                            array_push($packs_new, $pack);
                                        }
                                        $packs = $packs_new;
                                        

                                        // get number of rows
                                        $itemsperrow = apply_filters('marketking_membership_table_items_per_row', 4);
                                        $rows = ceil(count($packs)/$itemsperrow);

                                        for($i=1;$i<=$rows;$i++){
                                            ?>
                                            <div class="row g-gs">
                                                <?php
                                                foreach ($packs as $index=> $pack){
                                                    // index starts at 0 so we add 1
                                                    if (($index+1) <= ($i*$itemsperrow) && ($index+1) > (($i-1)*$itemsperrow)){
                                                        marketking_display_pack($pack);
                                                    }
                                                }

                                                ?>                                           
                                            </div>
                                            <?php
                                        }

                                    }

                                    // Get all packages but do not apply visibility this time.
                                    $packs = get_posts([
                                      'post_type' => 'marketking_mpack',
                                      'post_status' => 'publish',
                                      'numberposts' => -1,
                                      'meta_key' => 'marketking_pack_sort_order',
                                      'orderby' => 'meta_value_num',
                                      'order' => 'ASC',
                                    ]);

                                    // go through vendor's orders and see if there's anything in purchase history
                                    $customer_orders = get_posts(array(
                                        'numberposts' => -1,
                                        'meta_key' => '_customer_user',
                                        'orderby' => 'date',
                                        'order' => 'DESC',
                                        'meta_value' => $current_id,
                                        'post_type' => 'shop_order',
                                        'post_status' => 'any',
                                        'fields' => 'ids'
                                    ));

                                    $history = array();

                                    foreach ($customer_orders as $customer_order) {
                                        $order = wc_get_order($customer_order);
                                        
                                        foreach ($order->get_items() as $item_id => $item ) {

                                            // Get the WC_Order_Item_Product object properties in an array
                                            $item_data = $item->get_data();

                                            if ($item['quantity'] > 0) {
                                                // get the WC_Product object
                                                $product_id = $item['product_id'];

                                                // check if product ID is the product within any of the memberships
                                                foreach ($packs as $pack){
                                                    $pack_product_id = get_post_meta($pack->ID,'marketking_pack_product', true);
                                                    if (intval($pack_product_id) === intval($product_id) && intval($product_id) !== 0 && $product_id !== '' && $product_id !== null){

                                                        $history_item = array($order->get_id(), $product_id, $order->get_date_created(), $pack->post_title);

                                                        array_push($history, $history_item);                                                        
                                                    }
                                                }

                                            }
                                        }
                                    }

                                    if (!empty($history)){

                                        ?>
                                        <br><br><br>
                                        <div class="nk-block-head marketking_membership_purchase_history_container">
                                            <div class="nk-block-between g-3">
                                                <div class="nk-block-head-content">
                                                    <h5><?php echo esc_html__('Purchase History','marketking');?></h5><br>
                                                    <div class="nk-block-des text-soft">
                                                        <?php

                                                        foreach ($history as $history_item){
                                                            ?>
                                                            <p><?php echo esc_html__('Order #','marketking').$history_item[0].': '.esc_html__('You have purchased the following option: ','marketking').esc_html($history_item[3].' ('.ucfirst(strftime("%B %e, %G", strtotime(explode('T',$history_item[2])[0])))).')';?> </p>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- .nk-block-head -->
                                        <?php
                                    }
                                    ?>
                                    
                                </div><!-- .nk-block -->
                            </div>
                           
                    </div>
                </div>
            </div>
            <?php
        }
    }
}

function marketking_display_pack($pack){
    $title = $pack->post_title;
    $most_popular = get_post_meta($pack->ID,'marketking_mpack_featured_pack_setting', true);
    $price_text = get_post_meta($pack->ID,'marketking_pack_price', true);
    $price_description = get_post_meta($pack->ID,'marketking_pack_price_description', true);
    $description = get_post_meta($pack->ID,'marketking_pack_description', true);
    $image = get_post_meta($pack->ID,'marketking_pack_image', true);
    $product = get_post_meta($pack->ID,'marketking_pack_product', true);

    ?>
    <div class="col-md-6 col-xxl-3">
        <div class="card card-bordered pricing recommend text-center">
            <?php
                if (intval($most_popular) === 1){
                ?>
                <span class="pricing-badge badge badge-primary"><?php echo esc_html(apply_filters('marketking_membership_most_popular_text',esc_html__('Most Popular','marketking')));?></span>
                <?php
            }
            ?>
            <div class="pricing-body">
                <div class="pricing-media">
                    <img src="<?php echo esc_attr($image);?>">

                </div>
                <div class="pricing-title w-220px mx-auto">
                    <h5 class="title"><?php echo esc_html($title);?></h5>
                    <span class="sub-text"><?php echo nl2br(esc_html($description));?></span>
                </div>
                <div class="pricing-amount">
                    <div class="amount"><?php echo esc_html($price_text);?></div>
                    <span class="bill"><?php echo esc_html($price_description);?></span>
                </div>
                <div class="pricing-action">
                    <button type="button" class="btn btn-primary marketking_membership_select_plan_button" value="<?php echo esc_attr($product);?>"><?php echo apply_filters('marketking_select_plan_text',esc_html__('Select Option', 'marketking'));?></button>
                </div>
            </div>
        </div><!-- .pricing -->
    </div><!-- .col -->
    <?php
}