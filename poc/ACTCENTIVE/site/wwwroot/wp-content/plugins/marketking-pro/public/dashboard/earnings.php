<?php

/*

* @version 1.0.1

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (intval(get_option( 'marketking_enable_earnings_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('earnings')){
        $user_id = marketking()->get_data('user_id');
        $currentuser = new WP_User($user_id);
        
        ?>
        <div class="nk-content marketking_earnings_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block-head nk-block-head-sm">
                            <div class="nk-block-between">
                                <div class="nk-block-head-content">
                                    <h3 class="nk-block-title page-title"><?php esc_html_e('Earnings','marketking');?></h3>
                                    <div class="nk-block-des text-soft">
                                        <p><?php esc_html_e('Here you can view and keep track of your earnings.', 'marketking');?></p>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                                <div class="nk-block-head-content">
                                    <div class="toggle-wrap nk-block-tools-toggle">
                                        <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                                        <div class="toggle-expand-content" data-content="pageMenu">
                                            
                                        </div>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                            </div><!-- .nk-block-between -->
                        </div><!-- .nk-block-head -->
                        <div class="nk-block">
                            <div class="row g-gs">
                                <div class="<?php echo esc_attr(apply_filters('marketking_earnings_card_classes', 'col-xxl-4 col-sm-6'));?>">

                                    <div class="card">
                                        <div class="nk-ecwg nk-ecwg6">
                                            <div class="card-inner">
                                                <div class="card-title-group">
                                                    <div class="card-title">
                                                        <h6 class="title"><?php esc_html_e('Earnings this month','marketking');?></h6>
                                                    </div>
                                                </div>
                                                <div class="data">
                                                    <div class="data-group">
                                                        <div class="amount"><?php
                                                        
                                                        $earnings_number = marketking()->get_earnings($user_id,'current_month');
                                                        echo wc_price($earnings_number);

                                                        ?></div>
                                                    </div>
                                                    <div class="info"><span><?php esc_html_e('since the start of the current month','marketking');?></span></div>
                                                </div>
                                            </div><!-- .card-inner -->
                                        </div><!-- .nk-ecwg -->
                                    </div><!-- .card -->
                                </div><!-- .col -->
                                <div class="<?php echo esc_attr(apply_filters('marketking_earnings_card_classes', 'col-xxl-4 col-sm-6'));?>">
                                    <div class="card">
                                        <div class="nk-ecwg nk-ecwg6">
                                            <div class="card-inner">
                                                <div class="card-title-group">
                                                    <div class="card-title">
                                                        <h6 class="title"><?php esc_html_e('Balance available','marketking');?></h6>
                                                    </div>
                                                </div>
                                                <div class="data">
                                                    <div class="data-group">

                                                        <div class="amount"><?php
                                                        $outstanding_balance = get_user_meta($user_id,'marketking_outstanding_earnings', true);
                                                        if (empty($outstanding_balance)){
                                                            $outstanding_balance = 0;
                                                        }
                                                        echo wc_price($outstanding_balance);
                                                        ?></div>

                                                    </div>
                                                    <div class="info"><span><?php esc_html_e('currently available for payouts','marketking');?></span></div>

                                                </div>
                                            </div><!-- .card-inner -->
                                        </div><!-- .nk-ecwg -->
                                    </div><!-- .card -->
                                </div><!-- .col -->
                                <div class="<?php echo esc_attr(apply_filters('marketking_earnings_card_classes', 'col-xxl-4 col-sm-6'));?>">
                                    <div class="card">
                                        <div class="nk-ecwg nk-ecwg6">
                                            <div class="card-inner">
                                                <div class="card-title-group">
                                                    <div class="card-title">
                                                        <h6 class="title"><?php esc_html_e('Total earnings','marketking');?></h6>
                                                    </div>
                                                </div>
                                                <div class="data">
                                                    <div class="data-group">
                                                        <div class="amount"><?php
                                                        $outstanding_balance = get_user_meta($user_id,'marketking_outstanding_earnings', true);
                                                        if (empty($outstanding_balance)){
                                                            $outstanding_balance = 0;
                                                        }
                                                        // add all payouts to balance
                                                        $user_payout_history = sanitize_text_field(get_user_meta($user_id,'marketking_user_payout_history', true));

                                                        if ($user_payout_history){
                                                            $transactions = explode(';', $user_payout_history);
                                                            $transactions = array_filter($transactions);
                                                        } else {
                                                            // empty, no transactions
                                                            $transactions = array();
                                                        }
                                                        foreach ($transactions as $transaction){
                                                            $elements = explode(':', $transaction);
                                                            $amount = $elements[1];
                                                            $outstanding_balance += floatval($amount);
                                                        }


                                                        echo wc_price($outstanding_balance);

                                                        $last_30_day_earnings = marketking()->get_earnings($user_id,'last_days', 30);


                                                        ?></div>
                                                    </div>
                                                    <div class="info"><span><?php echo '('.strip_tags(wc_price($last_30_day_earnings)).' '.esc_html__('earnings in the last 30 days','marketking').')';?></span></div>
                                                </div>
                                            </div><!-- .card-inner -->
                                        </div><!-- .nk-ecwg -->
                                    </div><!-- .card -->
                                </div><!-- .col -->

                                <div class="col-xxl-12">
                                    <div class="card h-100">
                                        <div class="card-inner">
                                            <div class="card-title-group align-start gx-3 mb-3">
                                            </div>
                                            <div class="nk-sale-data-group align-center justify-between gy-3 gx-5">
                                                <div class="card-title">
                                                    <?php
                                                     $months_removed = $id = sanitize_text_field(get_query_var('id')); // id is the number of months removed from current month
                                                     if (empty($id)){
                                                        $months_removed = $id = 0;
                                                     }

                                                    ?>
                                                    <h6 class="title"><?php esc_html_e('Earnings Overview','marketking');?></h6>
                                                    <p><?php echo esc_html__('Completed Orders - Earnings during ','marketking').ucfirst(date_i18n("M Y", strtotime('-'.$id.' months')));?></p>

                                                </div>
                                                <div class="nk-sale-data">
                                                    <span class="amount"><?php
                                                        // get month requested
                                                        $month_number = date('n', strtotime('-'.$months_removed.' months', strtotime(date("F") . "1")));
                                                        $month_year = date('Y', strtotime('-'.$months_removed.' months', strtotime(date("F") . "1")));
                                                        $days_number = date('t', mktime(0, 0, 0, $month_number, 1, $month_year)); 

                                                        $days_array = array();
                                                        

                                                        $earnings_number = marketking()->get_earnings($user_id,'by_month', false, $month_number, $month_year);
                                                        echo wc_price($earnings_number);

                                                    ?></span>
                                                </div>
                                                <div class="drodown">
                                                    <a href="#" class="dropdown-toggle btn btn-white btn-dim btn-outline-light" data-toggle="dropdown"><em class="d-none d-sm-inline icon ni ni-calender-date"></em><span><?php
                                                    $id = sanitize_text_field(get_query_var('id')); // id is the number of months removed from current month
                                                    if (empty($id)){
                                                       $id = 0;
                                                    }

                                                    echo ucfirst(date_i18n("M Y", strtotime('-'.$id.' months', strtotime(date("F") . "1"))));

                                                    ?></span><em class="dd-indc icon ni ni-chevron-right"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <ul class="link-list-opt no-bdr">
                                                            <?php
                                                            // show all months since user registered
                                                            $udataagent = get_userdata( $user_id );
                                                            $registered_date = $udataagent->user_registered;
                                                            $time_since_registration = time()-strtotime($registered_date);
                                                            $months_since_registration = ceil($time_since_registration/2678400)+1;
                                                            $i = 0;
                                                            while ($months_since_registration > 0){
                                                                // show month
                                                                ?>
                                                                <li><a href="<?php echo esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'earnings/?id='.$i;?>"><span><?php echo ucfirst(date_i18n("M Y", strtotime('-'.$i.' months', strtotime(date("F") . "1"))));?></span></a></li>
                                                                <?php
                                                                $months_since_registration--;
                                                                $i++;
                                                            }

                                                            ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                                

                                            </div>
                                            <div class="nk-sales-ck large pt-3">
                                                <canvas class="sales-overview-chart" id="salesOverview"></canvas>
                                            </div>
                                        </div>
                                    </div><!-- .card -->
                                </div><!-- .col -->

                                <div class="col-xxl-12">
                                    <div class="nk-block-head nk-block-head-sm">
                                        <div class="nk-block-between">
                                            <div class="nk-block-head-content">
                                                <h3 class="nk-block-title page-title"><?php esc_html_e('Your earnings','marketking');?></h3>
                                            </div><!-- .nk-block-head-content -->
                                            <div class="nk-block-head-content">
                                                <div class="toggle-wrap nk-block-tools-toggle">
                                                    <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="more-options"><em class="icon ni ni-more-v"></em></a>
                                                    <div class="toggle-expand-content" data-content="more-options">
                                                        <ul class="nk-block-tools g-3">
                                                            <li>
                                                                <div class="form-control-wrap">
                                                                    <div class="form-icon form-icon-right">
                                                                        <em class="icon ni ni-search"></em>
                                                                    </div>
                                                                    <input type="text" class="form-control" id="marketking_earnings_search" placeholder="<?php esc_html_e('Search transactions...','marketking');?>">
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div><!-- .nk-block-head-content -->
                                        </div><!-- .nk-block-between -->
                                    </div><!-- .nk-block-head -->
                                    <table id="marketking_dashboard_earnings_table" class="nk-tb-list is-separate mb-3">
                                        <thead>
                                            <tr class="nk-tb-item nk-tb-head">
                                                <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Order','marketking'); ?></span></th>
                                                <th class="nk-tb-col tb-col-md"><span class="sub-text"><?php esc_html_e('Date','marketking'); ?></span></th>
                                                <th class="nk-tb-col"><span class="sub-text d-none d-mb-block"><?php esc_html_e('Earnings Status','marketking'); ?></span></th>
                                                <th class="nk-tb-col tb-col-sm"><span class="sub-text"><?php esc_html_e('Customer','marketking'); ?></span></th>
                                                <th class="nk-tb-col tb-col-sm"><span class="sub-text"><?php esc_html_e('Purchased','marketking'); ?></span></th>

                                                <th class="nk-tb-col tb-col-sm"><span class="sub-text"><?php esc_html_e('Order Total','marketking'); ?></span></th>
                                                <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Your Earnings','marketking'); ?></span></th>
                                                <?php
                                                if (intval(get_option( 'marketking_agents_can_manage_orders_setting', 1 )) === 1){
                                                    if(marketking()->vendor_has_panel('orders')){
                                                        ?>
                                                        <th class="nk-tb-col"><span class="sub-text"><?php esc_html_e('Actions','marketking'); ?></span></th>
                                                        <?php
                                                    }
                                                }
                                                ?>

                                            </tr>
                                        </thead>
                                        <?php
                                        if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                            ?>
                                            <tfoot>
                                                <tr class="nk-tb-item nk-tb-head">
                                                    <th class="nk-tb-col tb-col-md"><?php esc_html_e('order','marketking'); ?></th>
                                                    <th class="nk-tb-col tb-col-md"><?php esc_html_e('date','marketking'); ?></th>
                                                    <th class="nk-tb-col tb-col-md"><?php esc_html_e('earnings status','marketking'); ?></th>
                                                    <th class="nk-tb-col tb-col-md"><?php esc_html_e('customer','marketking'); ?></th>
                                                    <th class="nk-tb-col tb-col-md"><?php esc_html_e('purchased','marketking'); ?></th>
                                                    <th class="nk-tb-col tb-col-md"><?php esc_html_e('order total','marketking'); ?></th>
                                                    <th class="nk-tb-col tb-col-md"><?php esc_html_e('your earnings','marketking'); ?></th>
                                                    <?php
                                                    if (intval(get_option( 'marketking_agents_can_manage_orders_setting', 1 )) === 1){
                                                        if(marketking()->vendor_has_panel('orders')){

                                                            ?>
                                                            <th class="nk-tb-col tb-col-md"><?php esc_html_e('actions','marketking'); ?></th>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </tr>
                                            </tfoot>
                                            <?php
                                        }
                                        ?>
                                        <tbody>
                                            <?php

                                            if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                                $earnings = get_posts( array( 
                                                    'post_type' => 'marketking_earning',
                                                    'numberposts' => -1,
                                                    'post_status'    => 'any',
                                                    'fields'    => 'ids',
                                                    'meta_key'   => 'vendor_id',
                                                    'meta_value' => $user_id,
                                                ));

                                                foreach ($earnings as $earning_id){
                                                    $order_id = get_post_meta($earning_id,'order_id', true);
                                                    $orderobj = wc_get_order($order_id);
                                                    if ($orderobj !== false){
                                                        $earnings_total = get_post_meta($earning_id,'marketking_commission_total', true);
                                                        if (!empty($earnings_total) && floatval($earnings_total) !== 0){
                                                            ?>
                                                            <tr class="nk-tb-item">
                                                                <td class="nk-tb-col" data-order="<?php echo esc_html($order_id);?>">

                                                                    <div>
                                                                        <span class="tb-lead">#<?php 

                                                                        // sequential
                                                                        $order_nr_sequential = get_post_meta($order_id,'_order_number', true);
                                                                        if (!empty($order_nr_sequential)){
                                                                            echo $order_nr_sequential;
                                                                        } else {
                                                                            echo esc_html($order_id);
                                                                        }

                                                                        // subscription renewal
                                                                        $renewal = get_post_meta($order_id, '_subscription_renewal', true);
                                                                        if (!empty($renewal)){
                                                                            echo ' ('.esc_html__('susbcription renewal', 'marketking').')';
                                                                        }



                                                                        ?></span>
                                                                    </div>

                                                                </td>
                                                                <td class="nk-tb-col tb-col-md" data-order="<?php 
                                                                    $date = explode('T',$orderobj->get_date_created())[0];
                                                                    echo strtotime($date);

                                                                ?>">
                                                                    <div>
                                                                        <span class="tb-sub"><?php 
                                                                        echo date_i18n( get_option('date_format'), strtotime($date)+(get_option('gmt_offset')*3600) );
                                                                        ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col"> 
                                                                    <div >
                                                                        <span class="dot bg-warning d-mb-none"></span>
                                                                        <?php
                                                                        $status = $orderobj->get_status();
                                                                        $statustext = $badge = '';
                                                                        if ($status === 'processing'){
                                                                            $badge = 'badge-warning';
                                                                            $statustext = esc_html__('Pending Order Completion','marketking');
                                                                        } else if ($status === 'on-hold'){
                                                                            $badge = 'badge-warning';
                                                                            $statustext = esc_html__('Pending Order Completion','marketking');
                                                                        } else if (in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                                                                            $badge = 'badge-success';
                                                                            $statustext = esc_html__('Completed','marketking');
                                                                        } else if ($status === 'refunded'){
                                                                            $badge = 'badge-danger';
                                                                            $statustext = esc_html__('Order Refunded','marketking');
                                                                        } else if ($status === 'cancelled'){
                                                                            $badge = 'badge-danger';
                                                                            $statustext = esc_html__('Order Cancelled','marketking');
                                                                        } else if ($status === 'pending'){
                                                                            $badge = 'badge-warning';
                                                                            $statustext = esc_html__('Pending Order Payment','marketking');
                                                                        } else if ($status === 'failed'){
                                                                            $badge = 'badge-danger';
                                                                            $statustext = esc_html__('Order Failed','marketking');
                                                                        } else {
                                                                            // custom status
                                                                            $badge = apply_filters('marketking_custom_status_badge', 'badge-gray', $status);
                                                                            $wcstatuses = wc_get_order_statuses();
                                                                            if (isset($wcstatuses['wc-'.$status])){
                                                                                $statustext = $wcstatuses['wc-'.$status];
                                                                            } else {
                                                                                $statustext = '';
                                                                            }
                                                                            $statustext = apply_filters('marketking_custom_status_text', $statustext, $status);
                                                                        }

                                                                        // paid via stripe = earnings completed
                                                                        $paidstripe = (get_post_meta($order_id, 'marketking_paid_via_stripe', true ) === 'yes');
                                                                       
                                                                        ?>
                                                                        <span class="badge badge-sm badge-dot has-bg <?php echo esc_attr($badge);?> d-none d-mb-inline-flex"><?php
                                                                        echo esc_html($statustext);
                                                                        ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col tb-col-sm">
                                                                    <div>
                                                                         <span class="tb-sub"><?php
                                                                         $customer_id = $orderobj -> get_customer_id();

                                                                         if ($customer_id === 0){
                                                                            $name = $orderobj->get_billing_first_name().' '.$orderobj->get_billing_last_name();
                                                                         } else {
                                                                            $data = get_userdata($customer_id);
                                                                            $name = $data->first_name.' '.$data->last_name;
                                                                         }
                                                                         
                                                                         $name = apply_filters('marketking_customers_page_name_display', $name, $customer_id);
                                                                         echo esc_html($name);
                                                                         ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col tb-col-md"> 
                                                                    <div>
                                                                        <span class="tb-sub text-primary"><?php
                                                                        $items = $orderobj->get_items();
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
                                                                <td class="nk-tb-col tb-col-sm" data-order="<?php echo esc_attr(apply_filters('marketking_earnings_order_total', $orderobj->get_total(), $orderobj));?>"> 
                                                                    <div>
                                                                        <span class="tb-lead"><?php echo wc_price(apply_filters('marketking_earnings_order_total', $orderobj->get_total(), $orderobj), array('currency' => $orderobj->get_currency()));?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col" data-order="<?php echo esc_attr($earnings_total);?>"> 
                                                                    <div>
                                                                        <?php
                                                                        if (in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                                                                            $text_color = 'text-success';
                                                                        } else {
                                                                            $text_color = 'text-soft';
                                                                        }

                                                                        // paid via stripe
                                                                        $paidstripe = ($orderobj->get_meta('marketking_paid_via_stripe') === 'yes');
                                 
                                                                        ?>
                                                                        <span class="tb-lead <?php echo esc_attr($text_color);?>"><?php 
                                                                        
                                                                        echo wc_price($earnings_total);

                                                                        if (in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                                                                            $tax_fee_recipient = $orderobj->get_meta('tax_fee_recipient');
                                                                            if (empty($tax_fee_recipient)){
                                                                                $tax_fee_recipient = get_option('marketking_tax_fee_recipient_setting', 'vendor');
                                                                            }
                                                                            if ($tax_fee_recipient === 'vendor'){
                                                                                $tax = $orderobj->get_total_tax();
                                                                                if (floatval($tax) > 0){
                                                                                    if (apply_filters('marketking_show_tax_earnings', true)){
                                                                                        echo ' ('.esc_html__('tax','marketking').' '.wc_price($tax).')';
                                                                                    }
                                                                                }
                                                                            }
                                                                        }

                                                                        if (!$paidstripe){
                                                                           if (!in_array($status,apply_filters('marketking_earning_completed_statuses', array('completed')))){
                                                                               esc_html_e(' (pending)', 'marketking');
                                                                           } 
                                                                        }

                                                                        if ($paidstripe){
                                                                            ?>
                                                                            <span class="text-info fs-13px"><?php esc_html_e('(Stripe)','marketking');?></span>
                                                                            <?php
                                                                        }
                                                                        
                                                                        ?></span>
                                                                    </div>
                                                                </td>
                                                                <?php
                                                                if (intval(get_option( 'marketking_agents_can_manage_orders_setting', 1 )) === 1){
                                                                    if(marketking()->vendor_has_panel('orders')){
                                                                        ?>
                                                                        <td class="nk-tb-col">
                                                                            <div class="marketking_manage_order_container"> 
                                                                                <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'manage-order/'.$order_id);?>"><button class="btn btn-sm btn-dim btn-secondary marketking_manage_order" value="<?php echo esc_attr($order_id);?>"><em class="icon ni ni-bag-fill"></em><span><?php esc_html_e('View Order','marketking');?></span></button></a>
                                                                            </div>
                                                                        </td>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </tr>
                                                        <?php
                                                        }
                                                    }

                                                    // display manual adjustments
                                                    if ($order_id == 'manual'){
                                                        $earnings_total = get_post_meta($earning_id,'marketking_commission_total', true);
                                                        if (!empty($earnings_total) && floatval($earnings_total) !== 0){
                                                            ?>
                                                            <tr class="nk-tb-item">
                                                                <td class="nk-tb-col" data-order="<?php echo esc_html($order_id);?>">

                                                                    <div>
                                                                        <span class="tb-lead">#<?php 

                                                                        esc_html_e('Manual Adjustment','marketking');

                                                                        ?></span>
                                                                    </div>

                                                                </td>
                                                                <td class="nk-tb-col tb-col-md" data-order="<?php 
                                                                    $date = get_post_meta($earning_id,'time', true);
                                                                    echo $date;

                                                                ?>">
                                                                    <div>
                                                                        <span class="tb-sub"><?php 
                                                                        echo date_i18n( get_option('date_format'), $date+(get_option('gmt_offset')*3600) );
                                                                        ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col"> 
                                                                    <div >
                                                                        <span class="dot bg-warning d-mb-none"></span>
                                                                        <?php
                                                                        $note = get_post_meta($earning_id,'note', true);
                                                                        if (empty($note)){
                                                                            echo '-';
                                                                        } else {
                                                                            echo $note;
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col tb-col-sm">
                                                                    <div>
                                                                         <span class="tb-sub"><?php
                                                                         echo '-';
                                                                         ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col tb-col-md"> 
                                                                    <div>
                                                                        <span class="tb-sub text-primary"><?php
                                                                        echo '-';
                                                                        ?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col tb-col-sm"> 
                                                                    <div>
                                                                        <span class="tb-lead"><?php echo '-';?></span>
                                                                    </div>
                                                                </td>
                                                                <td class="nk-tb-col" data-order="<?php echo esc_attr($earnings_total);?>"> 
                                                                    <div>
                                                                        <?php
                                                                        echo wc_price($earnings_total);
                                                                        
                                                                        ?>
                                                                    </div>
                                                                </td>
                                                                <?php
                                                                if (intval(get_option( 'marketking_agents_can_manage_orders_setting', 1 )) === 1){
                                                                    if(marketking()->vendor_has_panel('orders')){
                                                                        ?>
                                                                        <td class="nk-tb-col">
                                                                            -
                                                                        </td>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </tr>
                                                        <?php
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            ?>
                                            
                                        </tbody>
                                        
                                    </table>
                                </div>
                            </div><!-- .row -->
                        </div><!-- .nk-block -->
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>