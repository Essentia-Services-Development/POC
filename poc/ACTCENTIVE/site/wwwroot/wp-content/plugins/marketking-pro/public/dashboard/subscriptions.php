<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (class_exists('WC_Subscriptions')){
    if (intval(get_option( 'marketking_enable_subscriptions_setting', 0 )) === 1){
        if(marketking()->vendor_has_panel('subscriptions')){
            $user_id = marketking()->get_data('user_id');
            $currentuser = new WP_User($user_id);
            
            ?>
            <div class="nk-content marketking_subscriptions_page">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title"><?php esc_html_e('Subscriptions','marketking'); ?></h3>
                                    </div><!-- .nk-block-head-content -->
                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle">
                                            <div>
                                                <ul class="nk-block-tools g-3">
                                                    <li>
                                                        <div class="form-control-wrap">
                                                            <div class="form-icon form-icon-right">
                                                                <em class="icon ni ni-search"></em>
                                                            </div>
                                                            <input type="text" class="form-control" id="marketking_subscriptions_search" placeholder="<?php esc_html_e('Search subscriptions...','marketking');?>">
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div><!-- .nk-block-head-content -->
                                </div><!-- .nk-block-between -->
                            </div><!-- .nk-block-head -->

                            <table id="marketking_dashboard_subscriptions_table" class="nk-tb-list is-separate mb-3">
                                <thead>
                                    <tr class="nk-tb-item nk-tb-head">
                                        <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Subscription','marketking'); ?></span></th>
                                        <th class="nk-tb-col tb-col-md"><span class="sub-text"><?php esc_html_e('Start Date','marketking'); ?></span></th>
                                        <th class="nk-tb-col tb-col-md"><span class="sub-text d-none d-mb-block"><?php esc_html_e('Status','marketking'); ?></span></th>
                                        <th class="nk-tb-col tb-col-sm"><span class="sub-text"><?php esc_html_e('Customer','marketking'); ?></span></th>
                                        <th class="nk-tb-col tb-col-md"><span class="sub-text"><?php esc_html_e('Purchased','marketking'); ?></span></th>
                                        <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Total','marketking'); ?></span></th>
                                        <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Orders','marketking'); ?></span></th>
                                        <th class="nk-tb-col tb-col-md marketking-column-small"><span class="sub-text"><?php esc_html_e('Actions','marketking'); ?></span></th>                           

                                    </tr>
                                </thead>
                                <?php
                                if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                    ?>
                                    <tfoot>
                                        <tr class="nk-tb-item nk-tb-head">
                                            <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('subscription','marketking'); ?></th>
                                            <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('date','marketking'); ?></th>
                                            <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('status','marketking'); ?></th>
                                            <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('customer','marketking'); ?></th>
                                            <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('purchased','marketking'); ?></th>
                                            <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('total','marketking'); ?></th>
                                            <th class="nk-tb-col tb-col-md tb-non-tools"><?php esc_html_e('orders','marketking'); ?></th>
                                            <th class="nk-tb-col tb-col-md"></th>
                                        </tr>
                                    </tfoot>
                                    <?php
                                }
                                ?>
                                <tbody>
                                    <?php

                                    if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                        $vendor_subscriptions = get_posts( array( 'post_type' => 'shop_subscription','post_status'=>'any','numberposts' => -1, 'author'   => $user_id, 'fields' =>'ids') );


                                        foreach ($vendor_subscriptions as $subscriptionid){
                                            $subscription = new WC_Subscription($subscriptionid);
                                            if ($subscription !== false){
                                            ?>
                                            <tr class="nk-tb-item">
                                                <td class="nk-tb-col" data-order="<?php
                                                    echo esc_attr($subscriptionid);
                                                ?>">

                                                    <div>
                                                        <span class="tb-lead">#<?php 

                                                        // sequential
                                                        $order_nr_sequential = get_post_meta($subscriptionid,'_order_number', true);
                                                        if (!empty($order_nr_sequential)){
                                                            echo $order_nr_sequential;
                                                        } else {
                                                            echo esc_html($subscriptionid);
                                                        }
                                                        echo ' ';

                                                        $name = $subscription->get_formatted_billing_full_name();

                                                        $name = apply_filters('marketking_customers_page_name_display', $name, $subscription);
                                                        
                                                        echo esc_html($name);


                                                    ?></span>
                                                    </div>
                                                </td>
                                                <td class="nk-tb-col tb-col-md" data-order="<?php
                                                    $date = $subscription->get_date_created();
                                                    echo $date->getTimestamp();
                                                ?>">
                                                    <div>
                                                        <span class="tb-sub"><?php 
                                                        
                                                        echo $date->date_i18n( get_option('date_format'), $date->getTimestamp()+(get_option('gmt_offset')*3600) );

                                                        
                                                        ?></span>
                                                    </div>
                                                </td>
                                                <td class="nk-tb-col tb-col-md"> 
                                                    <div >
                                                        <span class="dot bg-warning d-mb-none"></span>
                                                        <?php
                                                        $status = $subscription->get_status();
                                                        $badge = '';
                                                        if ($status === 'active'){
                                                            $badge = 'badge-success';
                                                        } else if ($status === 'on-hold'){
                                                            $badge = 'badge-warning';
                                                        } else if (in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                                                            $badge = 'badge-info';
                                                        } else if ($status === 'refunded'){
                                                            $badge = 'badge-gray';
                                                        } else if ($status === 'cancelled' or $status === 'pending-cancel' or $status === 'suspended' or $status === 'expired'){
                                                            $badge = 'badge-gray';
                                                        } else if ($status === 'pending'){
                                                            $badge = 'badge-dark';
                                                        } else if ($status === 'failed'){
                                                            $badge = 'badge-danger';
                                                        } else {
                                                            $badge = 'badge-gray';
                                                        }

                                                        ?>
                                                        <span class="badge badge-sm badge-dot has-bg <?php echo esc_attr($badge);?> d-none d-mb-inline-flex"><?php
                                                        echo wcs_get_subscription_status_name( $status );

                                                        ?></span>
                                                    </div>
                                                </td>


                                                <td class="nk-tb-col tb-col-sm">
                                                    <div>
                                                         <span class="tb-sub"><?php
                                                         $customer_id = $subscription -> get_customer_id();
                                                         $data = get_userdata($customer_id);
                                                         if (isset($data->first_name)){
                                                            $name = $data->first_name.' '.$data->last_name;
                                                         }

                                                         // if guest user, show name by order
                                                         if ($data === false){
                                                            $name = $subscription -> get_formatted_billing_full_name() . ' '.esc_html__('(guest user)','marketking');
                                                         }
                                                         $name = apply_filters('marketking_customers_page_name_display', $name, $customer_id);

                                                         echo esc_html($name);
                                                         ?></span>
                                                    </div>
                                                </td>
                                                <td class="nk-tb-col tb-col-md"> 
                                                    <div>
                                                        <span class="tb-sub text-primary"><?php
                                                        $items = $subscription->get_items();
                                                        $items_count = count( $items );
                                                        if ($items_count > apply_filters('marketking_dashboard_item_count_limit', 4)){
                                                            echo esc_html($items_count).' '.esc_html__('Items', 'marketking');
                                                        } else {
                                                            // show the items
                                                            foreach ($items as $item){
                                                                echo apply_filters('marketking_item_display_dashboard', $item->get_name().' x '.$item->get_quantity().'<br>', $item);
                                                            }
                                                        }
                                                        ?></span>
                                                    </div>
                                                </td>
                                                <td class="nk-tb-col" data-order="<?php echo esc_attr($subscription->get_total());?>"> 
                                                    <div>
                                                        <span class="tb-lead"><?php 

                                                        echo $subscription->get_formatted_order_total();


                                                        $meta_content = ' ';
                                                        $meta_content .= '<small class="meta">(';
                                                        // translators: placeholder is the display name of a payment gateway a subscription was paid by
                                                        $meta_content .= esc_html( sprintf( __( 'Via %s', 'marketking' ), $subscription->get_payment_method_to_display() ) );

                                                        if ( WCS_Staging::is_duplicate_site() && $subscription->has_payment_gateway() && ! $subscription->get_requires_manual_renewal() ) {
                                                            $meta_content .= WCS_Staging::get_payment_method_tooltip( $subscription );
                                                        }

                                                        $meta_content .= ')</small>';

                                                        echo $meta_content;




                                                    ?></span>
                                                    </div>
                                                </td>

                                                <td class="nk-tb-col"> 
                                                    <div>
                                                        <span class="tb-lead"><?php 

                                                        $orders = $subscription->get_related_orders();

                                                        $i = 0;
                                                        foreach ($orders as $order_id){

                                                            if ($i !== 0){
                                                                echo ', ';
                                                            }
                                                            ?><a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'manage-order/'.$order_id);?>"><?php echo esc_html($order_id); ?></a><?php
                                                            $i++;
                                                        }


                                                    ?></span>
                                                    </div>
                                                </td>

                                                <td class="nk-tb-col tb-col-md marketking-column-mid">
                                                    <ul class="nk-tb-actions gx-1 my-n1">
                                                        <li class="mr-n1">
                                                            <?php
                                                            if (!marketking()->is_vendor_team_member()){
                                                                ?>
                                                                    <div class="dropdown">
                                                                        <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                                        <div class="dropdown-menu dropdown-menu-right">
                                                                            
                                                                                <ul class="link-list-opt no-bdr">
                                                                                    <?php

                                                                                    if ($status === 'on-hold'){
                                                                                        ?>
                                                                                        <li><a href="#" class="toggle marketking_reactivate_button_subscription" value="<?php echo esc_attr($subscription->get_id());?>"><em class="icon ni ni-play-circle"></em><span><?php esc_html_e('Reactivate','marketking'); ?></span></a></li>
                                                                                        <?php
                                                                                    }

                                                                                    if ($status === 'active'){
                                                                                        ?>
                                                                                        <li><a href="#" class="toggle marketking_pause_button_subscription" value="<?php echo esc_attr($subscription->get_id());?>"><em class="icon ni ni-pause-circle"></em><span><?php esc_html_e('Pause subscription','marketking'); ?></span></a></li>
                                                                                        <?php
                                                                                    }


                                                                                    if (in_array($status, array('active', 'on-hold'))){
                                                                                        ?>
                                                                                        <li><a href="#" class="toggle marketking_cancel_button_subscription" value="<?php echo esc_attr($subscription->get_id());?>"><em class="icon ni ni-cross-circle"></em><span><?php esc_html_e('Cancel subscription','marketking'); ?></span></a></li>
                                                                                        <?php
                                                                                    }
                                                                                    ?>
                                                                                    
                                                                                </ul>
                                                                                
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </li>
                                                    </ul>
                                                </td>
                                                
                                            </tr>
                                            <?php
                                            }
                                        }
                                    }
                                    
                                    ?>
                                    
                                </tbody>
                                
                            </table>

                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}

?>