<?php

/*

* @version 1.0.1

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
// get data
$unread_msg = marketking()->get_data('unread_msg');
$messages = marketking()->get_data('messages');
$user_id = marketking()->get_data('user_id');
$currentuser = new WP_User($user_id);

$currentuser = wp_get_current_user();
$currentuserlogin = $currentuser -> user_login;

if (intval(get_option( 'marketking_enable_messages_setting', 1 )) === 1){
    if(marketking()->vendor_has_panel('messages')){
        ?>
        <div class="nk-content p-0 marketking_messages_page">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    <div class="nk-msg">
                        <div class="nk-msg-aside">
                            <div class="nk-msg-nav">
                                <ul class="nk-msg-menu">
                                    <?php
                                    // if this page is CLOSED messages, or current message is closed
                                    $closed = sanitize_text_field(get_query_var('closed'));
                                    $is_closed = 'no';
                                    // get currently selected message
                                    $aid = sanitize_text_field(get_query_var('id'));

                                    if (!empty($aid)){
                                        $anr_messages = get_post_meta ($aid, 'marketking_message_messages_number', true);
                                        $alast_message = get_post_meta ($aid, 'marketking_message_message_'.$anr_messages, true);

                                        // check if message is closed
                                        $alast_closed_time = get_user_meta($user_id,'marketking_message_last_closed_'.$aid, true);
                                        if (!empty($alast_closed_time)){
                                            $alast_message_time = get_post_meta ($aid, 'marketking_message_message_'.$anr_messages.'_time', true);
                                            if (floatval($alast_closed_time) > floatval($alast_message_time)){
                                                 $is_closed = 'yes';
                                            }
                                        }
                                    }
                                    ?>

                                    <li class="nk-msg-menu-item <?php if ($closed !== 'yes' && $is_closed !== 'yes'){ echo 'active'; }?>"><a href="<?php echo esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'messages';?>"><?php esc_html_e('Active','marketking');?></a></li>
                                    <li class="nk-msg-menu-item <?php if ($closed === 'yes' || $is_closed === 'yes'){ echo 'active'; }?>"><a href="<?php echo esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'messages?closed=yes';?>"><?php esc_html_e('Closed','marketking');?></a></li>
                                    <li class="nk-msg-menu-item ml-auto"><a href="#" class="link link-primary" data-toggle="modal" data-target="#compose-mail"><em class="icon ni ni-plus"></em> <span><?php esc_html_e('Compose','marketking');?></span></a></li>
                                </ul><!-- .nk-msg-menu -->
                                
                            </div><!-- .nk-msg-nav -->

                            <?php
                            $closedmsg = array();
                            // remove messages which are not closed
                            foreach ($messages as $message){
                                $last_closed_time = get_user_meta($user_id,'marketking_message_last_closed_'.$message, true);
                                if (!empty($last_closed_time)){
                                    $nr_messages = get_post_meta ($message, 'marketking_message_messages_number', true);
                                    $last_message_time = get_post_meta ($message, 'marketking_message_message_'.$nr_messages.'_time', true);
                                    if (floatval($last_closed_time) > floatval($last_message_time)){
                                        array_push($closedmsg, $message);
                                    }
                                }
                            }
                                
                            $activemessages = array_diff($messages, $closedmsg);
                            // if this page is CLOSED messages, or current msg is closed
                            if ($closed === 'yes' || $is_closed === 'yes'){
                                $messages = $closedmsg;
                            } else {
                                $messages = $activemessages;
                            }

                            // cut messages for pagination
                            $items_per_page = 30;
                            $pagenr = sanitize_text_field(marketking()->get_pagenr_query_var());
                            if (empty($pagenr)){
                                $pagenr = 1;
                            }
                            $pagesnr = count($messages)/$items_per_page;

                            $messages = array_slice($messages, (($pagenr-1)*$items_per_page), $items_per_page);

                            ?>
                            <div class="nk-msg-list" data-simplebar>
                                <?php
                                
                                foreach ($messages as $message){ // message is a message thread e.g. conversation

                                    $title = get_the_title($message);
                                    $nr_messages = get_post_meta ($message, 'marketking_message_messages_number', true);

                                    $last_message_time = get_post_meta ($message, 'marketking_message_message_'.$nr_messages.'_time', true);
                                    // build time string
                                    // if today
                                    if((time()-$last_message_time) < 86400){
                                        // show time
                                        $timestring = date_i18n( 'h:i A', $last_message_time+(get_option('gmt_offset')*3600) );
                                    } else if ((time()-$last_message_time) < 172800){
                                    // if yesterday
                                        $timestring = 'Yesterday at '.date_i18n( 'h:i A', $last_message_time+(get_option('gmt_offset')*3600) );
                                    } else {
                                    // date
                                        $timestring = date_i18n( get_option('date_format'), $last_message_time+(get_option('gmt_offset')*3600) ); 
                                    }

                                    $last_message = get_post_meta ($message, 'marketking_message_message_'.$nr_messages, true);
                                    // first 100 chars
                                    $last_message = substr($last_message, 0, 100);

                                    // check if message is unread
                                    $is_unread = '';
                                    $last_message_author = get_post_meta ($message, 'marketking_message_message_'.$nr_messages.'_author', true);
                                    if ($last_message_author !== $currentuserlogin){
                                        $last_read_time = get_user_meta($user_id,'marketking_message_last_read_'.$message, true);
                                        if (!empty($last_read_time)){
                                            $last_message_time = get_post_meta ($message, 'marketking_message_message_'.$nr_messages.'_time', true);
                                            if (floatval($last_read_time) < floatval($last_message_time)){
                                                $is_unread = 'is-unread';
                                            }
                                        } else {
                                            $is_unread = 'is-unread';
                                        }
                                    } 

                              

                                    // get the other party in the chat
                                    $author = get_post_meta ($message, 'marketking_message_message_1_author', true);
                                    $convuser = get_post_meta ($message, 'marketking_message_user', true);
                                    if ($convuser === 'shop'){
                                        $convuser = esc_html__('Shop','marketking');
                                        if (get_post_meta ($message, 'marketking_message_message_2_author', true) !== $author && !empty(get_post_meta ($message, 'marketking_message_message_2_author', true))){
                                            $convuser = get_post_meta ($message, 'marketking_message_message_2_author', true);
                                        }
                                    }
                                    if ($author === $currentuserlogin){
                                        $author = $convuser;
                                    }

                                    $otherparty = marketking()->get_other_chat_party($message);
                                    $icon = marketking()->get_display_icon_image($otherparty);

                                    ?>
                                     <a href="<?php echo trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true))).'messages?id='.esc_attr($message);?>">
                                        <div class="nk-msg-item <?php if(intval($aid)===intval($message)){echo 'current ';}?><?php echo esc_attr($is_unread);?>" data-msg-id="1">
                                            <div class="nk-msg-media user-avatar" style="<?php if (strlen($icon) != 2){
                                                                echo 'background-image: url('.$icon.') !important;background-size: contain!important;';
                                                            } ?>">
                                                <span><?php  if (strlen($icon) == 2){
                                                                    echo esc_html($icon);
                                                                }?></span>
                                            </div>
                                           
                                            <div class="nk-msg-info">
                                                <div class="nk-msg-from">
                                                    <div class="nk-msg-sender">
                                                        <div class="name"><?php echo esc_html($author);?></div>
                                                    </div>
                                                    <div class="nk-msg-meta">
                                                        <div class="date"><?php echo esc_html($timestring); ?></div>
                                                    </div>
                                                </div>
                                                
                                                    <div class="nk-msg-context">
                                                        <div class="nk-msg-text">
                                                            <h6 class="title"><?php echo esc_html($title);?></h6>
                                                            <p><?php echo nl2br(wp_kses(
                                                                $last_message,
                                                                array(
                                                                    'br' => true,
                                                                    'b'  => true,
                                                                )
                                                            ));?></p>
                                                        </div>
                                                        <div class="nk-msg-lables">
                                                            <?php 
                                                            if ($is_unread !== ''){
                                                                ?>
                                                                <div class="unread"><span class="badge badge-primary"><?php esc_html_e('New','marketking');?></span></div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                
                                            </div>
                                        </div><!-- .nk-msg-item -->
                                    </a>
                                    <?php
                                }
                                ?>

                            </div><!-- .nk-msg-list -->
                            <ul class="pagination justify-content-center ">
                            <?php
                            // pagination
                            if ($pagesnr > 1){
                                $i = 1;
                                $closedcls = '';
                                if ($closed === 'yes' || $is_closed === 'yes'){
                                    $closedcls = '?closed=yes';
                                    $pagecls = '&pagenr=';
                                } else {
                                    $pagecls = '?pagenr=';
                                }
                                while ($pagesnr > 0){
                                    ?>
                                    <li class="page-item"><a class="page-link" href="<?php echo trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true))).'messages/'.$closedcls.$pagecls.esc_attr($i);?>"><?php echo esc_html($i);?></a></li>
                                    <?php
                                    $i++;
                                    $pagesnr--;
                                }
                            }
                            ?>
                            </ul>
                        </div><!-- .nk-msg-aside -->
                        <?php
                        // get currently selected message
                        $message = $id = sanitize_text_field(get_query_var('id'));

                        if (empty($id)){
                            // no message selected, get the first active one, if any.
                            if (!empty($activemessages)){
                                $message = $id = reset($activemessages);
                            }
                        }


                        $title = get_the_title($message);
                        $nr_messages = get_post_meta ($message, 'marketking_message_messages_number', true);

                        $last_message = get_post_meta ($message, 'marketking_message_message_'.$nr_messages, true);
                        // first 100 chars
                        $last_message = substr($last_message, 0, 100);

                        // check if message is closed
                        $is_closed = 'no';
                        $last_closed_time = get_user_meta($user_id,'marketking_message_last_closed_'.$message, true);
                        if (!empty($last_closed_time)){
                            $last_message_time = get_post_meta ($message, 'marketking_message_message_'.$nr_messages.'_time', true);
                            if (floatval($last_closed_time) > floatval($last_message_time)){
                                 $is_closed = 'yes';
                            }
                        }

                        if (!empty($id)){
                            // check that user has permission
                            // get the other party in the chat
                            $author = get_post_meta ($id, 'marketking_message_message_1_author', true);
                            $convuser = get_post_meta ($id, 'marketking_message_user', true);
                            if ($currentuserlogin === $author || $currentuserlogin === $convuser || current_user_can('activate_plugins')){

                            ?>
                            <div class="nk-msg-body bg-white <?php if (!empty(sanitize_text_field(get_query_var('id')))){echo 'show-message';} ?>">
                                <div class="nk-msg-head">
                                    <h4 class="title d-none d-lg-block"><?php echo esc_html($title);?></h4>
                                    <div class="nk-msg-head-meta">
                                        <div class="d-none d-lg-block">
                                            <?php
                                            $custom_info = get_post_meta( $message, 'marketking_custom_discussion_info', true );
                                            if (!empty($custom_info)){
                                                echo esc_html($custom_info);
                                            }
                                            ?>
                                        </div>
                                        <div class="d-lg-none"><a href="<?php echo esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'messages'; ?>" class="btn btn-icon btn-trigger nk-msg-hide ml-n1"><em class="icon ni ni-arrow-left"></em></a></div>
                                        <ul class="nk-msg-actions">
                                            <li><button id="marketking_mark_conversation_read" value="<?php echo esc_attr($id);?>" class="btn btn-dim btn-sm btn-outline-light"><em class="icon ni ni-eye"></em><span><?php esc_html_e('Mark as Read','marketking');?></span></button></li>
                                            <?php
                                            if ($is_closed === 'yes'){
                                                ?>
                                                <li><button id="marketking_mark_conversation_closed" value="<?php echo esc_attr($id);?>" class="btn btn-dim btn-sm btn-outline-light"><em class="icon ni ni-check"></em><span><?php esc_html_e('Mark as Active','marketking');?></span></button></li>
                                                <?php
                                            } else {
                                                ?>
                                                <li><button id="marketking_mark_conversation_closed" value="<?php echo esc_attr($id);?>" class="btn btn-dim btn-sm btn-outline-light"><em class="icon ni ni-check"></em><span><?php esc_html_e('Mark as Closed','marketking');?></span></button></li>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if ($is_closed === 'yes'){
                                                ?>
                                                <li><span class="badge badge-dim badge-success badge-sm"><em class="icon ni ni-check"></em><span><?php esc_html_e('Closed','marketking');?></span></span></li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div><!-- .nk-msg-head -->
                                <div class="nk-msg-reply nk-reply" data-simplebar>
                                    <div class="nk-msg-head py-4 d-lg-none">
                                        <h4 class="title"><?php echo esc_html($title);?></h4>
                                    </div>

                                    <?php
                                    // display all messages in the thread
                                    $guest_message = 'no';

                                    for ($i = 1; $i <= $nr_messages; $i++) {
                                       
                                        $message_time = get_post_meta ($message, 'marketking_message_message_'.$i.'_time', true);
                                        // build time string
                                        // if today
                                        if((time()-$message_time) < 86400){
                                            // show time
                                            $timestring = date_i18n( 'h:i A', $message_time+(get_option('gmt_offset')*3600) );
                                        } else if ((time()-$message_time) < 172800){
                                        // if yesterday
                                            $timestring = 'Yesterday at '.date_i18n( 'h:i A', $message_time+(get_option('gmt_offset')*3600) );
                                        } else {
                                        // date
                                            $timestring = date_i18n( get_option('date_format'), $message_time+(get_option('gmt_offset')*3600) ); 
                                        }

                                        $messagecontent = get_post_meta( $message, 'marketking_message_message_'.$i, true);
                                        $author = get_post_meta( $message, 'marketking_message_message_'.$i.'_author', true);

                                        if (strpos($author, '@') !== false) {
                                            // if it contains an email, it's not necessarily a guest message. Check if it has an account
                                            $acc = get_user_by('login', $author, true);
                                            if ($acc !== false){
                                                // has acc
                                            } else {
                                                $guest_message = 'yes';
                                            }
                                            
                                        }

                                        $icon = marketking()->get_display_icon_image($author);

                                        ?>
                                        <div class="nk-reply-item <?php if ($guest_message === 'yes' && $i === 2){echo 'marketking_system_message';}?>">
                                            <div class="nk-reply-header">
                                                <div class="user-card">
                                                    <div class="user-avatar sm bg-blue" style="<?php
                                                            if (strlen($icon) != 2){
                                                                echo 'background-image: url('.$icon.') !important;background-size: contain!important;';
                                                            }
                                                            ?>">
                                                        <span><?php if (strlen($icon) ==2){echo $icon;}?></span>
                                                    </div>
                                                    <div class="user-name"><?php 
                                                    $acc = get_user_by('login', $author, true);
                                                    if ($acc !== false){
                                                        if (marketking()->is_vendor($acc->ID)){
                                                            $author = marketking()->get_store_name_display($acc->ID);
                                                        }
                                                    }
                                                    echo esc_html($author);

                                                    ?></div>
                                                </div>
                                                <div class="date-time"><?php echo esc_html($timestring);?></div>
                                            </div>
                                            <div class="nk-reply-body">
                                                <div class="nk-reply-entry entry">
                                                    <?php echo nl2br($messagecontent); ?>
                                                </div>
                                            </div>
                                        </div><!-- .nk-reply-item -->
                                    <?php
                                    }

                                    if($guest_message === 'no'){
                                        ?>
                                        <div class="nk-reply-form">
                                            <div class="nk-reply-form-header">
                                                <ul class="nav nav-tabs-s2 nav-tabs nav-tabs-sm">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#reply-form"><?php esc_html_e('Reply','marketking');?></a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="reply-form">
                                                    <div class="nk-reply-form-editor">
                                                        <div class="nk-reply-form-field">
                                                            <textarea id="marketking_dashboard_reply_message_content" class="form-control form-control-simple no-resize" placeholder="<?php esc_attr_e('Enter your message here...','marketking');?>"></textarea>
                                                        </div>
                                                        <div class="nk-reply-form-tools">
                                                            <ul class="nk-reply-form-actions g-1">
                                                                <li class="mr-2"><button class="btn btn-primary" type="submit" id="marketking_dashboard_reply_message" value="<?php echo esc_attr($id);?>"><?php esc_html_e('Send','marketking');?></button></li>
                                                                <?php
                                                                    if (defined('B2BKING_DIR') && defined('MARKETKINGPRO_DIR') && intval(get_option('marketking_enable_b2bkingintegration_setting', 1)) === 1){

                                                                        if (intval(get_option('b2bking_enable_offers_setting', 1)) === 1){
                                                                            if(marketking()->vendor_has_panel('b2bkingoffers')){
                                                                                ?>
                                                                                <a href="<?php 
                                                                                $offers_link = esc_attr(trailingslashit(get_page_link(apply_filters( 'wpml_object_id', get_option( 'marketking_vendordash_page_setting', 'disabled' ), 'post' , true)))).'offers';
                                                                                $otheruser = new WP_User($otherparty);
                                                                                $link = $offers_link.'?quote='.$otheruser->user_login;
                                                                                echo $link;
                                                                                ?>"><button class="btn btn-secondary" id="b2bking_conversation_make_offer_vendor" type="button" class="b2bking_myaccount_conversation_endpoint_button"><?php esc_html_e('Make Offer','b2bking'); ?></button></a>
                                                                                <?php
                                                                            }
                                                                        }
                                                                    }
                                                                ?>
                                                            </ul>


                                                        </div><!-- .nk-reply-form-tools -->
                                                    </div><!-- .nk-reply-form-editor -->
                                                </div>
                                                
                                            </div>
                                        </div><!-- .nk-reply-form -->
                                        <?php
                                    }
                                    ?>
                                    
                                </div><!-- .nk-reply -->
                            </div><!-- .nk-msg-body -->
                            <?php
                            }
                        }
                        ?>
                    </div><!-- .nk-msg -->
                </div>
            </div>
        </div>
        <div class="modal fade" tabindex="-1" role="dialog" id="compose-mail">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title"><?php esc_html_e('Compose Message','marketking');?></h6>
                        <a href="#" class="close" data-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                    </div>
                    <div class="modal-body p-0">
                        <div class="nk-reply-form-header">
                            <div class="nk-reply-form-group">
                                <div class="nk-reply-form-input-group">
                                    <div class="nk-reply-form-input nk-reply-form-input-to">
                                        <label class="label">To</label>
                                        <select name="marketking_dashboard_recipient" id="marketking_dashboard_recipient">
                                            <optgroup label="<?php esc_html_e('Shop', 'marketking'); ?>">
                                                <option value="shop"><?php echo apply_filters('marketking_recipient_shop',esc_html__('Shop','marketking')); ?></option>
                                            </optgroup>
                                           
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="nk-reply-form-editor">
                            <div class="nk-reply-form-field">
                                <input type="text" class="form-control form-control-simple" id="marketking_compose_send_message_title" placeholder="<?php esc_attr_e('Subject','marketking');?>">
                            </div>
                            <div class="nk-reply-form-field">
                                <textarea class="form-control form-control-simple no-resize ex-large" id="marketking_compose_send_message_content" placeholder="<?php esc_attr_e('Enter your message here...','marketking');?>"></textarea>
                            </div>
                        </div><!-- .nk-reply-form-editor -->
                        <div class="nk-reply-form-tools">
                            <ul class="nk-reply-form-actions g-1">
                                <li class="mr-2"><button class="btn btn-primary" id="marketking_compose_send_message" type="submit"><?php esc_html_e('Send','marketking');?></button></li>
                            </ul>
                           
                        </div><!-- .nk-reply-form-tools -->
                    </div><!-- .modal-body -->
                </div><!-- .modal-content -->
            </div><!-- .modla-dialog -->
        </div><!-- .modal -->

        <div class="nk-footer">
            <div class="container-fluid">
                <div class="nk-footer-wrap">
                    <div class="nk-footer-copyright"><?php esc_html_e('Messages & Inbox','marketking'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>
