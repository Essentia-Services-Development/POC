<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (intval(get_option( 'marketking_enable_refunds_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('refunds')){
        $user_id = marketking()->get_data('user_id');
        $currentuser = new WP_User($user_id);
        
        ?>
        <div class="nk-content marketking_refunds_page">
            <div class="container-fluid">
                <div class="nk-content-inner">
                    <div class="nk-content-body">
                        <div class="nk-block-head nk-block-head-sm">
                            <div class="nk-block-between">
                                <div class="nk-block-head-content">
                                    <h3 class="nk-block-title page-title"><?php esc_html_e('Refund Requests','marketking'); ?></h3>
                                </div><!-- .nk-block-head-content -->
                                <div class="nk-block-head-content">
                                    <div class="toggle-wrap nk-block-tools-toggle">
                                        <a href="#" class="btn btn-icon btn-trigger toggle-expand mr-n1" data-target="pageMenu"><em class="icon ni ni-more-v"></em></a>
                                        <div class="toggle-expand-content" data-content="pageMenu">
                                            <ul class="nk-block-tools g-3">
                                                <li>
                                                    <div class="form-control-wrap">
                                                        <div class="form-icon form-icon-right">
                                                            <em class="icon ni ni-search"></em>
                                                        </div>
                                                        <input type="text" class="form-control" id="marketking_refunds_search" placeholder="<?php esc_html_e('Search refunds...','marketking');?>">
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div><!-- .nk-block-head-content -->
                            </div><!-- .nk-block-between -->
                        </div><!-- .nk-block-head -->

                        <table id="marketking_dashboard_refunds_table" class="nk-tb-list is-separate mb-3">
                            <thead>
                                <tr class="nk-tb-item nk-tb-head">
                                    <th class="nk-tb-col tb-col-sm marketking-column-mid"><span class="sub-text"><?php esc_html_e('Date','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-sm marketking-column-mid"><span class="sub-text"><?php esc_html_e('Order','marketking'); ?></span></th>
                                    <th class="nk-tb-col marketking-column-small"><span class="sub-text"><?php esc_html_e('Reason','marketking'); ?></span></th>
                                    <th class="nk-tb-col marketking-column-small"><span class="sub-text"><?php esc_html_e('Status','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-sm marketking-column-mid"><span class="sub-text"><?php esc_html_e('Value','marketking'); ?></span></th>
                                    <th class="nk-tb-col tb-col-sm marketking-column-mid"><span class="sub-text"><?php esc_html_e('User','marketking'); ?></span></th>

                                    <th class="nk-tb-col tb-col-sm marketking-column-mid marketking-column-min"><span class="sub-text"><?php esc_html_e('Actions','marketking'); ?></span></th>                           

                                </tr>
                            </thead>
                            <?php
                            if (!marketking()->load_tables_with_ajax(get_current_user_id())){
                                ?>
                                <tfoot>
                                    <tr class="nk-tb-item nk-tb-head">
                                        <th class="nk-tb-col tb-col-sm tb-non-tools"><?php esc_html_e('date','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-sm tb-non-tools"><?php esc_html_e('order','marketking'); ?></th>
                                        <th class="nk-tb-col tb-non-tools"><?php esc_html_e('reason','marketking'); ?></th>
                                        <th class="nk-tb-col tb-non-tools"><?php esc_html_e('status','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-sm tb-non-tools"><?php esc_html_e('value','marketking'); ?></th>
                                        <th class="nk-tb-col tb-col-sm tb-non-tools"><?php esc_html_e('user','marketking'); ?></th>
                                        
                                        <th class="nk-tb-col tb-col-sm tb-non-tools marketking-column-min"></th>
                                    </tr>
                                </tfoot>
                                <?php
                            }
                            ?>
                            <tbody>
                                <?php

                                if (!marketking()->load_tables_with_ajax(get_current_user_id())){

                                    // get all refund requests
                                    $refund_requests = get_posts( array( 
                                        'post_type' => 'marketking_refund',
                                        'numberposts' => -1,
                                        'post_status'    => 'any',
                                        'fields'    => 'ids',
                                        'meta_key'  => 'vendor_id',
                                        'meta_value'   => $user_id,
                                    ));

                                    foreach ($refund_requests as $request){
                                        $order_id = get_post_meta($request,'order_id', true);
                                        $order = wc_get_order($order_id);
                                        $value = get_post_meta($request,'value', true);
                                        $status = get_post_meta($request,'request_status', true);
                                        $reason = get_post_meta($request,'reason', true);
                                        $author_id = get_post_field ('post_author', $request);
                                        $user = new WP_User($author_id);
                                        $user = $user->user_login;
                                        ?>
                                       

                                        <tr class="nk-tb-item">
                                            <td class="nk-tb-col tb-col-sm marketking-column-mid" data-order="<?php
                                                $date = get_the_date('',$request);
                                                ?>">
                                                <div>
                                                    <span class="tb-sub"><?php 
                                                    echo esc_html($date);
                                                    ?></span>
                                                </div>
                                            </td>
                                            <td class="nk-tb-col tb-col-sm marketking-column-mid">
                                                <?php echo esc_html__('Order','marketking').' '; ?>
                                                <?php 
                                                if(marketking()->vendor_has_panel('orders')){
                                                    ?>
                                                    <a href="<?php echo esc_attr(trailingslashit(get_page_link(get_option( 'marketking_vendordash_page_setting', 'disabled' ))).'manage-order/'.$order_id); ?>"><?php 

                                                        echo '#';
                                                        
                                                        // sequential
                                                        $order_nr_sequential = $order->get_meta('_order_number');
                                                        if (!empty($order_nr_sequential)){
                                                            echo $order_nr_sequential;
                                                        } else {
                                                            echo esc_html($order_id);
                                                        }

                                                        ?></a>

                                                    <?php
                                                } else {
                                                    echo '#';

                                                    // sequential
                                                    $order_nr_sequential = $order->get_meta('_order_number');
                                                    if (!empty($order_nr_sequential)){
                                                        echo $order_nr_sequential;
                                                    } else {
                                                        echo esc_html($order_id);
                                                    }

                                                }
                                                ?>
                                            </td>
                                            <td class="nk-tb-col marketking-column-small">
                                                <span class="tb-lead"><?php 
                                                    echo substr($reason,0, 150);
                                                    if (substr($reason,0, 150) !== $reason){
                                                        echo '...';
                                                    }
                                                    ?></span>
                                            </td>
                                            <td class="nk-tb-col">
                                                <span class="tb-sub">
                                                <?php
                                                    if ($status === 'open'){
                                                        esc_html_e('Open','marketking');
                                                    } else if ($status === 'closed'){
                                                        esc_html_e('Closed','marketking');
                                                    } else if ($status === 'approved'){
                                                        esc_html_e('Approved','marketking');
                                                    } else if ($status === 'rejected'){
                                                        esc_html_e('Denied','marketking');
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td class="nk-tb-col tb-col-sm marketking-column-mid">
                                                <span class="tb-sub">
                                                <?php
                                                   if ($value === 'full'){
                                                    esc_html_e('Full Refund','marketking');
                                                   } else if ($value === 'partial'){
                                                    esc_html_e('Partial: ','marketking');
                                                    $partialamount = floatval(get_post_meta($request, 'partialamount', true));
                                                    echo wc_price($partialamount);
                                                    if ($order){
                                                        echo ' / '.wc_price($order->get_total());
                                                    }
                                                   }
                                                    ?>
                                                </span>
                                            </td>

                                            <td class="nk-tb-col tb-col-sm marketking-column-mid">
                                                <span class="tb-sub">
                                                <?php
                                                    echo esc_html($user);
                                                    ?>
                                                </span>
                                            </td>
                                            
                                            <td class="nk-tb-col tb-col-sm marketking-column-mid">
                                               <div class="btn-group">

                                                   <a href="#b2bking_marketking_conversation_container" rel="modalzz:open"><button type="button" class="btn btn-sm btn-outline-primary marketking_view_refund_button b2bking_conversation_table" type="button" value="<?php echo esc_attr($request);?>"><em class="icon ni ni-eye-fill"></em><span><?php esc_html_e('View','marketking');?></span></button></a>

                                                    <span class="refunds_hidden_id"><?php echo esc_html($request);?></span>
                                               </div>
                                               
                                            </td>
                                            
                                        </tr>
                                        <?php
                                        
                                    }
                                }
                                
                                ?>
                                
                            </tbody>
                            
                        </table>

                    </div>
                </div>
            </div>
        </div>


        <div id="b2bking_marketking_conversation_container" class="modalzz">
          <div id="b2bking_myaccount_conversation_endpoint_container_top">
            <div id="b2bking_myaccount_conversation_endpoint_title">
              -
            </div>
          </div>
          <div id="b2bking_myaccount_conversation_endpoint_container_top_header">
            <div class="b2bking_myaccount_conversation_endpoint_container_top_header_item"><?php esc_html_e('Refund Request','marketking'); ?> <span class="b2bking_myaccount_conversation_endpoint_top_header_text_bold b2bking_myaccount_conversation_endpoint_typed">-</span></div>
           
           
          </div>
          <!-- MESSAGES START -->
          <div id="b2bking_conversation_messages_container_container">
          
          </div>
        
          <!-- MESSAGES END -->
        </div>
        <?php
    }
}
?>