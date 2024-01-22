<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
if (defined('B2BKING_DIR') && defined('MARKETKINGPRO_DIR') && intval(get_option('marketking_enable_b2bkingintegration_setting', 1)) === 1){
	if (intval(get_option('b2bking_enable_conversations_setting', 1)) === 1){
    if(marketking()->vendor_has_panel('b2bkingconversations')){
	    ?>
	    <div class="nk-content marketking_b2bmessaging_page">
	        <div class="container-fluid">
	            <div class="nk-content-inner">
	                <div class="nk-content-body">
	                	<div class="nk-block-head nk-block-head-sm">
    	                    <div class="nk-block-between">
    	                        <div class="nk-block-head-content">
    	                            <h3 class="nk-block-title page-title"><?php esc_html_e('B2B Messaging','marketking');?></h3>
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
    	                                                <input type="text" class="form-control" id="marketking_offers_search" placeholder="<?php esc_html_e('Search conversations...','marketking');?>">
    	                                            </div>
    	                                        </li>
    	                                        
    	                                    </ul>
    	                                </div>
    	                            </div>
    	                        </div><!-- .nk-block-head-content -->
    	                    </div><!-- .nk-block-between -->
    	                </div>

	                    <div class="nk-block">
	                        <div class="row g-gs">
	                        	<div class="col-xxl-12">
                                <article class="messaging-content-area">
                                <div id="b2bkingmarketking_dashboard_offers_table_container">
                                  <table id="b2bkingmarketking_dashboard_offers_table">
                                      <thead>
                                          <tr>
                                            <th><?php esc_html_e('ID','marketking'); ?></th>
                                              <th><?php esc_html_e('Title','marketking'); ?></th>
                                              <th><?php esc_html_e('Type','marketking'); ?></th>
                                              <th><?php esc_html_e('User','marketking'); ?></th>
                                              <th><?php esc_html_e('Last Reply','marketking'); ?></th>
                                              <th><?php esc_html_e('Actions','marketking'); ?></th>
                                          </tr>
                                      </thead>
                                      <tbody>
                                        <?php
                                        // get all vendor conversations
                                        $user_id = marketking()->get_data('user_id');
                                        $currentuser = new WP_User($user_id);
                                        
                                        $vendor_conversations = get_user_meta($user_id,'b2bking_marketking_vendor_conversations_list_ids', true);
                                        if (!empty($vendor_conversations)){
                                          $ids_array=array_unique(explode(',',$vendor_conversations));
                                          foreach($ids_array as $conversation_id){
                                            if (!empty($conversation_id) && $conversation_id !== NULL && get_post_type($conversation_id) === 'b2bking_conversation'){
                                                // title
                                                $title = get_the_title($conversation_id);
                                                // price
                                                $type = get_post_meta($conversation_id,'b2bking_conversation_type', true);
                                                $username = get_post_meta($conversation_id,'b2bking_conversation_user', true);

                                                $nr_messages = get_post_meta ($conversation_id, 'b2bking_conversation_messages_number', true);
                                                  $last_reply_time = intval(get_post_meta ($conversation_id, 'b2bking_conversation_message_'.$nr_messages.'_time', true));

                                                  // build time string
                                                // if today
                                                if((time()-$last_reply_time) < 86400){
                                                  // show time
                                                  $conversation_last_reply = date_i18n( 'h:i A', $last_reply_time+(get_option('gmt_offset')*3600) );
                                                } else if ((time()-$last_reply_time) < 172800){
                                                // if yesterday
                                                  $conversation_last_reply = 'Yesterday at '.date_i18n( 'h:i A', $last_reply_time+(get_option('gmt_offset')*3600) );
                                                } else {
                                                // date
                                                  $conversation_last_reply = date_i18n( get_option('date_format'), $last_reply_time+(get_option('gmt_offset')*3600) ); 
                                                }
                                                ?>
                                                <tr>
                                                  <td><?php echo esc_html($conversation_id); ?></td>
                                                    <td><?php echo esc_html($title); ?></td>
                                                    <td><?php echo esc_html($type); ?></td>
                                                    <td><?php echo esc_html($username); ?></td>
                                                    <td><?php echo esc_html($conversation_last_reply); ?></td>
                                                    <td><a href="#b2bking_marketking_conversation_container" rel="modalzz:open"><button class="marketking-btn marketking-btn-default b2bking_conversation_table btn btn-secondary" type="button" value="<?php echo esc_attr($conversation_id);?>"><?php esc_html_e('View Conversation','marketking');?></button></a></td>
                                                </tr>
                                                <?php
                                              }
                                          }
                                        }
                                        ?>
                                      </tbody>
                                      <tfoot>
                                          <tr>
                                            <th><?php esc_html_e('ID','marketking'); ?></th>
                                              <th><?php esc_html_e('Title','marketking'); ?></th>
                                              <th><?php esc_html_e('Type','marketking'); ?></th>
                                              <th><?php esc_html_e('User','marketking'); ?></th>
                                              <th><?php esc_html_e('Last Reply','marketking'); ?></th>
                                              <th><?php esc_html_e('Actions','marketking'); ?></th>
                                          </tr>
                                      </tfoot>
                                  </table>
                                </div>
                                </article>

                                <div id="b2bking_marketking_conversation_container" class="modalzz">
                                  <div id="b2bking_myaccount_conversation_endpoint_container_top">
                                    <div id="b2bking_myaccount_conversation_endpoint_title">
                                      -
                                    </div>
                                  </div>
                                  <div id="b2bking_myaccount_conversation_endpoint_container_top_header">
                                    <div class="b2bking_myaccount_conversation_endpoint_container_top_header_item"><?php esc_html_e('Type:','marketking'); ?> <span class="b2bking_myaccount_conversation_endpoint_top_header_text_bold b2bking_myaccount_conversation_endpoint_typed">-</span></div>
                                    <div class="b2bking_myaccount_conversation_endpoint_container_top_header_item"><?php esc_html_e('User:','marketking'); ?> <span class="b2bking_myaccount_conversation_endpoint_top_header_text_bold b2bking_myaccount_conversation_endpoint_usernamed">-</span></div>
                                    <div class="b2bking_myaccount_conversation_endpoint_container_top_header_item"><?php esc_html_e('Last Reply:','marketking'); ?> <span class="b2bking_myaccount_conversation_endpoint_top_header_text_bold b2bking_myaccount_conversation_endpoint_replyd">-</span></div>
                                  </div>
                                  <!-- MESSAGES START -->
                                  <div id="b2bking_conversation_messages_container_container">
                                  
                                  </div>
                                
                                  <!-- MESSAGES END -->
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
}